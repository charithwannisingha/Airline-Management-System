<?php
// ============================================================
//  AMS — PDF E-Ticket Generator (Full Version)
// ============================================================
require_once 'includes/auth.php';
requireLogin();

// Composer හරහා install කළ Dompdf ලෝඩ් කිරීම
require_once 'vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$db = getDB();
$user = currentUser();

$booking_id = (int)($_GET['booking_id'] ?? 0);

if (!$booking_id) {
    die("Invalid Booking ID.");
}

// Booking සහ Flight විස්තර Database එකෙන් ලබා ගැනීම
$stmt = $db->prepare("
    SELECT b.*, f.flight_number, f.origin, f.destination, f.departure_time, f.arrival_time, 
           a.model as aircraft_model
    FROM bookings b
    JOIN flights f ON b.flight_id = f.id
    LEFT JOIN aircraft a ON f.aircraft_id = a.id
    WHERE b.id = ? AND b.user_id = ?
");
$stmt->execute([$booking_id, $user['id']]);
$ticket = $stmt->fetch();

if (!$ticket) {
    die("Ticket not found or access denied.");
}

// ගුවන් ගමන් කාලය (Flight Duration) ගණනය කිරීම
$dep = new DateTime($ticket['departure_time']);
$arr = new DateTime($ticket['arrival_time']);
$duration = $dep->diff($arr)->format('%hH %IM');

// 1. PDF එකට අවශ්‍ය සම්පූර්ණ HTML සහ CSS කේතය
$html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>E-Ticket - ' . htmlspecialchars($ticket['booking_ref']) . '</title>
    <style>
        body { font-family: "Helvetica Neue", Helvetica, Arial, sans-serif; color: #334155; margin: 0; padding: 20px; }
        .ticket-box { border: 2px dashed #0ea5e9; padding: 30px; border-radius: 12px; background: #ffffff; }
        .header { text-align: center; border-bottom: 2px solid #e2e8f0; padding-bottom: 15px; margin-bottom: 25px; }
        .airline-name { color: #0f172a; font-size: 28px; font-weight: bold; margin: 0; letter-spacing: 1px; }
        .title { color: #0ea5e9; font-size: 14px; letter-spacing: 3px; text-transform: uppercase; margin-top: 5px; font-weight: bold; }
        
        .route { text-align: center; margin-bottom: 30px; background: #f8fafc; padding: 15px; border-radius: 8px; }
        .route h1 { font-size: 38px; color: #0f172a; margin: 0; letter-spacing: 2px; }
        .route p { color: #64748b; margin: 5px 0 0 0; font-size: 14px; }
        
        .details-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .details-table td { padding: 12px 10px; border-bottom: 1px solid #f1f5f9; width: 50%; }
        
        .label { font-size: 11px; color: #64748b; text-transform: uppercase; font-weight: bold; letter-spacing: 1px; margin-bottom: 4px; }
        .value { font-size: 18px; color: #0f172a; font-weight: bold; }
        .seat { font-size: 28px; color: #0ea5e9; font-weight: bold; }
        
        .footer { font-size: 12px; color: #64748b; text-align: center; margin-top: 30px; padding-top: 15px; border-top: 1px solid #e2e8f0; line-height: 1.5; }
        .barcode-area { text-align: center; margin-top: 20px; }
        .barcode-text { font-family: monospace; font-size: 16px; letter-spacing: 4px; color: #0f172a; margin-top: 5px;}
    </style>
</head>
<body>
    <div class="ticket-box">
        <div class="header">
            <h1 class="airline-name">✈ Nexus Airlines</h1>
            <div class="title">Official Electronic Ticket</div>
        </div>
        
        <div class="route">
            <h1>' . htmlspecialchars($ticket['origin']) . ' ✈ ' . htmlspecialchars($ticket['destination']) . '</h1>
            <p>Flight Duration: <strong>' . $duration . '</strong></p>
        </div>

        <table class="details-table">
            <tr>
                <td>
                    <div class="label">Passenger Name</div>
                    <div class="value">' . htmlspecialchars(strtoupper($ticket['passenger_first'] . ' ' . $ticket['passenger_last'])) . '</div>
                </td>
                <td>
                    <div class="label">Booking Reference (PNR)</div>
                    <div class="value" style="color: #0ea5e9;">' . htmlspecialchars($ticket['booking_ref']) . '</div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="label">Flight Number</div>
                    <div class="value">' . htmlspecialchars($ticket['flight_number']) . '</div>
                </td>
                <td>
                    <div class="label">Date & Departure Time</div>
                    <div class="value">' . date('d M Y, H:i', strtotime($ticket['departure_time'])) . '</div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="label">Class</div>
                    <div class="value" style="text-transform: capitalize;">' . htmlspecialchars($ticket['class']) . '</div>
                </td>
                <td>
                    <div class="label">Seat Number</div>
                    <div class="seat">' . htmlspecialchars($ticket['seat_number']) . '</div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="label">Document (' . strtoupper($ticket['doc_type']) . ')</div>
                    <div class="value">' . htmlspecialchars($ticket['doc_number']) . '</div>
                </td>
                <td>
                    <div class="label">Aircraft</div>
                    <div class="value">' . htmlspecialchars($ticket['aircraft_model']) . '</div>
                </td>
            </tr>
        </table>

        <div class="barcode-area">
            <div class="barcode-text">||| | || ||| || ||| || |||</div>
            <div class="barcode-text">' . htmlspecialchars($ticket['booking_ref']) . '</div>
        </div>

        <div class="footer">
            <strong>IMPORTANT NOTICE:</strong><br> 
            Check-in counters close 45 minutes before departure. Gates close 30 minutes before departure. <br>
            Please bring a valid ' . strtoupper($ticket['doc_type']) . ' for security verification.<br><br>
            <em>Ticket generated by AMS System on ' . date('Y-m-d H:i:s') . '</em>
        </div>
    </div>
</body>
</html>
';

// 2. Dompdf සකස් කිරීම
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true); 
$options->set('defaultFont', 'Helvetica'); // අකුරු මෝස්තරය (Font)

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);

// 3. කඩදාසි ප්‍රමාණය සහ දිශාව (A4 / Portrait)
$dompdf->setPaper('A4', 'portrait');

// 4. HTML එක PDF එකක් බවට පත් කිරීම (Render)
$dompdf->render();

// 5. PDF එක Browser එක හරහා ස්වයංක්‍රීයව Download වෙන්න ලබා දීම
$dompdf->stream('Hardy_ATI_Ticket_' . $ticket['booking_ref'] . '.pdf', array("Attachment" => true));
exit();
?>
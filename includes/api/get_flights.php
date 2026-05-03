<?php
// ============================================================
//  AMS — API Endpoint: Get Flights
//  Mobile App එකකට හෝ වෙනත් පද්ධතියකට දත්ත ලබා දීමට
// ============================================================

// පිටතින් එන ඕනෑම පද්ධතියකට (Cross-Origin) දත්ත ලබා ගැනීමට අවසර දීම
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");

// Database එකට සම්බන්ධ වීම
require_once '../includes/db.php';

try {
    $db = getDB();

    // සෙවුම් පරාමිතීන් (Search parameters) ලබා ගැනීම - උදා: ?origin=CMB&destination=DXB
    $origin = isset($_GET['origin']) ? strtoupper(trim($_GET['origin'])) : '';
    $destination = isset($_GET['destination']) ? strtoupper(trim($_GET['destination'])) : '';

    // මූලික SQL කේතය
    $sql = "
        SELECT f.id, f.flight_number, f.origin, f.destination, f.departure_time, 
               f.arrival_time, f.price_economy, f.status, a.model as aircraft_model
        FROM flights f
        LEFT JOIN aircraft a ON f.aircraft_id = a.id
        WHERE f.status != 'cancelled'
    ";

    $params = [];

    // පරිශීලකයා Origin හෝ Destination දීලා තියෙනවා නම් ඒක SQL එකට එකතු කිරීම
    if ($origin !== '') {
        $sql .= " AND f.origin = ?";
        $params[] = $origin;
    }
    if ($destination !== '') {
        $sql .= " AND f.destination = ?";
        $params[] = $destination;
    }

    $sql .= " ORDER BY f.departure_time ASC";

    // Query එක Run කිරීම
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $flights = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ගුවන් ගමන් තියේ නම් ඒවා JSON විදිහට පෙන්වීම
    if (count($flights) > 0) {
        http_response_code(200); // 200 OK
        echo json_encode([
            "success" => true,
            "count" => count($flights),
            "data" => $flights
        ]);
    } else {
        // ගුවන් ගමන් නැති නම්
        http_response_code(404); // 404 Not Found
        echo json_encode([
            "success" => false,
            "message" => "No flights found for the given route."
        ]);
    }

} catch (PDOException $e) {
    // දෝෂයක් වුවහොත්
    http_response_code(500); // 500 Internal Server Error
    echo json_encode([
        "success" => false,
        "message" => "Database connection error."
    ]);
}
?>
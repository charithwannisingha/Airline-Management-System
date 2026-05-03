/* ============================================================
   AMS — Main JavaScript
   Author: K.G.S.H. Madumali (AMP/IT/2324/F/104)
   Hardy ATI Ampara · 2023/2024
   ============================================================ */

'use strict';

/* ─── Password Toggle ─── */
function togglePass() {
  const inp = document.getElementById('password');
  if (!inp) return;
  inp.type = inp.type === 'password' ? 'text' : 'password';
}

/* ─── Login form loading state ─── */
(function () {
  const form = document.getElementById('loginForm');
  if (!form) return;
  form.addEventListener('submit', function () {
    const btn = document.getElementById('loginBtn');
    if (btn) { btn.disabled = true; btn.innerHTML = '<span>Signing in…</span>'; }
  });
})();

/* ─── Modal helpers ─── */
function openModal(id) {
  const el = document.getElementById(id);
  if (el) el.classList.add('open');
}
function closeModal(id) {
  const el = document.getElementById(id);
  if (el) el.classList.remove('open');
}
// Close on overlay click
document.addEventListener('click', function (e) {
  if (e.target.classList.contains('modal-overlay')) {
    e.target.classList.remove('open');
  }
});
// Close on ESC
document.addEventListener('keydown', function (e) {
  if (e.key === 'Escape') {
    document.querySelectorAll('.modal-overlay.open').forEach(m => m.classList.remove('open'));
  }
});

/* ─── Auto-dismiss alerts ─── */
setTimeout(function () {
  document.querySelectorAll('.alert').forEach(a => {
    a.style.transition = 'opacity .5s';
    a.style.opacity = '0';
    setTimeout(() => a.remove(), 500);
  });
}, 4000);

/* ─── Mobile sidebar toggle ─── */
function toggleSidebar() {
  const sb = document.getElementById('sidebar');
  if (sb) sb.classList.toggle('open');
}

/* ─── Active nav highlight (fallback) ─── */
(function () {
  const path = window.location.pathname.split('/').pop();
  document.querySelectorAll('.nav-item').forEach(item => {
    const href = item.getAttribute('href') || '';
    if (href.includes(path) && path !== '') {
      item.classList.add('active');
    }
  });
})();

/* ─── Table search filter ─── */
function tableFilter(inputId, tableId) {
  const input = document.getElementById(inputId);
  const table = document.getElementById(tableId);
  if (!input || !table) return;
  input.addEventListener('input', function () {
    const q = this.value.toLowerCase();
    table.querySelectorAll('tbody tr').forEach(row => {
      row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
  });
}

/* ─── Confirm delete ─── */
function confirmDelete(msg) {
  return confirm(msg || 'Are you sure you want to delete this record?');
}

/* ─── Card number formatting ─── */
document.addEventListener('input', function (e) {
  if (e.target && e.target.id === 'cardNum') {
    let v = e.target.value.replace(/\D/g, '').substring(0, 16);
    e.target.value = v.replace(/(.{4})/g, '$1 ').trim();
  }
});

/* ─── Print helper ─── */
function printPage() { window.print(); }

/* ─── Staggered card animations ─── */
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.kpi-card, .card, .flight-result').forEach((el, i) => {
    if (!el.style.animationDelay) {
      el.style.animationDelay = (i * 0.07) + 's';
    }
  });
});
// ============================================================
// NEXUS AIRLINES - GLOBAL PREMIUM FAVICON (Apply to all pages)
// ============================================================
(function() {
    var link = document.querySelector("link[rel~='icon']");
    if (!link) {
        link = document.createElement('link');
        link.rel = 'icon';
        link.type = 'image/svg+xml';
        document.head.appendChild(link);
    }
    // ලස්සන ලා නිල් පාට ගුවන් යානයක (Premium Airplane) SVG අයිකනයක්
    link.href = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%2338bdf8'%3E%3Cpath d='M21 16v-2l-8-5V3.5c0-.83-.67-1.5-1.5-1.5S10 2.67 10 3.5V9l-8 5v2l8-2.5V19l-2 1.5V22l3.5-1 3.5 1v-1.5L13 19v-5.5l8 2.5z'/%3E%3C/svg%3E";
})();

// ඔයාගේ app.js එකේ කලින් තිබුණු වෙනත් කේත (Animations/Spinners) තියෙනවා නම්, ඒවා මේකට යටින් ඒ විදිහටම තියෙන්න හරින්න.

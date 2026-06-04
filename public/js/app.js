// ============================================
// SISTEM INFORMASI APOTEK - MAIN JS
// ============================================

document.addEventListener('DOMContentLoaded', function () {

    // ---- SIDEBAR TOGGLE ----
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');
    const mainWrapper = document.getElementById('mainWrapper');
    const body = document.body;

    // Desktop: collapse
    if (window.innerWidth > 992) {
        sidebarToggle?.addEventListener('click', function () {
            body.classList.toggle('sidebar-collapsed');
            localStorage.setItem('sidebarCollapsed', body.classList.contains('sidebar-collapsed'));
        });

        // Restore state
        if (localStorage.getItem('sidebarCollapsed') === 'true') {
            body.classList.add('sidebar-collapsed');
        }
    } else {
        // Mobile: slide in/out
        let overlay = document.createElement('div');
        overlay.classList.add('sidebar-overlay');
        document.body.appendChild(overlay);

        sidebarToggle?.addEventListener('click', function () {
            sidebar?.classList.toggle('mobile-open');
            overlay.classList.toggle('active');
        });

        overlay.addEventListener('click', function () {
            sidebar?.classList.remove('mobile-open');
            overlay.classList.remove('active');
        });
    }

    // ---- SUBMENU TOGGLE ----
    const menuToggles = document.querySelectorAll('.menu-toggle');
    menuToggles.forEach(function (toggle) {
        toggle.addEventListener('click', function (e) {
            e.preventDefault();
            const parent = this.closest('.menu-item');
            const isOpen = parent.classList.contains('open');

            // Close all other open submenus
            document.querySelectorAll('.menu-item.open').forEach(function (item) {
                if (item !== parent) {
                    item.classList.remove('open');
                }
            });

            parent.classList.toggle('open', !isOpen);
        });
    });

    // Open active submenu on load
    const activeSubmenu = document.querySelector('.menu-item.active.has-submenu');
    if (activeSubmenu) {
        activeSubmenu.classList.add('open');
    }

    // ---- CURRENT DATE ----
    const dateEl = document.getElementById('currentDate');
    if (dateEl) {
        const now = new Date();
        const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        dateEl.textContent = now.toLocaleDateString('id-ID', options);
    }

    // ---- DATATABLE INIT ----
    const dataTables = document.querySelectorAll('.data-table');
    dataTables.forEach(function (table) {
        if ($.fn.DataTable && !$.fn.DataTable.isDataTable(table)) {
            $(table).DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json'
                },
                pageLength: 10,
                dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                     "<'row'<'col-sm-12'tr>>" +
                     "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                responsive: true
            });
        }
    });

    // ---- AUTO DISMISS ALERTS ----
    const alerts = document.querySelectorAll('.custom-alert');
    alerts.forEach(function (alert) {
        setTimeout(function () {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            bsAlert?.close();
        }, 5000);
    });

    // ---- CONFIRM DELETE ----
    document.querySelectorAll('.btn-delete-confirm').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            if (!confirm('Apakah Anda yakin ingin menghapus data ini?')) {
                e.preventDefault();
            }
        });
    });

    // ---- NUMBER FORMAT ----
    window.formatNumber = function (num) {
        return new Intl.NumberFormat('id-ID').format(num);
    };

    window.formatCurrency = function (num) {
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(num);
    };

    // ---- COUNTER ANIMATION ----
    function animateCounter(el) {
        const target = parseInt(el.getAttribute('data-target')) || 0;
        const duration = 1200;
        const step = Math.ceil(target / (duration / 16));
        let current = 0;
        const timer = setInterval(function () {
            current += step;
            if (current >= target) {
                current = target;
                clearInterval(timer);
            }
            el.textContent = formatNumber(current);
        }, 16);
    }

    document.querySelectorAll('.stat-value[data-target]').forEach(function (el) {
        animateCounter(el);
    });

});

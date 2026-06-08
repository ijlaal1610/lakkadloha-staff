/* ============================================================
   LAKKAD LOHA — app.js
   Theme toggle · Sidebar · PWA install · Chart defaults · Utils
   ============================================================ */

/* ---------- Theme ---------- */
(function () {
    const saved = localStorage.getItem('ll_theme') || 'light';
    document.documentElement.setAttribute('data-theme', saved);
    updateThemeIcon(saved);
})();

function updateThemeIcon(theme) {
    const icon = document.getElementById('themeIcon');
    if (!icon) return;
    icon.className = theme === 'dark' ? 'bi bi-moon-fill' : 'bi bi-sun-fill';
}

document.addEventListener('DOMContentLoaded', function () {

    /* --- Theme toggle --- */
    const themeToggle = document.getElementById('themeToggle');
    if (themeToggle) {
        themeToggle.addEventListener('click', function () {
            const current = document.documentElement.getAttribute('data-theme');
            const next = current === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', next);
            localStorage.setItem('ll_theme', next);
            updateThemeIcon(next);
        });
    }

    /* --- Sidebar toggle (mobile) --- */
    const sidebar        = document.getElementById('sidebar');
    const overlay        = document.getElementById('sidebarOverlay');
    const sidebarToggle  = document.getElementById('sidebarToggle');
    const sidebarClose   = document.getElementById('sidebarClose');

    function openSidebar() {
        sidebar.classList.add('open');
        overlay.classList.add('open');
        document.body.style.overflow = 'hidden';
    }

    function closeSidebar() {
        sidebar.classList.remove('open');
        overlay.classList.remove('open');
        document.body.style.overflow = '';
    }

    if (sidebarToggle) sidebarToggle.addEventListener('click', openSidebar);
    if (sidebarClose)  sidebarClose.addEventListener('click', closeSidebar);
    if (overlay)       overlay.addEventListener('click', closeSidebar);

    /* --- Chart.js global defaults --- */
    if (typeof Chart !== 'undefined') {
        const isDark = () => document.documentElement.getAttribute('data-theme') === 'dark';
        const getTextColor  = () => isDark() ? '#a0a6c0' : '#8b93ad';
        const getGridColor  = () => isDark() ? '#2a2d45' : '#f0f1f8';
        const getBorderColor = () => isDark() ? '#2a2d45' : '#e5e7f0';

        Chart.defaults.font.family = "'Inter', -apple-system, sans-serif";
        Chart.defaults.font.size   = 12;
        Chart.defaults.color       = getTextColor();

        Chart.defaults.plugins.legend.labels.usePointStyle = true;
        Chart.defaults.plugins.legend.labels.padding       = 20;
        Chart.defaults.plugins.tooltip.backgroundColor     = isDark() ? '#1e2030' : '#ffffff';
        Chart.defaults.plugins.tooltip.titleColor          = isDark() ? '#f1f2f8' : '#0f1117';
        Chart.defaults.plugins.tooltip.bodyColor           = isDark() ? '#a0a6c0' : '#4b5169';
        Chart.defaults.plugins.tooltip.borderColor         = getBorderColor();
        Chart.defaults.plugins.tooltip.borderWidth         = 1;
        Chart.defaults.plugins.tooltip.padding             = 10;
        Chart.defaults.plugins.tooltip.cornerRadius        = 8;

        Chart.defaults.scale.grid.color      = getGridColor();
        Chart.defaults.scale.ticks.color     = getTextColor();
        Chart.defaults.scale.border.color    = getBorderColor();
    }

    /* --- Auto-dismiss flash toasts (handled inline) --- */

    /* --- Confirm dialogs for destructive actions --- */
    document.querySelectorAll('[data-confirm]').forEach(function (el) {
        el.addEventListener('click', function (e) {
            const msg = el.getAttribute('data-confirm') || 'Are you sure?';
            if (!confirm(msg)) {
                e.preventDefault();
                e.stopPropagation();
            }
        });
    });

    /* --- Form submit with loading state --- */
    document.querySelectorAll('form[data-loading]').forEach(function (form) {
        form.addEventListener('submit', function () {
            const btn = form.querySelector('[type="submit"]');
            if (btn) {
                btn.disabled = true;
                const origText = btn.innerHTML;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing…';
                // restore if stays on page
                setTimeout(() => { btn.disabled = false; btn.innerHTML = origText; }, 8000);
            }
        });
    });

    /* --- Number formatting --- */
    document.querySelectorAll('[data-rupee]').forEach(function (el) {
        const val = parseFloat(el.textContent.replace(/[^\d.]/g, ''));
        if (!isNaN(val)) el.textContent = formatRupee(val);
    });

    /* --- Responsive table — add data-label from <th> --- */
    document.querySelectorAll('.table-responsive table').forEach(function (table) {
        const headers = Array.from(table.querySelectorAll('thead th')).map(th => th.textContent.trim());
        table.querySelectorAll('tbody tr').forEach(function (row) {
            Array.from(row.cells).forEach(function (cell, i) {
                if (headers[i]) cell.setAttribute('data-label', headers[i]);
            });
        });
    });

    /* --- PWA install prompt --- */
    let deferredPrompt = null;
    window.addEventListener('beforeinstallprompt', function (e) {
        e.preventDefault();
        deferredPrompt = e;

        // Show install banner if not already installed
        if (!localStorage.getItem('pwa_dismissed')) {
            showPWABanner();
        }
    });

    function showPWABanner() {
        const banner = document.createElement('div');
        banner.className = 'pwa-banner';
        banner.id = 'pwaBanner';
        banner.innerHTML = `
            <div class="brand-icon" style="width:42px;height:42px;border-radius:10px;background:var(--primary);display:flex;align-items:center;justify-content:center;color:white;font-size:18px;flex-shrink:0;">
                <i class="bi bi-building"></i>
            </div>
            <div class="pwa-banner-text">
                <strong>Install Lakkad Loha</strong>
                <span>Add to home screen for quick access</span>
            </div>
            <button class="btn btn-primary btn-sm" id="pwaInstallBtn">Install</button>
            <button class="btn-icon" id="pwaDismissBtn" style="border:none;">
                <i class="bi bi-x-lg" style="font-size:14px;"></i>
            </button>
        `;
        document.body.appendChild(banner);

        document.getElementById('pwaInstallBtn').addEventListener('click', async function () {
            if (!deferredPrompt) return;
            deferredPrompt.prompt();
            const { outcome } = await deferredPrompt.userChoice;
            deferredPrompt = null;
            banner.remove();
            if (outcome === 'accepted') localStorage.setItem('pwa_installed', '1');
        });

        document.getElementById('pwaDismissBtn').addEventListener('click', function () {
            banner.remove();
            localStorage.setItem('pwa_dismissed', '1');
        });
    }
});

/* ---------- Global Helpers ---------- */

function formatRupee(amount) {
    return '₹' + new Intl.NumberFormat('en-IN', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(amount);
}

function formatNumber(n) {
    return new Intl.NumberFormat('en-IN').format(n);
}

function showToast(message, type = 'success') {
    const icons = { success: 'bi-check-circle-fill', danger: 'bi-exclamation-circle-fill', warning: 'bi-exclamation-triangle-fill', info: 'bi-info-circle-fill' };
    const toast = document.createElement('div');
    toast.className = `alert-toast alert-toast-${type}`;
    toast.innerHTML = `
        <i class="bi ${icons[type] || icons.info}"></i>
        <span>${message}</span>
        <button onclick="this.parentElement.remove()"><i class="bi bi-x"></i></button>
    `;
    document.body.appendChild(toast);
    setTimeout(() => {
        toast.style.animation = 'slideOutRight 0.3s ease forwards';
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}

function csrfPost(url, data = {}) {
    return fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
        body: JSON.stringify(data),
    }).then(r => r.json());
}

/* ---------- Sale form — live total calculation ---------- */
function initSaleForm() {
    const qtyInput   = document.getElementById('quantity');
    const priceInput = document.getElementById('selling_price');
    const totalEl    = document.getElementById('total_preview');
    const stockEl    = document.getElementById('stock_info');
    const productSel = document.getElementById('product_id');

    function updateTotal() {
        const qty   = parseFloat(qtyInput?.value || 0);
        const price = parseFloat(priceInput?.value || 0);
        if (totalEl) {
            const total = qty * price;
            totalEl.textContent = isNaN(total) ? '₹0.00' : formatRupee(total);
        }
    }

    if (qtyInput)   qtyInput.addEventListener('input', updateTotal);
    if (priceInput) priceInput.addEventListener('input', updateTotal);

    if (productSel) {
        productSel.addEventListener('change', function () {
            const opt = this.options[this.selectedIndex];
            const stock = opt.getAttribute('data-stock');
            if (stockEl && stock !== null) {
                stockEl.textContent = 'Available: ' + formatNumber(parseInt(stock)) + ' units';
                stockEl.className   = parseInt(stock) <= 0 ? 'text-danger fs-12' : 'text-muted fs-12';
            }
            if (qtyInput) qtyInput.max = stock || 9999;
        });
    }
}

/* ---------- Service Worker registration ---------- */
if ('serviceWorker' in navigator) {
    window.addEventListener('load', function () {
        navigator.serviceWorker.register('/sw.js')
            .catch(err => console.log('SW registration failed:', err));
    });
}

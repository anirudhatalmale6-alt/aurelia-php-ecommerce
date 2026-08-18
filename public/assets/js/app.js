/* -------------------------------------------------------------------------
   Storefront interactions. Vanilla JS, no dependencies, ~2 KB.
   ------------------------------------------------------------------------- */
(function () {
    'use strict';

    /* --- Flash messages fade out ------------------------------------------ */
    document.querySelectorAll('.flash').forEach(function (el) {
        setTimeout(function () {
            el.style.transition = 'opacity .4s ease, transform .4s ease';
            el.style.opacity = '0';
            el.style.transform = 'translateX(12px)';
            setTimeout(function () { el.remove(); }, 400);
        }, 4200);
    });

    /* --- Quantity stepper on the product page ----------------------------- */
    var qtyInput = document.getElementById('qty-input');
    document.querySelectorAll('[data-qty]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            if (!qtyInput) return;
            var step = parseInt(btn.getAttribute('data-qty'), 10);
            var max  = parseInt(qtyInput.max || '99', 10);
            var next = Math.min(max, Math.max(1, (parseInt(qtyInput.value, 10) || 1) + step));
            qtyInput.value = next;
        });
    });

    /* --- Search typeahead -------------------------------------------------- */
    var search = document.getElementById('site-search');
    var panel  = document.getElementById('suggest-panel');

    if (search && panel) {
        var timer = null;

        var close = function () { panel.innerHTML = ''; };

        var render = function (results) {
            if (!results.length) { close(); return; }
            var html = '<div class="suggestions">';
            results.forEach(function (r) {
                html += '<a href="/product/' + encodeURIComponent(r.slug) + '">'
                     +  '<img src="' + r.image + '" alt="">'
                     +  '<span style="flex:1">' + escapeHtml(r.name) + '</span>'
                     +  '<span class="faint">' + escapeHtml(r.price) + '</span></a>';
            });
            panel.innerHTML = html + '</div>';
        };

        search.addEventListener('input', function () {
            var q = search.value.trim();
            clearTimeout(timer);
            if (q.length < 2) { close(); return; }

            timer = setTimeout(function () {
                fetch('/api/suggest?q=' + encodeURIComponent(q))
                    .then(function (r) { return r.json(); })
                    .then(function (data) { render(data.results || []); })
                    .catch(close);
            }, 180);
        });

        document.addEventListener('click', function (event) {
            if (!panel.contains(event.target) && event.target !== search) close();
        });
        search.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') close();
        });
    }

    /* --- Escape untrusted text before injecting it ------------------------- */
    function escapeHtml(value) {
        return String(value).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }
})();

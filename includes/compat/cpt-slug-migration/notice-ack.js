(function () {
    'use strict';

    document.addEventListener('click', function (event) {
        var btn = event.target.closest ? event.target.closest('.ppcart-cpt-migration-later') : null;
        if (!btn) {
            return;
        }

        event.preventDefault();

        var cfg = window.ppcartcompCptMigrationNotice || {};
        var body = new URLSearchParams();
        body.set('action', cfg.action || 'ppcart_cpt_slug_migration_ack');
        body.set('nonce', btn.getAttribute('data-nonce') || cfg.nonce || '');

        fetch(cfg.ajaxUrl || '/wp-admin/admin-ajax.php', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
            body: body.toString()
        }).then(function () {
            var notice = btn.closest('.ppcart-cpt-migration-notice');
            if (notice) {
                notice.style.display = 'none';
            }
        });
    });
})();

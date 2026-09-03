(function ($) {
    'use strict';

    var cfg = window.ppcartCptSlugMigration || {};
    var runLock = false;

    function post(action, data) {
        data = data || {};
        data.action = action;
        data.nonce = cfg.nonce;
        return $.post(cfg.ajaxUrl, data);
    }

    function setStatus($el, text) {
        if ($el && $el.length) {
            $el.text(text);
        }
    }

    $(document).on('click', '.ppcart-cpt-migration-later', function (e) {
        e.preventDefault();
        post(cfg.actions.ack).done(function () {
            $('.ppcart-cpt-migration-notice').slideUp();
        });
    });

    $(document).on('click', '.ppcart-cpt-migration-stop', function (e) {
        e.preventDefault();
        post(cfg.actions.stop);
    });

    $(document).on('click', '.ppcart-cpt-migration-delete-backup', function (e) {
        e.preventDefault();
        if (!window.confirm('Delete the reverse-map backup? Restore will no longer be possible from Cart.')) {
            return;
        }

        var $btn = $(this);
        var $status = $('.ppcart-cpt-migration-status[data-step="backup"]');
        $btn.prop('disabled', true);
        post(cfg.actions.delete).done(function (res) {
            if (!res || !res.ok) {
                setStatus($status, cfg.i18n.error);
                $btn.prop('disabled', false);
                return;
            }
            setStatus($status, cfg.i18n.deleted);
            window.location.reload();
        }).fail(function () {
            setStatus($status, cfg.i18n.error);
            $btn.prop('disabled', false);
        });
    });

    $(document).on('click', '.ppcart-cpt-migration-restore', function (e) {
        e.preventDefault();
        var step = $(this).data('step');
        if (!window.confirm('Restore only undoes completed chunks. Continue?')) {
            return;
        }
        post(cfg.actions.restore, { step: step }).done(function () {
            window.location.reload();
        });
    });

    $(document).on('click', '.ppcart-cpt-migration-run', function (e) {
        e.preventDefault();
        if (runLock) {
            return;
        }

        var $btn = $(this);
        var step = parseInt($btn.data('step'), 10) || 1;
        var $status = $('.ppcart-cpt-migration-status[data-step="' + step + '"]');
        runLock = true;
        $btn.prop('disabled', true);

        function unlock() {
            runLock = false;
            $btn.prop('disabled', false);
        }

        function tick() {
            setStatus($status, cfg.i18n.running);
            post(cfg.actions.run, { step: step }).done(function (res) {
                if (!res || !res.ok) {
                    setStatus($status, cfg.i18n.error);
                    unlock();
                    return;
                }
                if (res.paused) {
                    setStatus($status, cfg.i18n.paused);
                    unlock();
                    return;
                }
                if (res.done) {
                    setStatus($status, cfg.i18n.done);
                    window.location.reload();
                    return;
                }
                if (1 === step) {
                    tick();
                    return;
                }
                unlock();
            }).fail(function () {
                setStatus($status, cfg.i18n.error);
                unlock();
            });
        }

        tick();
    });
})(jQuery);

(function ($) {
    'use strict';

    var cfg = window.ppcartShortcodeTagMigration || {};
    var runLock = false;

    function post(action, data) {
        data = data || {};
        data.action = action;
        data.nonce = cfg.nonce;
        return $.post(cfg.ajaxUrl, data);
    }

    function setStatus(text) {
        $('.ppcart-shortcode-tag-migration-status').text(text);
    }

    $(document).on('click', '.ppcart-shortcode-tag-migration-stop', function (e) {
        e.preventDefault();
        post(cfg.actions.stop);
    });

    $(document).on('click', '.ppcart-shortcode-tag-migration-delete-backup', function (e) {
        e.preventDefault();
        if (!window.confirm('Delete the reverse-map backup? Restore will no longer be possible from Cart.')) {
            return;
        }

        var $btn = $(this);
        $btn.prop('disabled', true);
        post(cfg.actions.delete).done(function (res) {
            if (!res || !res.ok) {
                setStatus(cfg.i18n.error);
                $btn.prop('disabled', false);
                return;
            }
            setStatus(cfg.i18n.deleted);
            window.location.reload();
        }).fail(function () {
            setStatus(cfg.i18n.error);
            $btn.prop('disabled', false);
        });
    });

    $(document).on('click', '.ppcart-shortcode-tag-migration-restore', function (e) {
        e.preventDefault();
        if (!window.confirm('Restore only undoes completed chunks. Continue?')) {
            return;
        }
        post(cfg.actions.restore).done(function () {
            window.location.reload();
        });
    });

    $(document).on('click', '.ppcart-shortcode-tag-migration-run', function (e) {
        e.preventDefault();
        if (runLock) {
            return;
        }

        var $btn = $(this);
        runLock = true;
        $btn.prop('disabled', true);

        function unlock() {
            runLock = false;
            $btn.prop('disabled', false);
        }

        function tick() {
            setStatus(cfg.i18n.running);
            post(cfg.actions.run).done(function (res) {
                if (!res || !res.ok) {
                    setStatus(cfg.i18n.error);
                    unlock();
                    return;
                }
                if (res.paused) {
                    setStatus(cfg.i18n.paused);
                    unlock();
                    return;
                }
                if (res.done) {
                    setStatus(cfg.i18n.done);
                    window.location.reload();
                    return;
                }
                tick();
            }).fail(function () {
                setStatus(cfg.i18n.error);
                unlock();
            });
        }

        tick();
    });
}(jQuery));

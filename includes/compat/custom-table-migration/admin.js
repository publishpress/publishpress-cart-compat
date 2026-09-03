(function ($) {
    'use strict';

    var cfg = window.ppcartcompCustomTableMigration || {};

    function bindCustomTableMigration() {
        var $page = $('.ppcartcomp-studiocart-page');
        if (!$page.length) {
            return;
        }

        $page.on('click', '[data-ppcart-migrate-custom-tables]', function (event) {
            event.preventDefault();

            var $button = $(this);
            if ($button.prop('disabled')) {
                return;
            }

            var confirmed = window.confirm(
                cfg.confirm ||
                    'Rename leftover custom tables to ppcart_* names? Take a backup first and run this during a maintenance window.'
            );
            if (!confirmed) {
                return;
            }

            var $result = $page.find('[data-ppcart-migrate-custom-tables-result]');
            $button.prop('disabled', true);

            $.post(window.ajaxurl || '/wp-admin/admin-ajax.php', {
                action: 'ppcart_migrate_custom_tables',
                nonce: $button.attr('data-nonce')
            })
                .done(function (response) {
                    if (response && response.success) {
                        if ($result.length) {
                            $result.removeAttr('hidden').text(response.data.message || 'Migration complete.');
                        }
                        window.location.reload();
                        return;
                    }

                    var message =
                        response && response.data && response.data.message
                            ? response.data.message
                            : 'Custom table migration failed.';
                    if ($result.length) {
                        $result.removeAttr('hidden').text(message);
                    }
                    $button.prop('disabled', false);
                })
                .fail(function () {
                    if ($result.length) {
                        $result.removeAttr('hidden').text('Custom table migration failed.');
                    }
                    $button.prop('disabled', false);
                });
        });
    }

    $(bindCustomTableMigration);
})(jQuery);

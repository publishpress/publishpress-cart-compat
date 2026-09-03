<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Registers the custom-tables Data migration AJAX handler.
 *
 * @return void
 */
function ppcart_custom_table_migration_admin_init()
{
    add_action('wp_ajax_ppcart_migrate_custom_tables', 'ppcart_ajax_migrate_custom_tables');
}

/**
 * Sibling block on the StudioCart Data migration screen.
 *
 * @return void
 */
function ppcart_custom_table_migration_render_block()
{
    if (! current_user_can('manage_options')) {
        return;
    }

    echo '<div class="ppcart-custom-table-migration__block">';
    echo '<h3>' . esc_html__('Custom tables', 'publishpress-cart') . '</h3>';
    echo '<p class="description">' . esc_html__('Rename leftover ncs_* custom tables to ppcart_* names. This does not run on plugin upgrade.', 'publishpress-cart') . '</p>';
    echo ppcart_render_custom_table_migration_step(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Step renderer escapes its own HTML.
    echo '</div>';
}

/**
 * @return string
 */
function ppcart_render_custom_table_migration_step()
{
    $needs   = ppcart_custom_table_needs_migration();
    $pending = ppcart_custom_table_pending_families();
    $nonce   = wp_create_nonce('ppcart_migrate_custom_tables');
    $state   = ppcart_custom_table_migration_state();

    ob_start();
    ?>
    <p>
        <?php if ($needs) : ?>
            <button
                type="button"
                class="button button-secondary"
                data-ppcart-migrate-custom-tables
                data-nonce="<?php echo esc_attr($nonce); ?>"
            >
                <?php esc_html_e('Rename custom tables', 'publishpress-cart'); ?>
            </button>
        <?php else : ?>
            <button type="button" class="button button-secondary" disabled>
                <?php esc_html_e('Rename custom tables', 'publishpress-cart'); ?>
            </button>
        <?php endif; ?>
    </p>
    <?php if ($needs) : ?>
        <p class="description">
            <?php
            echo esc_html(
                sprintf(
                    /* translators: %s: comma-separated leftover table families. */
                    __('Leftover tables still in use: %s.', 'publishpress-cart'),
                    $pending ? implode(', ', $pending) : __('order item meta column', 'publishpress-cart')
                )
            );
            ?>
        </p>
        <p class="description">
            <?php esc_html_e('This runs RENAME TABLE (and ALTER for ncs_order_item_id). Rows are preserved. Pro tax code must keep using ppcart_live_table( \'tax_rate\' ).', 'publishpress-cart'); ?>
        </p>
    <?php else : ?>
        <p class="description">
            <?php esc_html_e('Custom tables already use canonical ppcart_* names.', 'publishpress-cart'); ?>
        </p>
    <?php endif; ?>
    <?php if (! empty($state['completed_at'])) : ?>
        <p class="description">
            <?php
            echo esc_html(
                sprintf(
                    /* translators: %s: formatted datetime. */
                    __('Last custom table migration: %s', 'publishpress-cart'),
                    wp_date(get_option('date_format') . ' ' . get_option('time_format'), (int) $state['completed_at'])
                )
            );
            ?>
        </p>
    <?php endif; ?>
    <div class="ppcart-data-migration__result" data-ppcart-migrate-custom-tables-result hidden></div>
    <?php

    return (string) ob_get_clean();
}

/**
 * AJAX handler for the custom table Data migration step.
 *
 * @return void
 */
function ppcart_ajax_migrate_custom_tables()
{
    check_ajax_referer('ppcart_migrate_custom_tables', 'nonce');

    if (! current_user_can('manage_options')) {
        wp_send_json_error(
            [ 'message' => __('You do not have permission to migrate custom tables.', 'publishpress-cart') ],
            403
        );
    }

    $result = ppcart_migrate_custom_tables();
    $errors = isset($result['errors']) && is_array($result['errors']) ? $result['errors'] : [];

    if ($errors) {
        wp_send_json_error(
            [
                'message' => implode(' ', array_map('strval', $errors)),
                'result'  => $result,
            ]
        );
    }

    $renamed_count = isset($result['renamed']) && is_array($result['renamed']) ? count($result['renamed']) : 0;
    $column_status = isset($result['column']['status']) ? (string) $result['column']['status'] : '';

    wp_send_json_success(
        [
            'message' => sprintf(
                /* translators: 1: renamed table count, 2: column migration status. */
                __('Custom table migration complete. Tables renamed: %1$d. Item meta column: %2$s.', 'publishpress-cart'),
                $renamed_count,
                $column_status
            ),
            'result'  => $result,
        ]
    );
}

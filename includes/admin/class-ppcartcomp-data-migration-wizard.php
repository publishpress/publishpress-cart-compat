<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * StudioCart Data migration landing + step wizard (React).
 *
 * Package AJAX stays on existing admin classes. This class owns enqueue,
 * bootstrap/status payload, URL helpers, and the status endpoint.
 */
class PPCartComp_Data_Migration_Wizard
{
    public const VIEW_QUERY    = 'ppcartcomp_migration_view';
    public const STEP_QUERY    = 'ppcartcomp_migration_step';
    public const AJAX_STATUS   = 'ppcartcomp_data_migration_status';
    public const NONCE_ACTION  = 'ppcartcomp_data_migration_wizard';
    public const SCRIPT_HANDLE = 'ppcartcomp-migration-wizard';

    /** @var bool */
    private static $initialized = false;

    /**
     * @return void
     */
    public static function init()
    {
        if (self::$initialized) {
            return;
        }
        self::$initialized = true;

        add_action('admin_enqueue_scripts', [ self::class, 'enqueue_assets' ]);
        add_action('wp_ajax_' . self::AJAX_STATUS, [ self::class, 'ajax_status' ]);
    }

    /**
     * @param string $step Wizard step slug.
     * @return string
     */
    public static function wizard_url($step = '')
    {
        $url = class_exists('PPCartComp_Studiocart_Admin', false)
            ? PPCartComp_Studiocart_Admin::screen_url()
            : admin_url('admin.php?page=ppcart-studiocart');

        $args = [ self::VIEW_QUERY => 'wizard' ];
        if (is_string($step) && '' !== $step) {
            $args[ self::STEP_QUERY ] = sanitize_key($step);
        }

        if (function_exists('add_query_arg')) {
            return add_query_arg($args, $url);
        }

        $sep = false !== strpos($url, '?') ? '&' : '?';

        return $url . $sep . http_build_query($args);
    }

    /**
     * @param string $hook_suffix Hook.
     * @return void
     */
    public static function enqueue_assets($hook_suffix)
    {
        if (! class_exists('PPCartComp_Studiocart_Admin', false)
            || ! PPCartComp_Studiocart_Admin::is_screen(is_string($hook_suffix) ? $hook_suffix : '')
        ) {
            return;
        }

        $base_file = defined('PPCARTCOMP_BASE_FILE') ? PPCARTCOMP_BASE_FILE : '';
        if ('' === $base_file) {
            return;
        }

        $build_dir  = dirname($base_file) . '/assets/admin/migration-wizard/build';
        $asset_file = $build_dir . '/index.asset.php';
        $script_rel = 'assets/admin/migration-wizard/build/index.js';
        $style_rel  = 'assets/admin/migration-wizard/build/style-index.css';
        $version    = defined('PPCARTCOMP_VERSION') ? PPCARTCOMP_VERSION : '1.0.0';
        $asset      = [
            'dependencies' => [ 'react-jsx-runtime', 'wp-element', 'wp-components', 'wp-i18n', 'wp-dom-ready' ],
            'version'      => $version,
        ];

        if (file_exists($asset_file)) {
            $loaded = require $asset_file;
            if (is_array($loaded)) {
                $asset = array_merge($asset, $loaded);
            }
        }

        wp_enqueue_style('wp-components');

        $style_path = dirname($base_file) . '/' . $style_rel;
        if (file_exists($style_path)) {
            wp_enqueue_style(
                self::SCRIPT_HANDLE,
                plugins_url($style_rel, $base_file),
                [ 'wp-components' ],
                isset($asset['version']) ? $asset['version'] : $version
            );
            wp_style_add_data(self::SCRIPT_HANDLE, 'rtl', 'replace');
        }

        if (! file_exists($build_dir . '/index.js')) {
            return;
        }

        wp_enqueue_script(
            self::SCRIPT_HANDLE,
            plugins_url($script_rel, $base_file),
            isset($asset['dependencies']) && is_array($asset['dependencies']) ? $asset['dependencies'] : [ 'react-jsx-runtime', 'wp-element', 'wp-components', 'wp-i18n', 'wp-dom-ready' ],
            isset($asset['version']) ? $asset['version'] : $version,
            true
        );
        wp_localize_script(self::SCRIPT_HANDLE, 'ppcartcompMigrationWizard', self::build_bootstrap());

        if (function_exists('wp_set_script_translations') && defined('PPCARTCOMP_BASE_DIR')) {
            wp_set_script_translations(self::SCRIPT_HANDLE, 'publishpress-cart-compat', PPCARTCOMP_BASE_DIR . 'languages');
        }
    }

    /**
     * @return void
     */
    public static function ajax_status()
    {
        if (! current_user_can('manage_options')) {
            wp_send_json([ 'ok' => false, 'error' => 'forbidden' ], 403);
        }

        $nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
        $valid = function_exists('ppcart_verify_nonce')
            ? ppcart_verify_nonce($nonce, self::NONCE_ACTION)
            : ( function_exists('wp_verify_nonce') && wp_verify_nonce($nonce, self::NONCE_ACTION) );
        if (! $valid) {
            wp_send_json([ 'ok' => false, 'error' => 'nonce' ], 403);
        }

        wp_send_json(array_merge([ 'ok' => true ], self::build_bootstrap()));
    }

    /**
     * Bootstrap and status AJAX share this shape.
     *
     * @return array<string, mixed>
     */
    public static function build_bootstrap()
    {
        $statuses  = self::status_map();
        $tables    = self::tables_payload();
        $options   = self::options_payload();
        $progress  = self::progress_from_status($statuses, $tables, $options);
        $view      = self::requested_view();
        $step      = self::requested_step($progress['firstIncomplete']);

        return [
            'ajaxUrl'      => admin_url('admin-ajax.php'),
            'nonce'        => function_exists('wp_create_nonce') ? wp_create_nonce(self::NONCE_ACTION) : '',
            'actions'      => self::action_map(),
            'completion'   => self::completion_map($statuses),
            'statuses'     => $statuses,
            'remaining'    => self::remaining_map(),
            'backupExists' => self::backup_exists_map(),
            'tables'       => $tables,
            'options'      => $options,
            'progress'     => $progress,
            'urls'         => [
                'landing' => class_exists('PPCartComp_Studiocart_Admin', false)
                    ? PPCartComp_Studiocart_Admin::screen_url()
                    : admin_url('admin.php?page=ppcart-studiocart'),
                'wizard'  => self::wizard_url($step),
            ],
            'initialView'  => $view,
            'initialStep'  => $step,
            'assetsReady'  => self::assets_ready(),
        ];
    }

    /**
     * @param array<string, string> $statuses Step status map.
     * @param array<string, mixed>  $tables   Tables payload.
     * @param array<string, mixed>  $options  Options payload.
     * @return array{complete: int, total: int, firstIncomplete: string|null}
     */
    public static function progress_from_status(array $statuses, array $tables, array $options = [])
    {
        $slugs = [ 'cpt', 'roles', 'meta', 'shortcodes' ];
        if (self::options_counts_toward_progress($options)) {
            $slugs[] = 'options';
        }
        if (self::tables_counts_toward_progress($tables)) {
            $slugs[] = 'tables';
        }

        $complete = 0;
        $first    = null;
        foreach ($slugs as $slug) {
            $mark = isset($statuses[ $slug ]) ? (string) $statuses[ $slug ] : 'idle';
            if (in_array($mark, [ 'done', 'skipped' ], true)) {
                $complete++;
            } elseif (null === $first) {
                $first = $slug;
            }
        }

        return [
            'complete'        => $complete,
            'total'           => count($slugs),
            'firstIncomplete' => $first,
        ];
    }

    /**
     * @param array<string, mixed> $tables Tables payload.
     * @return bool
     */
    public static function tables_counts_toward_progress(array $tables)
    {
        return ! empty($tables['needsMigration']) || ! empty($tables['completedAt']);
    }

    /**
     * @param array<string, mixed> $options Options payload.
     * @return bool
     */
    public static function options_counts_toward_progress(array $options)
    {
        return ! empty($options['needsMigration']) || ! empty($options['completedAt']);
    }

    /**
     * @param string[]             $present_tables Prefixed table names that exist.
     * @param array<string, string> $backup_tables  Map of backup keys to table names.
     * @return array{cpt: bool, meta: bool, shortcode: bool}
     */
    public static function backup_exists_for_tables(array $present_tables, array $backup_tables)
    {
        $keys = [ 'cpt', 'meta', 'shortcode' ];
        $out  = [];
        foreach ($keys as $key) {
            $name       = isset($backup_tables[ $key ]) ? (string) $backup_tables[ $key ] : '';
            $out[ $key ] = '' !== $name && in_array($name, $present_tables, true);
        }

        return $out;
    }

    /**
     * @return array{cpt: bool, meta: bool, shortcode: bool}
     */
    public static function backup_exists_map()
    {
        $list = function_exists('ppcart_custom_table_list') ? ppcart_custom_table_list() : [];
        if (! is_array($list)) {
            $list = [];
        }

        return self::backup_exists_for_tables($list, self::backup_table_names());
    }

    /**
     * @return bool
     */
    public static function assets_ready()
    {
        if (! defined('PPCARTCOMP_BASE_DIR')) {
            return false;
        }

        $dir = PPCARTCOMP_BASE_DIR . 'assets/admin/migration-wizard/build/';

        return file_exists($dir . 'index.js') && file_exists($dir . 'index.asset.php');
    }

    /**
     * @return array<string, mixed>
     */
    private static function action_map()
    {
        $cpt = class_exists('PPCartComp_Cpt_Slug_Migration_Admin', false)
            ? PPCartComp_Cpt_Slug_Migration_Admin::class
            : null;
        $meta = class_exists('PPCartComp_Meta_Key_Migration_Admin', false)
            ? PPCartComp_Meta_Key_Migration_Admin::class
            : null;
        $shortcode = class_exists('PPCartComp_Shortcode_Tag_Migration_Admin', false)
            ? PPCartComp_Shortcode_Tag_Migration_Admin::class
            : null;

        return [
            'status'    => self::AJAX_STATUS,
            'cpt'       => $cpt ? [
                'run'     => $cpt::AJAX_RUN,
                'stop'    => $cpt::AJAX_STOP,
                'restore' => $cpt::AJAX_RESTORE,
                'delete'  => $cpt::AJAX_DELETE,
                'nonce'   => function_exists('wp_create_nonce') ? wp_create_nonce($cpt::NONCE_ACTION) : '',
            ] : [],
            'meta'      => $meta ? [
                'run'     => $meta::AJAX_RUN,
                'stop'    => $meta::AJAX_STOP,
                'restore' => $meta::AJAX_RESTORE,
                'delete'  => $meta::AJAX_DELETE,
                'nonce'   => function_exists('wp_create_nonce') ? wp_create_nonce($meta::NONCE_ACTION) : '',
            ] : [],
            'shortcode' => $shortcode ? [
                'run'     => $shortcode::AJAX_RUN,
                'stop'    => $shortcode::AJAX_STOP,
                'restore' => $shortcode::AJAX_RESTORE,
                'delete'  => $shortcode::AJAX_DELETE,
                'nonce'   => function_exists('wp_create_nonce') ? wp_create_nonce($shortcode::NONCE_ACTION) : '',
            ] : [],
            'tables'    => [
                'run'   => 'ppcart_migrate_custom_tables',
                'nonce' => function_exists('wp_create_nonce') ? wp_create_nonce('ppcart_migrate_custom_tables') : '',
            ],
            'options'   => [
                'run'   => 'ppcart_migrate_option_keys',
                'nonce' => function_exists('wp_create_nonce') ? wp_create_nonce('ppcart_migrate_option_keys') : '',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    private static function status_map()
    {
        $cpt_state = function_exists('ppcart_cpt_slug_migration_get_state')
            ? ppcart_cpt_slug_migration_get_state()
            : [ 'step1' => 'idle', 'step2' => 'idle' ];
        $meta_state = function_exists('ppcart_meta_key_migration_get_state')
            ? ppcart_meta_key_migration_get_state()
            : [ 'step1' => 'idle' ];
        $sc_state = function_exists('ppcart_shortcode_tag_migration_get_state')
            ? ppcart_shortcode_tag_migration_get_state()
            : [ 'step1' => 'idle' ];

        $cpt_done  = function_exists('ppcart_cpt_slug_migration_step1_complete') && ppcart_cpt_slug_migration_step1_complete();
        $role_done = function_exists('ppcart_cpt_slug_migration_step2_complete') && ppcart_cpt_slug_migration_step2_complete();
        $meta_done = function_exists('ppcart_meta_key_migration_complete') && ppcart_meta_key_migration_complete();
        $sc_done   = function_exists('ppcart_shortcode_tag_migration_complete') && ppcart_shortcode_tag_migration_complete();

        $tables  = self::tables_payload();
        $options = self::options_payload();

        return [
            'cpt'        => self::map_state_status(isset($cpt_state['step1']) ? $cpt_state['step1'] : 'idle', $cpt_done),
            'roles'      => self::map_state_status(isset($cpt_state['step2']) ? $cpt_state['step2'] : 'idle', $role_done),
            'meta'       => self::map_state_status(isset($meta_state['step1']) ? $meta_state['step1'] : 'idle', $meta_done),
            'shortcodes' => self::map_state_status(isset($sc_state['step1']) ? $sc_state['step1'] : 'idle', $sc_done),
            'options'    => self::options_status($options),
            'tables'     => self::tables_status($tables),
            'backup'     => 'idle',
        ];
    }

    /**
     * @param array<string, string> $statuses Status map.
     * @return array<string, bool>
     */
    private static function completion_map(array $statuses)
    {
        $out = [];
        foreach ([ 'cpt', 'roles', 'meta', 'shortcodes', 'options', 'tables' ] as $slug) {
            $mark         = isset($statuses[ $slug ]) ? $statuses[ $slug ] : 'idle';
            $out[ $slug ] = in_array($mark, [ 'done', 'skipped' ], true);
        }

        return $out;
    }

    /**
     * @return array<string, mixed>
     */
    private static function remaining_map()
    {
        $cpt = [ 'posts' => 0, 'terms' => 0, 'templates' => 0, 'caps' => 0 ];
        if (class_exists('PPCartComp_Cpt_Slug_Migrator', false) && isset($GLOBALS['wpdb'])) {
            $cpt = PPCartComp_Cpt_Slug_Migrator::count_step1_remaining();
        }

        $meta = [ 'postmeta' => 0, 'usermeta' => 0 ];
        if (class_exists('PPCartComp_Meta_Key_Migrator', false) && isset($GLOBALS['wpdb'])) {
            $meta = PPCartComp_Meta_Key_Migrator::count_remaining();
        }

        $shortcodes = [ 'posts' => 0, 'options' => 0, 'postmeta' => 0 ];
        if (class_exists('PPCartComp_Shortcode_Tag_Migrator', false) && isset($GLOBALS['wpdb'])) {
            $shortcodes = PPCartComp_Shortcode_Tag_Migrator::count_remaining();
        }

        $pending_tables = function_exists('ppcart_custom_table_pending_families')
            ? ppcart_custom_table_pending_families()
            : [];
        $pending_options = function_exists('ppcart_option_key_migration_pending_names')
            ? ppcart_option_key_migration_pending_names()
            : [];

        return [
            'cpt'        => $cpt,
            'roles'      => [],
            'meta'       => $meta,
            'shortcodes' => $shortcodes,
            'options'    => [ 'rows' => is_array($pending_options) ? count($pending_options) : 0 ],
            'tables'     => [ 'families' => is_array($pending_tables) ? $pending_tables : [] ],
        ];
    }

    /**
     * @return array{needsMigration: bool, completedAt: int|null, pendingFamilies: string[]}
     */
    private static function tables_payload()
    {
        $needs = function_exists('ppcart_custom_table_needs_migration')
            ? (bool) ppcart_custom_table_needs_migration()
            : false;
        $state = function_exists('ppcart_custom_table_migration_state')
            ? ppcart_custom_table_migration_state()
            : [];
        $pending = function_exists('ppcart_custom_table_pending_families')
            ? ppcart_custom_table_pending_families()
            : [];

        $completed = isset($state['completed_at']) ? absint($state['completed_at']) : 0;

        return [
            'needsMigration'  => $needs,
            'completedAt'     => $completed > 0 ? $completed : null,
            'pendingFamilies' => is_array($pending) ? $pending : [],
        ];
    }

    /**
     * @return array{needsMigration: bool, completedAt: int|null, pendingCount: int}
     */
    private static function options_payload()
    {
        $pending = function_exists('ppcart_option_key_migration_pending_names')
            ? ppcart_option_key_migration_pending_names()
            : [];
        $needs = function_exists('ppcart_option_key_needs_migration')
            ? (bool) ppcart_option_key_needs_migration()
            : ( is_array($pending) && [] !== $pending );
        $state = function_exists('ppcart_option_key_migration_state')
            ? ppcart_option_key_migration_state()
            : [];

        $completed = isset($state['completed_at']) ? absint($state['completed_at']) : 0;

        return [
            'needsMigration' => $needs,
            'completedAt'    => $completed > 0 ? $completed : null,
            'pendingCount'   => is_array($pending) ? count($pending) : 0,
        ];
    }

    /**
     * @param array<string, mixed> $tables Tables payload.
     * @return string
     */
    private static function tables_status(array $tables)
    {
        if (! empty($tables['completedAt'])) {
            return 'done';
        }
        if (empty($tables['needsMigration'])) {
            return 'skipped';
        }

        return 'idle';
    }

    /**
     * @param array<string, mixed> $options Options payload.
     * @return string
     */
    private static function options_status(array $options)
    {
        if (! empty($options['completedAt'])) {
            return 'done';
        }
        if (empty($options['needsMigration'])) {
            return 'skipped';
        }

        return 'idle';
    }

    /**
     * @param mixed $raw      Raw state value.
     * @param bool  $complete Whether the step helper reports complete.
     * @return string
     */
    private static function map_state_status($raw, $complete)
    {
        if ($complete) {
            return 'done';
        }

        $raw = is_string($raw) ? $raw : 'idle';
        if (in_array($raw, [ 'idle', 'running', 'paused', 'failed' ], true)) {
            return $raw;
        }

        return 'complete' === $raw ? 'done' : 'idle';
    }

    /**
     * @return array<string, string>
     */
    private static function backup_table_names()
    {
        $map = [
            'cpt'       => 'PPCartComp_Cpt_Slug_Migrator',
            'meta'      => 'PPCartComp_Meta_Key_Migrator',
            'shortcode' => 'PPCartComp_Shortcode_Tag_Migrator',
        ];
        $names = [ 'cpt' => '', 'meta' => '', 'shortcode' => '' ];
        foreach ($map as $key => $class) {
            if (class_exists($class, false) && method_exists($class, 'table_name')) {
                $names[ $key ] = (string) $class::table_name();
            }
        }

        return $names;
    }

    /**
     * @return string
     */
    private static function requested_view()
    {
        if (! isset($_GET[ self::VIEW_QUERY ]) || ! is_string($_GET[ self::VIEW_QUERY ])) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only view key.
            return 'landing';
        }

        $view = sanitize_key(wp_unslash($_GET[ self::VIEW_QUERY ])); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only view key.

        return 'wizard' === $view ? 'wizard' : 'landing';
    }

    /**
     * @param string|null $fallback First incomplete slug.
     * @return string
     */
    private static function requested_step($fallback)
    {
        $allowed = [ 'cpt', 'roles', 'meta', 'shortcodes', 'options', 'tables', 'backup' ];
        if (isset($_GET[ self::STEP_QUERY ]) && is_string($_GET[ self::STEP_QUERY ])) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only step key.
            $step = sanitize_key(wp_unslash($_GET[ self::STEP_QUERY ])); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only step key.
            if (in_array($step, $allowed, true)) {
                return $step;
            }
        }

        return is_string($fallback) && '' !== $fallback ? $fallback : 'cpt';
    }
}

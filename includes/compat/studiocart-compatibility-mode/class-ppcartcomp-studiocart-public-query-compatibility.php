<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Legacy public query-var redirects for Compatibility Mode.
 */
final class PPCartComp_Studiocart_Public_Query_Compatibility
{
    public const REDIRECT_STATUS = 302;

    /**
     * Whether WordPress hooks were registered this request.
     *
     * @var bool
     */
    private static $registered = false;

    /**
     * Register gated legacy URL handling.
     *
     * @return void
     */
    public static function init()
    {
        if (self::$registered) {
            return;
        }

        add_filter('query_vars', [ self::class, 'register_legacy_query_vars' ]);
        add_action('init', [ self::class, 'register_legacy_webhook_rewrite' ], 0);
        add_action('init', [ self::class, 'copy_legacy_webhook_into_request' ], 1);
        add_filter('request', [ self::class, 'copy_legacy_webhook_request' ], 0);
        add_action('template_redirect', [ self::class, 'maybe_redirect_legacy_query_vars' ], 0);

        self::$registered = true;
    }

    /**
     * Legacy public query vars mapped to their canonical replacements.
     *
     * @return array<string, string>
     */
    public static function legacy_query_var_map()
    {
        return [
            'sc-preview'     => 'ppcart-preview',
            'sc-order'       => 'ppcart-order',
            'sc-plan'        => 'ppcart-plan',
            'sc-manage'      => 'ppcart-manage',
            'sc-download'    => 'ppcart-download',
            'sc-csv-export'  => 'ppcart-csv-export',
            'sc-api'         => 'ppcart-api',
            'sc-invoice'     => 'ppcart-invoice',
        ];
    }

    /**
     * Leftover pretty webhook rewrite when Compatibility Mode is on.
     *
     * Maps `/sc-webhook/{gateway}` onto the canonical `ppcart-api` query var
     * without a 302 so Stripe/PayPal POSTs keep their bodies.
     *
     * @return array{regex: string, query: string}
     */
    public static function leftover_webhook_rewrite()
    {
        return [
            'regex' => 'sc-webhook/?([^/]*)',
            'query' => 'index.php?ppcart-api=$matches[1]',
        ];
    }

    /**
     * Register the leftover webhook rewrite only while Compatibility Mode is on.
     *
     * @return void
     */
    public static function register_legacy_webhook_rewrite()
    {
        if (! self::is_enabled()) {
            return;
        }

        $rule = self::leftover_webhook_rewrite();
        add_rewrite_rule($rule['regex'], $rule['query'], 'top');
    }

    /**
     * Copy leftover webhook identifiers onto canonical request keys for POST.
     *
     * GET/HEAD leftover query args 302 instead. Pretty leftover paths and
     * `?sc-api=` POSTs cannot redirect without dropping the processor body.
     *
     * @return void
     */
    public static function copy_legacy_webhook_into_request()
    {
        if (! self::is_enabled() || self::is_safe_public_request()) {
            return;
        }

        $gateway = self::resolve_legacy_webhook_gateway();
        if ('' === $gateway) {
            return;
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Inbound processor POST copy, not a merchant form.
        if (! isset($_GET['ppcart-api']) || '' === (string) $_GET['ppcart-api']) {
            $_GET['ppcart-api']     = $gateway;
            $_REQUEST['ppcart-api'] = $gateway;
        }
    }

    /**
     * Copy leftover webhook query vars onto canonical vars for non-GET requests.
     *
     * @param array $query_vars Resolved query vars.
     * @return array
     */
    public static function copy_legacy_webhook_request($query_vars)
    {
        if (! self::is_enabled() || ! is_array($query_vars) || self::is_safe_public_request()) {
            return $query_vars;
        }

        $gateway = '';
        if (isset($query_vars['sc-api']) && is_scalar($query_vars['sc-api'])) {
            $gateway = sanitize_key((string) $query_vars['sc-api']);
        }
        if ('' === $gateway) {
            $gateway = self::resolve_legacy_webhook_gateway($query_vars);
        }

        if ('' === $gateway) {
            return $query_vars;
        }

        if (! isset($query_vars['ppcart-api']) || '' === (string) $query_vars['ppcart-api']) {
            $query_vars['ppcart-api'] = $gateway;
        }

        return $query_vars;
    }

    /**
     * Resolve a leftover webhook gateway from query args or the request path.
     *
     * @param array|null  $request_args Raw GET or resolved query args.
     * @param string|null $request_uri  Request URI.
     * @return string
     */
    public static function resolve_legacy_webhook_gateway($request_args = null, $request_uri = null)
    {
        if (null === $request_args) {
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only leftover webhook identifier.
            $request_args = is_array($_GET) ? $_GET : [];
        }

        if (isset($request_args['sc-api']) && is_scalar($request_args['sc-api'])) {
            return sanitize_key(wp_unslash((string) $request_args['sc-api']));
        }

        if (null === $request_uri) {
            $request_uri = isset($_SERVER['REQUEST_URI'])
                ? sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI']))
                : '';
        }

        if (is_string($request_uri) && preg_match('#(?:^|/)sc-webhook/([^/?]+)#', $request_uri, $matches)) {
            return sanitize_key($matches[1]);
        }

        return '';
    }

    /**
     * Register legacy query vars only while Compatibility Mode is enabled.
     *
     * @param array $query_vars Public query vars.
     * @return array
     */
    public static function register_legacy_query_vars($query_vars)
    {
        if (! self::is_enabled()) {
            return $query_vars;
        }

        foreach (array_keys(self::legacy_query_var_map()) as $query_var) {
            if (! in_array($query_var, $query_vars, true)) {
                $query_vars[] = $query_var;
            }
        }

        return $query_vars;
    }

    /**
     * Redirect safe public requests from legacy query vars to canonical vars.
     *
     * A temporary 302 avoids permanently caching URLs that can contain order,
     * plan, or download identifiers and can stop being recognized when the
     * merchant disables Compatibility Mode.
     *
     * @return void
     */
    public static function maybe_redirect_legacy_query_vars()
    {
        if (! self::is_enabled() || ! self::is_safe_public_request()) {
            return;
        }

        global $wp;

        $resolved_query_vars = is_object($wp) && isset($wp->query_vars) && is_array($wp->query_vars)
            ? $wp->query_vars
            : [];
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only URL canonicalization for public GET/HEAD requests.
        $request_args = is_array($_GET) ? $_GET : [];
        $redirect_url = self::get_legacy_redirect_url(
            self::get_current_request_url(),
            $request_args,
            $resolved_query_vars
        );

        if (! is_string($redirect_url) || '' === $redirect_url) {
            return;
        }

        if (wp_safe_redirect($redirect_url, self::REDIRECT_STATUS, 'PublishPress Cart')) {
            exit;
        }
    }

    /**
     * Whether this request is safe to redirect.
     *
     * Optional context arguments keep the policy directly testable. Production
     * callers omit them and WordPress/request globals provide the context.
     *
     * @param string|null $method           HTTP method.
     * @param bool|null   $is_admin_request Whether this is an admin request.
     * @param bool|null   $is_ajax_request  Whether this is an AJAX request.
     * @param bool|null   $is_rest_request  Whether this is a REST request.
     * @return bool
     */
    public static function is_safe_public_request(
        $method = null,
        $is_admin_request = null,
        $is_ajax_request = null,
        $is_rest_request = null
    ) {
        if (null === $method) {
            $method = isset($_SERVER['REQUEST_METHOD'])
                ? sanitize_key(wp_unslash($_SERVER['REQUEST_METHOD']))
                : 'GET';
        }

        if (! in_array(strtoupper((string) $method), [ 'GET', 'HEAD' ], true)) {
            return false;
        }

        if (null === $is_admin_request) {
            $is_admin_request = is_admin();
        }

        if (null === $is_ajax_request) {
            $is_ajax_request = function_exists('wp_doing_ajax')
                ? wp_doing_ajax()
                : (defined('DOING_AJAX') && DOING_AJAX);
        }

        if (null === $is_rest_request) {
            $is_rest_request = function_exists('wp_is_serving_rest_request')
                ? wp_is_serving_rest_request()
                : (defined('REST_REQUEST') && REST_REQUEST);
        }

        return ! $is_admin_request && ! $is_ajax_request && ! $is_rest_request;
    }

    /**
     * Build a canonical same-site URL for mapped legacy query vars.
     *
     * Scalar values, including integer/string zero, are preserved. Canonical
     * values take precedence when both forms are present. Array-shaped legacy
     * values are ignored; when another scalar causes a redirect, they are
     * removed from the destination.
     *
     * @param string $request_url         Current same-site request URL.
     * @param array  $request_args        Raw GET arguments.
     * @param array  $resolved_query_vars WordPress-resolved vars (stale rules).
     * @return string|null
     */
    public static function get_legacy_redirect_url($request_url, $request_args, $resolved_query_vars = [])
    {
        if (! is_string($request_url) || '' === $request_url) {
            return null;
        }

        $redirect_data = self::map_legacy_query_args($request_args, $resolved_query_vars);

        if (! $redirect_data['should_redirect']) {
            return null;
        }

        $redirect_url = remove_query_arg($redirect_data['legacy_args'], $request_url);
        $redirect_url = add_query_arg($redirect_data['canonical_args'], $redirect_url);
        $redirect_url = esc_url_raw($redirect_url);

        return $redirect_url !== esc_url_raw($request_url) ? $redirect_url : null;
    }

    /**
     * Convert scalar legacy values into canonical redirect arguments.
     *
     * Existing canonical values win; their legacy counterparts are removed
     * without replacing the canonical value.
     *
     * @param array $request_args        Raw GET arguments.
     * @param array $resolved_query_vars WordPress-resolved vars (stale rules).
     * @return array{
     *     canonical_args: array<string, scalar>,
     *     legacy_args: array<int, string>,
     *     should_redirect: bool
     * }
     */
    public static function map_legacy_query_args($request_args, $resolved_query_vars = [])
    {
        $canonical_args  = [];
        $legacy_args     = [];
        $should_redirect = false;

        foreach (self::legacy_query_var_map() as $legacy => $canonical) {
            $has_raw_value = array_key_exists($legacy, $request_args);

            if ($has_raw_value) {
                $legacy_args[] = $legacy;
                $value         = $request_args[ $legacy ];

                if (! is_scalar($value)) {
                    continue;
                }

                $should_redirect = true;

                if (array_key_exists($canonical, $request_args)) {
                    continue;
                }
            } elseif (
                'sc-download' === $legacy
                && ! array_key_exists($canonical, $request_args)
                && array_key_exists($legacy, $resolved_query_vars)
            ) {
                $value = $resolved_query_vars[ $legacy ];
            } else {
                continue;
            }

            if (! is_scalar($value)) {
                continue;
            }

            $should_redirect = true;
            $canonical_args[ $canonical ] = wp_unslash($value);
        }

        return [
            'canonical_args'  => $canonical_args,
            'legacy_args'     => $legacy_args,
            'should_redirect' => $should_redirect,
        ];
    }

    /**
     * Build the current URL from the site's trusted origin and request path.
     *
     * @return string
     */
    private static function get_current_request_url()
    {
        if (! isset($_SERVER['REQUEST_URI'])) {
            return '';
        }

        $home_parts  = wp_parse_url(home_url('/'));
        $request_uri = esc_url_raw(wp_unslash($_SERVER['REQUEST_URI']));
        $path        = wp_parse_url($request_uri, PHP_URL_PATH);
        $query       = wp_parse_url($request_uri, PHP_URL_QUERY);

        if (
            ! is_array($home_parts)
            || empty($home_parts['scheme'])
            || empty($home_parts['host'])
            || ! is_string($path)
        ) {
            return '';
        }

        $origin = $home_parts['scheme'] . '://' . $home_parts['host'];

        if (isset($home_parts['port'])) {
            $origin .= ':' . absint($home_parts['port']);
        }

        $request_url = $origin . '/' . ltrim($path, '/');

        if (is_string($query) && '' !== $query) {
            $request_url .= '?' . $query;
        }

        return esc_url_raw($request_url);
    }

    /**
     * Whether Compatibility Mode is currently enabled.
     *
     * @return bool
     */
    private static function is_enabled()
    {
        return class_exists('PPCartComp_Studiocart_Compatibility_Mode', false)
            && PPCartComp_Studiocart_Compatibility_Mode::is_enabled();
    }
}

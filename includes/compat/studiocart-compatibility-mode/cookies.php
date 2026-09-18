<?php

if (! defined('ABSPATH')) {
    exit;
}

if (! function_exists('ncs_cart_get_legacy_cookie_map')) {
    /**
     * @return array<string, array<int, string>>
     */
    function ncs_cart_get_legacy_cookie_map()
    {
        static $map = null;

        if (null !== $map) {
            return $map;
        }

        $map_file = __DIR__ . '/maps/cookies.php';
        $map      = is_readable($map_file) ? include $map_file : [];

        return is_array($map) ? $map : [];
    }
}

if (! function_exists('ncs_cart_legacy_cookies_copied')) {
    /**
     * @return bool
     */
    function &ncs_cart_legacy_cookies_copied()
    {
        static $copied = false;

        return $copied;
    }
}

if (! function_exists('ppcart_copy_legacy_affiliate_cookies')) {
    /**
     * Copy leftover affiliate cookies onto the canonical name.
     *
     * Canonical wins. Leftover cookies stay in place. Does not write DB rows.
     *
     * @return void
     */
    function ppcart_copy_legacy_affiliate_cookies()
    {
        $copied = &ncs_cart_legacy_cookies_copied();

        if ($copied) {
            return;
        }

        if (
            ! class_exists('PPCartComp_Studiocart_Compatibility_Mode', false)
            || ! PPCartComp_Studiocart_Compatibility_Mode::is_enabled()
        ) {
            return;
        }

        foreach (ncs_cart_get_legacy_cookie_map() as $canonical => $leftovers) {
            if (! is_string($canonical) || '' === $canonical || ! is_array($leftovers)) {
                continue;
            }

            if (isset($_COOKIE[ $canonical ]) && '' !== (string) $_COOKIE[ $canonical ]) {
                continue;
            }

            foreach ($leftovers as $leftover) {
                if (! is_string($leftover) || '' === $leftover) {
                    continue;
                }

                if (! isset($_COOKIE[ $leftover ]) || '' === (string) $_COOKIE[ $leftover ]) {
                    continue;
                }

                $_COOKIE[ $canonical ] = $_COOKIE[ $leftover ];
                break;
            }
        }

        $copied = true;
    }
}

if (! function_exists('ppcart_print_legacy_affiliate_cookie_script')) {
    /**
     * Copy leftover affiliate cookies into document.cookie before Pro JS reads them.
     *
     * @return void
     */
    function ppcart_print_legacy_affiliate_cookie_script()
    {
        if (
            ! class_exists('PPCartComp_Studiocart_Compatibility_Mode', false)
            || ! PPCartComp_Studiocart_Compatibility_Mode::is_enabled()
        ) {
            return;
        }

        echo '<script>';
        echo '(function(){';
        echo 'function g(n){var p=n+"=";var a=document.cookie.split(";");for(var i=0;i<a.length;i++){var c=a[i];while(c.charAt(0)===" ")c=c.substring(1);if(c.indexOf(p)===0)return decodeURIComponent(c.substring(p.length));}return "";}';
        echo 'function s(n,v){document.cookie=n+"="+encodeURIComponent(v)+"; path=/";}';
        echo 'if(!g("ppcart_affiliate_id")){var v=g("sc_affiliate_id")||g("affiliate_id");if(v)s("ppcart_affiliate_id",v);}';
        echo '})();';
        echo '</script>' . "\n";
    }
}

if (! function_exists('ppcart_register_legacy_cookie_bridges')) {
    /**
     * @return void
     */
    function ppcart_register_legacy_cookie_bridges()
    {
        ppcart_copy_legacy_affiliate_cookies();

        if (function_exists('add_action')) {
            add_action('wp_head', 'ppcart_print_legacy_affiliate_cookie_script', 0);
        }
    }
}

if (! function_exists('ppcart_unregister_legacy_cookie_bridges')) {
    /**
     * Intended for isolated tests.
     *
     * @return void
     */
    function ppcart_unregister_legacy_cookie_bridges()
    {
        $copied = &ncs_cart_legacy_cookies_copied();
        $copied = false;

        if (function_exists('remove_action')) {
            remove_action('wp_head', 'ppcart_print_legacy_affiliate_cookie_script', 0);
        }
    }
}

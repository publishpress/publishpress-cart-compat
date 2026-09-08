<?php

if (! defined('ABSPATH')) {
    exit;
}

if (! function_exists('ncs_cart_get_legacy_download_upload_directory_map')) {
    /**
     * Canonical download upload directory basename => leftover StudioCart names.
     *
     * @return array<string, array<int, string>>
     */
    function ncs_cart_get_legacy_download_upload_directory_map()
    {
        return [
            'ppcart-uploads' => [ 'sc-uploads' ],
        ];
    }
}

if (! function_exists('ppcart_legacy_download_upload_directory_basename')) {
    /**
     * Leftover StudioCart upload directory basename.
     *
     * @return string
     */
    function ppcart_legacy_download_upload_directory_basename()
    {
        return 'sc-uploads';
    }
}

if (! function_exists('ppcart_add_legacy_download_upload_root')) {
    /**
     * Append leftover sc-uploads to allowed download roots when that directory exists.
     *
     * In-memory extra root only. Does not create, copy, or move files.
     *
     * @param mixed $roots Allowed download roots.
     * @return array
     */
    function ppcart_add_legacy_download_upload_root($roots)
    {
        if (! is_array($roots)) {
            $roots = [];
        }

        $upload_dir = wp_upload_dir();

        if (! empty($upload_dir['error']) || empty($upload_dir['basedir'])) {
            return $roots;
        }

        $leftover = realpath(
            $upload_dir['basedir'] . '/' . ppcart_legacy_download_upload_directory_basename()
        );

        if (false === $leftover) {
            return $roots;
        }

        if (! in_array($leftover, $roots, true)) {
            $roots[] = $leftover;
        }

        return $roots;
    }
}

if (! function_exists('ppcart_register_legacy_download_upload_roots')) {
    /**
     * Allow leftover sc-uploads as an extra download root while Compatibility Mode is on.
     *
     * @return void
     */
    function ppcart_register_legacy_download_upload_roots()
    {
        if (! function_exists('add_filter')) {
            return;
        }

        if (function_exists('has_filter') && has_filter('ppcart_download_allowed_roots', 'ppcart_add_legacy_download_upload_root')) {
            return;
        }

        add_filter('ppcart_download_allowed_roots', 'ppcart_add_legacy_download_upload_root');
    }
}

if (! function_exists('ppcart_unregister_legacy_download_upload_roots')) {
    /**
     * Remove the leftover sc-uploads extra root. Intended for isolated tests.
     *
     * @return void
     */
    function ppcart_unregister_legacy_download_upload_roots()
    {
        if (! function_exists('remove_filter')) {
            return;
        }

        remove_filter('ppcart_download_allowed_roots', 'ppcart_add_legacy_download_upload_root');
    }
}

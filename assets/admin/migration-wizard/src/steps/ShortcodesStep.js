import { __ } from '@wordpress/i18n';

/**
 * @param {object} props
 */
export default function ShortcodesStep({ status, busy }) {
    const remaining = (status.remaining && status.remaining.shortcodes) || {};
    const mark = (status.statuses && status.statuses.shortcodes) || 'idle';
    const done = mark === 'done';

    return (
        <div>
            <h3>{__('Shortcode tags in content and emails', 'publishpress-cart-compat')}</h3>
            <p className="description">
                {__(
                    'Rewrites leftover StudioCart shortcode tags in post content, excerpts, Elementor data, email options, and product notification HTML.',
                    'publishpress-cart-compat'
                )}
            </p>
            <p>
                {__('Remaining:', 'publishpress-cart-compat') +
                    ' ' +
                    Number(remaining.posts || 0) +
                    ' ' +
                    __('posts', 'publishpress-cart-compat') +
                    ', ' +
                    Number(remaining.options || 0) +
                    ' ' +
                    __('email options', 'publishpress-cart-compat') +
                    ', ' +
                    Number(remaining.postmeta || 0) +
                    ' ' +
                    __('notification rows', 'publishpress-cart-compat') +
                    ', ' +
                    Number(remaining.elementor || 0) +
                    ' ' +
                    __('Elementor rows', 'publishpress-cart-compat') +
                    '.'}
            </p>
            <p className="description">
                {done
                    ? __('Completed', 'publishpress-cart-compat')
                    : busy
                        ? __('Running as part of the continuous migration…', 'publishpress-cart-compat')
                        : __('This step runs automatically with Start / Continue.', 'publishpress-cart-compat')}
            </p>
        </div>
    );
}

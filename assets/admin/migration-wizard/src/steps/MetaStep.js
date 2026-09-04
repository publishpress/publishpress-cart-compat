import { __ } from '@wordpress/i18n';

/**
 * @param {object} props
 */
export default function MetaStep({ status, busy }) {
    const remaining = (status.remaining && status.remaining.meta) || {};
    const mark = (status.statuses && status.statuses.meta) || 'idle';
    const done = mark === 'done';

    return (
        <div>
            <h3>{__('Post-meta and user-meta keys', 'publishpress-cart-compat')}</h3>
            <p className="description">
                {__(
                    'Renames leftover _sc_* product, order, subscription, and profile meta keys to _ppcart_*.',
                    'publishpress-cart-compat'
                )}
            </p>
            <p>
                {__('Remaining:', 'publishpress-cart-compat') +
                    ' ' +
                    Number(remaining.postmeta || 0) +
                    ' ' +
                    __('post-meta rows', 'publishpress-cart-compat') +
                    ', ' +
                    Number(remaining.usermeta || 0) +
                    ' ' +
                    __('user-meta rows', 'publishpress-cart-compat') +
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

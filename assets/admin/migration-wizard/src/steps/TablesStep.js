import { __ } from '@wordpress/i18n';

/**
 * @param {object} props
 */
export default function TablesStep({ status, busy }) {
    const tables = status.tables || {};
    const needs = !!tables.needsMigration;
    const families = tables.pendingFamilies || [];
    const done = (status.statuses && status.statuses.tables) === 'done';

    return (
        <div>
            <h3>{__('Custom tables', 'publishpress-cart-compat')}</h3>
            <p className="description">
                {__('Rename leftover ncs_* custom tables to ppcart_* names. Opt-in; no in-plugin restore.', 'publishpress-cart-compat')}
            </p>
            {needs ? (
                <p className="description">
                    {__('Leftover tables still in use:', 'publishpress-cart-compat') +
                        ' ' +
                        (families.length ? families.join(', ') : __('order item meta column', 'publishpress-cart-compat')) +
                        '.'}
                </p>
            ) : (
                <p className="description">
                    {__('Custom tables already use canonical ppcart_* names.', 'publishpress-cart-compat')}
                </p>
            )}
            <p className="description">
                {done
                    ? __('Completed', 'publishpress-cart-compat')
                    : busy
                        ? __('Waiting for confirmation, then renaming…', 'publishpress-cart-compat')
                        : needs
                            ? __(
                                'Start / Continue will ask once before renaming leftover custom tables.',
                                'publishpress-cart-compat'
                            )
                            : __('Nothing to do for custom tables.', 'publishpress-cart-compat')}
            </p>
        </div>
    );
}

import { __ } from '@wordpress/i18n';

/**
 * @param {object} props
 */
export default function OptionsStep({ status, busy }) {
    const options = status.options || {};
    const needs = !!options.needsMigration;
    const pending = typeof options.pendingCount === 'number' ? options.pendingCount : 0;
    const done = (status.statuses && status.statuses.options) === 'done';

    return (
        <div>
            <h3>{__('Option keys', 'publishpress-cart-compat')}</h3>
            <p className="description">
                {__(
                    'Rename leftover _sc_* / sc_* / ncs_* option rows (including transient option rows) to ppcart_* names. Opt-in; no in-plugin restore.',
                    'publishpress-cart-compat'
                )}
            </p>
            {needs ? (
                <p className="description">
                    {__('Leftover option rows still in use:', 'publishpress-cart-compat') +
                        ' ' +
                        String(pending) +
                        '.'}
                </p>
            ) : (
                <p className="description">
                    {__('Option keys already use canonical ppcart_* names.', 'publishpress-cart-compat')}
                </p>
            )}
            <p className="description">
                {done
                    ? __('Completed', 'publishpress-cart-compat')
                    : busy
                        ? __('Renaming leftover option keys…', 'publishpress-cart-compat')
                        : needs
                            ? __(
                                'Start / Continue runs this step with the rest of the migration.',
                                'publishpress-cart-compat'
                            )
                            : __('Nothing to do for option keys.', 'publishpress-cart-compat')}
            </p>
        </div>
    );
}

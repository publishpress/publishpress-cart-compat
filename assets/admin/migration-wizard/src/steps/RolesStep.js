import { __ } from '@wordpress/i18n';

/**
 * @param {object} props
 */
export default function RolesStep({ status, busy }) {
    const cptDone = (status.statuses && status.statuses.cpt) === 'done';
    const mark = (status.statuses && status.statuses.roles) || 'idle';
    const done = mark === 'done';

    return (
        <div>
            <h3>{__('Role slugs', 'publishpress-cart-compat')}</h3>
            <p>
                {__(
                    'Renames Cart Manager and Cart Administrator role keys after the post type step.',
                    'publishpress-cart-compat'
                )}
            </p>
            {!cptDone && !done && (
                <p className="description">
                    {__('Waiting for the post type step to finish.', 'publishpress-cart-compat')}
                </p>
            )}
            <p className="description">
                {done
                    ? __('This step is complete.', 'publishpress-cart-compat')
                    : busy
                        ? __('Running as part of the continuous migration…', 'publishpress-cart-compat')
                        : __('This step runs automatically with Start / Continue.', 'publishpress-cart-compat')}
            </p>
        </div>
    );
}

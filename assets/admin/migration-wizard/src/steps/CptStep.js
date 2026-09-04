import { __ } from '@wordpress/i18n';

/**
 * @param {object} props
 */
export default function CptStep({ status, busy }) {
    const remaining = (status.remaining && status.remaining.cpt) || {};
    const mark = (status.statuses && status.statuses.cpt) || 'idle';
    const done = mark === 'done';

    return (
        <div>
            <h3>{__('Post types, taxonomies, and capabilities', 'publishpress-cart-compat')}</h3>
            <p>
                {__('Remaining:', 'publishpress-cart-compat') +
                    ' ' +
                    Number(remaining.posts || 0) +
                    ' ' +
                    __('posts', 'publishpress-cart-compat') +
                    ', ' +
                    Number(remaining.terms || 0) +
                    ' ' +
                    __('terms', 'publishpress-cart-compat') +
                    '.'}
            </p>
            <p className="description">
                {done
                    ? __('This step is complete.', 'publishpress-cart-compat')
                    : busy
                        ? __('Running as part of the continuous migration…', 'publishpress-cart-compat')
                        : __(
                            'This step runs automatically with Start / Continue. It writes a reverse-map backup first. Restore from Backup & restore.',
                            'publishpress-cart-compat'
                        )}
            </p>
        </div>
    );
}

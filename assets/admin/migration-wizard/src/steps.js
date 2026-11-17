import { __, _n, sprintf } from '@wordpress/i18n';

const sum = (group, keys) => keys.reduce((total, key) => total + Number((group && group[key]) || 0), 0);

/**
 * One row per migration step, in pipeline order. `optional` steps show only
 * when the site has that kind of leftover data or already migrated it.
 */
const STEPS = [
    {
        slug: 'cpt',
        label: () => __('Products, orders, and subscriptions', 'publishpress-cart-compat'),
        description: () =>
            __(
                'Moves your StudioCart products, orders, subscriptions, and their categories to PublishPress Cart.',
                'publishpress-cart-compat'
            ),
        pending: (status) => sum(status.remaining && status.remaining.cpt, [ 'posts', 'terms' ]),
    },
    {
        slug: 'roles',
        label: () => __('User roles', 'publishpress-cart-compat'),
        description: () =>
            __(
                'Moves the Cart Manager and Cart Administrator roles, so your team keeps the same access.',
                'publishpress-cart-compat'
            ),
        pending: () => 0,
    },
    {
        slug: 'meta',
        label: () => __('Product, order, and customer details', 'publishpress-cart-compat'),
        description: () =>
            __(
                'Moves prices, order details, subscription details, and customer profiles.',
                'publishpress-cart-compat'
            ),
        pending: (status) => sum(status.remaining && status.remaining.meta, [ 'postmeta', 'usermeta' ]),
    },
    {
        slug: 'shortcodes',
        label: () => __('Shortcodes in your content', 'publishpress-cart-compat'),
        description: () =>
            __(
                'Updates StudioCart shortcodes in your pages, Elementor layouts, and emails.',
                'publishpress-cart-compat'
            ),
        pending: (status) =>
            sum(status.remaining && status.remaining.shortcodes, [ 'posts', 'options', 'postmeta', 'elementor' ]),
    },
    {
        slug: 'options',
        optional: true,
        label: () => __('Store settings', 'publishpress-cart-compat'),
        description: () =>
            __('Moves your StudioCart store settings to PublishPress Cart.', 'publishpress-cart-compat'),
        pending: (status) => Number((status.options && status.options.pendingCount) || 0),
    },
    {
        slug: 'tables',
        optional: true,
        label: () => __('Store database tables', 'publishpress-cart-compat'),
        description: () =>
            __(
                'Renames the StudioCart database tables for tax rates, order items, downloads, and affiliates.',
                'publishpress-cart-compat'
            ),
        pending: (status) => ((status.tables && status.tables.pendingFamilies) || []).length,
    },
];

/**
 * @param {Record<string, unknown>} status
 * @return {Array<{ slug: string, label: string, description: string, pending: number }>}
 */
export function migrationSteps(status) {
    return STEPS.filter((step) => {
        if (!step.optional) {
            return true;
        }
        const group = status[step.slug] || {};
        return !!(group.needsMigration || group.completedAt);
    }).map((step) => ({
        slug: step.slug,
        label: step.label(),
        description: step.description(),
        pending: step.pending(status),
    }));
}

/**
 * @param {Record<string, unknown>} status
 * @param {string} slug
 * @return {string}
 */
export function stepMark(status, slug) {
    return (status.statuses && status.statuses[slug]) || 'idle';
}

/**
 * @param {Record<string, unknown>} status
 * @return {boolean}
 */
export function hasStarted(status) {
    return migrationSteps(status).some((step) =>
        [ 'done', 'running', 'paused', 'failed' ].includes(stepMark(status, step.slug))
    );
}

/**
 * @param {Record<string, unknown>} status
 * @return {boolean}
 */
export function hasMigratedSomething(status) {
    return migrationSteps(status).some((step) => stepMark(status, step.slug) === 'done');
}

/**
 * @param {string} mark
 * @return {string}
 */
export function statusLabel(mark) {
    switch (mark) {
        case 'done':
            return __('Migrated', 'publishpress-cart-compat');
        case 'skipped':
            return __('Nothing to migrate', 'publishpress-cart-compat');
        case 'running':
            return __('Migrating…', 'publishpress-cart-compat');
        case 'paused':
            return __('Paused', 'publishpress-cart-compat');
        case 'failed':
            return __('Needs attention', 'publishpress-cart-compat');
        default:
            return __('Not started', 'publishpress-cart-compat');
    }
}

/**
 * @param {number} count
 * @return {string}
 */
export function pendingLabel(count) {
    return sprintf(
        /* translators: %d: number of StudioCart items still to migrate. */
        _n('%d item to migrate', '%d items to migrate', count, 'publishpress-cart-compat'),
        count
    );
}

/**
 * @param {object} props
 * @param {Record<string, unknown>} props.status
 * @param {string} [props.activeSlug]
 * @param {string} [props.runningSlug]
 * @param {boolean} [props.disabled]
 * @param {(slug: string) => void} [props.onSelect]
 */
export function StepList({ status, activeSlug, runningSlug, disabled, onSelect }) {
    return (
        <ol className="ppcartcomp-migration-steps">
            {migrationSteps(status).map((step) => {
                const stored = stepMark(status, step.slug);
                const mark =
                    step.slug === runningSlug && stored !== 'done' && stored !== 'skipped' ? 'running' : stored;
                const body = (
                    <>
                        <span className="ppcartcomp-migration-steps__icon" aria-hidden="true" />
                        <span className="ppcartcomp-migration-steps__label">{step.label}</span>
                        <span className="ppcartcomp-migration-steps__status">{statusLabel(mark)}</span>
                    </>
                );

                return (
                    <li
                        key={step.slug}
                        data-status={mark}
                        className={step.slug === activeSlug ? 'is-active' : ''}
                    >
                        {onSelect ? (
                            <button
                                type="button"
                                className="ppcartcomp-migration-steps__row"
                                onClick={() => onSelect(step.slug)}
                                disabled={disabled && step.slug !== activeSlug}
                                aria-current={step.slug === activeSlug ? 'step' : undefined}
                            >
                                {body}
                            </button>
                        ) : (
                            <div className="ppcartcomp-migration-steps__row">{body}</div>
                        )}
                    </li>
                );
            })}
        </ol>
    );
}

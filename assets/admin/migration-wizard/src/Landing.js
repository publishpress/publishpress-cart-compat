import { __ } from '@wordpress/i18n';
import { Button } from '@wordpress/components';

/**
 * @param {object} props
 * @param {Record<string, unknown>} props.status
 * @param {() => void} props.onStart
 * @param {() => void} props.onBackup
 */
export default function Landing({ status, onStart, onBackup }) {
    const progress = status.progress || { complete: 0, total: 0, firstIncomplete: null };
    const backupExists = status.backupExists || {};
    const hasBackup = !!(backupExists.cpt || backupExists.meta || backupExists.shortcode);
    const nothingToDo = !progress.firstIncomplete && progress.total > 0 && progress.complete === progress.total;
    const started = progress.complete > 0 && !!progress.firstIncomplete;

    return (
        <div className="ppcartcomp-migration-landing">
            <h2>{__('Data migration', 'publishpress-cart-compat')}</h2>
            <p>
                {__(
                    'Start runs every incomplete migration step in order (post types → roles → meta → shortcodes → option keys when needed → custom tables when needed). You can stop after a chunk and continue later. Restore and delete backups are on Backup & restore.',
                    'publishpress-cart-compat'
                )}
            </p>
            <p className="ppcartcomp-migration-landing__progress">
                {progress.total > 0
                    ? sprintfProgress(progress.complete, progress.total)
                    : __('No migration steps apply on this site.', 'publishpress-cart-compat')}
            </p>
            <StepSummary status={status} />
            <p className="ppcartcomp-migration-landing__actions">
                {!nothingToDo && (
                    <Button variant="primary" onClick={onStart}>
                        {started
                            ? __('Continue migration', 'publishpress-cart-compat')
                            : __('Start migration', 'publishpress-cart-compat')}
                    </Button>
                )}
                {hasBackup && (
                    <Button variant="secondary" onClick={onBackup}>
                        {__('Manage backups', 'publishpress-cart-compat')}
                    </Button>
                )}
            </p>
        </div>
    );
}

/**
 * @param {number} complete
 * @param {number} total
 * @return {string}
 */
function sprintfProgress(complete, total) {
    return __('Progress:', 'publishpress-cart-compat') + ' ' + complete + ' / ' + total;
}

/**
 * @param {object} props
 * @param {Record<string, unknown>} props.status
 */
function StepSummary({ status }) {
    const statuses = status.statuses || {};
    const options = status.options || {};
    const tables = status.tables || {};
    const includeOptions = !!(options.needsMigration || options.completedAt);
    const includeTables = !!(tables.needsMigration || tables.completedAt);
    const rows = [
        { slug: 'cpt', label: __('Post types, taxonomies, capabilities', 'publishpress-cart-compat') },
        { slug: 'roles', label: __('Role slugs', 'publishpress-cart-compat') },
        { slug: 'meta', label: __('Meta keys', 'publishpress-cart-compat') },
        { slug: 'shortcodes', label: __('Shortcode tags', 'publishpress-cart-compat') },
    ];
    if (includeOptions) {
        rows.push({ slug: 'options', label: __('Option keys', 'publishpress-cart-compat') });
    }
    if (includeTables) {
        rows.push({ slug: 'tables', label: __('Custom tables', 'publishpress-cart-compat') });
    }

    return (
        <ul className="ppcartcomp-migration-landing__steps">
            {rows.map((row) => {
                const mark = statuses[row.slug] || 'idle';

                return (
                    <li key={row.slug} data-status={mark}>
                        <span className="ppcartcomp-migration-mark" aria-hidden="true">
                            {markFor(mark)}
                        </span>
                        <span className="ppcartcomp-migration-landing__step-label">{row.label}</span>
                        <span className="ppcartcomp-migration-landing__step-status">
                            {statusLabel(mark)}
                        </span>
                    </li>
                );
            })}
        </ul>
    );
}

/**
 * @param {string} mark
 * @return {string}
 */
function markFor(mark) {
    if (mark === 'done') {
        return '✓';
    }
    if (mark === 'skipped') {
        return '–';
    }
    if (mark === 'failed') {
        return '!';
    }
    if (mark === 'paused' || mark === 'running') {
        return '…';
    }
    return '○';
}

/**
 * @param {string} mark
 * @return {string}
 */
function statusLabel(mark) {
    if (mark === 'done' || mark === 'skipped') {
        return __('Completed', 'publishpress-cart-compat');
    }
    if (mark === 'running') {
        return __('Running', 'publishpress-cart-compat');
    }
    if (mark === 'paused') {
        return __('Paused', 'publishpress-cart-compat');
    }
    if (mark === 'failed') {
        return __('Failed', 'publishpress-cart-compat');
    }
    return __('Pending', 'publishpress-cart-compat');
}

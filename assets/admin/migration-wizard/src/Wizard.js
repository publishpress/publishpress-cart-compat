import { __ } from '@wordpress/i18n';
import { Button } from '@wordpress/components';
import CptStep from './steps/CptStep';
import RolesStep from './steps/RolesStep';
import MetaStep from './steps/MetaStep';
import ShortcodesStep from './steps/ShortcodesStep';
import TablesStep from './steps/TablesStep';
import BackupPanel from './backup';

/**
 * @param {object} props
 */
export default function Wizard({
    status,
    step,
    busy,
    autoRunning,
    message,
    hasWork,
    onSelectStep,
    onContinue,
    onStop,
    onExit,
    onRestore,
    onDelete,
    steps: stepsProp,
}) {
    const tables = status.tables || {};
    const includeTables = !!(tables.needsMigration || tables.completedAt);
    const steps = stepsProp || wizardSteps(includeTables);
    const currentIndex = Math.max(0, steps.findIndex((item) => item.slug === step));
    const current = steps[currentIndex] || steps[0];
    const statuses = status.statuses || {};

    return (
        <div className="ppcartcomp-migration-wizard">
            <div className="ppcartcomp-migration-wizard__chrome">
                <h2>{__('Data migration', 'publishpress-cart-compat')}</h2>
                <p className="description">
                    {__(
                        'Start runs every incomplete step in order. You can stop after the current chunk and continue later.',
                        'publishpress-cart-compat'
                    )}
                </p>
                <ol className="ppcartcomp-migration-wizard__steps">
                    {steps.map((item) => (
                        <li
                            key={item.slug}
                            className={item.slug === current.slug ? 'is-active' : ''}
                            data-status={statuses[item.slug] || 'idle'}
                        >
                            <button
                                type="button"
                                className="ppcartcomp-migration-wizard__step-btn"
                                onClick={() => onSelectStep(item.slug)}
                                disabled={busy && item.slug !== current.slug}
                            >
                                <span className="ppcartcomp-migration-mark" aria-hidden="true">
                                    {item.slug === 'backup'
                                        ? '↺'
                                        : item.slug === current.slug && autoRunning
                                            ? '…'
                                            : markFor(statuses[item.slug])}
                                </span>
                                {item.label}
                            </button>
                        </li>
                    ))}
                </ol>
            </div>
            <div className="ppcartcomp-migration-wizard__panel">
                {current.slug === 'cpt' && <CptStep status={status} busy={busy} />}
                {current.slug === 'roles' && <RolesStep status={status} busy={busy} />}
                {current.slug === 'meta' && <MetaStep status={status} busy={busy} />}
                {current.slug === 'shortcodes' && <ShortcodesStep status={status} busy={busy} />}
                {current.slug === 'tables' && <TablesStep status={status} busy={busy} />}
                {current.slug === 'backup' && (
                    <BackupPanel status={status} busy={busy} onRestore={onRestore} onDelete={onDelete} />
                )}
                {message ? <p className="ppcartcomp-migration-wizard__message">{message}</p> : null}
            </div>
            <div className="ppcartcomp-migration-wizard__nav">
                {busy ? (
                    <Button variant="secondary" onClick={onStop}>
                        {__('Stop after this chunk', 'publishpress-cart-compat')}
                    </Button>
                ) : (
                    hasWork && (
                        <Button variant="primary" onClick={onContinue}>
                            {__('Continue migration', 'publishpress-cart-compat')}
                        </Button>
                    )
                )}
                <Button variant="tertiary" onClick={onExit} disabled={busy}>
                    {__('Exit', 'publishpress-cart-compat')}
                </Button>
            </div>
        </div>
    );
}

/**
 * @param {boolean} includeTables
 * @return {Array<{ slug: string, label: string }>}
 */
export function wizardSteps(includeTables) {
    const steps = [
        { slug: 'cpt', label: __('Post types, taxonomies, capabilities', 'publishpress-cart-compat') },
        { slug: 'roles', label: __('Role slugs', 'publishpress-cart-compat') },
        { slug: 'meta', label: __('Meta keys', 'publishpress-cart-compat') },
        { slug: 'shortcodes', label: __('Shortcode tags', 'publishpress-cart-compat') },
    ];
    if (includeTables) {
        steps.push({ slug: 'tables', label: __('Custom tables', 'publishpress-cart-compat') });
    }
    steps.push({ slug: 'backup', label: __('Backup & restore', 'publishpress-cart-compat') });
    return steps;
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

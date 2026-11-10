import { __ } from '@wordpress/i18n';
import { Button } from '@wordpress/components';
import { StepList, migrationSteps, pendingLabel, statusLabel, stepMark } from './steps';

/**
 * @param {object} props
 */
export default function Wizard({ status, step, busy, autoRunning, message, hasWork, onSelectStep, onContinue, onStop, onExit }) {
    const steps = migrationSteps(status);
    const current = steps.find((item) => item.slug === step) || steps[0];
    const stored = stepMark(status, current.slug);
    const mark = autoRunning && stored !== 'done' && stored !== 'skipped' ? 'running' : stored;

    return (
        <div className="ppcartcomp-migration-wizard">
            <div className="ppcartcomp-migration-wizard__chrome">
                <h2>{__('Your migration', 'publishpress-cart-compat')}</h2>
                <StepList
                    status={status}
                    activeSlug={current.slug}
                    runningSlug={autoRunning ? current.slug : ''}
                    disabled={busy}
                    onSelect={onSelectStep}
                />
            </div>
            <div className="ppcartcomp-migration-wizard__panel" aria-live="polite">
                <h3>{current.label}</h3>
                <p>{current.description}</p>
                <p className="ppcartcomp-migration-wizard__state" data-status={mark}>
                    {statusLabel(mark)}
                    {mark !== 'done' && mark !== 'skipped' && current.pending > 0
                        ? ' · ' + pendingLabel(current.pending)
                        : ''}
                </p>
                {message ? <p className="ppcartcomp-migration-wizard__message">{message}</p> : null}
            </div>
            <div className="ppcartcomp-migration-wizard__nav">
                {busy ? (
                    <Button variant="secondary" onClick={onStop}>
                        {__('Pause', 'publishpress-cart-compat')}
                    </Button>
                ) : (
                    hasWork && (
                        <Button variant="primary" className="ppcartcomp-migration-go" onClick={onContinue}>
                            {__('Continue your migration', 'publishpress-cart-compat')}
                        </Button>
                    )
                )}
                <Button variant="tertiary" onClick={onExit} disabled={busy}>
                    {__('Back', 'publishpress-cart-compat')}
                </Button>
            </div>
        </div>
    );
}

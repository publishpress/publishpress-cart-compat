import { __ } from '@wordpress/i18n';
import { Button } from '@wordpress/components';
import { StepList, hasMigratedSomething, hasStarted } from './steps';

/**
 * @param {object} props
 * @param {Record<string, unknown>} props.status
 * @param {() => void} props.onStart
 */
export default function Landing({ status, onStart }) {
    const progress = status.progress || { firstIncomplete: null };
    const finished = !progress.firstIncomplete;

    if (finished) {
        return hasMigratedSomething(status) ? <Complete status={status} /> : <NothingToDo />;
    }

    const started = hasStarted(status);

    return (
        <div className="ppcartcomp-migration-landing">
            <h2>{__('Migrate from StudioCart to PublishPress Cart', 'publishpress-cart-compat')}</h2>
            <p>{__('This process will help you migrate StudioCart to PublishPress Cart.', 'publishpress-cart-compat')}</p>
            {!started && (
                <div className="ppcartcomp-migration-backup-tip">
                    <strong>{__('Back up your site first.', 'publishpress-cart-compat')}</strong>{' '}
                    {__(
                        'The migration changes your StudioCart data. A recent backup lets you go back if anything looks wrong.',
                        'publishpress-cart-compat'
                    )}
                </div>
            )}
            <StepList status={status} />
            <p className="ppcartcomp-migration-actions">
                <Button variant="primary" className="ppcartcomp-migration-go" onClick={onStart}>
                    {started
                        ? __('Continue your migration', 'publishpress-cart-compat')
                        : __('Start your migration', 'publishpress-cart-compat')}
                </Button>
            </p>
        </div>
    );
}

/**
 * @param {object} props
 * @param {Record<string, unknown>} props.status
 */
function Complete({ status }) {
    return (
        <div className="ppcartcomp-migration-complete" role="status">
            <div className="ppcartcomp-migration-complete__badge" aria-hidden="true">
                ✓
            </div>
            <h2>{__('Congratulations! Your migration is complete.', 'publishpress-cart-compat')}</h2>
            <p>
                {__(
                    'Your StudioCart store now runs on PublishPress Cart. Your products, orders, subscriptions, and customers are ready to use.',
                    'publishpress-cart-compat'
                )}
            </p>
            <StepList status={status} />
        </div>
    );
}

function NothingToDo() {
    return (
        <div className="ppcartcomp-migration-complete" role="status">
            <div className="ppcartcomp-migration-complete__badge" aria-hidden="true">
                ✓
            </div>
            <h2>{__('You are all set', 'publishpress-cart-compat')}</h2>
            <p>
                {__(
                    'This site has no StudioCart data to migrate. PublishPress Cart is ready to use.',
                    'publishpress-cart-compat'
                )}
            </p>
        </div>
    );
}

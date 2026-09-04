import { useCallback, useEffect, useMemo, useRef, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { post } from './api';
import { fetchStatus } from './status';
import { createChunkLoop } from './chunkLoop';
import { readQuery, writeQuery } from './url';
import { renameTablesConfirmOptions, startMigrationConfirmOptions, useConfirm } from './ConfirmDialog';
import Landing from './Landing';
import Wizard, { wizardSteps } from './Wizard';

/**
 * @param {object} props
 * @param {Record<string, unknown>} props.config
 */
export default function App({ config }) {
    const confirm = useConfirm();
    const [status, setStatus] = useState(config);
    const [view, setView] = useState(config.initialView === 'wizard' ? 'wizard' : 'landing');
    const [step, setStep] = useState(config.initialStep || 'cpt');
    const [busy, setBusy] = useState(false);
    const [autoRunning, setAutoRunning] = useState(false);
    const [message, setMessage] = useState('');
    const abortRef = useRef(false);
    const currentPkgRef = useRef(null);
    const statusRef = useRef(status);

    useEffect(() => {
        statusRef.current = status;
    }, [status]);

    const ajaxPost = useCallback(
        (data) => post(config.ajaxUrl, data),
        [config.ajaxUrl]
    );

    const loop = useMemo(
        () =>
            createChunkLoop({
                post: ajaxPost,
                onStatus: (code) => setMessage(statusText(code)),
            }),
        [ajaxPost]
    );

    const go = useCallback((nextView, nextStep) => {
        setView(nextView);
        if (nextStep) {
            setStep(nextStep);
        }
        writeQuery(nextView, nextView === 'wizard' ? nextStep || step : '');
    }, [step]);

    useEffect(() => {
        const wrap = document.querySelector('.ppcartcomp-studiocart-page');
        if (!wrap) {
            return undefined;
        }
        wrap.classList.remove('is-wizard-active');
        wrap.classList.toggle('is-chunk-running', busy);
        return undefined;
    }, [busy]);

    useEffect(() => {
        const onPop = () => {
            const query = readQuery();
            setView(query.view);
            if (query.step) {
                setStep(query.step);
            }
        };
        window.addEventListener('popstate', onPop);
        return () => window.removeEventListener('popstate', onPop);
    }, []);

    const refreshStatus = useCallback(async () => {
        const next = await fetchStatus(config);
        setStatus((prev) => Object.assign({}, prev, next, { actions: next.actions || prev.actions }));
        return next;
    }, [config]);

    const showStep = useCallback((slug) => {
        setView('wizard');
        setStep(slug);
        writeQuery('wizard', slug);
    }, []);

    const packageFromStatus = useCallback((snapshot, pkg) => {
        const actions = (snapshot && snapshot.actions) || {};
        if (pkg === 'cpt') {
            return actions.cpt;
        }
        if (pkg === 'meta') {
            return actions.meta;
        }
        if (pkg === 'shortcode') {
            return actions.shortcode;
        }
        if (pkg === 'tables') {
            return actions.tables;
        }
        if (pkg === 'options') {
            return actions.options;
        }
        return null;
    }, []);

    const runOneStep = useCallback(
        async (slug, snapshot) => {
            const statuses = snapshot.statuses || {};
            const mark = statuses[slug] || 'idle';
            if (mark === 'done' || mark === 'skipped') {
                return { ok: true, skipped: true };
            }

            showStep(slug);

            if (slug === 'cpt') {
                const actions = packageFromStatus(snapshot, 'cpt');
                if (!actions || !actions.run) {
                    return { ok: false };
                }
                currentPkgRef.current = 'cpt';
                return loop.run({
                    action: actions.run,
                    nonce: actions.nonce,
                    extra: { step: 1 },
                    loopUntilDone: true,
                });
            }

            if (slug === 'roles') {
                const actions = packageFromStatus(snapshot, 'cpt');
                if (!actions || !actions.run) {
                    return { ok: false };
                }
                currentPkgRef.current = 'cpt';
                return loop.run({
                    action: actions.run,
                    nonce: actions.nonce,
                    extra: { step: 2 },
                    loopUntilDone: false,
                });
            }

            if (slug === 'meta') {
                const actions = packageFromStatus(snapshot, 'meta');
                if (!actions || !actions.run) {
                    return { ok: false };
                }
                currentPkgRef.current = 'meta';
                return loop.run({
                    action: actions.run,
                    nonce: actions.nonce,
                    loopUntilDone: true,
                });
            }

            if (slug === 'shortcodes') {
                const actions = packageFromStatus(snapshot, 'shortcode');
                if (!actions || !actions.run) {
                    return { ok: false };
                }
                currentPkgRef.current = 'shortcode';
                return loop.run({
                    action: actions.run,
                    nonce: actions.nonce,
                    loopUntilDone: true,
                });
            }

            if (slug === 'options') {
                const actions = packageFromStatus(snapshot, 'options');
                const options = snapshot.options || {};
                if (!options.needsMigration) {
                    return { ok: true, skipped: true };
                }
                if (!actions || !actions.run) {
                    return { ok: false };
                }
                currentPkgRef.current = 'options';
                setMessage(statusText('running'));
                const optionsResult = await ajaxPost({
                    action: actions.run,
                    nonce: actions.nonce,
                });
                if (optionsResult && optionsResult.ok !== false) {
                    setMessage(statusText('done'));
                }
                return optionsResult;
            }

            if (slug === 'tables') {
                const actions = packageFromStatus(snapshot, 'tables');
                const tables = snapshot.tables || {};
                if (!tables.needsMigration) {
                    return { ok: true, skipped: true };
                }
                if (!actions || !actions.run) {
                    return { ok: false };
                }
                currentPkgRef.current = 'tables';
                const agreed = await confirm(renameTablesConfirmOptions());
                if (!agreed || abortRef.current) {
                    return { ok: true, skipped: true, cancelled: true };
                }
                setMessage(statusText('running'));
                const tablesResult = await ajaxPost({
                    action: actions.run,
                    nonce: actions.nonce,
                });
                if (tablesResult && tablesResult.ok !== false) {
                    setMessage(statusText('done'));
                }
                return tablesResult;
            }

            return { ok: true, skipped: true };
        },
        [ajaxPost, confirm, loop, packageFromStatus, showStep]
    );

    const runPipeline = useCallback(async () => {
        if (busy || loop.isLocked()) {
            return;
        }

        const agreed = await confirm(startMigrationConfirmOptions());
        if (!agreed) {
            return;
        }

        abortRef.current = false;
        setBusy(true);
        setAutoRunning(true);
        setView('wizard');
        setMessage(statusText('running'));

        let latest = await refreshStatus();
        const tables = latest.tables || {};
        const options = latest.options || {};
        const pipeline = [ 'cpt', 'roles', 'meta', 'shortcodes' ];
        if (options.needsMigration) {
            pipeline.push('options');
        }
        if (tables.needsMigration) {
            pipeline.push('tables');
        }

        let stopped = false;
        for (const slug of pipeline) {
            if (abortRef.current) {
                stopped = true;
                break;
            }

            const mark = (latest.statuses && latest.statuses[slug]) || 'idle';
            if (mark === 'done' || mark === 'skipped') {
                continue;
            }

            const result = await runOneStep(slug, latest);
            latest = await refreshStatus();

            if (abortRef.current || (result && result.paused)) {
                stopped = true;
                setMessage(statusText('paused'));
                break;
            }
            if (result && result.ok === false) {
                stopped = true;
                setMessage(statusText('error'));
                break;
            }
        }

        currentPkgRef.current = null;
        setBusy(false);
        setAutoRunning(false);

        latest = await refreshStatus();
        if (!stopped && !(latest.progress && latest.progress.firstIncomplete)) {
            showStep('backup');
            setMessage(
                __('Migration finished. You can review or delete backups below.', 'publishpress-cart-compat')
            );
        } else if (!stopped && latest.progress && latest.progress.firstIncomplete) {
            showStep(latest.progress.firstIncomplete);
        }
    }, [busy, confirm, loop, refreshStatus, runOneStep, showStep]);

    const onStop = useCallback(async () => {
        abortRef.current = true;
        const pkg = currentPkgRef.current;
        const snapshot = statusRef.current;
        if (pkg === 'cpt' || pkg === 'meta' || pkg === 'shortcode') {
            const actions = packageFromStatus(snapshot, pkg);
            if (actions && actions.stop) {
                await loop.stop({ action: actions.stop, nonce: actions.nonce });
            }
        }
        setMessage(statusText('paused'));
    }, [loop, packageFromStatus]);

    const onRestore = useCallback(
        async (pkg, restoreStep) => {
            const actions = packageFromStatus(statusRef.current, pkg);
            if (!actions || !actions.restore) {
                return;
            }
            setBusy(true);
            const extra = restoreStep ? { step: restoreStep } : {};
            await ajaxPost(Object.assign({ action: actions.restore, nonce: actions.nonce }, extra));
            await refreshStatus();
            setBusy(false);
            setMessage(__('Restore finished.', 'publishpress-cart-compat'));
        },
        [ajaxPost, packageFromStatus, refreshStatus]
    );

    const onDelete = useCallback(
        async (pkg) => {
            const actions = packageFromStatus(statusRef.current, pkg);
            if (!actions || !actions.delete) {
                return;
            }
            setBusy(true);
            await ajaxPost({ action: actions.delete, nonce: actions.nonce });
            await refreshStatus();
            setBusy(false);
            setMessage(__('Backup deleted.', 'publishpress-cart-compat'));
        },
        [ajaxPost, packageFromStatus, refreshStatus]
    );

    const includeOptions = !!(status.options && (status.options.needsMigration || status.options.completedAt));
    const includeTables = !!(status.tables && (status.tables.needsMigration || status.tables.completedAt));
    const steps = wizardSteps(includeOptions, includeTables);
    const hasWork = !!(status.progress && status.progress.firstIncomplete);

    const startWizard = () => {
        const first = (status.progress && status.progress.firstIncomplete) || 'cpt';
        showStep(first);
        runPipeline();
    };

    const openBackup = () => go('wizard', 'backup');

    const onSelectStep = (slug) => {
        if (busy) {
            return;
        }
        go('wizard', slug);
    };

    if (!config.assetsReady && config.assetsReady !== undefined && config.assetsReady === false) {
        return null;
    }

    if (view !== 'wizard') {
        return (
            <Landing status={status} onStart={startWizard} onBackup={openBackup} />
        );
    }

    return (
        <Wizard
            status={status}
            step={step}
            busy={busy}
            autoRunning={autoRunning}
            message={message}
            hasWork={hasWork}
            onSelectStep={onSelectStep}
            onContinue={runPipeline}
            onStop={onStop}
            onExit={() => go('landing', '')}
            onRestore={onRestore}
            onDelete={onDelete}
            steps={steps}
        />
    );
}

/**
 * @param {string} code
 * @return {string}
 */
function statusText(code) {
    if (code === 'running') {
        return __('Running…', 'publishpress-cart-compat');
    }
    if (code === 'paused') {
        return __('Paused. Use Continue to resume from the first incomplete step.', 'publishpress-cart-compat');
    }
    if (code === 'done') {
        return __('Completed', 'publishpress-cart-compat');
    }
    if (code === 'error') {
        return __('The migration request failed.', 'publishpress-cart-compat');
    }
    return '';
}

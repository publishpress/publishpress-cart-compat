import { createContext, useCallback, useContext, useMemo, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Button, Modal } from '@wordpress/components';

const ConfirmContext = createContext(null);

/**
 * Promise-based confirm dialog (Modal). Replaces window.confirm.
 *
 * @param {object} props
 * @param {import('@wordpress/element').ReactNode} props.children
 */
export function ConfirmProvider({ children }) {
    const [dialog, setDialog] = useState(null);

    const confirm = useCallback((options) => {
        const opts = typeof options === 'string' ? { message: options } : options || {};
        return new Promise((resolve) => {
            setDialog({
                title: opts.title || __('Confirm', 'publishpress-cart-compat'),
                message: opts.message || '',
                confirmText: opts.confirmText || __('Continue', 'publishpress-cart-compat'),
                cancelText: opts.cancelText || __('Cancel', 'publishpress-cart-compat'),
                confirmClassName: opts.confirmClassName || '',
                resolve,
            });
        });
    }, []);

    const finish = useCallback((result) => {
        setDialog((current) => {
            if (current && typeof current.resolve === 'function') {
                current.resolve(result);
            }
            return null;
        });
    }, []);

    const value = useMemo(() => confirm, [confirm]);

    return (
        <ConfirmContext.Provider value={value}>
            {children}
            {dialog ? (
                <Modal
                    title={dialog.title}
                    onRequestClose={() => finish(false)}
                    className="ppcartcomp-migration-confirm"
                >
                    <p className="ppcartcomp-migration-confirm__message">{dialog.message}</p>
                    <div className="ppcartcomp-migration-confirm__actions">
                        <Button variant="secondary" onClick={() => finish(false)}>
                            {dialog.cancelText}
                        </Button>
                        <Button
                            variant="primary"
                            className={dialog.confirmClassName}
                            onClick={() => finish(true)}
                        >
                            {dialog.confirmText}
                        </Button>
                    </div>
                </Modal>
            ) : null}
        </ConfirmContext.Provider>
    );
}

/**
 * @return {(options: string|{ title?: string, message: string, confirmText?: string, cancelText?: string, confirmClassName?: string }) => Promise<boolean>}
 */
export function useConfirm() {
    const confirm = useContext(ConfirmContext);
    if (!confirm) {
        throw new Error('useConfirm requires ConfirmProvider');
    }
    return confirm;
}

/**
 * The one confirm before the first run. Later runs resume without asking.
 *
 * @return {{ title: string, message: string, confirmText: string, cancelText: string, confirmClassName: string }}
 */
export function startMigrationConfirmOptions() {
    return {
        title: __('Start your migration', 'publishpress-cart-compat'),
        message: __(
            'Have you backed up your site? The migration moves your StudioCart data to PublishPress Cart. It can take a few minutes. Keep this page open until it finishes.',
            'publishpress-cart-compat'
        ),
        confirmText: __('Yes, start my migration', 'publishpress-cart-compat'),
        cancelText: __('Not yet', 'publishpress-cart-compat'),
        confirmClassName: 'ppcartcomp-migration-go',
    };
}

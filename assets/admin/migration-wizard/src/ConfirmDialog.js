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
                isDestructive: !!opts.isDestructive,
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
                    shouldCloseOnClickOutside={!dialog.isDestructive}
                >
                    <p className="ppcartcomp-migration-confirm__message">{dialog.message}</p>
                    <div className="ppcartcomp-migration-confirm__actions">
                        <Button variant="secondary" onClick={() => finish(false)}>
                            {dialog.cancelText}
                        </Button>
                        <Button
                            variant="primary"
                            isDestructive={dialog.isDestructive}
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
 * @return {(options: string|{ title?: string, message: string, confirmText?: string, cancelText?: string, isDestructive?: boolean }) => Promise<boolean>}
 */
export function useConfirm() {
    const confirm = useContext(ConfirmContext);
    if (!confirm) {
        throw new Error('useConfirm requires ConfirmProvider');
    }
    return confirm;
}

/**
 * Shared copy for restore confirms.
 *
 * @return {{ title: string, message: string, confirmText: string }}
 */
export function restoreConfirmOptions() {
    return {
        title: __('Restore backup', 'publishpress-cart-compat'),
        message: __('Restore only undoes completed chunks. Continue?', 'publishpress-cart-compat'),
        confirmText: __('Restore', 'publishpress-cart-compat'),
    };
}

/**
 * @return {{ title: string, message: string, confirmText: string, isDestructive: boolean }}
 */
export function deleteBackupConfirmOptions() {
    return {
        title: __('Delete backup', 'publishpress-cart-compat'),
        message: __(
            'Delete the reverse-map backup? Restore will no longer be possible from Cart.',
            'publishpress-cart-compat'
        ),
        confirmText: __('Delete backup', 'publishpress-cart-compat'),
        isDestructive: true,
    };
}

/**
 * @return {{ title: string, message: string, confirmText: string, isDestructive: boolean }}
 */
export function renameTablesConfirmOptions() {
    return {
        title: __('Rename custom tables', 'publishpress-cart-compat'),
        message: __(
            'Rename leftover custom tables to ppcart_* names? Take a backup first and run this during a maintenance window.',
            'publishpress-cart-compat'
        ),
        confirmText: __('Rename tables', 'publishpress-cart-compat'),
        isDestructive: true,
    };
}

/**
 * General confirm before Start / Continue runs the migration pipeline.
 *
 * @return {{ title: string, message: string, confirmText: string, isDestructive: boolean }}
 */
export function startMigrationConfirmOptions() {
    return {
        title: __('Run data migration', 'publishpress-cart-compat'),
        message: __(
            'Run every incomplete migration step now? Take a site backup first and prefer a maintenance window. Leftover StudioCart data will be renamed to PublishPress Cart names.',
            'publishpress-cart-compat'
        ),
        confirmText: __('Run migration', 'publishpress-cart-compat'),
        isDestructive: true,
    };
}

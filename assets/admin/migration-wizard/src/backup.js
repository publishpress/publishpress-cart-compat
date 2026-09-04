import { __ } from '@wordpress/i18n';
import { Button } from '@wordpress/components';
import { deleteBackupConfirmOptions, restoreConfirmOptions, useConfirm } from './ConfirmDialog';

/**
 * Three reverse-map backup groups. Custom tables are warning-only (no restore).
 *
 * @param {object} props
 */
export default function BackupPanel({ status, busy, onRestore, onDelete }) {
    const backupExists = status.backupExists || {};
    const tables = status.tables || {};

    return (
        <div className="ppcartcomp-migration-backup">
            <h3>{__('Backup & restore', 'publishpress-cart-compat')}</h3>
            <p>
                {__(
                    'Each of post types, meta keys, and shortcode tags keeps its own reverse-map backup. Delete a backup only when you are sure you will not restore that group from Cart.',
                    'publishpress-cart-compat'
                )}
            </p>
            <BackupGroup
                title={__('Post types, taxonomies, and roles', 'publishpress-cart-compat')}
                exists={!!backupExists.cpt}
                busy={busy}
                onRestoreStep1={() => onRestore('cpt', 1)}
                onRestoreStep2={() => onRestore('cpt', 2)}
                onDelete={() => onDelete('cpt')}
                footgun={__(
                    'Restoring post types does not clear meta-key or shortcode migration complete flags. Those steps keep their own backups.',
                    'publishpress-cart-compat'
                )}
            />
            <BackupGroup
                title={__('Meta keys', 'publishpress-cart-compat')}
                exists={!!backupExists.meta}
                busy={busy}
                onRestore={() => onRestore('meta')}
                onDelete={() => onDelete('meta')}
            />
            <BackupGroup
                title={__('Shortcode tags', 'publishpress-cart-compat')}
                exists={!!backupExists.shortcode}
                busy={busy}
                onRestore={() => onRestore('shortcode')}
                onDelete={() => onDelete('shortcode')}
            />
            <div className="ppcartcomp-migration-backup__group">
                <h4>{__('Custom tables', 'publishpress-cart-compat')}</h4>
                <p className="description">
                    {__(
                        'Custom table rename has no reverse-map backup. Restore is not available from Cart. Take a database backup before you run that step.',
                        'publishpress-cart-compat'
                    )}
                </p>
                {tables.completedAt ? (
                    <p className="description">
                        {__('A custom table migration has already run on this site.', 'publishpress-cart-compat')}
                    </p>
                ) : null}
            </div>
        </div>
    );
}

/**
 * @param {object} props
 */
function BackupGroup({
    title,
    exists,
    busy,
    onRestore,
    onRestoreStep1,
    onRestoreStep2,
    onDelete,
    footgun,
}) {
    const confirm = useConfirm();

    return (
        <div className="ppcartcomp-migration-backup__group">
            <h4>{title}</h4>
            {exists ? (
                <>
                    {onRestoreStep1 ? (
                        <p>
                            <Button
                                variant="secondary"
                                disabled={busy}
                                onClick={async () => {
                                    if (await confirm(restoreConfirmOptions())) {
                                        onRestoreStep1();
                                    }
                                }}
                            >
                                {__('Restore completed chunks', 'publishpress-cart-compat')}
                            </Button>{' '}
                            <Button
                                variant="secondary"
                                disabled={busy}
                                onClick={async () => {
                                    if (await confirm(restoreConfirmOptions())) {
                                        onRestoreStep2();
                                    }
                                }}
                            >
                                {__('Restore role slugs', 'publishpress-cart-compat')}
                            </Button>
                        </p>
                    ) : (
                        <p>
                            <Button
                                variant="secondary"
                                disabled={busy}
                                onClick={async () => {
                                    if (await confirm(restoreConfirmOptions())) {
                                        onRestore();
                                    }
                                }}
                            >
                                {__('Restore completed chunks', 'publishpress-cart-compat')}
                            </Button>
                        </p>
                    )}
                    <p>
                        <Button
                            variant="secondary"
                            isDestructive
                            disabled={busy}
                            onClick={async () => {
                                if (await confirm(deleteBackupConfirmOptions())) {
                                    onDelete();
                                }
                            }}
                        >
                            {__('Delete backup', 'publishpress-cart-compat')}
                        </Button>
                    </p>
                    {footgun ? <p className="description">{footgun}</p> : null}
                </>
            ) : (
                <p className="description">
                    {__('No reverse-map backup is stored for this group.', 'publishpress-cart-compat')}
                </p>
            )}
        </div>
    );
}

/**
 * Port of the jQuery chunk loops: runLock, stop-after-chunk keeps ticking
 * until { paused }, step 1 / meta / shortcode loop until done, step 2 is one shot.
 *
 * @param {object} options
 * @param {(data: Record<string, unknown>) => Promise<Record<string, unknown>>} options.post
 * @param {(text: string) => void} options.onStatus
 * @return {{ run: Function, stop: Function, isLocked: () => boolean }}
 */
export function createChunkLoop({ post, onStatus }) {
    let runLock = false;

    async function run({ action, extra, loopUntilDone, nonce }) {
        if (runLock) {
            return { skipped: true };
        }

        runLock = true;
        let last = { ok: false };

        try {
            while (true) {
                if (onStatus) {
                    onStatus('running');
                }
                last = await post(
                    Object.assign({ action, nonce }, extra || {})
                );
                if (!last || last.ok === false) {
                    if (onStatus) {
                        onStatus('error');
                    }
                    return last || { ok: false };
                }
                if (last.paused) {
                    if (onStatus) {
                        onStatus('paused');
                    }
                    return last;
                }
                if (last.done) {
                    if (onStatus) {
                        onStatus('done');
                    }
                    return last;
                }
                if (!loopUntilDone) {
                    return last;
                }
            }
        } catch (err) {
            if (onStatus) {
                onStatus('error');
            }
            return { ok: false, error: err && err.message ? err.message : 'error' };
        } finally {
            runLock = false;
        }
    }

    async function stop({ action, nonce, extra }) {
        await post(Object.assign({ action, nonce }, extra || {}));
    }

    return {
        run,
        stop,
        isLocked: () => runLock,
    };
}

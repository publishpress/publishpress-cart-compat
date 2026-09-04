export const VIEW_KEY = 'ppcartcomp_migration_view';
export const STEP_KEY = 'ppcartcomp_migration_step';

/**
 * @return {{ view: string, step: string }}
 */
export function readQuery() {
    const params = new URLSearchParams(window.location.search);
    const view = params.get(VIEW_KEY) === 'wizard' ? 'wizard' : 'landing';
    return {
        view,
        step: params.get(STEP_KEY) || '',
    };
}

/**
 * @param {string} view
 * @param {string} step
 * @return {void}
 */
export function writeQuery(view, step) {
    const url = new URL(window.location.href);
    if (view === 'wizard') {
        url.searchParams.set(VIEW_KEY, 'wizard');
        if (step) {
            url.searchParams.set(STEP_KEY, step);
        } else {
            url.searchParams.delete(STEP_KEY);
        }
    } else {
        url.searchParams.delete(VIEW_KEY);
        url.searchParams.delete(STEP_KEY);
    }
    window.history.replaceState({ view, step: step || '' }, '', url.toString());
}

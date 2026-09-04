import { post } from './api';

/**
 * @param {Record<string, unknown>} config Localized bootstrap.
 * @return {Promise<Record<string, unknown>>}
 */
export async function fetchStatus(config) {
    const payload = await post(config.ajaxUrl, {
        action: config.actions.status,
        nonce: config.nonce,
    });

    if (!payload || payload.ok === false) {
        throw new Error(payload && payload.error ? String(payload.error) : 'status');
    }

    return payload;
}

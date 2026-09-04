/**
 * urlencoded admin-ajax POST. Not wp-api-fetch. Not JSON body.
 *
 * Normalizes two dialects:
 * - { ok, … } (CPT / meta / shortcode)
 * - { success, data } (custom tables wp_send_json_*)
 *
 * @param {string} ajaxUrl
 * @param {Record<string, string|number|boolean>} data
 * @return {Promise<Record<string, unknown>>}
 */
export async function post(ajaxUrl, data) {
    const body = new URLSearchParams();
    Object.keys(data || {}).forEach((key) => {
        if (data[key] !== undefined && data[key] !== null) {
            body.set(key, String(data[key]));
        }
    });

    const response = await fetch(ajaxUrl, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
        },
        body: body.toString(),
    });

    let json;
    try {
        json = await response.json();
    } catch (err) {
        throw new Error('invalid-json');
    }

    if (json === -1 || json === '-1') {
        throw new Error('forbidden');
    }

    const normalized = normalize(json);

    if (response.status === 403) {
        throw new Error(normalized.error || 'forbidden');
    }

    if (!response.ok && normalized.ok !== true && normalized.ok !== false) {
        throw new Error('http');
    }

    return normalized;
}

/**
 * @param {unknown} json
 * @return {Record<string, unknown>}
 */
export function normalize(json) {
    if (!json || typeof json !== 'object') {
        return { ok: false, error: 'empty' };
    }

    if (typeof json.success === 'boolean') {
        const data = json.data && typeof json.data === 'object' ? json.data : {};
        return Object.assign({ ok: json.success }, data);
    }

    return json;
}

/**
 * Phone display mask, mirroring formatPhoneNumber() in public/assets/js/form-handle.js
 * and CommonFunction::formatPhone() on the PHP side.
 *
 * The mask is a *display* concern: the server stores and validates bare digits
 * (`phone` => 'regex:/^[0-9]{10,20}$/'), so digitsOnly() is what gets submitted.
 */

export function formatPhoneNumber(value) {
    const digits = digitsOnly(value);

    if (!digits) {
        return '';
    }

    if (digits.length <= 3) {
        return `(${digits}`;
    }

    const area = digits.slice(0, 3);
    const prefix = digits.slice(3, 6);
    const line = digits.slice(6, 10);
    const rest = digits.slice(10);

    let formatted = `(${area})`;

    if (prefix) {
        formatted += ` ${prefix}`;
    }
    if (line) {
        formatted += `-${line}`;
    }
    if (rest) {
        formatted += ` ${rest}`;
    }

    return formatted;
}

export function digitsOnly(value) {
    return String(value ?? '').replace(/\D/g, '').slice(0, 20);
}

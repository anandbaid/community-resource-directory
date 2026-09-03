/**
 * SweetAlert helpers for Inertia pages.
 *
 * SweetAlert2 is loaded globally by resources/views/backend/includes/foot.blade.php,
 * so these mirror resources/views/common/flash.blade.php rather than bundling a
 * second copy of the library.
 */

export function swalAlert(type, message, timer = 3000) {
    if (!window.Swal || !message) {
        return;
    }

    const config = {
        icon: type,
        html: String(message).replace(/\n/g, '<br>'),
        timer,
    };

    if (type === 'error') {
        config.title = 'Something went wrong!';
    }

    window.Swal.fire(config);
}

/**
 * Raise the `flash` bag shared by HandleInertiaRequests.
 */
export function flashFromProps(flash) {
    if (!flash) {
        return;
    }

    const levels = {
        status: 'success',
        success: 'success',
        error: 'error',
        warning: 'warning',
        info: 'info',
    };

    Object.entries(levels).forEach(([key, icon]) => {
        if (flash[key]) {
            swalAlert(icon, flash[key], icon === 'error' ? 7000 : 3000);
        }
    });
}

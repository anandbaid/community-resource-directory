<script type="text/javascript">
    let status = "{!! __(Session::pull('status')) !!}"
    let success = "{!! __(Session::pull('success')) !!}"
    let error = "{!! __(Session::pull('error')) !!}"
    let warning = "{!! __(Session::pull('warning')) !!}"
    let info = "{!! __(Session::pull('info')) !!}"
    //auto close SWAL alert after 3s
    const swalAlert = (type, message, timer = 3000) => {
        let swalConfig = {
            icon: type,
            html: message.replace(/\n/g, '<br>'),
            // showConfirmButton: false,
            timer: timer
        }
        if (type == 'error')
            swalConfig.title = "{{ __('Something went wrong!') }}"
        Swal.fire(swalConfig)
    }
    const swalAlert2 = (type, message, redirect) => {
        Swal.fire({
            icon: type,
            text: message,
            showCancelButton: false,
            confirmButtonText: 'Ok',
            reverseButtons: true
        }).then((result) => {
            if (result.value) {
                window.location.href = redirect
            }
        });
    }
    if (status) {
        swalAlert('success', status)
        status = ''
    }
    if (success) {
        swalAlert('success', success)
        success = ''
    }
    if (error) {
        swalAlert('error', error)
        error = ''
    }
    if (warning) {
        swalAlert('warning', warning)
        warning = ''
    }
    if (info) {
        swalAlert('info', info)
        info = ''
    }
</script>
@if ($errors->any())
    @php($msg = implode(',', $errors->all()))
    <script type="text/javascript">
        Swal.fire({
            icon: 'error',
            title: "{{ __('Something went wrong!') }}",
            text: "{{ $msg }}",
        })
    </script>
@endif
@if (Session::has('csrfExpire'))
    <script type="text/javascript">
        Swal.fire({
            icon: 'warning',
            title: "{{ Session::pull('csrfExpire') }}",
            confirmButtonText: "{{ __('Reload') }}"
        }).then((result) => {
            if (result.isDismissed || result.isConfirmed) {
                window.location.reload();
            }
        })
    </script>
@endif

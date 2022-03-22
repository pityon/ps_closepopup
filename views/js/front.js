$(document).ready(function() {

    function setCookie(cname, cvalue, expiry_minutes) {
        const d = new Date();
        d.setTime(d.getTime() + (expiry_minutes * 60 * 1000));
        let expires = "expires=" + d.toUTCString();
        document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
    }

    const closepopup_modal = $('#closepopup.modal');
    if (closepopup_modal.length) {
        const closepopup_evt = function(event) {
            if(event.clientY <= 0 || event.clientX <= 0 || (event.clientX >= window.innerWidth || event.clientY >= window.innerHeight))
            {
                closepopup_modal.modal('show');
            }
        }
        const interval = parseInt(closepopup_modal.attr('data-custom-interval'));
        closepopup_modal.modal('hide');
        closepopup_modal.on('hide.bs.modal', function () {
            setCookie('closepopup_cookie', true, interval);
            document.removeEventListener("mouseleave", closepopup_evt);
        });

        document.addEventListener("mouseleave", closepopup_evt);
    }
});
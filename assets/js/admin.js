jQuery(document).ready(function($){
    "use strict";

    $('.puba-wrapper a[href^="#"]').on('click',function(e) {
        e.preventDefault();
        var target = this.hash;
        var $target = $(target);
        $('html, body').stop().animate({
            'scrollTop': $target.offset().top
        }, 900, 'swing', function () {
            window.location.hash = target;
        });
    });

    let check_status_and_update_notifications = function()
    {
        let $status = $('#status-blob');

        $.ajax( {
            url: bmpubaApiSettings.health_check,
            method: 'POST',
            beforeSend: function ( xhr ) {
                xhr.setRequestHeader( 'X-WP-Nonce', bmpubaApiSettings.nonce );
            },
            data:{},
        } ).done( function ( response ) {
            $status.removeClass('red green black yellow orange').addClass(response.color);
            $status.attr('title', response.message);

            if( typeof response.action !== 'undefined' && response.action === 'refresh') {
                Swal.fire({
                    title: response.message,
                    showDenyButton: true,
                    showCancelButton: false,
                    confirmButtonText: bmpubaApiSettings.msg_refresh,
                    denyButtonText: bmpubaApiSettings.msg_ignore,
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.reload();
                    } else if (result.isDenied) {

                    }
                });
            }

        } ).error( function(jqXHR, textStatus){
            $status.removeClass('red green black yellow orange').addClass('red');
            $status.attr('title', 'Cant connect to WP RestApi!');
        } );
    };

    if( $('#status-blob').length ) {
        check_status_and_update_notifications();
        let health = window.setInterval(check_status_and_update_notifications, 30000);

        $('#status-blob').on('click', function(e){
            let title = $(this).attr('title');
            Swal.fire({
                title: title
            });
        });
    }

    $('#auto-embed-pixel').on('change', function(e){
        let auto_embed_pixel;

        if( $(this).prop('checked') ) {
            auto_embed_pixel = 'on';
        } else {
            auto_embed_pixel = 'off';
        }

        $.ajax( {
            url: bmpubaApiSettings.update_settings,
            method: 'POST',
            beforeSend: function ( xhr ) {
                xhr.setRequestHeader( 'X-WP-Nonce', bmpubaApiSettings.nonce );
            },
            data: {
                'auto_embed_pixel': auto_embed_pixel
            },
        } ).done( function ( response ) {

            Swal.fire({
                position: 'bottom-end',
                icon: 'success',
                title: bmpubaApiSettings.msg_saved,
                showConfirmButton: false,
                timer: 1500
            });

        } ).error( function(jqXHR, textStatus){
            Swal.fire({
                position: 'bottom-end',
                icon: 'error',
                title: textStatus,
                showConfirmButton: false,
                timer: 1500
            });
        } );
    });

    $('.ajax-form input').on('change', function(){
        let form = $(this).closest('form.ajax-form').serializeArray();
        console.log(form);

        $.ajax( {
            url: bmpubaApiSettings.update_settings,
            method: 'POST',
            beforeSend: function ( xhr ) {
                xhr.setRequestHeader( 'X-WP-Nonce', bmpubaApiSettings.nonce );
            },
            data: form,
        } ).done( function ( response ) {

            Swal.fire({
                position: 'bottom-end',
                icon: 'success',
                title: bmpubaApiSettings.msg_saved,
                showConfirmButton: false,
                timer: 1000
            });

        } ).error( function(jqXHR, textStatus){

            Swal.fire({
                position: 'bottom-end',
                icon: 'error',
                title: textStatus,
                showConfirmButton: false,
                timer: 1500
            });

        } );
    });

    $('.reconnect').on('click', function(e){
        e.preventDefault();

        Swal.fire({
            title: bmpubaApiSettings.msg_reconnect_title,
            showDenyButton: true,
            showCancelButton: false,
            confirmButtonText: bmpubaApiSettings.msg_reconnect_no,
            denyButtonText: bmpubaApiSettings.msg_reconnect_yes,
        }).then((result) => {
            if (result.isConfirmed) {
            } else if (result.isDenied) {
                $.ajax( {
                    url: bmpubaApiSettings.reconnect,
                    method: 'POST',
                    beforeSend: function ( xhr ) {
                        xhr.setRequestHeader( 'X-WP-Nonce', bmpubaApiSettings.nonce );
                    },
                    data: {},
                } ).done( function ( response ) {

                    window.location.reload();

                } ).error( function(jqXHR, textStatus){
                } );
            }
        });
    });
});
/**
 * (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
 *
 * All Rights Reserved. See copyright.txt for details and a complete list of authors.
 * Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
 * $Id$
 */

/**
 * This function handles the run now request, displaying a loading and show server response in a modal.
 *
 * @param event
 */
function runNow(event) {

    event.preventDefault();

    var target = $('.modal.fade:not(.in)').first();
    $('.modal-content', target).html(
        '<div class="modal-body text-center">' +
        '<p>'+tr('Loading...')+'</p>' +
        '<i class="fa fa-spinner fa-spin" aria-hidden="true"></i></div>'
    );

    target.modal({keyboard:false, backdrop:'static'});
    target.modal('show');

    // Refresh logs when modal closes
    $(target).on('hide.bs.modal',function(){
        var url = window.location.href.split('#')[0];
        window.location =  url +'#contenttabs_admin_schedulers-1'; // keep user in 'Schedulers' tab
        window.location.reload();
    });

    var url = $(event.currentTarget).attr('href');

    $.ajax(url, {
        method: 'GET',
        preventGlobalErrorHandle: true, // In case of error, this request handles the error display.
        success: function (data) {
            $('.modal-content', target).html(data);
        },
        error: function (){
            $('.modal-content', target).html(
                '<div class="modal-header">' +
                '<h4 class="modal-title" id="myModalLabel">'+tr('Error')+'</h4>' +
                '</div>' +
                '<div class="modal-body">'+tr('An error occurred. Try again or contact the administrator.')+'</div>'+
                '<div class="modal-footer">'+
                '<button type="button" class="btn btn-secondary btn-dismiss btn-sm" data-bs-dismiss="modal">Close</button>' +
                '</div>'
            );
        }
    });

}



/**
 * This function handles the run now in background request.
 *
 * @param event
 */
function runNowBackground(event) {
    event.preventDefault();
    $('#admin_schedulers').tikiModal('Loading..');

    var url = $(event.currentTarget).attr('href');
    $.ajax(url, {
        method: 'GET',
        preventGlobalErrorHandle: true, // In case of error, this request handles the error display.
        success: function (data) {
            $('#admin_schedulers').tikiModal();
            var response = JSON.parse(data);
            feedback(response.title, "success", false);
        },
        error: function (){
            $('#admin_schedulers').tikiModal();
            feedback(tr(tr('An error occurred. Try again or contact the administrator.')), "error", false);
        }
    });

}


(function($) {
    let $modals = null;
    let $modal = null;

    $('.wikiplugin-include-replace').on('click', function() {
        let index = $(this).data().index;
        let page = $(this).data().page;
        let ticket = $(this).data().ticket;
        let tpl = '<div class="modal-content">' +
                    '<div class="modal-header">' +
                        '<h4 class="modal-title">' + tr('Replace include plugin') + '</h4>' +
                    '</div>' +
                    '<div class="modal-body">' +
                        '<div class="row">' +
                            '<div class="col-md-12">' +
                                '<p>' + tr('This will replace the Include plugin with its content, allowing to customize without changing the original Include content.') + '</p>' +
                                '<p>' + tr('Are you sure you want continue?') + '</p>' +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                    '<div class="modal-footer">' +
                        '<button type="button" class="btn btn-secondary btn-dismiss" data-bs-dismiss="modal">' + tr('Cancel') + '</button>' +
                        '<input onclick="$.fn.replaceInclude(\'' + index + '\', \'' + page + '\', \'' + ticket + '\')" type="button" class="btn btn-primary" value="' + tr('Confirm') + '"/>' +
                    '</div>' +
                '</div>';

        $modals = $('.modal.fade');
        $modal = $modals.filter(':not(.show)').first();
        $modal.find('.modal-dialog').html(tpl);
        $modal.modal('show');
    });

    $.fn.replaceInclude = function(index, page, ticket) {
        $modal.modal('hide');
        ajaxLoadingShow('page-data');

        $.ajax({
            type: 'POST',
            url: 'tiki-ajax_services.php',
            dataType: 'json',
            data: {
                controller: 'plugin',
                action: 'replace',
                type: 'include',
                page: page,
                ticket: ticket,
                index: index,
                appendParams: 1,
                message: tr('Include Plugin was replaced with its content.'),
                params: {
                    replace: 1,
                }
            }
        }).always(function() {
            window.location = window.location.href;
        });
    };

})(jQuery);
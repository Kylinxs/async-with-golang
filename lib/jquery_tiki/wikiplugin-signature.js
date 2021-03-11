function initSignature(id, isEditable)
{
    let container = $(id);
    let canvas = container.find('.signature-pad')[0];

    function resizeCanvas()
    {
        let ratio = Math.max(window.devicePixelRatio || 1, 1);
        canvas.width = canvas.offsetWidth * ratio;
        canvas.height = canvas.offsetHeight * ratio;
        canvas.getContext("2d").scale(ratio, ratio);
    }

    window.onresize = resizeCanvas;
    resizeCanvas();

    let signaturePad = new SignaturePad(canvas, {
        backgroundColor: 'rgb(255, 255, 255)'
    });
    signaturePad.off();

    if (isEditable) {
        signaturePad.on();
        container.find('.clear').click(function () {
            signaturePad.clear();
        });

        container.find('.save').click(function () {
            let button = $(this);
            let container = button.parents('.signature-container')[0];
            let index = $(container).data('index');

            let data = {
                controller: 'plugin',
                action: 'replace',
                ticket: $('input[name="csrf_'+index+'"]').val(),
                page: $('input[name="page_'+index+'"]').val(),
                message: tr('Signature added'),
                type: 'signature',
                content: signaturePad.toDataURL(),
                index: index,
                params: []
            };

            // Disable container buttons
            button.parents('.buttons').find('button').each(function(i, e){
                $(e).attr('disabled', 'disabled');
            });

            button.html(tr('Saving'));

            $.ajax({
                type: 'POST',
                url: 'tiki-ajax_services.php',
                dataType: 'json',
                data: data,
                success: function() {
                    window.location.reload();
                },
                error: function(xhr, status, message) {
                    button.html(tr('Save'));
                    button.parents('.buttons').find('button').each(function(i, e){
                        $(e).removeAttr('disabled');
                    });
                }
            });
        });
    }
}

$('.add-signature').on('click', function () {
    let index = $(this).data('index');
    let editPerm = $(this).data('editable');
    $(this).hide();
    $('#signature_' + index).show();
    initSignature("#signature_" + index, editPerm);
});


$('.cancel-signature').on('click', function () {
    let container = $(this).parents('.signature-container')[0];
    let index = $(container).data('index');
    $(container).hide();
    $('#add-signature-' + index).show();
});

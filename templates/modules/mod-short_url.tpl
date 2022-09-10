{* $Id$ *}
{strip}
    {tikimodule error=$module_params.error title=$tpl_module_title name="short_url" flip=$module_params.flip decorations=$module_params.decorations nobox=$module_params.nobox notitle=$module_params.notitle}
        <div id="short-url-module">
            <div>
                <div id="short-url-error" class="alert alert-danger" style="display:none"></div>
                <button id="short-url-get" class="btn btn-primary btn-sm form-control">{icon name="link"} {tr}Get short URL{/tr}</button>
            </div>

            <div id="short-url-link"style="display:none">
                <div class="mb-3">
                    <input type="text" class="form-control" readonly="readonly">
                </div>
                <button id="short-url-copy" class="btn btn-primary btn-sm form-control">{icon name="clipboard"} {tr}Copy{/tr}</button>
            </div>

        </div>
    {/tikimodule}
{/strip}

{jq}
    function getShortUrl() {
        $('#short-url-error').hide();
        $('#short-url-get').attr('disabled', 'disabled');

        $.ajax({
            method: 'GET',
            url: 'tiki-short_url.php?url='+encodeURIComponent(window.location.href)+'&title='+encodeURIComponent(document.title)+'&module=y',
            dataType: 'json',
            success: function (data) {
                if (data.error != undefined) {
                    $('#short-url-error').html(data.message);
                    $('#short-url-error').show();
                } else {
                    $('#short-url-link input').val(data.url);
                    $('#short-url-get').hide();
                    $('#short-url-link').show();
                }
            },
            complete: function () {
                $('#short-url-get').removeAttr('disabled');
            },
            error: function (data) {
                // todo implement
            },
        });
    }

    $('#short-url-get').on('click', function() { getShortUrl(); });
    $('#short-url-copy').on('click', function() {
        $('#short-url-link input[type="text"]').select();

        var defaultText = $('#short-url-copy').html();
        var successText = '{{icon name="check"}} {tr}Copied{/tr}';
        var errorText = '{{icon name="close"}} {tr}Error copying url{/tr}';

        try {
            document.execCommand('copy');
            $('#short-url-link input[type="text"]').blur();
            window.getSelection().removeAllRanges();

            $('#short-url-copy').addClass('btn-success').attr('disabled', 'disabled').html(successText);
            setTimeout(function(){
                $('#short-url-copy').removeClass('btn-success').removeAttr('disabled').html(defaultText);
            }, 1000);

        }
        catch (err) {
            $('#short-url-copy').addClass('btn-danger').html(errorText);
            setTimeout(function(){
                $('#short-url-copy').removeClass('btn-danger').html(defaultText);
            }, 1000);
        }
    });
{/jq}

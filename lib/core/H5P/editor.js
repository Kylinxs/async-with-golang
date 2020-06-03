(function ($) {
    H5PEditor.init = function () {
        H5PEditor.$ = H5P.jQuery;
        H5PEditor.basePath = H5PIntegration.editor.libraryUrl;
        H5PEditor.fileIcon = H5PIntegration.editor.fileIcon;
        H5PEditor.ajaxPath = H5PIntegration.editor.ajaxPath;
        H5PEditor.filesPath = H5PIntegration.editor.filesPath;
        H5PEditor.apiVersion = H5PIntegration.editor.apiVersion;
        H5PEditor.copyrightSemantics = H5PIntegration.editor.copyrightSemantics;
        H5PEditor.metadataSemantics = H5PIntegration.editor.metadataSemantics;
        H5PEditor.assets = H5PIntegration.editor.assets;
        H5PEditor.baseUrl = '';
        if (H5PIntegration.editor.nodeVersionId !== undefined) {
            H5PEditor.contentId = H5PIntegration.editor.nodeVersionId;
        }

        var $editor = $('.h5p-editor');
        var $library = $('input[name="library"]');
        var $params = $('input[name="parameters"]');
        var h5peditor = new ns.Editor($library.val(), $params.val(), $editor[0]);

        var timer = setInterval(function () {
            // i don't seem to be able to turn off the extra title field, so hide the tiki one and update it from there
            // also, there doesn't seem to be a usable event triggered when the form is created, so keep checking (yuk)
            if (typeof h5peditor.selector !== "undefined" && typeof h5peditor.selector.form !== "undefined") {
                var mainTitleField = h5peditor.selector.form.metadataForm.getExtraTitleField();
                mainTitleField.$input.val($("#edit-title").val());
                clearInterval(timer);
            }

        }, 200);

        $('.content-form').submit(function (event) {
            if (h5peditor !== undefined) {
                let params = h5peditor.getParams();

                if (params !== undefined) {
                    // Validate mandatory main title. Prevent submitting if that's not set.
                    // Deliberatly doing it after getParams(), so that any other validation
                    // problems are also revealed
                    if (!h5peditor.isMainTitleSet()) {
                        return event.preventDefault();
                    }

                    var mainTitleField = h5peditor.selector.form.metadataForm.getExtraTitleField();
                    $("#edit-title").val(mainTitleField.$input.val());

                    // Set main library
                    $library.val(h5peditor.getLibrary());

                    // Set params
                    $params.val(JSON.stringify(params));

                    // TODO - Calculate & set max score
                    // $maxscore.val(h5peditor.getMaxScore(params.params));

                    let e = event;

                    h5peditor.getContent(function () {
                        if ($(".h5p-errors:not(:empty)", h5peditor.iframeWindow.document.body).length > 0) {
                            return e.preventDefault();
                        }
                    });
                }
            }
        });
    };

    H5PEditor.getAjaxUrl = function (action, parameters) {
        var url = H5PIntegration.editor.ajaxPath + action.replace(/-/g, "_");

        if (parameters !== undefined) {
            for (var property in parameters) {
                if (parameters.hasOwnProperty(property)) {
                    url += '&' + property + '=' + parameters[property];
                }
            }
        }

        return url;
    };

    $(document).ready(H5PEditor.init);
})(H5P.jQuery);


/**
 * Tiki support js for toast ui and markdown wiki syntax
 */

function tikiToastEditor(options)
{
    if (!options.domId) {
        console.error("No element id 'domId' option provided to tikiToast init");
        return;
    }

    const reTikiPlugin = /\{(\w{2,}) .*}/,      // regex for tiki plugins (inline)
        reWikiLink = /\((\w*)\((.*?)\)\)/,           // regex for wiki link syntax
        Editor = toastui.Editor,                // global toastui Editor factory
        inputDomId = options.domId,             // the hidden input to hold a copy of the markdown
        editorDomId = inputDomId + "_editor";   // div to contain the toast ui editor
    let thisEditor;

    if (typeof window.tuiEditors !== 'object') {
        window.tuiEditors = {};
    }

    const execAutoSave = delayedExecutor(500, function () {
        auto_save(inputDomId);
    });

    let tuiOptions = $.extend(true, {}, {
        el: document.querySelector("#" + editorDomId),
        events: {
            change: function () {
                // update the hidden input with the markdown content
                document.querySelector("#" + inputDomId).value = thisEditor.getMarkdown();
                execAutoSave();
            },
            load: function (editor) {
                // maybe some more init here?
            },
            keyup: function (editorType, ev) {
                if (ev.key === '(') {
                    const popup = document.createElement('ul');
                    // ...

                    thisEditor.addWidget(popup, 'top');
                }
            },
        },
        plugins: [tikiPlugin],
        widgetRules: [{
            rule: reWikiLink,
            toDOM(text)
            {
                const match = text.match(reWikiLink),
                    anchorElement = document.createElement('a');

                const parts = match[2].split("|");

                let page, semantic, anchor = "", label;

                semantic = match[1];

                if (parts.length === 3) {
                    page = parts[0];
                    anchor = parts[1];
                    label = parts[2];
                } else if (parts.length === 2) {
                    page = parts[0];
                    label = parts[1];
                } else {
                    page = match[2];
                    label = match[2];
                }

                anchorElement.innerText = label;
                anchorElement.dataset.page = page;
                anchorElement.dataset.anchor = anchor;
                anchorElement.dataset.semantic = semantic;
                anchorElement.classList.add("wiki-link");
                anchorElement.onclick = function (event) {
                    displayDialog( this, 1, options.domId, event.target);
                };

                return anchorElement;
            }
        }],
    }, options);

// create the editor
    thisEditor = new Editor(tuiOptions);
    window.tuiEditors[inputDomId] = thisEditor;

}
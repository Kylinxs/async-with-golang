
// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

$(document).ready(function() {

    let tocDirty = false;

    const setupStructure = function() {
        const sortableOptions = {
            group: {
                name: 'shared',
            },
            dataIdAttr: 'data-id',
            ghostClass: 'draggable-background',
            animation: 150,
            // invertSwap: true,
            swapThreshold: 0.65,
            direction: 'vertical',
            forceFallback: true,
            fallbackOnBody: true,
            // Called when dragging element changes position
            onAdd: function(event) {
                const pageName = $(event.item).data('page-name');
                if (!jqueryTiki.structurePageRepeat && $(`.structure-container li .link:contains(${pageName})`).length > 0) {
                    $.getJSON($.service('object', 'report_error', {message:tr("Page only allowed once in a structure")}));
                    $(event.item).remove();
                }
            },
            onEnd: function(event) {
                if ($(".save_structure:visible").length === 0) {
                    $(".save_structure").show("fast").parent().show("fast");
                    tocDirty = true;
                }
            }
        };

        document.querySelectorAll('.admintoc').forEach(function(el) {
            new Sortable(el, sortableOptions);
        });

        $(".flip-children", ".admintoc").click(function (event)  {
            const $this = $(this),
                $children = $this.parents("li.admintoclevel:first").find("ol.admintoc" + ( event.altKey ? "" : ":first")).parent();

            if ($children.is(":visible")) {
                $this.find(".icon").setIcon("caret-right");
                if (event.altKey) {
                    $children.find(".icon-caret-down").setIcon("caret-right");
                }
                $children.hide("fast");
            } else {
                $this.find(".icon").setIcon("caret-down");
                if (event.altKey) {
                    $children.find(".icon-caret-right").setIcon("caret-down");
                }
               $children.show("fast");
            }
        });


        $(".page-alias-input").on("change", function () {
            $(".save_structure").show("fast").parent().show("fast");
            tocDirty = true;
        }).on("click", function () {    // for Firefox
            $(this).focus().selection($(this).val().length);
        });

        const sortableListOptions = {
            group: {
                name: 'shared',
                pull: 'clone',
                put: false // Do not allow items to be put into this list
            },
            sort: false,
            animation: 500,
            onEnd: function(event) {
                const pageName = $(event.item).data('page-name');

                if ($(event.to).closest('.structure-container').length > 0) {
                    $(event.item).text('');
                    $(event.item).removeClass('ui-state-default').addClass('row admintoclevel new').append(`
                        <div class="col-sm-12">
                            <label>${pageName}</label>
                            <div class="actions input-group input-group-sm mb-2"><input type="text" class="page-alias-input form-control" value="" placeholder="Page alias..."></div>
                        </div>
                        <div class="col-sm-12">
                            <ol class="admintoc"></ol>
                        </div>
                    `);
                    new Sortable($(event.item).find('.admintoc')[0], sortableOptions);

                    $(".save_structure").show("fast").parent().show("fast");
                    tocDirty = true;
                }
            }
        };

        new Sortable(document.querySelector('#page_list_container'), sortableListOptions);
    };

    $(window).on("beforeunload", function() {
        if (tocDirty) {
            return tr("You have unsaved changes to your structure, are you sure you want to leave the page without saving?");
        }
    });

    setupStructure();

    $(".save_structure").click(function(){

        const $sortable = $(this).parent().find(".admintoc:first");
        $sortable.tikiModal(tr("Saving..."));

        let fakeId = 1000000;
        $(".admintoclevel.new").each(function() {
            $(this).attr("id", "node_" + fakeId);
            $(this).data("id", fakeId);
            fakeId++;
        });
        // Adjusted to previous nestedSortable plugin result array
        const arr = [{
            item_id: 'root',
            parent_id: 'none',
            structure_id: $sortable.data("params").page_ref_id,
            depth: 0
        }];

        $sortable.find('li.admintoclevel').each(function() {
            const parentId = $(this).parent().closest('li.admintoclevel').data('id');
            const itemId = $(this).data('id');
            const pageAlias = $(this).find('.page-alias-input').val();
            const structureId = $sortable.data("params").page_ref_id;
            const pageName = $(this).find('> div').text().trim();
            const obj = {
                item_id: itemId,
                parent_id: parentId || 'root',
                structure_id: structureId,
                page_name: pageName.split('\n')[0],
                page_alias: pageAlias,
                depth: 1
                // el: $(this)[0] // Debug only
            };

            let item = arr.find(el => el.parent_id === parentId);
            if (!parentId) {
                obj.depth = 1;
            } else if (item) {
                obj.depth = item.depth;
            } else {
                obj.depth = arr[arr.length - 1].depth + 1;
            }

            arr.push(obj);
        });

        // console.log(arr, $sortable.data("params"))

        $.post($.service("wiki_structure", "save_structure"), {data: $.toJSON(arr), params: $.toJSON($sortable.data("params"))}, function (data) {
            $sortable.tikiModal();
            if (data) {
                $sortable.replaceWith(data.html);
                setupStructure();
                $(".save_structure").hide();
                tocDirty = false;
            }
        }, "json");
        return false;
    });

});

function movePageToStructure(element) {
    let id = $(element).parents(".admintoclevel:first").attr("id").match(/\d*$/);
    if (id) {
        id = id[0];
    }
    $("input[name=page_ref_id]", "#move_dialog").val(id);
    $("#move_dialog").dialog({
        title: tr("Move page")
    });
}

function addNewPage(element) {
    let id = $(element).parents(".admintoclevel:first").attr("id").match(/\d*$/);
    if (id) {
        id = id[0];
    }
    $("input[name=page_ref_id]", "#newpage_dialog").val(id);

    $("#newpage_dialog").dialog({
        title: tr("Add page")

    });
}

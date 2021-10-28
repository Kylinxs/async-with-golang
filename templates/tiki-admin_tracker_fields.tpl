{* $Id$ *}
{extends "layout_view.tpl"}

{block name="title"}
    {title help="Adding fields to a tracker" url="`$trackerId|sefurl:'trackerfields'`"}{tr}Tracker Fields:{/tr} {$tracker_info.name}{/title}
{/block}

{block name="navigation"}
    {assign var='title' value="{tr}Admin Tracker:{/tr} "|cat:$tracker_info.name|escape}
    <div class="t_navbar mb-4">
        <div class="btn-group">
            <a href="{service controller=tracker action=add_field trackerId=$trackerId}" class="btn btn-primary add-field">{icon name="create"} {tr}Add Field{/tr}</a>
            <a href="{bootstrap_modal controller=tracker action=import_fields trackerId=$trackerId}" class="btn btn-primary">{icon name="import"} {tr}Import Fields{/tr}</a>
        </div>
        {include file="tracker_actions.tpl"}
    </div>
{/block}

{block name="content"}
    <form class="form save-fields" method="post" action="{service controller=tracker action=save_fields}" role="form">
        <table id="fields" class="table table-responsive table-condensed table-hover">
            <thead>
                <tr>
                    <th>{select_all checkbox_names="fields[]"}</th>
                    <th>{tr}ID{/tr}</th>
                    <th>{tr}Name{/tr}</th>
                    <th>{tr}Type{/tr}</th>
                    {if $prefs.tracker_field_rules eq 'y'}
                        <th id="rulesColumn">{tr}Rules{/tr}</th>
                    {/if}
                    <th>{tr}List{/tr}</th>
                    <th>{tr}Title{/tr}</th>
                    <th>{tr}Search{/tr}</th>
                    <th>{tr}Public{/tr}</th>
                    <th>{tr}Mandatory{/tr}</th>
                    <th>{tr}Actions{/tr}</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
        <div class="mb-3 row">
            <div class="input-group col-sm-6">
                <select name="action" class="form-control">
                    <option value="save_fields">{tr}Save All{/tr}</option>
                    <option value="remove_fields">{tr}Remove Selected{/tr}</option>
                    <option value="export_fields">{tr}Export Selected{/tr}</option>
                </select>
                <input type="hidden" name="trackerId" value="{$trackerId|escape}">
                <input type="hidden" name="confirm" value="0">
                <button type="submit" class="btn btn-primary" name="submit">{tr}Go{/tr}</button>
            </div>
        </div>
    </form>

    {jq}
        var trackerId = {{$trackerId|escape}};
        $('.save-fields').submit(function () {
            var form = this, confirmed = false

            if ($(form.action).val() === 'remove_fields') {
                confirmed = confirm(tr('Do you really want to delete the selected fields?'));
                $(form.confirm).val(confirmed ? '1' : '0');

                if (! confirmed) {
                    return false;
                }
            }

            if ($(form.action).val() === 'export_fields') {
                var url = $.serviceUrl({ controller: 'tracker', action: 'export_fields' });
                var target = $('.modal.fade:not(.in)').first();
                $.post(url, $(form).serialize() + '&modal=1', function (data) {
                    $(".modal-content", target).html(data);
                    target.modal();
                });
                return false;

            } else {
                $.ajax($(form).attr('action'), {
                    type: 'POST',
                    data: $(form).serialize(),
                    dataType: 'json',
                    success: function () {
                        $container.tracker_load_fields(trackerId);
                        if ($(form.action).val() === 'remove_fields') {
                            $.fn.resetFieldsCache();
                        }
                    }
                });
            }
            return false;
        });
        var $container = $('.save-fields tbody');
        {{if $prefs.feature_jquery_ui eq 'y'}}
            $container.sortable({
                    update: function () {
                        $('td.id :hidden', this).each(function (k) {
                            $(this).val(k * 10);
                        });
                    }
                })
                .disableSelection()
                .css('cursor', 'move');
        {{/if}}

        $container.tracker_load_fields(trackerId);

        $('.add-field').clickModal({
            open: function () {
                $(this).tracker_add_field({
                    trackerId: trackerId
                });
            },
            success: function (data) {
                $container.tracker_load_fields(trackerId);
                $.fn.resetFieldsCache();

                $.closeModal({
                    done: function () {
                        if (! data.FORWARD) {
                            return false;
                        }

                        setTimeout(function () {
                            $.openModal({
                                remote: $.service(data.FORWARD.controller, data.FORWARD.action, data.FORWARD)
                            });
                        }, 0);
                    }
                });
            }
        });

        $('.import-fields').submit(function () {
            var form = this;
            $.ajax({
                url: $(form).attr('action'),
                type: 'POST',
                data: $(form).serialize(),
                success: function () {
                    $container.tracker_load_fields(trackerId);
                    $('textarea', form).val('');
                    tikitabs(1);
                }
            });

            return false;
        });

        var trackerFieldsUrl;

        $('.tracker-properties').clickModal({
            open: function () {
                var element = $("#fieldsDetails textarea");
                if (element) {
                    trackerFieldsUrl = lookupUrl(element);
                }
            },
        });

        function lookupUrl(selector) {
            var input = selector
                , filter = $(input).data('filters')
                , threshold = $(input).data('threshold')
                , format = $(input).data('format') || ''
                , sort = $(input).data('sort') || 'score_desc';

            var args = {
                maxRecords: threshold,
                format: format,
                sort_order: sort
            };

            var url;

            url = $.service('search', 'lookup', $.extend(args, {
                filter: filter
            }));

            return url;
        }

        $.fn.resetFieldsCache = function () {
            if (trackerFieldsUrl) {
                if ($.object_selector_cache && $.object_selector_cache.hasOwnProperty(trackerFieldsUrl)) {
                    delete $.object_selector_cache[trackerFieldsUrl];
                    trackerFieldsUrl = '';
                }
            }
        };
    {/jq}
{/block}


{extends "layout_view.tpl"}

{block name="title"}
    {title}{$title}{/title}
{/block}

{block name="navigation"}
    <div class="t_navbar mb-4">
        <a class="btn btn-link" href="{service controller=ml action=create}">{icon name=create} {tr}New{/tr}</a>
        <a class="btn btn-link" href="{service controller=ml action=list}">{icon name=list} {tr}Manage{/tr}</a>
    </div>
{/block}

{block name="content"}
    <div class="table-responsive">
        <form class="edit-ml" method="post" action="{service controller=ml action=edit mlmId=$model.mlmId}">
            <input type="hidden" name="trackerId" value="{$model.sourceTrackerId}">
            <div class="mb-3 row">
                <label class="col-form-label col-sm-2">{tr}Name{/tr}</label>
                <div class="col-sm-10">
                    <input class="form-control" type="text" name="name" value="{$model.name|escape}" required>
                </div>
            </div>
            <div class="mb-3 row">
                <label class="col-form-label col-sm-2">{tr}Description{/tr}</label>
                <div class="col-sm-10">
                    <textarea class="form-control" name="description">{$model.description|escape}</textarea>
                </div>
            </div>
            <div class="mb-3 row">
                <label class="col-form-label col-sm-2">{tr}Source tracker{/tr}</label>
                <div class="col-sm-10">
                    {object_link type=tracker id=$model.sourceTrackerId}
                </div>
            </div>
            <div class="mb-3 row">
                <label class="col-form-label col-sm-2">{tr}Dimension fields{/tr}</label>
                <div class="col-sm-10">
                    <select class="form-select" name="fields[]" multiple size="10">
                        <option value="">{tr}Item title{/tr}</option>
                        {foreach $fields as $field}
                            <option value="{$field.fieldId|escape}" {if in_array($field.fieldId, $model.trackerFields)}selected{/if}>{$field.name|escape}</option>
                        {/foreach}
                    </select>
                </div>
            </div>
            <div class="mb-3 row">
                <label class="col-form-label col-sm-2">{tr}Label field{/tr}</label>
                <div class="col-sm-10">
                    <select class="form-select" name="labelField">
                        <option value="">No label</option>
                        <option value="itemId">{tr}Item ID{/tr}</option>
                        <option value="itemTitle">{tr}Item title{/tr}</option>
                        {foreach $fields as $field}
                            <option value="{$field.fieldId|escape}" {if $field.fieldId eq $model.labelField}selected{/if}>{$field.name|escape}</option>
                        {/foreach}
                    </select>
                </div>
            </div>
            <div class="mb-3 row">
                <label class="col-form-label col-sm-2">{tr}Ignore items with empty values{/tr}</label>
                <div class="col-sm-10">
                    <input type="checkbox" name="ignoreEmpty" value="1" {if !empty($model.ignoreEmpty)} checked {/if}>
                </div>
            </div>
            <div class="mb-3 row">
                <label class="col-form-label col-sm-2">{tr}ML Model{/tr}</label>
                <div class="col-sm-10">
                    <table class="table model">
                        <thead>
                            <tr>
                                <th>
                                    {tr}Transformers and Learner{/tr}
                                    <a class="tikihelp alert-link" title="|RubixML Help" target="tikihelp" href="https://doc.tiki.org/Machine-Learning">
                                        {icon name="help"}
                                    </a>
                                </th>
                                <th>{tr}Arguments{/tr}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="d-none">
                                <td>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">{icon name=sort}</span>
                                        <input class="learner form-control" disabled>
                                    </div>
                                </td>
                                <td>
                                    <a href="{service controller=ml action=model_args mlmId=$model.mlmId}" class="arguments"></a>
                                    <textarea class="serialized-args d-none"></textarea>
                                </td>
                                <td class="text-end"><button class="remove btn-sm btn-outline-warning">{icon name=remove}</button></td>
                            </tr>
                            {foreach $model.instances as $instance}
                                <tr>
                                    <td>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">{icon name=sort}</span>
                                            <input class="learner form-control" disabled value="{$instance.learner|escape}">
                                        </div>
                                    </td>
                                    <td>
                                        <a href="{service controller=ml action=model_args mlmId=$model.mlmId class=$instance.class}" class="arguments">{$instance.instance|escape}</a>
                                        <textarea class="serialized-args d-none">{$instance.serialized_args}</textarea>
                                    </td>
                                    <td class="text-end"><button class="remove btn-sm btn-outline-warning">{icon name=remove}</button></td>
                                </tr>
                            {/foreach}
                        </tbody>
                        <tfoot>
                            <tr>
                                <td>
                                    <select class="selection form-select">
                                        <option disabled="disabled" selected="selected">{tr}Select...{/tr}</option>
                                        {foreach $learners as $label => $group}
                                            <optgroup label="{$label|escape}">
                                                {foreach $group.classes as $class}
                                                    <option value="{$group.path|escape}\{$class|escape}">{$class|escape}</option>
                                                {/foreach}
                                            </optgroup>
                                        {/foreach}
                                    </select>
                                </td>
                                <td>
                                    <a href="{service controller=ml action=model_args mlmId=$model.mlmId}" class="btn btn-secondary add-learner">{tr}Enter Arguments{/tr}</a>
                                    <textarea name="payload" class="d-none">{$model.payload}</textarea>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="mb-3 row submit">
                <div class="col-sm-10 offset-sm-2">
                    <input type="submit" class="btn btn-primary" value="{tr}Update{/tr}">
                </div>
            </div>
        </form>
    </div>
{/block}
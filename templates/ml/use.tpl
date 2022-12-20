{extends "layout_view.tpl"}

{block name="title"}
    {title}{$title}{/title}
{/block}

{block name="navigation"}
    <div class="t_navbar mb-4">
        {permission name=admin_machine_learning}
            <a class="btn btn-link" href="{service controller=ml action=create}">{icon name=create} {tr}New{/tr}</a>
        {/permission}
        <a class="btn btn-link" href="{service controller=ml action=list}">{icon name=list} {tr}Manage{/tr}</a>
    </div>
{/block}

{block name="content"}
    <p>{$model.description|escape|nl2br}</p>
    <p>{tr}Use this model by entering a sample information in the form below and execute a query against the trained model. This will produce results based on the chosen estimator and show you the most relevant matches or predict the result.{/tr}</p>
    <form class="use-ml" method="post" action="{service controller=ml action=use mlmId=$model.mlmId}">
        {trackerfields trackerId=$trackerId fields=$fields}
        <div class="mb-3 row">
            <label class="col-form-label col-sm-2">{tr}Type{/tr}</label>
            <div class="col-sm-10">
                <input type="radio" name="type" value="proba" {if $type neq 'predict'}checked{/if}>
                {tr}Probability (closest matches){/tr}
                <a href="https://doc.tiki.org/Machine-Learning" target="_blank" class="tikihelp text-info">{icon name=help}</a>
                <br/>
                <input type="radio" name="type" value="predict" {if $type eq 'predict'}checked{/if}> {tr}Prediction{/tr}
                <a href="https://doc.tiki.org/Machine-Learning" target="_blank" class="tikihelp text-info">{icon name=help}</a>
            </div>
        </div>
        <div class="submit">
            <input
                type="submit"
                class="btn btn-primary"
                value="{tr}Submit{/tr}"
            >
        </div>
    </form>
    {if $results}
        <br/>
        {foreach from=$results key=$itemId item=row}
            <p>
                {if !empty($row.proba)}
                    {$row.proba}%:
                {/if}
                <a href="{$itemId|sefurl:'trackeritem'}">
                    {foreach $row.fields as $field}
                        {trackeroutput field=$field}
                    {/foreach}
                </a>
            </p>
        {/foreach}
    {/if}
    {if $result}
        <br/>
        <p>{if $label}{$label}{else}{tr}Result{/tr}{/if}: {$result}</p>
    {/if}
{/block}

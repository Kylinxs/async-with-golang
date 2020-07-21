
{extends "layout_view.tpl"}

{block name="title"}
    {title}{$title}{/title}
{/block}

{block name="content"}
    <form method="post" action="{service controller=ml action=model_args}">
        <input type="hidden" name="class" value="{$class|escape}">
        {foreach $args as $arg}
        <div class="mb-3 row">
            <label class="col-form-label col-sm-4">{$arg.name|escape} ({$arg.arg_type})</label>
            <div class="col-sm-8">
                {if $arg.input_type eq 'text'}
                    <input class="form-control" type="text" name="args[{$arg.name|escape}]" value="{$arg.value|escape}" {if !empty($arg.required)}required{/if}>
                {elseif $arg.input_type eq 'rubix'}
                    {if strstr($arg.arg_type,  'Tokenizers')}
                        {assign var="classes" value=$tokenizers}
                    {elseif strstr($arg.arg_type, 'Trees')}
                        {assign var="classes" value=$trees}
                    {elseif strstr($arg.arg_type, 'Kernels')}
                        {assign var="classes" value=$kernels}
                    {elseif strstr($arg.arg_type, 'NeuralNet\Optimizers')}
                        {assign var="classes" value=$neuralnet_optimizers}
                    {elseif strstr($arg.arg_type, 'NeuralNet\CostFunctions')}
                        {assign var="classes" value=$neuralnet_cost_functions}
                    {elseif strstr($arg.arg_type, 'NeuralNet\ActivationFunctions')}
                        {assign var="classes" value=$neuralnet_activation_functions}
                    {elseif strstr($arg.arg_type, 'NeuralNet\Initializers')}
                        {assign var="classes" value=$neuralnet_initializers}
                    {elseif strstr($arg.arg_type, 'Learner')}
                        {assign var="classes" value=$learners}
                    {elseif strstr($arg.arg_type, 'Metrics')}
                        {assign var="classes" value=$metrics}
                    {else}
                        {assign var="classes" value=[]}
                    {/if}
                    {if !empty($classes.path)}
                        <select class="form-select ml-class" name="args[{$arg.name|escape}][class]" data-path="{$arg.name|escape}" data-href="{service controller=ml action=model_args}" {if !empty($arg.required)}required{/if}>
                            <option value=''>Default</option>
                            {foreach $classes.classes as $tokenizer}
                                <option value="{$classes.path}\{$tokenizer|escape}">{$tokenizer|escape}</option>
                            {/foreach}
                        </select>
                    {elseif $classes}
                        <select class="form-select ml-class" name="args[{$arg.name|escape}][class]" data-path="{$arg.name|escape}" data-href="{service controller=ml action=model_args}" {if !empty($arg.required)}required{/if}>
                            <option value=''>Default</option>
                            {foreach $classes as $label => $group}
                                <optgroup label="{$label|escape}">
                                {foreach $group.classes as $learner}
                                    <option value="{$group.path|escape}\{$learner|escape}">{$learner|escape}</option>
                                {/foreach}
                                </optgroup>
                            {/foreach}
                        </select>
                    {else}
                        <input class="form-control ml-class" type="text" name="args[{$arg.name|escape}][class]" data-path="{$arg.name|escape}" data-href="{service controller=ml action=model_args}">
                    {/if}
                    <textarea name="args[{$arg.name|escape}][args]" class="d-none">{$arg.args}</textarea>
                {elseif $arg.input_type eq 'layers'}
                    <div class="ml-layers">
                        <select class="form-select ml-class" name="args[{$arg.name|escape}][classes][]" data-path="{$arg.name|escape}" data-href="{service controller=ml action=model_args}" {if !empty($arg.required)}required{/if}>
                            <option value=''>Skip</option>
                            {foreach $neuralnet_layers.classes as $layer}
                                <option value="{$neuralnet_layers.path}\{$layer|escape}">{$layer|escape}</option>
                            {/foreach}
                        </select>
                        <textarea name="args[{$arg.name|escape}][args][]" class="d-none">{$arg.args}</textarea>
                    </div>
                    <input type="button" class="btn btn-primary btn-sm ml-add-layer" value="{tr}Add Layer{/tr}">
                {else}
                    {tr}Not Supported{/tr}
                {/if}
            </div>
        </div>
        {foreachelse}
        <p>No arguments available.</p>
        {/foreach}
        <div class="mb-3 submit">
            <div class="col-sm-9 offset-sm-3">
                <input type="submit" class="btn btn-primary" value="{tr}Submit{/tr}">
            </div>
        </div>
    </form>
{/block}
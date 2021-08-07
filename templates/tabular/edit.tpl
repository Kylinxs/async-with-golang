
{extends "layout_view.tpl"}

{block name="title"}
    {title}{$title}{/title}
{/block}

{block name="navigation"}
    {include file='templates/tabular/include_tabular_navbar.tpl' mode='edit'}
{/block}

{block name="content"}
    <div class="table-responsive">
        <form class="edit-tabular" method="post" action="{service controller=tabular action=edit tabularId=$tabularId}">
            <div class="mb-3 row">
                <label class="col-form-label col-sm-2">{tr}Name{/tr}</label>
                <div class="col-sm-10">
                    <input class="form-control" type="text" name="name" value="{$name|escape}" required>
                </div>
            </div>
            {if $has_odbc}
            <div class="mb-3 row">
                <label class="form-check-label col-sm-2">{tr}External ODBC source?{/tr}</label>
                <div class="col-sm-10">
                    <div class="form-check">
                        <input class="form-check-input use-odbc" type="checkbox" name="use_odbc" {if $odbc_config}checked{/if} value="1">
                    </div>
                </div>
            </div>
            <div class="odbc-container" {if !$odbc_config}style="display: none"{/if}>
                <div class="mb-3 row">
                    <label class="col-form-label col-sm-2 offset-sm-1">{tr}DSN{/tr}</label>
                    <div class="col-sm-9">
                        <input class="form-control" type="text" name="odbc[dsn]" value="{$odbc_config.dsn|escape}">
                    </div>
                </div>
                <div class="mb-3 row">
                    <label class="col-form-label col-sm-2 offset-sm-1">{tr}User{/tr}</label>
                    <div class="col-sm-9">
                        <input class="form-control" type="text" name="odbc[user]" value="{$odbc_config.user|escape}">
                    </div>
                </div>
                <div class="mb-3 row">
                    <label class="col-form-label col-sm-2 offset-sm-1">{tr}Password{/tr}</label>
                    <div class="col-sm-9">
                        <input class="form-control" type="password" name="odbc[password]" value="{$odbc_config.password|escape}" autocomplete="new-password">
                    </div>
                </div>
                <div class="mb-3 row">
                    <label class="col-form-label col-sm-2 offset-sm-1">{tr}Table/Schema{/tr}</label>
                    <div class="col-sm-9">
                        <input class="form-control" type="text" name="odbc[table]" value="{$odbc_config.table|escape}">
                    </div>
                </div>
                <div class="mb-3 row">
                    <label class="col-form-label col-sm-2 offset-sm-1">{tr}Sync deletes{/tr}</label>
                    <div class="col-sm-9">
                        <input class="form-check-input" type="checkbox" name="odbc[sync_deletes]" {if !empty($odbc_config.sync_deletes)}checked{/if} value="1">
                        <a class="tikihelp text-warning" title="{tr}Synchronization:{/tr} {tr}Deleting a tracker item or clearing the local tracker will also erase items remotely. Use with care!{/tr}">
                            {icon name=warning}
                        </a>
                    </div>
                </div>
            </div>
            {/if}
            <div class="mb-3 row">
                <label class="form-check-label col-sm-2">
                    {tr}External API source?{/tr}
                    <a class="tikihelp text-info" title="{tr}Hook External API:{/tr} {tr}Configure authentication through Admin Data Sources (DSN).{/tr}">
                        {icon name=information}
                    </a>
                </label>
                <div class="col-sm-10">
                    <div class="form-check">
                        <input class="form-check-input use-api" type="checkbox" name="use_api" {if $api_config}checked{/if} value="1">
                    </div>
                </div>
            </div>
            <div class="api-container" {if !$api_config}style="display: none"{/if}>
                <div class="mb-3 row">
                    <label class="col-form-label col-sm-2 offset-sm-1">
                        {tr}List endpoint URL{/tr}
                        <a class="tikihelp text-info" title="{tr}List URL:{/tr} {tr}URL of the endpoint to read data from.{/tr}">
                            {icon name=information}
                        </a>
                    </label>
                    <div class="col-sm-9">
                        <input class="form-control" type="text" name="api[list_url]" value="{$api_config.list_url|escape}">
                    </div>
                </div>
                <div class="mb-3 row">
                    <label class="col-form-label col-sm-2 offset-sm-1">
                        {tr}List endpoint method{/tr}
                        <a class="tikihelp text-info" title="{tr}List method:{/tr} {tr}HTTP method to access the endpoint. Usually GET.{/tr}">
                            {icon name=information}
                        </a>
                    </label>
                    <div class="col-sm-9">
                        <select class="form-control" name="api[list_method]">
                            <option value=""></option>
                            <option value="GET" {if $api_config.list_method eq 'GET'}selected{/if}>GET</option>
                            <option value="PUT" {if $api_config.list_method eq 'PUT'}selected{/if}>PUT</option>
                            <option value="POST" {if $api_config.list_method eq 'POST'}selected{/if}>POST</option>
                            <option value="PATCH" {if $api_config.list_method eq 'PATCH'}selected{/if}>PATCH</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label class="col-form-label col-sm-2 offset-sm-1">
                        {tr}List endpoint parameters{/tr}
                        <a class="tikihelp text-info" title="{tr}List parameters:{/tr} {tr}Parameters to submit with the list endpoint request (mainly for PUT and POST requests).{/tr}">
                            {icon name=information}
                        </a>
                    </label>
                    <div class="col-sm-9">
                        <input class="form-control" type="text" name="api[list_parameters]" value="{$api_config.list_parameters|escape}">
                    </div>
                </div>
                <div class="mb-3 row">
                    <label class="col-form-label col-sm-2 offset-sm-1">
                        {tr}List endpoint data path{/tr}
                        <a class="tikihelp text-info" title="{tr}List Data Path:{/tr} {tr}Reading response data might require traversing a data structure. Define the data path here with parent/child relations separated by a dot. E.g. use 'response.data' if response JSON is wrapped in 'response' => { 'data' => ... } structure OR .time_entry if response JSON is of type [ { 'time_entry' => ... }, { 'time_entry' => ... } ].{/tr}">
                            {icon name=information}
                        </a>
                    </label>
                    <div class="col-sm-9">
                        <input class="form-control" type="text" name="api[list_data_path]" value="{$api_config.list_data_path|escape}">
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-form-label col-sm-2 offset-sm-1">
                        {tr}List endpoint data mapping{/tr}
                        <a class="tikihelp text-info" title="{tr}Data Mapping:{/tr} {tr}Instead of mapping fields to data keys directly (e.g. JSON or NDJSON) use special mapping here. Leave empty to map directly.{/tr}">
                            {icon name=information}
                        </a>
                    </label>
                    <div class="col-sm-9">
                        <textarea class="form-control" name="api[list_mapping]" rows="5">{$api_config['list_mapping']|escape}</textarea>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label class="col-form-label col-sm-2 offset-sm-1">
                        {tr}Create endpoint URL{/tr}
                        <a class="tikihelp text-info" title="{tr}Create URL:{/tr} {tr}URL of the endpoint to create new entries when exporting.{/tr}">
                            {icon name=information}
                        </a>
                    </label>
                    <div class="col-sm-9">
                        <input class="form-control" type="text" name="api[create_url]" value="{$api_config.create_url|escape}">
                    </div>
                </div>
                <div class="mb-3 row">
                    <label class="col-form-label col-sm-2 offset-sm-1">
                        {tr}Create endpoint method{/tr}
                        <a class="tikihelp text-info" title="{tr}Create method:{/tr} {tr}HTTP method to access the endpoint. Usually POST.{/tr}">
                            {icon name=information}
                        </a>
                    </label>
                    <div class="col-sm-9">
                        <select class="form-control" name="api[create_method]">
                            <option value=""></option>
                            <option value="GET" {if $api_config.create_method eq 'GET'}selected{/if}>GET</option>
                            <option value="PUT" {if $api_config.create_method eq 'PUT'}selected{/if}>PUT</option>
                            <option value="POST" {if $api_config.create_method eq 'POST'}selected{/if}>POST</option>
                            <option value="PATCH" {if $api_config.create_method eq 'PATCH'}selected{/if}>PATCH</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label class="col-form-label col-sm-2 offset-sm-1">
                        {tr}Create endpoint format{/tr}
                        <a class="tikihelp text-info" title="{tr}Create format:{/tr} {tr}Use special formatting when sending data to create endpoint if tabular's data format is not sufficient. E.g. [&quot;%field1%&quot;, &quot;%field3%&quot;]{/tr}">
                            {icon name=information}
                        </a>
                    </label>
                    <div class="col-sm-9">
                        <textarea class="form-control" name="api[create_format]" rows="5">{$api_config.create_format|escape}</textarea>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label class="col-form-label col-sm-2 offset-sm-1">
                        {tr}Update endpoint URL{/tr}
                        <a class="tikihelp text-info" title="{tr}Update URL:{/tr} {tr}URL of the endpoint to update entries when exporting. Placeholder #id will be replaced with the remote item id if previously imported from remote source.{/tr}">
                            {icon name=information}
                        </a>
                    </label>
                    <div class="col-sm-9">
                        <input class="form-control" type="text" name="api[update_url]" value="{$api_config.update_url|escape}">
                    </div>
                </div>
                <div class="mb-3 row">
                    <label class="col-form-label col-sm-2 offset-sm-1">
                        {tr}Update endpoint method{/tr}
                        <a class="tikihelp text-info" title="{tr}Update method:{/tr} {tr}HTTP method to access the endpoint. Usually PATCH or PUT.{/tr}">
                            {icon name=information}
                        </a>
                    </label>
                    <div class="col-sm-9">
                        <select class="form-control" name="api[update_method]">
                            <option value=""></option>
                            <option value="GET" {if $api_config.update_method eq 'GET'}selected{/if}>GET</option>
                            <option value="PUT" {if $api_config.update_method eq 'PUT'}selected{/if}>PUT</option>
                            <option value="POST" {if $api_config.update_method eq 'POST'}selected{/if}>POST</option>
                            <option value="PATCH" {if $api_config.update_method eq 'PATCH'}selected{/if}>PATCH</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label class="col-form-label col-sm-2 offset-sm-1">
                        {tr}Update endpoint format{/tr}
                        <a class="tikihelp text-info" title="{tr}Update format:{/tr} {tr}Use special formatting when sending data to update endpoint if tabular's data format is not sufficient. E.g. [&quot;%field1%&quot;, &quot;%field3%&quot;]{/tr}">
                            {icon name=information}
                        </a>
                    </label>
                    <div class="col-sm-9">
                        <textarea class="form-control" name="api[update_format]" rows="5">{$api_config.update_format|escape}</textarea>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label class="col-form-label col-sm-2 offset-sm-1">
                        {tr}Update endpoint query limit{/tr}
                        <a class="tikihelp text-info" title="{tr}Update query limit:{/tr} {tr}URL query string encoded parameter list to limit the number of results sent to the update endpoint when exporting. E.g. exclude all entries not having a value in a field: field= (empty) or field=value to limit by specific value.{/tr}">
                            {icon name=information}
                        </a>
                    </label>
                    <div class="col-sm-9">
                        <input class="form-control" type="text" name="api[update_limit]" value="{$api_config.update_limit|escape}">
                    </div>
                </div>
                <div class="mb-3 row">
                    <label class="col-form-label col-sm-2 offset-sm-1">
                        {tr}Modify data path{/tr}
                        <a class="tikihelp text-info" title="{tr}Modify Data Path:{/tr} {tr}Wrap request parameters in a (possibly nested) data structure. E.g. to send the request parameters as 'request' => { 'data' => ... }, specify 'request.data' here.{/tr}">
                            {icon name=information}
                        </a>
                    </label>
                    <div class="col-sm-9">
                        <input class="form-control" type="text" name="api[modify_data_path]" value="{$api_config.modify_data_path|escape}">
                    </div>
                </div>
            </div>
            <div class="mb-3 row">
                <label class="col-form-label col-sm-2">{tr}Fields{/tr}</label>
                <div class="col-sm-10">
                    <table class="table fields">
                        <thead>
                            <tr>
                                <th>{tr}Field{/tr}</th>
                                <th>{tr}Mode{/tr}</th>
                                {if $has_odbc}
                                    <th><abbr title="{tr}Remote Field{/tr}">{tr}RF{/tr}</abbr></th>
                                {/if}
                                <th><abbr title="{tr}Primary Key{/tr}">{tr}PK{/tr}</abbr></th>
                                <th><abbr title="{tr}Unique Key{/tr}">{tr}UK{/tr}</abbr></th>
                                <th><abbr title="{tr}Read-Only{/tr}">{tr}RO{/tr}</abbr></th>
                                <th><abbr title="{tr}Export-Only{/tr}">{tr}EO{/tr}</abbr></th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="d-none">
                                <td>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">{icon name=sort}</span>
                                        <input type="text" class="field-label form-control">
                                        <div class="input-group-text">
                                            <button type="button" class="btn btn-light dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                                <span class="align">{tr}Left{/tr}</span>
                                                <input class="display-align" type="hidden" value="left">
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end" role="menu">
                                                <a class="dropdown-item align-option" href="#left">{tr}Left{/tr}</a>
                                                <a class="dropdown-item align-option" href="#center">{tr}Center{/tr}</a>
                                                <a class="dropdown-item align-option" href="#right">{tr}Right{/tr}</a>
                                                <a class="dropdown-item align-option" href="#justify">{tr}Justify{/tr}</a>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="field">Field Name</span>:<span class="mode">Mode</span></td>
                                {if $has_odbc}
                                    <td>
                                        <div class="input-group input-group-sm">
                                            <input class="remote-field form-control" type="text" name="remoteField" size="5">
                                        </div>
                                    </td>
                                {/if}
                                <td><input class="primary" type="radio" name="pk"></td>
                                <td><input class="unique-key" type="checkbox"></td>
                                <td><input class="read-only" type="checkbox"></td>
                                <td><input class="export-only" type="checkbox"></td>
                                <td class="text-end"><button class="remove btn-sm btn-outline-warning">{icon name=remove}</button></td>
                            </tr>
                            {foreach $schema->getColumns() as $column}
                                <tr>
                                    <td>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">{icon name=sort}</span>
                                            <input type="text" class="field-label form-control" value="{$column->getLabel()|escape}">
                                            <div class="input-group-text">
                                                <button type="button" class="btn btn-light dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <span class="align">{$column->getDisplayAlign()|ucfirst|tra}</span>
                                                    <input class="display-align" type="hidden" value="{$column->getDisplayAlign()|escape}">
                                                </button>
                                                <div class="dropdown-menu dropdown-menu-end" role="menu">
                                                    <a class="dropdown-item align-option" href="#left">{tr}Left{/tr}</a>
                                                    <a class="dropdown-item align-option" href="#center">{tr}Center{/tr}</a>
                                                    <a class="dropdown-item align-option" href="#right">{tr}Right{/tr}</a>
                                                    <a class="dropdown-item align-option" href="#justify">{tr}Justify{/tr}</a>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="{service controller=tabular action=select trackerId=$trackerId permName=$column->getField()
                                                columnIndex=$column@index mode=$column->getMode()}"
                                                   class="btn btn-sm btn-secondary add-field tips"
                                                title="{tr}Field{/tr} {$column->getField()|escape}|{tr}Mode:{/tr} {$column->getMode()|escape}">
                                            <span class="field d-none">{$column->getField()|escape}</span>:
                                            <span class="mode">{$column->getMode()|escape}</span>
                                        </a>
                                    </td>
                                    {if $has_odbc}
                                        <td>
                                            <div class="input-group input-group-sm">
                                                <input class="remote-field form-control" type="text" value="{$column->getRemoteField()|escape}" size="5">
                                            </div>
                                        </td>
                                    {/if}
                                    <td><input class="primary" type="radio" name="pk" {if $column->isPrimaryKey()} checked {/if}></td>
                                    <td><input class="unique-key" type="checkbox" {if $column->isUniqueKey()} checked {/if}></td>
                                    <td><input class="read-only" type="checkbox" {if $column->isReadOnly()} checked {/if}></td>
                                    <td><input class="export-only" type="checkbox" {if $column->isExportOnly()} checked {/if}></td>
                                    <td class="text-end"><button class="remove btn-sm btn-outline-warning">{icon name=remove}</button></td>
                                </tr>
                            {/foreach}
                        </tbody>
                        <tfoot>
                            <tr>
                                <td>
                                    <select class="selection form-select">
                                        <option disabled="disabled" selected="selected">{tr}Select a field...{/tr}</option>
                                        {foreach $schema->getAvailableFields() as $permName => $label}
                                            <option value="{$permName|escape}">{$label|escape}</option>
                                        {/foreach}
                                    </select>
                                </td>
                                <td>
                                    <a href="{service controller=tabular action=select trackerId=$trackerId}" class="btn btn-secondary add-field">{tr}Select Mode{/tr}</a>
                                    <textarea name="fields" class="d-none">{$schema->getFormatDescriptor()|json_encode}</textarea>
                                </td>
                                <td colspan="6">
                                    <div class="radio">
                                        <label>
                                            <input class="primary" type="radio" name="pk" {if ! $schema->getPrimaryKey()} checked {/if}>
                                            {tr}No primary key{/tr}
                                        </label>
                                    </div>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                    <div class="form-text">
                        <p><strong>{tr}Remote Field:{/tr}</strong> {tr}When connecting to an external ODBC schema, this should reference the remote schema field.{/tr}</p>
                        <p><strong>{tr}Primary Key:{/tr}</strong> {tr}Can be any field as long as it is unique. If none is specified full record matching will be executed upon import to prevent duplicates which can be slow.{/tr}</p>
                        <p><strong>{tr}Unique Key:{/tr}</strong> {tr}Impose unique value requirement for the target column. This only works with Transactional Import feature.{/tr}</p>
                        <p><strong>{tr}Read-only:{/tr}</strong> {tr}When importing a file, read-only fields will be skipped, preventing them from being modified, but also speeding-up the process.{/tr}</p>
                        <p>{tr}When two fields affecting the same value are included in the format, such as the ID and the text value for an Item Link field, one of the two fields must be marked as read-only to prevent a conflict.{/tr}</p>
                    </div>
                </div>
            </div>
            <div class="mb-3 row submit">
                <div class="col-sm-10 offset-sm-2">
                    <input type="submit" class="btn btn-primary" value="{tr}Update{/tr}" onclick="$(window).off('beforeunload');return true;">
                </div>
            </div>
            <div class="mb-3 row">
                <label class="col-form-label col-sm-2">{tr}Filters{/tr}</label>
                <div class="col-sm-10">
                    <table class="table filters">
                        <thead>
                            <tr>
                                <th>{tr}Field{/tr}</th>
                                <th>{tr}Mode{/tr}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="d-none">
                                <td>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">{icon name=sort}</span>
                                        <input type="text" class="filter-label form-control" value="Label">
                                        <div class="input-group-text">
                                            <button type="button" class="btn btn-light dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                                <span class="position-label">{tr}Default{/tr}</span>
                                                <input class="position" type="hidden" value="default">
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end" role="menu">
                                                <a class="dropdown-item position-option" href="#default">{tr}Default{/tr}</a>
                                                <a class="dropdown-item position-option" href="#primary">{tr}Primary{/tr}</a>
                                                <a class="dropdown-item position-option" href="#side">{tr}Side{/tr}</a>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="field">Field Name</span>:<span class="mode">Mode</span></td>
                                <td class="text-end"><button class="remove btn-sm btn-outline-warning">{icon name=remove}</button></td>
                            </tr>
                            {foreach $filterCollection->getFilters() as $filter}
                                <tr>
                                    <td>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">{icon name=sort}</span>
                                            <input type="text" class="field-label form-control" value="{$filter->getLabel()|escape}">
                                            <div class="input-group-text">
                                                <button type="button" class="btn btn-light dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <span class="position-label">{$filter->getPosition()|ucfirst|tra}</span>
                                                    <input class="position" type="hidden" value="{$filter->getPosition()|escape}">
                                                </button>
                                                <div class="dropdown-menu dropdown-menu-end" role="menu">
                                                    <a class="dropdown-item position-option" href="#default">{tr}Default{/tr}</a>
                                                    <a class="dropdown-item position-option" href="#primary">{tr}Primary{/tr}</a>
                                                    <a class="dropdown-item position-option" href="#side">{tr}Side{/tr}</a>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="field">{$filter->getField()|escape}</span>:<span class="mode">{$filter->getMode()|escape}</td>
                                    <td class="text-end"><button class="remove btn-sm btn-outline-warning">{icon name=remove}</button></td>
                                </tr>
                            {/foreach}
                        </tbody>
                        <tfoot>
                            <tr>
                                <td>
                                    <select class="selection form-select">
                                        <option disabled="disabled" selected="selected">{tr}Select a field...{/tr}</option>
                                        {foreach $filterCollection->getAvailableFields() as $permName => $label}
                                            <option value="{$permName|escape}">{$label|escape}</option>
                                        {/foreach}
                                    </select>
                                </td>
                                <td>
                                    <a href="{service controller=tabular action=select_filter trackerId=$trackerId}" class="btn btn-secondary add-filter">{tr}Select Mode{/tr}</a>
                                    <textarea name="filters" class="d-none">{$filterCollection->getFilterDescriptor()|json_encode}</textarea>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                    <div class="form-text">
                        <p>{tr}Filters will be available in partial export menus.{/tr}</p>
                    </div>
                </div>
            </div>
            <div class="mb-3 row submit">
                <div class="col-sm-10 offset-sm-2">
                    <input type="submit" class="btn btn-primary" value="{tr}Update{/tr}" onclick="$(window).off('beforeunload');return true;">
                </div>
            </div>
            <div class="row mb-3">
                <label class="col-form-label col-sm-2">{tr}Options{/tr}</label>
                <div class="col-sm-5">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" name="config[simple_headers]" value="1" {if $config['simple_headers']} checked {/if}>
                        <label class="form-check-label">{tr}Simple headers{/tr}</label>
                        <a class="tikihelp text-info" title="{tr}Simple headers:{/tr} {tr}Allow using field labels only as a header row when importing rather than the full &quot;Field [permName:type]&quot; format.{/tr}">
                            {icon name=information}
                        </a>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" name="config[import_update]" value="1" {if $config['import_update']} checked {/if}>
                        <label class="form-check-label">{tr}Import updates{/tr}</label>
                        <a class="tikihelp text-info" title="{tr}Import update:{/tr} {tr}Allow updating existing entries matched by either PK or full record when importing. If this is disabled, only new items will be imported.{/tr}">
                            {icon name=information}
                        </a>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" name="config[ignore_blanks]" value="1" {if $config['ignore_blanks']} checked {/if}>
                        <label class="form-check-label">{tr}Ignore blanks{/tr}</label>
                        <a class="tikihelp text-info" title="{tr}Ignore blanks:{/tr} {tr}Ignore blank values when import is updating existing items. Only non-blank values will be updated this way.{/tr}">
                            {icon name=information}
                        </a>
                    </div>
                </div>
                <div class="col-sm-5">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" name="config[import_transaction]" value="1" {if $config['import_transaction']} checked {/if}>
                        <label class="form-check-label">{tr}Transactional import{/tr}</label>
                        <a class="tikihelp text-info" title="{tr}Import transaction:{/tr} {tr}Import in a single transaction. If any of the items fails validation, the whole import is rejected and nothing is saved.{/tr}">
                            {icon name=information}
                        </a>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" name="config[bulk_import]" value="1" {if $config['bulk_import']} checked {/if}>
                        <label class="form-check-label">{tr}Bulk import{/tr}</label>
                        <a class="tikihelp text-info" title="{tr}Bulk Import:{/tr} {tr}Import in 'bulk' mode so the search index is not updated for each item and no notifications should be sent.{/tr}">
                            {icon name=information}
                        </a>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" name="config[skip_unmodified]" value="1" {if $config['skip_unmodified']} checked {/if}>
                        <label class="form-check-label">{tr}Skip Unmodified{/tr}</label>
                    </div>
                </div>
            </div>
            <div class="row mb-3">
                <label class="col-form-label col-sm-2">
                    {tr}CSV/JSON Encoding{/tr}
                    <a class="tikihelp text-info" title="{tr}Encoding:{/tr} {tr}Excel will often expect 'Windows-1252' encoding{/tr}">
                        {icon name=information}
                    </a>
                </label>
                <div class="col-sm-3">
                    <select class="form-select" name="config[encoding]">
                        <option value=""{if empty($config['encoding']) or $config['encoding'] eq 'UTF-8'} selected="selected"{/if}>
                            {tr}Default (UTF-8){/tr}
                        </option>
                        {foreach $encodings as $encoding}
                            <option value="{$encoding}"{if $config['encoding'] eq $encoding} selected="selected"{/if}>
                                {$encoding}
                            </option>
                        {/foreach}
                    </select>
                </div>
            </div>
            <div class="row mb-3">
                <label class="col-form-label col-sm-2">
                    {tr}Data Format{/tr}
                    <a class="tikihelp text-info" title="{tr}Data Format:{/tr} {tr}Data type generated/expected from export/import functions.{/tr}">
                        {icon name=information}
                    </a>
                </label>
                <div class="col-sm-3">
                    <select class="form-select config-format" name="config[format]">
                        <option value=""{if empty($config['format']) or $config['format'] eq 'csv'} selected="selected"{/if}>
                            {tr}Default (CSV){/tr}
                        </option>
                        <option value="json"{if $config['format'] eq 'json'} selected="selected"{/if}>
                            JSON
                        </option>
                        <option value="ndjson"{if $config['format'] eq 'ndjson'} selected="selected"{/if}>
                            NDJSON
                        </option>
                        <option value="ical"{if $config['format'] eq 'ical'} selected="selected"{/if}>
                            iCal
                        </option>
                    </select>
                </div>
            </div>
            <div class="mb-3 row submit">
                <div class="col-sm-10 offset-sm-2">
                    <input type="submit" class="btn btn-primary" value="{tr}Update{/tr}" onclick="$(window).off('beforeunload');return true;">
                </div>
            </div>
        </form>
    </div>
{/block}
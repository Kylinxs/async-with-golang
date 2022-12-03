
{title help="Newsletters"}{tr}Newsletters{/tr}{/title}

{if $tiki_p_admin_newsletters eq "y"}
    <div class="t_navbar">
        <a role="link" href="tiki-admin_newsletters.php" class="btn btn-link" title="{tr}Admin Newsletters{/tr}">
            {icon name="cog"} {tr}Admin Newsletters{/tr}
        </a>
{*        {button href="tiki-admin_newsletters.php" class="btn btn-primary" _text="{tr}Admin Newsletters{/tr}"}*}
    </div>
{/if}
<br>
{if $subscribe eq 'y'}
    <h2>
        {tr}Subscribe to Newsletter{/tr}
    </h2>
    <br>
    <form method="post" action="tiki-newsletters.php">
        {ticket}
        <input type="hidden" name="nlId" value="{$nlId|escape}">
        <div class="mb-3 row">
            <label class="col-sm-3 col-form-label">{tr}Name{/tr}</label>
            <div class="col-sm-7">
                <p class="form-control-plaintext">{$nl_info.name|escape}</p>
            </div>
        </div>
        <div class="mb-3 row">
            <label class="col-sm-3 col-form-label">{tr}Description{/tr}</label>
            <div class="col-sm-7">
                <p class="form-control-plaintext">{$nl_info.description|escape|nl2br}</p>
            </div>
        </div>
        {if ($nl_info.allowUserSub eq 'y') or ($tiki_p_admin_newsletters eq 'y')}
            {if $tiki_p_subscribe_email eq 'y' and (($nl_info.allowAnySub eq 'y' and $user) || !$user)}
                <div class="mb-3 row">
                    <label class="col-sm-3 col-form-label" for="email">{tr}Email{/tr}</label>
                    <div class="col-sm-7">
                        <input type="email" name="email" id="email" value="{$email|escape}" class="form-control">
                    </div>
                </div>
            {else}
                <input type="hidden" name="email" value="{$email|escape}">
            {/if}
            {if !$user and $prefs.feature_antibot eq 'y'}
                {include file='antibot.tpl' tr_style="formcolor"}
            {/if}
            <div class="mb-3 row">
                <label class="col-sm-3 col-form-label"></label>
                <div class="col-sm-7">
                    <input
                        type="submit"
                        class="btn btn-primary"
                        name="subscribe"
                        value="{tr}Subscribe to this Newsletter{/tr}"
                    >
                </div>
            </div>
        {/if}
    </form>
{/if}
{if $showlist eq 'y'}
    <h2>{tr}Available Newsletters{/tr}</h2>

    {if $channels or $find ne ''}
        {include file='find.tpl'}
    {/if}

    <div class="{if $js}table-responsive{/if}"> {*the table-responsive class cuts off dropdown menus *}
        <table class="table table-striped table-hover">
            <tr>
                <th>{self_link _sort_arg='sort_mode' _sort_field='name'}{tr}Newsletter{/tr}{/self_link}</th>
                <th style="width:100px"></th>
            </tr>

            {section name=user loop=$channels}
                {if $channels[user].tiki_p_subscribe_newsletters eq 'y' or $channels[user].tiki_p_list_newsletters eq 'y'}
                    <tr>
                        <td class="text">
                            <a class="tablename" href="tiki-newsletters.php?nlId={$channels[user].nlId}&amp;info=1" title="{tr}Subscribe to Newsletter{/tr}">{$channels[user].name|escape}</a>
                            <div class="subcomment">{$channels[user].description|escape|nl2br}</div>
                        </td>
                        <td class="action">
                            {actions}
                                {strip}
                                    {if $channels[user].tiki_p_subscribe_newsletters eq 'y'}
                                        <action>
                                            <a href="tiki-newsletters.php?nlId={$channels[user].nlId}&amp;info=1">
                                                {icon name='add' _menu_text='y' _menu_icon='y' alt="{tr}Subscribe{/tr}"}
                                            </a>
                                        </action>
                                    {/if}
                                    {if $channels[user].tiki_p_send_newsletters eq 'y'}
                                        <action>
                                            <a href="tiki-send_newsletters.php?nlId={$channels[user].nlId}">
                                                {icon name='envelope' _menu_text='y' _menu_icon='y' alt="{tr}Send{/tr}"}
                                            </a>
                                        </action>
                                    {/if}
                                    {if $tiki_p_view_newsletter eq 'y'}
                                        <action>
                                            <a href="tiki-newsletter_archives.php?nlId={$channels[user].nlId}">
                                                {icon name='file-archive' _menu_text='y' _menu_icon='y' alt="{tr}Archives{/tr}"}
                                            </a>
                                        </action>
                                    {/if}
                                    {if $channels[user].tiki_p_admin_newsletters eq 'y'}
                                        <action>
                                            <a href="tiki-admin_newsletters.php?nlId={$channels[user].nlId}&amp;cookietab=2#anchor2">
                                                {icon name='cog' _menu_text='y' _menu_icon='y' alt="{tr}Admin{/tr}"}
                                            </a>
                                        </action>
                                    {/if}
                                {/strip}
                            {/actions}
                        </td>
                    </tr>
                {/if}
            {sectionelse}
                {norecords _colspan=2}
            {/section}
        </table>
    </div>
    {pagination_links cant=$cant offset=$offset step=$maxRecords}{/pagination_links}
{/if}
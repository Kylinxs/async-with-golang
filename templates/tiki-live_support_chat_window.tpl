<!DOCTYPE html>

<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        {* <link rel="StyleSheet" href="styles/{$prefs.style}" type="text/css"> *}
        <title>Live support:{$role} window</title>
        {$headerlib->output_headers()}
        {$headerlib->add_jsfile("vendor_bundled/vendor/components/jquery/jquery.js", true)}
        {literal}
            <link rel="stylesheet" href="lib/live_support/live-support.css" type="text/css">
            <script type="text/javascript" src="vendor_bundled/vendor/components/jquery/jquery.js"></script>
        {/literal}
    </head>
    <body onUnload="javascript:chat_close(document.getElementById('role').value,document.getElementById('username').value);" style="background-color: white">
        <input type="hidden" id="reqId" value="{$reqId|escape}">
        <input type="hidden" id="senderId" value="{$senderId|escape}">
        <input type="hidden" id="role" value="{$role|escape}">
        {if $role eq 'user'}
            {if !empty($req_info.tiki_user)}
                <input type="hidden" id="username" value="{$req_info.tiki_user|escape}">
            {else}
                <input type="hidden" id="username" value="{$req_info.user|escape}">
            {/if}
            <table>
                <tr>
                    <td valign="top" style="text-align:center;">{$req_info.operator|avatarize}<br>
                        <b>{$req_info.operator}</b>
                    </td>
                    <td valign="top" >
                        {tr}Chat started{/tr}<br>
                        <i>{$req_info.reason}</i>
                    </td>
                </tr>
            </table>
        {elseif $role eq 'operator'}
            <input type="hidden" id="username" value="{$req_info.operator|escape}">

            {if !empty($req_info.tiki_user)}
                <table>
                    <tr>
                        <td valign="top" style="text-align:center;">{$req_info.tiki_user|avatarize}<br>
                            <b>{$req_info.tiki_user}</b>({$IP})
                        </td>
                        <td valign="top" >
                            {tr}Chat started{/tr}<br>
                            <i>{$req_info.reason}</i>
                        </td>
                    </tr>
                </table>
            {else}
                <table>
                    <tr>
                        <td valign="top" style="text-align:center;">
                            <b>{$req_info.user}</b>({$IP})
                        </td>
                        <td valign="top" >
                            {tr}Chat started{/tr}<br>
                            <i>{$req_info.reason}</i>
                        </td>
                    </tr>
                </table>
            {/if}
        {else}
            <table >
                <tr>
                    <td style="text-align:center;" valign="top">
                        <b>{tr}User:{/tr}</b><br>
                        {if !empty($req_info.tiki_user)}
                            {$req_info.tiki_user|avatarize}<br>
                            <b>{$req_info.tiki_user}</b>
                        {else}
                            <b>{$req_info.user}</b>
                        {/if}
                    </td>
                    <td valign="top">
                        <i>{$req_info.reason}</i>
                    </td>
                    <td style="text-align:center;" valign="top">
                        <b>{tr}Operator:{/tr}</b><br>
                        {$req_info.operator|avatarize}<br>
                        <b>{$req_info.operator}</b>
                    </td>
                </tr>
            </table>
        {/if}

        {* <iframe name='chat_data' src='tiki-live_support_chat_frame.php' width="500" height="500" scrolling="yes">
        </iframe> *}
        <div id="chat_data" class="card card-body">

        </div>
        <br/>
        {literal}
            <div class="form-group row" style="width:500px">
                <input placeholder="{tr}write a new message...{/tr}" type="text" id="data" size="30" class="form-control col-sm-9" onKeyPress="javascript:if(event.keyCode == 13) {write_msg(document.getElementById('data').value,document.getElementById('role').value,document.getElementById('username').value);}">
                <button type="button" class="btn btn-primary col-sm-3" onClick="javascript:write_msg(document.getElementById('data').value,document.getElementById('role').value,document.getElementById('username').value);"><span class="fa fa-paper-plane"></span> {tr}send{/tr}</button>
            </div>
        {/literal}
        <script type='text/javascript'>
            /* Activate polling of requests */
            $('document').ready(function () {
                event_poll();
            });

        </script>
        {literal}
            <script type="text/javascript" src="lib/live_support/live-support.js">
            </script>
        {/literal}
    </body>
</html>

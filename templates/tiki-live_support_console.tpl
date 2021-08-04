<!DOCTYPE html>

<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <link rel="StyleSheet" href="styles/{$prefs.style}" type="text/css">
        <title>{tr}Live support:Console{/tr}</title>
        {$headerlib->output_headers()}
        <meta name="viewport" content="width=device-width, initial-scale=1">
        {literal}
            <script type="text/javascript" src="lib/live_support/live-support.js"></script>
        {/literal}
        {$trl}
    </head>
    <body style="background-color: white">

        <div class="w-100 vh-100 d-flex justify-content-center">
            <div class="container w-100">
                <div class="row justify-content-md-center mt-5">
                    <div class="col-12 col-lg-5 col-md-6">

                        {if $isOperator}
                            <h2>{tr}Operator:{/tr} {$user}</h2>
                            <p class="d-flex align-items-center justify-content-between">
                                <span>{tr}Status:{/tr} <b>{tr}{$status}{/tr}</b></span>
                                <span>
                                    {if $status eq 'offline'}
                                        <a class="btn btn-outline-primary" href="tiki-live_support_console.php?status=online">{tr}Be online{/tr}</a>
                                    {else}
                                        <a class="btn btn-outline-danger" href="tiki-live_support_console.php?status=offline">{tr}Be offline{/tr}</a>
                                    {/if}
                                </span>
                            </p>
                        {else}
                            <p class="text-center">{tr}You are not an operator.{/tr} <a href="tiki-live_support_admin.php">{tr}Live support system{/tr}</a></p>
                        {/if}

                        <div class="card">
                            <div class="card-body">

                                {if count($requests) > 0}
                                    <h3>{tr}Support requests{/tr}</h3>
                                    {if $new_requests eq 'y'}
                                        <script type='text/javascript'>
                                            sound();
                                        </script>
                                    {/if}
                                    <table id='reqs' class="table table-responsive normal">
                                        <thead>
                                            <tr>
                                                <th>{tr}User{/tr}</th>
                                                <th>{tr}Reason{/tr}</th>
                                                <th>{tr}Requested{/tr}</th>
                                                <th>&nbsp;</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        {section loop=$requests name=ix}
                                            <tr>
                                                <td>{$requests[ix].user}</td>
                                                <td>{$requests[ix].reason}</td>
                                                <td>{$requests[ix].timestamp|tiki_short_time}</td>
                                                <td>
                                                    {if $status eq 'online'}
                                                        {assign var=thereqId value=$requests[ix].reqId}
                                                        <a class="btn btn-outline-success" class="link" {jspopup href="tiki-live_support_chat_window.php?reqId=$thereqId&amp;role=operator"}>{tr}Accept{/tr}</a>
                                                        <a class="btn btn-outline-primary" class="link" {jspopup href="tiki-live_support_chat_window.php?reqId=$thereqId&amp;role=observer"}>{tr}Join{/tr}</a>
                                                    {else}
                                                        &nbsp;
                                                    {/if}
                                                </td>
                                            </tr>
                                        {/section}
                                        </tbody>
                                    </table>
                                {else}
                                    <h3 class="text-center">{tr}No support requests{/tr}</h3>
                                {/if}
                            </div>

                        </div>

                    </div>
                </div>
            </div>
        </div>

    <script type='text/javascript'>
        var last_support_req={$last};
        console_poll();
    </script>

    </body>
</html>

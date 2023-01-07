
{* $Id$ *}
<form action="tiki-admin.php?page=community" method="post" class="admin">
    {ticket}
    <div class="t_navbar mb-4 clearfix">
        {button href="tiki-admingroups.php" _class="btn-link tips" _type="text" _icon_name="group" _text="{tr}Groups{/tr}" _title=":{tr}Group Administration{/tr}"}
        {button href="tiki-adminusers.php" _class="btn-link tips" _type="text" _icon_name="user" _text="{tr}Users{/tr}" _title=":{tr}User Administration{/tr}"}
        {permission_link addclass="btn btn-link" _type="text" mode=text label="{tr}Permissions{/tr}"}
        <a href="{service controller=managestream action=list}" class="btn btn-link tips">{tr}Activity Rules{/tr}</a>
        {include file='admin/include_apply_top.tpl'}
    </div>
    {tabset name="admin_community"}
        {tab name="{tr}Community Features{/tr}"}
            <br>
            <fieldset>
                <legend>{tr}Community{/tr}{help url="Community"}</legend>
                {preference name=feature_community_gender}
                {preference name=feature_community_mouseover}
                <div class="adminoptionboxchild" id="feature_community_mouseover_childcontainer">
                    {preference name=feature_community_mouseover_name}
                    {preference name=feature_community_mouseover_gender}
                    {preference name=feature_community_mouseover_picture}
                    {preference name=feature_community_mouseover_score}
                    {preference name=feature_community_mouseover_country}
                    {preference name=feature_community_mouseover_email}
                    {preference name=feature_community_mouseover_lastlogin}
                    {preference name=feature_community_mouseover_distance}
                </div>
            </fieldset>
            <fieldset>
                <legend>{tr}Additional options{/tr}</legend>
                <div class="adminoptionbox">
                    {preference name=feature_invite}
                    {preference name=auth_token_share}
                    {preference name=feature_group_transition}
                    <div class="adminoptionboxchild" id="feature_group_transition_childcontainer">
                        {preference name=default_group_transitions}
                    </div>
                    {preference name=user_likes}
                    {preference name=mustread_enabled}
                    <div class="adminoptionboxchild" id="mustread_enabled_childcontainer">
                        {preference name=mustread_tracker}
                    </div>
                    {preference name=user_multilike_config}
                </div>
            </fieldset>
        {/tab}
        {tab name="{tr}Social Interaction{/tr}"}
            <br>
            <fieldset class="mb-3 w-100">
                <legend>{tr}Friendship and followers{/tr}</legend>
                {preference name=feature_friends}
                <div class="adminoptionboxchild" id="feature_friends_childcontainer">
                    {preference name=social_network_type}
                    <fieldset>
                        <legend>{tr}Select which items to display when listing users{/tr}</legend>
                        {preference name=user_list_order}
                        {preference name=feature_community_list_name}
                        {preference name=feature_community_list_score}
                        {preference name=feature_community_list_country}
                        {preference name=feature_community_list_distance}
                    </fieldset>
                </div>
            </fieldset>
            <fieldset class="mb-3 w-100">
                <legend>{tr}Activity Stream{/tr}</legend>
                {preference name=activity_basic_events}
                <div class="adminoptionboxchild" id="activity_basic_events_childcontainer">
                    {preference name=activity_basic_tracker_create}
                    {preference name=activity_basic_tracker_update}
                    {preference name=activity_basic_user_follow_add}
                    {preference name=activity_basic_user_follow_incoming}
                    {preference name=activity_basic_user_friend_add}
                </div>
                {preference name=activity_custom_events}
                {if $activity_custom_events eq 'y'}
                    <div class="adminoptionboxchild clearfix text-start" id="activity_custom_events_childcontainer">
                        <div class="mb-3 row">
                            <div class="col-4" for="total_activity_stream">{tr}Total activity rules{/tr}</div>
                            <div class="col-2" id="total_activity_stream">{$total_activity_custom_events}</div>
                        </div>
                        <div class="mb-3 row">
                            <div class="col">
                                <a href="tiki-ajax_services.php?controller=managestream&action=list">{tr}List Activity Rules{/tr}
                            </div>
                        </div>
                    </div>
                {/if}

                {preference name=activity_notifications}
            </fieldset>
            <fieldset>
                <legend>{tr}Goal, recognition and rewards{/tr}</legend>
                {preference name=goal_enabled}
                {preference name=goal_badge_tracker}
                {preference name=goal_group_blacklist}
            </fieldset>
            <fieldset>
                <legend>{tr}Score{/tr}</legend>
                {preference name=feature_score}
            </fieldset>
        {/tab}
        {tab name="{tr}Plugins{/tr}"}
            <br>
            {preference name=wikiplugin_author}
            {preference name=wikiplugin_avatar}
            {preference name=wikiplugin_favorite}
            {preference name=wikiplugin_group}
            {preference name=wikiplugin_groupexpiry}
            {preference name=wikiplugin_invite}
            {preference name=wikiplugin_mail}
            {preference name=wikiplugin_map}
            {preference name=wikiplugin_memberlist}
            {preference name=wikiplugin_memberpayment}
            {preference name=wikiplugin_perm}
            {preference name=wikiplugin_proposal}
            {preference name=wikiplugin_realnamelist}
            {preference name=wikiplugin_subscribegroup}
            {preference name=wikiplugin_subscribegroups}
            {preference name=wikiplugin_usercount}
            {preference name=wikiplugin_userlink}
            {preference name=wikiplugin_userlist}
            {preference name=wikiplugin_userpref}
        {/tab}
    {/tabset}
    {include file='admin/include_apply_bottom.tpl'}
</form>
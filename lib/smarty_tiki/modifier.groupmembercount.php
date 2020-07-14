<?php

function smarty_modifier_groupmembercount($group)
{
    return TikiLib::lib('user')->nb_users_in_group($group);
}

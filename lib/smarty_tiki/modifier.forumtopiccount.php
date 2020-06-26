<?php

function smarty_modifier_forumtopiccount($forumId)
{
    return TikiLib::lib('comments')->count_forum_topics($forumId);
}

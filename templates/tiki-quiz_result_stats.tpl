{title help="Quiz"}{tr}Quiz result stats{/tr}{/title}

<div class="t_navbar mb-4">
    {button href="tiki-list_quizzes.php" class="btn btn-info" _text="{tr}List Quizzes{/tr}"}
    {button href="tiki-quiz_stats.php" class="btn btn-info" _text="{tr}Quiz Stats{/tr}"}
    {button href="tiki-quiz_stats_quiz.php?quizId=$quizId" class="btn btn-info" _text="{tr}This Quiz Stats{/tr}"}
    {button href="tiki-edit_quiz.php?quizId=$quizId" class="btn btn-primary" _text="{tr}Edit this Quiz{/tr}"}
    {button href="tiki-edit_quiz.php" class="btn btn-primary" _text="{tr}Admin Quizzes{/tr}"}
</div>
<div class="table-responsive">
    <table class="table">
        <tr>
            <th colspan="2">{tr}Quiz stats{/tr}</th>
        </tr>
        <tr>
            <td class="even">{tr}Quiz{/tr}</td>
            <td class="even">{$quiz_info.name}</td>
        </tr>
        <tr>
            <td class="even">{tr}User{/tr} </td>
            <td class="even">{$ur_info.user|userlink}</td>
        </tr>
        <tr>
            <td class="even">{tr}Date{/tr}</td>
            <td class="even">{$ur_info.timestamp|tiki_short_datetime}</td>
        </tr>
        <tr>
            <td class="even">{tr}Points{/tr}</td>
            <td class="even">{$ur_info.points} / {$ur_info.maxPoints}</td>
        </tr>
        <tr>
            <td class="even">{tr}Time{/tr}</td>
            <td class="even">{$ur_info.timeTaken} secs</td>
        </tr>
    </table>
</div>

<br>
{tr}Answer:{/tr}

<div class="quizanswer">{$result.answer}</div>

<h2>{tr}User answers{/tr}</h2>
<div class="table-responsive">
<table class="table">
    <tr>
        <th>
            <a href="tiki-quiz_result_stats.php?quizId={$quizId}&amp;offset={$offset}&amp;sort
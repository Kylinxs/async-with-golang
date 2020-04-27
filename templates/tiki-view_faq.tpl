{title help="FAQs"}{tr}{$faq_info.title}{/tr}{/title}
<div class="description form-text">{$faq_info.description|escape}</div>

<div class="t_navbar mb-4">
    {button href="tiki-list_faqs.php" class="btn btn-info" _text="{tr}List FAQs{/tr}"}

    {if $tiki_p_admin_faqs eq 'y'}
        {button href="tiki-list_faqs.php?faqId=$faqId" class="btn btn-primary" _text="{tr}Edit this FAQ{/tr}"}
    {/if}
    {if $tiki_p_admin_faqs eq 'y'}
        {button href="tiki-faq_questions.php?faqId=$faqId" class="btn btn-primary" _text="{tr}New Question{/tr}"}
    {/if}

    {self_link print='y' _icon_name='print' _menu_text='y' _menu_icon='y' _class='btn btn-link'}
        {tr}Print{/tr}
    {/self_link}
</div>

<h2>{tr}Questions{/tr}</h2>
{if !$channels}
    {tr}There are no questions in this FAQ.{/tr}
{else}
    <div class="faqlistquestions">
        <ol>
            {section name=ix loop=$channels}
                <li>
                    <a class="link" href="#q{$channels[ix].questionId}">{$channels[ix].question|escape}</a>
                </li>
            {/section}
        </ol>
    </div>

    <h2>{tr}Answers{/tr}</h2>
    {section name=ix loop=$channels}
        <a id="q{$channels[ix].questionId}"></a>
        <div class="faqqa">
            <div class="faqquestion">
                {if $prefs.faq_prefix neq 'none'}
                    <span class="faq_question_prefix">
                        {if 
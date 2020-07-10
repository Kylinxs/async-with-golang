
{* $Id$ *}

{remarksbox type="note" title="{tr}Tip{/tr}"}{tr}<b>Orphan preferences </b> are preferences that exist in previous versions of Tiki but for various reasons have been removed. This page allows you to view the values you have configured for these preferences and gives you the option to clear the data if necessary.{/tr}
{/remarksbox}

<div class="text-center mb-4">
    <a href="tiki-admin.php?page=orphanprefs&clear=all" class="btn btn-primary" title="{tr}Delete all{/tr}">{icon name="trash"} {tr}Clear all data{/tr}</a>
</div>

<div class="table-responsive">
  <table class="table table-striped">
    <thead>
      <tr class="bg-info">
        <th>{tr}Preferences{/tr}</th>
        <th>{tr}Values{/tr}</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
        {if ! (empty($orphanPrefs))}
            {foreach $orphanPrefs as $pref}
              <tr>
                <th scope="row">{$pref.name|escape}</th>
                <td>{$pref.value|escape}</td>
                <td><a href="tiki-admin.php?page=orphanprefs&clear={$pref.name|escape}" class="tips" title=":{tr}Delete{/tr}">{icon name="trash"}</a></td>
              </tr>
            {/foreach}
        {else}
            <tr>
                <td colspan="2" class="text-center"><b>{tr}You have no orphan preferences. All is well !{/tr}</br></td>
                <td></td>
            <tr>
        {/if}
    </tbody>
  </table>
</div>
<br>
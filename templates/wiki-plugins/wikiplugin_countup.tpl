{strip}
    <div class="mainContainer text-center d-flex flex-column align-items-center justify-content-center" style="{$mainContainerStyle}">
        {if !empty($pluginInfos.icon)}
            <img src="tiki-download_file.php?fileId={$pluginInfos.icon}" width="{($pluginInfos.iconWidth) ? $pluginInfos.iconWidth : 64}" height="{($pluginInfos.iconHeight) ? $pluginInfos.iconHeight : 64}">
        {/if}
        <h1{if $numberStyle} style="{$numberStyle}"{/if}>
            {$pluginInfos.prefix}
            <span data-target="{$pluginInfos.endingNumber}" id="{$cleanedTitle}Count_{$counterId}" class="count">
                {($pluginInfos.startingNumber) ? $pluginInfos.startingNumber : 0}
            </span>
            {$pluginInfos.suffix}
        </h1>
        {if !empty($pluginInfos.title)}
            <h3{if $titleStyle} style="{$titleStyle}"{/if}>
                {$pluginInfos.title}
            </h3>
        {/if}
        {if !empty($pluginInfos.description)}
            <h6{if $descriptionStyle} style="{$descriptionStyle}"{/if}>
                {$pluginInfos.description}
            </h6>
        {/if}
    </div>
{/strip}

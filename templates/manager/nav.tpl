
<div class="mb-4">
    <a class="btn btn-light m-1" href="{service controller=manager action=index}">{icon name=list} {tr}Instances{/tr}</a>
    <a class="btn btn-light m-1" href="{service controller=manager action=create}">{icon name=create} {tr}New Instance{/tr}</a>
    <a class="btn btn-light m-1" href="{service controller=manager action=virtualmin_create}">{icon name=create} {tr}New Virtualmin Instance{/tr}</a>
    <a class="btn btn-light m-1" href="{service controller=manager action=info}">{icon name=info} {tr}Info{/tr}</a>
    <a class="btn btn-light m-1" href="{service controller=manager action=requirements}">{icon name=check} {tr}Requirements{/tr}</a>
    <a class="btn btn-light m-1" href="{service controller=manager action=tiki_versions}">{icon name=list} {tr}Tiki Versions{/tr}</a>
    <a class="btn btn-light m-1" href="{service controller=manager action=test_send_email}">{icon name=envelope} {tr}Test Send Email{/tr}</a>
    <a class="btn btn-light m-1" href="{service controller=manager action=setup_watch}">{icon name="clock-o"} {tr}Setup Watch{/tr}</a>
    <a class="btn btn-light m-1" href="{service controller=manager action=clear_cache}" title="{tr}Clear Tiki Manager cache. This can be useful for testing and debugging during development, or if your server is short on disk space and you need a temporary relief.{/tr}">
        {icon name=trash} {tr}Clear Cache{/tr}
    </a>
    <a class="btn btn-light m-1" href="{service controller=manager action=setup_clone}">{icon name=copy} {tr}Setup Clone{/tr}</a>
    <a class="btn btn-light m-1" href="{service controller=manager action=manager_backup}">{icon name=download} {tr}Setup Backup{/tr}</a>
    <a class="btn btn-light m-1" href="{service controller=manager action=manager_update}">{icon name=import} {tr}Setup Update{/tr}</a>
</div>
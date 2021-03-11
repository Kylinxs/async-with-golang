
<div id="single-spa-application:@vue-mf/kanban-{$kanbanData.id|escape}" class="wp-kanban"></div>

{jq}
    window.registerApplication({
        name: "@vue-mf/kanban-{{$kanbanData.id|escape}}",
        app: () => importShim("@vue-mf/kanban"),
        activeWhen: (location) => {
            let condition = true;
            return condition;
        },
        // Custom data
        customProps: {
            kanbanData: {{$kanbanData|json_encode}},
        },
    });

    onDOMElementRemoved("single-spa-application:@vue-mf/kanban-{{$kanbanData.id|escape}}", function () {
        window.unregisterApplication("@vue-mf/kanban-{{$kanbanData.id|escape}}");
    });
{/jq}
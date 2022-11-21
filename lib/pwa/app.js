(function () {

    const cacheName = 'pages-cache-v1';

    $.loadCache = function (pages) {
        console.warn("Lets fetch", pages);
        let urls = pages["wiki"].map(function (page) {
            return 'tiki-index.php?page=' + encodeURI(page);
        });
        urls = urls.concat(pages["urls"]);
        urls = urls.concat(
            pages["trackers"].map(function (page) {
                return 'tiki-view_tracker_item.php?itemId=' + page.itemId;
            })
        );
        urls = urls.concat(
            pages["trackers"].map(function (tracker) {
                return 'tiki-ajax_services.php?controller=tracker&action=update_item&trackerId=' + tracker.id + '&itemId=' + tracker.itemId;
            })
        );
        caches.open(cacheName)
            .then(cache => urls.map(url => cache.match(url).then(z => (!z) ? cache.add(url) : false).catch(x => console.error(x))));

    };
    if (!navigator.serviceWorker) {
        console.warn("Service Worker Unavailable");
        return;
    }
    navigator.serviceWorker.register('./sw.js').then(() => {
        //init database
        console.warn("init app");


        const db = new Dexie("post_cache");
        db.version(1).stores({
            messages: 'name,value', //table work like a flag. SW change the message to flag the ui that a warning need to be shown
            post_cache: 'key,request,timestamp',
        });

        $.updatePWACount = function () { //update pwa requests count and check that need to show the warning message
            db.post_cache.count().then(function (n) {
                $("#pwa-n-requests").text(n);
            });
            db.messages.get({name: "show-warning"}, function (row) {
                if (row && row.value == true) {
                    $("#tikifeedback").showError("You are offline. Tiki will try to handle your requests to send them later.");
                    db.messages.where("name").aboveOrEqual("show-warning").modify({value: false}).then(function () {
                    });
                }
            });
        };

        $.updatePWACount();

        $("#sync-pwa").on("click touchstart", function (event) {
            console.log("#sync-pwa clicked");
            const callsArray = [];
            db.post_cache.each(function ({key, request}) {
                callsArray.push(new Promise(function (deferrer, reject) {
                        console.warn(request);
                        if (request) {
                            $.ajax({
                                async: false,
                                type: request.method,
                                url: request.url,
                                headers: {...request.headers, pwa: true},
                                data: request.body,
                                success: function (ret) {
                                },
                                error: function (ret) {
                                }
                            });
                            deferrer(key);
                        }
                    })
                );
            }).then(function () {

                Promise.all(callsArray).then(function (keys) {
                    console.warn(keys);
                    const att = [];
                    keys.forEach(function (k) {
                        att.push(db.post_cache.where('key').equals(k).delete().then($.updatePWACount));
                    });
                    return Promise.all(att);

                }).then(function () {
                    location.reload();
                });
            });
            event.preventDefault();

        });


    }).catch((err) => {
        console.log('registration failed', err);
    });
})();

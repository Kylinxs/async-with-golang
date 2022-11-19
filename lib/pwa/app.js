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

  
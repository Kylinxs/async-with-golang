/* (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
 *
 * All Rights Reserved. See copyright.txt for details and a complete list of authors.
 * Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
 *
 * $Id$
 *
 * Include file for wikiplugin_mediaplayer.php
 *
 */

(function ($) {
    $('.mediaplayerDataUrl').each(function (index, element) {
        var pdfDoc = null,
            pageNum = 1,
            pageRendering = false,
            pageNumPending = null,
            scale = 1,
            mediaplayerId = this.id.replace('mediaplayer-pdf-', ''),
            canvas = $('#mediaplayer-pdf-canvas-' + mediaplayerId).get(0),
            ctx = canvas.getContext('2d'),
            url = this.value;

        $('#mediaplayer-pdf-prev-' + mediaplayerId).get(0).addEventListener('click', function () {
            onPrevPage(pdfDoc, mediaplayerId, scale, canvas, ctx, pageRendering, pageNumPending);
        }, false);

        $('#mediaplayer-pdf-next-' + mediaplayerId).get(0).addEventListener('click', function () {
            onNextPage(pdfDoc, mediaplayerId, scale, canvas, ctx, pageRendering, pageNumPending);
        }, false);

        pdfjsLib.getDocument(url).then(function (pdfDoc_) {
            pdfDoc = pdfDoc_;
            $('#mediaplayer-pdf-page-count-' + mediaplayerId).get(0).textContent = pdfDoc.numPages;

            // Initial/first page rendering
            renderPage(pdfDoc, pageNum, mediaplayerId, scale, canvas, ctx, pageRendering, pageNumPending);
        });

    });

    /**
     * Get page info from document, resize canvas accordingly, and render page.
     * @param num Page number.
     */
    function renderPage(pdfDoc, num, mediaplayerId, scale, canvas, ctx, pageRendering, pageNumPending) {
        pageRendering = true;
        // Using promise to fetch the page
        pdfDoc.getPage(num).then(function (page) {
            var viewport = page.getViewport(scale);
            canvas.height = viewport.height;
            canvas.width = viewport.width;

            // Render PDF page into canvas context
            var renderContext = {
                canvasContext: ctx,
                viewport: viewport
            };
            var renderTask = page.render(renderContext);

            // Wait for rendering to finish
            renderTask.promise.then(function () {
                pageRendering = false;
                if (pageNumPending !== null) {
                    // New page rendering is pending
                    renderPage(pdfDoc, pageNumPending, mediaplayerId, scale, canvas, ctx, pageRendering, pageNumPending);
                    pageNumPending = null;
                }
            });
        });

        // Update page counters
        $('#mediaplayer-pdf-page-num-' + mediaplayerId).get(0).textContent = num;
    }

    /**
     * If another page rendering in progress, waits until the rendering is
     * finised. Otherwise, executes rendering immediately.
     */
    function queueRenderPage(pdfDoc, num, mediaplayerId, scale, canvas, ctx, pageRendering, pageNumPending) {
        if (pageRendering) {
           
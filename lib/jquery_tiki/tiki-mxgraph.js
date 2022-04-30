// $Id$
// Tiki mxGraph loader and settings

// Rewrite mxgraph base directory
mxLoadResources = false;
mxBasePath = diagramVendorPath + 'tikiwiki/diagram/mxgraph';
grapheditorBase = diagramVendorPath + 'tikiwiki/diagram/';

TEMPLATE_PATH = grapheditorBase + 'templates';
STENCIL_PATH = grapheditorBase + 'stencils';
SHAPES_PATH = grapheditorBase + 'shapes';
IMAGE_PATH = grapheditorBase + 'images';
STYLE_PATH = grapheditorBase + 'styles';
CSS_PATH = grapheditorBase + 'styles';
RESOURCES_PATH = grapheditorBase + 'resources';
GRAPH_IMAGE_PATH  = IMAGE_PATH;

// isLocalStorage controls access to local storage
window.isLocalStorage = window.isLocalStorage || false;

function handleXmlData(graph, data, container) {

    let xml = data;
    let doc = mxUtils.parseXml(xml);

    let getSvg = function(doc, graph) {
        // Executes the layout
        let codec = new mxCodec(doc);
        codec.decode(doc.documentElement, graph.getModel());

        // Include 15px border (space) to render lines on edges properly
        let svg = graph.getSvg(null, null, 15);

        // Remove this to use css properties or viewbox to adapt to container size
        $(svg).removeAttr('width').removeAttr('height');

        return svg;
    };

    let containerElm = document.getElementById(container.id);
    containerElm.style.display = 'none';
    let temp = document.createElement('div');

    if (doc.documentElement != null && doc.documentElement.nodeName === 'mxfile') {
        let diagrams = doc.documentElement.getElementsByTagName('diagram');

        if (diagrams.length > 0) {
            let page = container.getAttribute('page');

            $.each(diagrams, function (i, diagramInfo) {
                if (i > 0 && page === '') {
                    temp.insertAdjacentHTML('beforeend', '<br/><br/>');
                }

                let diagramPage = diagramInfo.getAttribute('name');
                let uncompressedXML = diagramInfo.innerHTML;

                if (uncompressedXML.indexOf('mxGraphModel') != -1) {
                    xml = diagramInfo.innerHTML;
                } else {
                    let xmlCompressed = mxUtils.getTextContent(diagramInfo);
                    xml = graph.decompress(xmlCompressed);
                }
                doc = mxUtils.parseXml(xml);

                if (page === '' || diagramPage === page) {
                    let svg = getSvg(doc, graph);
                    $(temp).append(svg);
                }
            });
        }
    } else if (doc.documentElement != null){
        let svg = getSvg(doc, graph);
        $(temp).append(svg);
    }

    if (temp.innerHTML !== '') {
        containerElm.innerHTML = temp.innerHTML;
    }

    $(temp).remove();
    containerElm.style.display = 'block';
}

// Main function that loads and render the graph
function mxGraphMain(container, graph_data, themes) {

    var graph = new Graph(container, null, null, null, themes);

    graph.setEnabled(false);
    graph.setPanning(false);
    graph.autoScroll = false;
    graph.isHtmlLabel = function () {
        return true;
    };

    handleXmlData(graph, graph_data, container);

    // Hack to make the svg responsive
    if (!!navigator.userAgent.match(/Trident/g) || !!navigator.userAgent.match(/MSIE/g)) {
        return; // svg responsive hack bellow is not working in IE
    }

    graph.addListener('refresh', function(evt)
    {
        $(container).find('svg').each(function (index, el) {
            let svg = $(el);
            let parent = svg.parent();

            let viewBox = svg.attr('viewBox') || '';
            let vbProperties = viewBox.split(' ');

            let width = parseInt(vbProperties[2] || svg.css('width') || svg.css('min-width'));
            let height = parseInt(vbProperties[3] || svg.css('height') || svg.css('min-height'));

            if (width > parent.width()) {
                svg.attr('viewBox', '0 0 ' + width + ' ' + height);
                svg.css('min-height', '').css('height', '');
                svg.css('min-width', '').css('width', '');
            } else {
                svg.css('width', width);
                svg.css('height', height);
                svg.removeAttr('viewBox');
            }
        });

    });

    $(window).resize(function () {
        graph.refresh();
    });

    graph.refresh();
}

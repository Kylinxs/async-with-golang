
var casper = require('casper').create();

var filename = casper.cli.options.filename;
if (! filename) {
    casper.echo('No filename option passed', 'ERROR').exit();
}

var url = casper.cli.options.htmlfile;
if (! url) {
    casper.echo('No exporthtmlfile option passed', 'ERROR').exit();
}

casper.start(url, function(){
    this.page.injectJs('temp/do_' + filename + '.js');
});

casper.then(function() {
    var dim = this.evaluate(function() {
        var graph = document.getElementById('graph');
        return {
            'width': graph.clientWidth,
            'height': graph.clientHeight
        };
    });

    var clipRect = {
        top: 0,
        left: 0,
        width: dim.width,
        height: dim.height
    };
    var imgOptions = {
        format: 'png',
        quality: 75
    };
    this.capture('temp/diagram_' + filename + '.png', clipRect, imgOptions);
});

casper.run();
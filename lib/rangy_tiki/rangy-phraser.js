rangy.createModule("Phraser", ["WrappedSelection", "WrappedRange"], function(api, module) {

    function getParts(val, id) {
        var words = [];
        var chs = [];
        var i = 0;

        id = (!id ? val : id);

           if (!api.words[id]) {
               api.analyse(val, {
                   wordHandler: function(word) {
                       i++;
                       words.push(word);
                       return word;
                   },
                   charHandler: function(ch) {
                       if (!chs[i]) chs[i] = '';
                           chs[i] += ch;

                       return ch;
                   }
               });

               api.words[id] = words;
               api.chs[id] = chs;
           } else {
               words = api.words[id];
               chs = api.chs[id];
           }

           return {
               words: words,
               chs: chs
           };
    }

    function expandIndexesToCh(parentParts, indexes, ch) {
        for(var start = indexes.start; start > 0; start--) {
            if (parentParts.chs[start])
                if (parentParts.chs[start].match(ch)) {
                    indexes.start = start;
                    break;
                }
        }

        for(var end = indexes.end; end < parentParts.words.length; end++) {
            if (parentParts.chs[end])
                if (parentParts.chs[end].match(ch)) {
         
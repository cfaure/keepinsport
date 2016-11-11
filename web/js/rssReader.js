var rssReader = {

    // initialization function
    init : function(id) {
            elt = document.getElementById(id);
            
            // getting necessary variables
            var rssUrl = elt.getAttribute('rss_url');
            var num = elt.getAttribute('rss_num');
            //var id = elt.getAttribute('id');

            // creating temp scripts which will help us to transform XML (RSS) to JSON
            var url = encodeURIComponent(rssUrl);
            var googUrl = 'https://ajax.googleapis.com/ajax/services/feed/load?v=1.0&num='+num+'&q='+url+'&callback=rssReader.parse&context='+id;

            var script = document.createElement('script');
            script.setAttribute('type','text/javascript');
            script.setAttribute('charset','utf-8');
            script.setAttribute('src',googUrl);
            elt.appendChild(script);
    },

    // parsing of results by google
    parse : function(context, data) {
        var container = document.getElementById(context);
        container.innerHTML = '';

        // creating list of elements
        var table = document.createElement('table');
        //table.setAttribute("class", "table table-striped")
        table.className = table.className + "table table-striped";

        // also creating its childs (subitems)
        var entries = data.feed.entries;
        for (var i=0; i<entries.length; i++) {
            var tr = document.createElement('tr');
            var td = document.createElement('td');
            var title = entries[i].title;
            var contentSnippet = entries[i].contentSnippet;
            var contentSnippetText = document.createTextNode(contentSnippet);

            var link = document.createElement('a');
            link.setAttribute('href', entries[i].link);
            link.setAttribute('target','_blank');
            var text = document.createTextNode(title);
            link.appendChild(text);

            // add link to list item
            td.appendChild(link);

            /*var desc = document.createElement('p');
            desc.appendChild(contentSnippetText);

            // add description to list item
            td.appendChild(desc);*/
            
            tr.appendChild(td);

            // adding list item to main list
            table.appendChild(tr);
        }
        container.appendChild(table);
    }
};
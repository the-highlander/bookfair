/*
 */
Ext.define('Warehouse.data.proxy.Restful', {
    extend: 'Ext.data.proxy.Rest',
    listeners: {
        exception: { 
            fn: function(proxy, response, operation, eOpts) { 
			        if (response.status === 200) {
			           myDesktop.requestMessageProcessor(proxy, response);
			        } else {
			            rsp = JSON.parse(response.responseText);
			            //TODO: Strip the upper part of the directory location from rsp.error.file. Only
			            //      show the last 3 directories eg ...\bookfair\controllers\SectionController.php
			            Ext.MessageBox.show({
			                title: "Status " + response.status + " - " + response.statusText,
			                msg: rsp.error.message + "<br>File: " + rsp.error.file + "<br>Line: " + rsp.error.line,
			                buttons: Ext.MessageBox.OK,
			                icon: Ext.MessageBox.ERROR
			            });
			            //TODO: This dialogue is hidden because control flows back to the
			            // ui component bringing that component to the top of the stack.
			        }
			    },
            scope: this
        }
    },
    reader: {
        type: 'json'
    },
    buildUrl: function (request) {
    	var url = Ext.data.proxy.Rest.prototype.buildUrl.apply(this, arguments);
        return this.replaceTokens(url, request); 
    }, 
 
    replaceTokens: function(str, request) { 
        var me = this; 
 
        return str.replace(/{(.*?)}/g, function(full, token) { 
            // We read the id from the request params, the category is read from the proxy itself 
            return encodeURIComponent(request.params[token] || me[token]); 
        }); 
    },

    setBookfair: function (bookfair) {
    	this.bookfair = bookfair;
    },

    getBookfair: function () {
        return this.bookfair;
    }
});

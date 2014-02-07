Ext.Loader.setConfig({
    enabled: true,
    paths: {
        'Ext.ux': 'lib/Ext.ux'  // User Extensions (Base Classes)
    }
});

Ext.application({
    name: 'Desktop',
    appFolder: 'app',
    controllers: [],
    views: [],
    stores: [],
    launch: function(application) {
        delete Ext.tip.Tip.prototype.minWidth;
        // Called automatically when the page has completely loaded. 
        Ext.create('Desktop.view.Viewport');
    }
});
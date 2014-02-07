/*
 * File: app/view/MainViewport.js
 */
Ext.define('Desktop.view.Viewport', {
    extend: 'Ext.container.Viewport',
    alias: 'widget.desktopviewport',

    requires: [
		'Desktop.view.Desktop',
	    'Desktop.view.Wallpaper'
	],

    layout: 'fit',

    initComponent: function() {
        var me = this;
		
        Ext.applyIf(me, {
            items: [
                // Ext.create('Ext.ux.desktop.Desktop', desktopCfg);
				{
                    xtype: 'desktop'
                }
            ]
        });

        me.callParent(arguments);
    }

});		
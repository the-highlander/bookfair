Ext.define('Warehouse.view.PrintTabs', { 
    extend: 'Ext.window.Window',
    alias: 'widget.printtabs',

    initComponent: function () {
        var me = this,
            tabs = [];
        Ext.Array.each(me.initialConfig.tabs, 
            function (tab, i, alltabs) {
                tabs.push({ 
                    title: tab.title,
                    items: [{ 
                        xtype: 'component', 
                        id: 'iframe' + i,
                        autoEl: {
                            tag: 'iframe',
                            style: 'height: 100%; width: 100%; border: none;',
                            src : tab.url
                        },
                        listeners: {
                            load: {
                                element: 'el',
                                fn: function () {
                                    me.setLoading(false);
                                }
                            },
                            render: function () {
                                me.setLoading(true);
                            }
                        }            
                    }]
                });
            }
        );
        Ext.apply(me, {
            autoDestroy: true,
            floating: true,
            autoShow: true,
            height: 750,
            width: 650,
            tbar: [
                {
                    text: 'Print',
                    tooltip: 'Print',
                    handler: me.onPrint
                }
            ],
            items: [{
                xtype: 'tabpanel',
                height: 685,
                layout: 'fit',
                items: tabs
            }]
        });
        me.callParent();
    },

    onPrint: function () {
        console.log('Wait -- need to find the active tab first');
        var iframe = this.up('printwindow').child('#iframe').getEl();
        Ext.getDom(iframe).contentWindow.print();
    }

});
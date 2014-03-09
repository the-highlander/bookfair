Ext.define('Warehouse.view.Print', { 
    extend: 'Ext.window.Window',
    alias: 'widget.printwindow',

    initComponent: function () {
        var me = this;
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
                    iconCls: 'icon-print',
                    handler: me.onPrint
                }
            ],
            items: [
                {
                    xtype: 'component',
                    id: 'iframe',
                    autoEl: {
                        tag: 'iframe',
                        style: 'height: 100%; width: 100%; border: none;',
                        src : me.initialConfig.url
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
                }
            ]
        });
        me.callParent();
    },

    onPrint: function () {
        var iframe = this.up('printwindow').child('#iframe').getEl();
        Ext.getDom(iframe).contentWindow.print();
    }

});
/*
 */
Ext.define('Warehouse.SalesWindow', {
    extend: 'Ext.ux.desktop.Module',
    requires: [
        'Warehouse.view.statistics.Sales'
    ],
    bookfair: null,
    id: 'sales-win',
    init : function(){
        this.launcher = {
            text: 'Sales Statistics',
            iconCls:'stats-icon'
        };
    },

    createWindow : function () {
        var desktop = this.app.getDesktop(),
            win = desktop.getWindow('sales-win');
        if(!win){
            win = desktop.createWindow({
                id: 'sales-win',
                title: Ext.String.format('Sales Statistics for {0} {1}', this.getBookfair().get('season'), this.getBookfair().get('year')),
                width:1200,
                height:480,
                iconCls: 'stats-icon',
                animCollapse:false,
                constrainHeader:true,
                layout: 'fit',
                tools:[{
                    type:'refresh',
                    tooltip: 'Refresh Data',
                    callback: function(panel) {
                        panel.down('sales').store.reload();
                    }
                }],                
                items: [
                    {
                        xtype: 'sales',
                        id: 'salesStatsPanel',
                        bookfair: this.getBookfair()
                    }
                ]
            });
        }
        return win;
    },

    getBookfair: function () {
        return this.bookfair;
    },

    setBookfair: function (bookfair) {
        this.bookfair = bookfair;
    }

});
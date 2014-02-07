/*
 */
Ext.define('Warehouse.AllocationWindow', {
    extend: 'Ext.ux.desktop.Module',
    requires: [
        'Warehouse.view.statistics.Allocations'
    ],
    bookfair: null,
    id: 'allocations-win',
    init : function(){
        this.launcher = {
            text: 'Table Allocation',
            iconCls:'stats-icon'
        };
    },

    createWindow : function () {
        var desktop = this.app.getDesktop(),
            win = desktop.getWindow('allocations-win');
        if(!win){
            win = desktop.createWindow({
                id: 'allocations-win',
                title: Ext.String.format('Table Allocation for {0} {1}', this.getBookfair().get('season'), this.getBookfair().get('year')),
                width:1200,
                height:480,
                iconCls: 'stats-icon',
                animCollapse:false,
                constrainHeader:true,
                layout: 'fit',
                items: [
                    {
                        xtype: 'allocations',
                        id: 'allocationsPanel',
                        bookfair: this.getBookfair()
                    }
                ]
            });
        }
        return win;
    },

    /* onSelectBookfair: function (combo, rec, event) {
        var win = this.app.getDesktop().getWindow('stats-win');
        var stats = win.down('statistics');
        stats.loadStats(rec[0].get('id'));
    }*/

    getBookfair: function () {
        return this.bookfair;
    },

    setBookfair: function (bookfair) {
        console.log("Setting bookfair to ", bookfair);
        this.bookfair = bookfair;
    }
    
});
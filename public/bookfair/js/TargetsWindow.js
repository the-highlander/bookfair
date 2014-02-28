/*
  This is where to you control all the categories in a bookfair. You can set the allocate and tracking flags, enter packing targets and so on.
  TODO: Pallet assignments here too?
 */
Ext.define('Warehouse.TargetsWindow', {
    extend: 'Ext.ux.desktop.Module',
    requires: [
        'Warehouse.view.statistics.Targets',
        'Warehouse.view.statistics.Add'
    ],
    bookfair: null,
    id: 'targets-win',
    init : function(){
        this.launcher = {
            text: 'Packing Targets',
            iconCls:'stats-icon'
        };
    },

    createWindow : function () {
        var desktop = this.app.getDesktop(),
            win = desktop.getWindow('targets-win');
        if(!win){
            win = desktop.createWindow({
                id: 'targets-win',
                title: Ext.String.format('Packing Targets for {0} {1}', this.getBookfair().get('season'), this.getBookfair().get('year')),
                width:800,
                height:480,
                iconCls: 'stats-icon',
                animCollapse:false,
                constrainHeader:true,
                layout: 'fit',
                tools:[{
                    type:'refresh',
                    tooltip: 'Refresh Data',
                    callback: function(panel) {
                        panel.down('targets').store.reload();
                    }
                }],                
                items: [
                    {
                        xtype: 'targets',
                        id: 'targetsPanel',
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
        console.log("Setting bookfair to ", bookfair);
        this.bookfair = bookfair;
    }
    
});
/*
 */
Ext.define('Warehouse.BookfairWindow', {
    extend: 'Ext.ux.desktop.Module',

    requires: [
        'Warehouse.view.bookfair.List',
        'Warehouse.view.bookfair.Edit'
    ],

    id: 'bookfair-win',

    init : function(){
        this.launcher = {
            text: 'Bookfairs',
            iconCls:'bookfair-icon'
        };
    },

    createWindow : function () {
        var desktop = this.app.getDesktop();
        var win = desktop.getWindow('bookfair-win');
        if(!win){
            win = desktop.createWindow({
                id: 'bookfair-win',
                title:'Bookfair Register',
                width:740,
                height:480,
                iconCls: 'bookfair-icon',
                animCollapse:false,
                constrainHeader:true,
                layout: 'fit',
                items: [
                    {
                        xtype: 'bookfairlist'
                    }
                ]
            });
        }
        return win;
    }
    
});
/*
 */
Ext.define('Warehouse.CategoryWindow', {
    extend: 'Ext.ux.desktop.Module',

    requires: [
        'Warehouse.view.category.List'
    ],

    id: 'category-win',

    init : function(){
        this.launcher = {
            text: 'Categories',
            iconCls:'category-icon'
        };
    },

    createWindow : function(){
        var desktop = this.app.getDesktop();
        var win = desktop.getWindow('category-win');
        if(!win){
            win = desktop.createWindow({
                id: 'category-win',
                title:'Categories Master List',
                width:500,
                height:600,
                iconCls: 'category-icon',
                animCollapse:false,
                constrainHeader:true,
                layout: 'fit',
                items: [
                   {
                        xtype: 'categorylist',
                        flex: 4
                    }
                ]
            });
        }
        return win;
    }
    
});
/*
 */
Ext.define('Warehouse.UserListWindow', {
    extend: 'Ext.ux.desktop.Module',

    requires: [
        'Ext.data.ArrayStore',
        'Ext.util.Format',
        'Ext.grid.Panel',
        'Ext.grid.RowNumberer',
        'Warehouse.view.user.List',
        'Warehouse.view.user.Edit',
        'Warehouse.view.person.Add',
        'Warehouse.view.user.Add'
    ],

    id: 'user-mgmt-win',  //TODO Check is this right. Same id as cretewindow below


    init : function(){
        this.launcher = {
            text: 'User Management',
            iconCls:'user-mgmt-icon'
        };
    },

    createWindow : function(){
        var desktop = this.app.getDesktop();
        var win = desktop.getWindow('user-mgmt-win');
        if(!win){
            win = desktop.createWindow({
                id: 'user-mgmt-win',
                title:'User Management',
                width:740,
                height:480,
                iconCls: 'user-mgmt-icon',
                animCollapse:false,
                constrainHeader:true,
                layout: 'fit',
                items: [
                    {
                        border: false,
                        xtype: 'userlist'
                    }
                ]
            });
        }
        return win;
    }
    
});
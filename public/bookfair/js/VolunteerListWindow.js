/*
 */
Ext.define('Warehouse.VolunteerListWindow', {
    extend: 'Ext.ux.desktop.Module',

    requires: [
        'Ext.data.ArrayStore',
        'Ext.util.Format',
        'Ext.grid.Panel',
        'Ext.grid.RowNumberer',
        'Warehouse.view.person.List',
        //'Warehouse.view.person.Edit',
        'Warehouse.view.person.Add'
    ],

    id: 'volunteer-mgmt-win',


    init : function(){
        this.launcher = {
            text: 'Volunteer Management',
            iconCls:'volunteer-mgmt-icon'
        };
    },

    createWindow : function(){
        var desktop = this.app.getDesktop();
        var win = desktop.getWindow('volunteer-mgmt-win');
        if(!win){
            win = desktop.createWindow({
                id: 'volunteer-mgmt-win',
                title:'Volunteer Management',
                width:740,
                height:480,
                iconCls: 'volunteer-mgmt-icon',
                animCollapse:false,
                constrainHeader:true,
                layout: 'fit',
                items: [
                    {
                        border: false,
                        xtype: 'personlist'
                    }
                ]
            });
        }
        return win;
    }
    
});
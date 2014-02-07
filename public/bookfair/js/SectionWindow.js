/*
 */
Ext.define('Warehouse.SectionWindow', {
    extend: 'Ext.ux.desktop.Module',

    requires: [
        'Warehouse.view.section.List',
    ],

    id: 'section-win',

    init : function(){
        this.launcher = {
            text: 'Sections',
            iconCls:'section-icon'
        };
    },

    createWindow : function(){
        var desktop = this.app.getDesktop();
        var win = desktop.getWindow('section-win');
        if(!win){
            win = desktop.createWindow({
                id: 'section-win',
                title:'Sections Master List',
                width: 500,
                height:680,
                iconCls: 'section-icon',
                animCollapse:false,
                constrainHeader:true,
                layout: 'fit',
                items: [
                    {
                        xtype: 'sectionlist',
                    } 
                ]
            });
        }
        return win;
    }
    
});
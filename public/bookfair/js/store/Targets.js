/**
* @author Russell Nash
* @description text
*/
Ext.define('Warehouse.store.Targets', {
    extend: 'Ext.data.Store',
    model: 'Warehouse.model.Target',
    autoLoad: true,
    autoSync: true,
    groupers: [{
        property: 'section_name',
        direction: 'ASC'
    }],
    sorters: ['section_name', 'label', 'category_name']
});
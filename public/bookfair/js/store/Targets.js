/**
* @file /store/Targets.js
* @author Russell Nash
*/
Ext.define('Warehouse.store.Targets', {
    extend: 'Ext.data.Store',
    model: 'Warehouse.model.Target',
    autoLoad: true,
    autoSync: true,
    groupField: 'section_name',
    sorters: ['section_name', 'label', 'category_name']
});
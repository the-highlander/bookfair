/**
* @file /store/Allocations.js
* @author Russell Nash
*/
Ext.define('Warehouse.store.Allocations', {
    extend: 'Ext.data.Store',
    model: 'Warehouse.model.Allocation',
    autoLoad: true,
    autoSync: false,
    groupField: 'tablegroup_name',
    sorters: ['section_name', 'label', 'category_name']
});
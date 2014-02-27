/**
* @file: /store/Allocations.js
* @author Russell Nash
* @description text
*/
Ext.define('Warehouse.store.Allocations', {
    extend: 'Ext.data.Store',
    model: 'Warehouse.model.Allocation',
    autoLoad: true,
    autoSync: false,
	groupers: [{
		property: 'tablegroup_name',
		direction: 'ASC'
	}],
	sorters: ['section_name', 'label', 'category_name']
});
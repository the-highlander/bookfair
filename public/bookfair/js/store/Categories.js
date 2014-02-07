/*
* File: warehouse/store/Categories.js
*/
Ext.define('Warehouse.store.Categories', {
	extend: 'Ext.data.Store',
	model: 'Warehouse.model.Category',
	autoLoad: true,
	autoSync: true,
	groupers: [{
		property: 'section_name',
		direction: 'ASC'
	}],
	sorters: ['section_name', 'label', 'name']
});
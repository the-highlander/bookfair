/**
* @file: /model/Allocation.js
* @author Russell Nash
* @description text
*/
Ext.define('Warehouse.store.Sales', {
    extend: 'Ext.data.Store',
    model: 'Warehouse.model.Sale',
    autoLoad: true,
    autoSync: false,
	groupers: [{
		property: 'section_name',
		direction: 'ASC'
	}],
	sorters: ['section_name', 'label', 'category_name']
});
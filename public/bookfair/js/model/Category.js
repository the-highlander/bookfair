/*
* File: Warehouse/model/Category.js
*/
Ext.define('Warehouse.model.Category', {
	extend: 'Ext.data.Model',
	fields: [
		{name: 'id', type: 'int'},
		{name: 'section_id', type: 'int' },
		{name: 'name', type: 'string'},
		{name: 'label', type: 'string'},
		{name: 'measure', type: 'string'},
		{name: 'pallet_loading', type: 'int'},
        {name: 'section_name', type: 'string', mapping: 'section.name'}
    ],
	validations: [
		{type: 'presence',  field: 'name'},
 		{type: 'inclusion', field: 'measure', list: ['box', 'table', 'percent']}
    ],
    associations: [
        { type: 'belongsTo', model: 'Warehouse.model.Section' }
    ],
    proxy: Ext.create('Warehouse.data.proxy.Restful', {
        url: 'categories',
    })
})
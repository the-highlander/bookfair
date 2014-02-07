/**
* @author Russell Nash
* @description text
*/
Ext.define('Warehouse.store.Persons', {
	extend: 'Ext.data.Store',
	model: 'Warehouse.model.Person',
	autoLoad: true
});
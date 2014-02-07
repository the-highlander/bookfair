/*
* File: warehouse/store/TableGroups.js
*/
Ext.define('Warehouse.store.TableGroups', {
	extend: 'Ext.data.Store',
	model: 'Warehouse.model.TableGroup',
	autoLoad: true,
	autoSync: false
});
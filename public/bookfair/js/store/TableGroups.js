/*
* @file /store/TableGroups.js
* @author Rusell Nash
*/
Ext.define('Warehouse.store.TableGroups', {
    extend: 'Ext.data.Store',
    model: 'Warehouse.model.TableGroup',
    autoLoad: true,
    autoSync: false
});
/*
 * @file /store/Divisions.js
 * @author Russell Nash
 */
Ext.define('Warehouse.store.Divisions', {
    extend: 'Ext.data.Store',
    model: 'Warehouse.model.Division',
    autoLoad: true,
    autoSync: false
});
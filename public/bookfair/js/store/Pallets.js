/*
 * @file store/Pallets.js
 * @author Russell Nash
 */
Ext.define('Warehouse.store.Pallets', {
    extend: 'Ext.data.Store',
    model: 'Warehouse.model.Pallet',
    autoLoad: true,
    autoSync: false
});
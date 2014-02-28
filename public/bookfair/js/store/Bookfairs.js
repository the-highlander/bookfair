/**
 * @file /store/Bookfairs.js
 * @author: Russell Nash
 */
Ext.define('Warehouse.store.Bookfairs', {
    extend: 'Ext.data.Store',
    model: 'Warehouse.model.Bookfair',
    autoLoad: true,
    autoSync: false,
    sorters: ['start_date']
});
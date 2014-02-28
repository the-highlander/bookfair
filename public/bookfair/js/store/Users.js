/**
 * file /store/Users.js
 * @author: Russell Nash
 */
Ext.define('Warehouse.store.Users', {
    extend: 'Ext.data.Store',
    model: 'Warehouse.model.User',
    autoLoad: true
});
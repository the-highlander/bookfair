/*
 * @file /store/Persons.js
 * @author Russell Nash
 */
Ext.define('Warehouse.store.Persons', {
    extend: 'Ext.data.Store',
    model: 'Warehouse.model.Person',
    autoLoad: true
});
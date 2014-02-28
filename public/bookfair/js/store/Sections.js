/*
* @file /store/Sections.js
* @author Russell Nash
*/
Ext.define('Warehouse.store.Sections', {
    extend: 'Ext.data.Store',
    model: 'Warehouse.model.Section',
    autoLoad: true,
    autoSync: true
});
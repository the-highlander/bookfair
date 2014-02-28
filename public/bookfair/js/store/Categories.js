/*
 * @file /store/Categories.js
 * @author Russell Nash
 */
Ext.define('Warehouse.store.Categories', {
    extend: 'Ext.data.Store',
    model: 'Warehouse.model.Category',
    autoLoad: true,
    autoSync: true,
    groupField: 'section_name',
    sorters: ['section_name', 'label', 'name']
});
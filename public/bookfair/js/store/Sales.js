/**
 * @file /store/Sales.js
 * @author Russell Nash
 */
Ext.define('Warehouse.store.Sales', {
    extend: 'Ext.data.Store',
    model: 'Warehouse.model.Sale',
    autoLoad: true,
    autoSync: false,
    groupField: 'section_name',
    sorters: ['section_name', 'label', 'category_name']
});
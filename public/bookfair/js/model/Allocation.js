/*
 * @file model/Allocation.js
 * @author Russell Nash
 */
Ext.define('Warehouse.model.Allocation', {
    extend: 'Ext.data.Model',
    fields: [
        {name: 'id', type: 'int'},
        {name: 'bookfair_id', type: 'int', mapping: 'stats.bookfair_id'},
        {name: 'section_id', type: 'int', mapping: 'stats.category.section_id'},
        {name: 'category_id', type: 'int', mapping: 'stats.category_id'},
        {name: 'tablegroup_id', type: 'int'},
        {name: 'tablegroup_name', type: 'string', mapping: 'tablegroup.name'},
        {name: 'position', type: 'int' },
        {name: 'portion', type: 'float' },
        {name: 'tablegroup_tables', type: 'int', mapping: 'tablegroup.tables'},
        {name: 'label', type: 'string', mapping: 'stats.label' },
        {name: 'section_name', type: 'string', mapping: 'stats.category.section.name'},
        {name: 'name', type: 'string', mapping: 'stats.name'},
        {name: 'packed', type: 'int', mapping: 'stats.packed'},
        {name: 'loading', type: 'float', mapping: 'loading' },
        {name: 'suggested', type: 'float'},
        {name: 'tables', type: 'float'},
        {name: 'display', type: 'int'}, 
        {name: 'reserve', type: 'int'}
    ],
    proxy: Ext.create('Warehouse.data.proxy.Restful', {
        url: 'statistics/bookfair/{bookfair}/allocations'
    })
//    associations: [
//        { type: 'belongsTo', model: 'Warehouse.model.Category' }
//    ],
});
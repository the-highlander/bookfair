/*
 * @file model/Allocation.js
 * @author Russell Nash
 */
Ext.define('Warehouse.model.Allocation', {
    extend: 'Ext.data.Model',
    fields: [
        {name: 'id', type: 'int'},
        {name: 'bookfair_id', type: 'int'},
        {name: 'section_id', type: 'int'},
        {name: 'category_id', type: 'int'},
        {name: 'tablegroup_id', type: 'int'},
        {name: 'tablegroup_name', type: 'string', mapping: 'tablegroup.name'},
        {name: 'position', type: 'int' },
        {name: 'tablegroup_tables', type: 'int', mapping: 'tablegroup.tables'},
        {name: 'label', type: 'string'},
        {name: 'section_name', type: 'string', mapping: 'category.section.name'},
        {name: 'name', type: 'string'},
        {name: 'packed', type: 'int'},
        {name: 'base_load', type: 'float' },
        {name: 'suggested', type: 'float'},
        {name: 'allocated', type: 'float'},
        {name: 'setup_display', type: 'int'}, 
        {name: 'setup_reserve', type: 'int'}
    ],
    proxy: Ext.create('Warehouse.data.proxy.Restful', {
        url: 'statistics/bookfair/{bookfair}/allocations'
    })
//    associations: [
//        { type: 'belongsTo', model: 'Warehouse.model.Category' }
//    ],
});
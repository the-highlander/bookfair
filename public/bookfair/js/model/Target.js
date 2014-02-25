/*
* @file: model/Allocation.js
*/
Ext.define('Warehouse.model.Target', {
    extend: 'Ext.data.Model',
    fields: [
        {name: 'id', type: 'int'},
        {name: 'bookfair_id', type: 'int'},
        {name: 'section_id', type: 'int'},
        {name: 'category_id', type: 'int'},
        {name: 'section_name', type: 'string', mapping: 'category.section.name'},
        {name: 'label', type: 'string'},
        {name: 'name', type: 'string'},
        {name: 'measure', type: 'string'},
        {name: 'pallet_id', type: 'int'},
        {name: 'pallet_name', type: 'string', mapping: 'pallet.name'},
        {name: 'tablegroup_id', type: 'int'},
        {name: 'tablegroup_name', type: 'string', mapping: 'tablegroup.name'},
        {name: 'target', type: 'int'},
        {name: 'allocate', type: 'boolean'},
        {name: 'track', type: 'boolean'}
    ],
    proxy:  Ext.create('Warehouse.data.proxy.Restful', {
        url: 'statistics/bookfair/{bookfair}/targets'
    })
//    associations: [
//        { type: 'belongsTo', model: 'Warehouse.model.Category' }
//    ],
})
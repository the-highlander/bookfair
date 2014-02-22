/*
 * @file: model/Allocation.js
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
        {name: 'tablegroup_tables', type: 'int', mapping: 'tablegroup.tables'},
        {name: 'label', type: 'string'},
        {name: 'section_name', type: 'string', mapping: 'category.section.name'},
        {name: 'name', type: 'string'},
        {name: 'packed', type: 'int'},
        {name: 'loading', type: 'float'},
        {name: 'suggested', type: 'float'},
        {name: 'allocated', type: 'float'},
        {
            name: 'display', type: 'int',
            convert: function(val, row) {
                return Math.floor(row.data.loading * row.data.allocated);
            }
        }, {
            name: 'reserve', type: 'int',
            convert: function(val, row) {
                return Math.max(0, row.data.packed - Math.floor(row.data.loading * row.data.allocated));
            }
        }, {
            name: 'discrepancy', type: 'int',
            convert: function(val, row) {
                var alloc = row.data.loading * row.data.allocated;
                return (alloc > row.data.packed) ? row.data.packed - alloc : 0;
            }
        }
    ],
    proxy: Ext.create('Warehouse.data.proxy.Restful', {
        url: 'statistics/bookfair/{bookfair}/allocations'
    })
//    associations: [
//        { type: 'belongsTo', model: 'Warehouse.model.Category' }
//    ],
});
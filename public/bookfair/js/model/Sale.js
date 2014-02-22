/*
 * File: Warehouse/model/Sale.js
 */
Ext.define('Warehouse.model.Sale', {
    extend: 'Ext.data.Model',
    fields: [
        {name: 'id', type: 'int'},
        {name: 'bookfair_id', type: 'int'},
        {name: 'section_id', type: 'int', mapping: 'category.section.id'},
        {name: 'category_id', type: 'int'},
        {name: 'table_group_id', type: 'int'},
        {name: 'label', type: 'string'},
        {name: 'section_name', type: 'string', mapping: 'category.section.name'},
        {name: 'name', type: 'string'},
        {name: 'measure', type: 'string'},
        {name: 'delivered', type: 'float'},
        {name: 'loading', type: 'float'},
        {name: 'start_display', type: 'float'},
        {name: 'start_reserve', type: 'float'},
        {name: 'fri_extras', type: 'float'},
        {name: 'fri_end_display', type: 'float'},
        {name: 'fri_end_reserve', type: 'float'},
        {name: 'fri_sold', type: 'float'},
        {name: 'sat_extras', type: 'float'},
        {name: 'sat_end_display', type: 'float'},
        {name: 'sat_end_reserve', type: 'float'},
        {name: 'sat_sold', type: 'float'},
        {name: 'sun_extras', type: 'float'},
        {name: 'sun_end_display', type: 'float'},
        {name: 'sun_end_reserve', type: 'float'},
        {name: 'sun_sold', type: 'float'},
        {name: 'end_extras', type: 'float'},
        {name: 'end_display', type: 'float'},
        {name: 'end_reserve', type: 'float'},
        {name: 'end_sold', type: 'float'},
        {name: 'total_stock', type: 'float'},
        {name: 'total_sold', type: 'float'},
        {name: 'total_unsold', type: 'float'}
    ],
    validations: [
        // prohibit negative numbers.
        // is it possible to do more complex validation re display+reserve < previous display+reserve+extras
        {type: 'inclusion', field: 'measure', list: ['box', 'table', 'percent']}
    ],
    proxy: Ext.create('Warehouse.data.proxy.Restful', {
        url: 'statistics/bookfair/{bookfair}/sales'
    })
//    associations: [
//        { type: 'belongsTo', model: 'Warehouse.model.Category' }
//    ],
});
/*
 * File: Warehouse/model/Section.js
 */
Ext.define('Warehouse.model.Section', {
    extend: 'Ext.data.Model',
    requires: [
        'Warehouse.model.Division'
    ],
    fields: [
        {name: 'id', type: 'int'},
        {name: 'name', type: 'string'},
        {name: 'division_id', type: 'int'},
        {name: 'allocate_tables', type: 'boolean'},
        {name: 'published', type: 'boolean'}
    ],
    validations: [
        {type: 'presence', field: 'name'}
    ],
    associations: [
        {
            type: 'belongsTo',
            model: 'Warehouse.model.Division',
            getterName: 'getDivision',
            setterName: 'setDivision',
            foreignKey: 'division_id'
        }
    ],
    proxy: Ext.create('Warehouse.data.proxy.Restful', {
        url: 'sections'
    })
});
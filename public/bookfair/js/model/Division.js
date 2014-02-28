/*
 * @file /model/Division.js
 * @author Russell Nash
 */
Ext.define('Warehouse.model.Division', {
    extend: 'Ext.data.Model',
    fields: [
        {name: 'id', type: 'int'},
        {name: 'name', type: 'string'},
        {name: 'head_person_id', type: 'int'}
    ],
    validations: [
        {type: 'presence', field: 'name'}
    ],
    hasMany: {
        model: "Warehouse.model.Category",
        name: "categories"
    },
    proxy: Ext.create('Warehouse.data.proxy.Restful', {
        url: 'divisions'
    })
});
/*
 * @file /model/TableGroup.js
 * @author Russell Nash
 */
Ext.define('Warehouse.model.TableGroup', {
    extend: 'Ext.data.Model',
    fields: [
        {name: 'id', type: 'int'},
        {name: 'name', type: 'string'},
        {name: 'location', type: 'string'},
        {name: 'room', type: 'string'},
        {name: 'tables', type: 'int'}
    ],
    proxy: Ext.create('Warehouse.data.proxy.Restful', {
        url: 'tablegroups'
    })
//    associations: [
//        { type: 'hasMany', model: 'Warehouse.model.Category' }
//    ],
});
/*
 * @file: model/Pallet.js
 * @author Russell Nash
 */
Ext.define('Warehouse.model.Pallet', {
    extend: 'Ext.data.Model',
    fields: [
        {name: 'id', type: 'int'},
        {name: 'name', type: 'string'}        
    ],
    proxy: Ext.create('Warehouse.data.proxy.Restful', {
        url: 'pallets'
    })
//    associations: [
//        { type: 'hasMany', model: 'Warehouse.model.Category' }
//    ],
});
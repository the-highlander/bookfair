/*
 * @file /model/Bookfair.js
 * @author Russell Nash
 */
Ext.define('Warehouse.model.Bookfair', {
    extend: 'Ext.data.Model',
    fields: [
        {name: 'id', type: 'int'},
        {
            name: 'year',
            convert: function(v, record) {
                return record.data.start_date.getFullYear();
            }
        },
        {name: 'start_date', type: 'date', dateFormat: 'Y-m-d'},
        {name: 'end_date', type: 'date', dateFormat: 'Y-m-d'},
        {name: 'season', type: 'string'},
        {name: 'location', type: 'string'},
        {name: 'fri_open', type: 'date', dateFormat: 'Hi'},
        {name: 'fri_close', type: 'date', dateFormat: 'Hi'},
        {name: 'sat_open', type: 'date', dateFormat: 'Hi'},
        {name: 'sat_close', type: 'date', dateFormat: 'Hi'},
        {name: 'sun_open', type: 'date', dateFormat: 'Hi'},
        {name: 'sun_close', type: 'date', dateFormat: 'Hi'},
        {name: 'duration', type: 'int'},
        {name: 'bag_sale', type: 'boolean'},
        {name: 'locked', type: 'boolean'},
        {
            name: 'attendance',
            type: 'int',
            convert: function(v, record) {
                return record.raw.total_attendance.length > 0 ? record.raw.total_attendance[0].value : 0;
            }},
        {
            name: 'stock',
            type: 'int',
            convert: function(v, record) {
                return record.raw.sales_totals.length > 0 ? record.raw.sales_totals[0].total_stock : 0;
            }
        }, {
            name: 'sold',
            type: 'float',
            convert: function(v, record) {
                var sold = 0;
                if (record.raw.sales_totals.length > 0) {
                    if (record.raw.sales_totals[0].total_stock > 0) {
                        sold = record.raw.sales_totals[0].total_sold / record.raw.sales_totals[0].total_stock * 100;
                    }
                }
                return sold;
            }
        }
    ],
    proxy: Ext.create('Warehouse.data.proxy.Restful', {
        url: 'bookfairs'
    }),
    validations: [
        {type: 'presence', field: 'start_date'},
        {type: 'presence', field: 'end_date'},
        {type: 'presence', field: 'location'}
    ]
});

Ext.define('Warehouse.model.Bookfair', {
	extend : 'Ext.data.Model',
	fields : [
		{ name : 'id', type : 'int' }, 
		{ 
            name : 'year', 
            convert: function (v, record) {
                return record.data.start_date.getFullYear();
            }
        },
		{ name: 'start_date', type: 'date', dateFormat: 'Y-m-d' },
        { name: 'end_date', type: 'date', dateFormat: 'Y-m-d' },
        { name: 'season', type: 'string' },
        { name: 'location', type: 'string' },
        { name: 'fri_open', type: 'date', dateFormat: 'g:i A' },
        { name: 'fri_close', type: 'date', dateFormat: 'g:i A' },
        { name: 'sat_open', type: 'date', dateFormat: 'g:i A' },
        { name: 'sat_close', type: 'date', dateFormat: 'g:i A' },
        { name: 'sun_open', type: 'date', dateFormat: 'g:i A' },
        { name: 'sun_close', type: 'date', dateFormat: 'g:i A' },
		{ 
            name: 'duration', 
            convert: function (v, record) {
                if (record.data.start_date && record.data.end_date) {
                    return  Ext.Date.getElapsed(record.data.start_date, record.data.end_date) / 86400000;
                } else {
                    return "";
                }
            }
        },
		{ name: 'bag_sale', type: 'boolean' }, 
		{ name: 'published', type: 'boolean' },
        { name: 'locked', type: 'boolean' },
		{ 
            name: 'label', 
            convert: function (v, record) {
			    return record.data.season + ' ' + record.data.year;
		    }
        }
	],
    proxy: Ext.create('Warehouse.data.proxy.Restful', {
        url: 'bookfairs'
    }),
    validations: [
		{type: 'presence',  field: 'start_date'},
        {type: 'presence',  field: 'end_date'},
		{type: 'presence',  field: 'location'}
    ]
});

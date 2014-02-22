// Set up a model to use in our Store
Ext.define('Warehouse.model.Person', {
    extend: 'Ext.data.Model',
    fields: [
        {name: 'id', type: 'int'},
        {name: 'user_id', type: 'string', mapping: 'user.id'},
        {name: 'first_name', type: 'string'},
        {name: 'middle_names', type: 'string'},
        {name: 'last_name', type: 'string'},
        {name: 'dob', type: 'date', dateFormat: 'Y-m-d H:i:s'},
        {name: 'gender', type: 'string'},
        // { name: 'photograph',
        {name: 'email', type: 'string'},
        {name: 'mobile_phone', type: 'string'},
        {name: 'home_phone', type: 'string'},
        {name: 'unit_no', type: 'string'},
        {name: 'street_no', type: 'string'},
        {name: 'street_name', type: 'string'},
        {name: 'street_type', type: 'string'},
        {name: 'suburb', type: 'string'},
        {name: 'state', type: 'string'}
    ],
    validations: [
        {type: 'presence', field: 'last_name'},
        {type: 'length', field: 'first_name', min: 2},
        {type: 'inclusion', field: 'gender', list: ['Male', 'Female']},
        {type: 'exclusion', field: 'username', list: ['Admin']},
        {type: 'email', field: 'email'},
        {type: 'format', field: 'username', matcher: /([a-z]+)[0-9]{2,3}/}
    ],
    proxy: Ext.create('Warehouse.data.proxy.Restful', {
        url: 'people'
    })
});
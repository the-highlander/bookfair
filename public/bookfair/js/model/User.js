// Set up a model to use in our Store
Ext.define('Warehouse.model.User', {
    extend: 'Ext.data.Model',
    fields: [
        {name: 'id', type: 'int', mapping: 'person_id' },
        {name: 'username', type: 'string', mapping: 'id' },
        {name: 'first_name', type: 'string', mapping: 'person.first_name' },
        {name: 'last_name',  type: 'string', mapping: 'person.last_name' },
        {name: 'email', type: 'string', mapping: 'person.email' },
        {name: 'locked', type: 'boolean' },
        {name: 'last_login', type: 'date', dateFormat: 'Y-m-d H:i:s' },
        {name: 'password', type: 'string' }
    ],
    validations: [
        {type: 'presence',  field: 'username'},
        {type: 'format',    field: 'username',  matcher: /^[a-z]+[a-z0-9]{2,}$/},
        {type: 'exclusion', field: 'username',  list: ['admin']},
        {type: 'presence',  field: 'first_name' },
        {type: 'presence',  field: 'last_name' },
        {type: 'presence',  field: 'email' },
        {type: 'email',     field: 'email' }
    ],
    proxy: Ext.create('Warehouse.data.proxy.Restful', {
        url: 'users',
    })
});
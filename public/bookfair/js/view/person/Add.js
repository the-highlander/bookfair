// View for Adding a Person. Access from the Add User action on the User Management page.
// will be an extension of a Base view that will be used for Volunteer management but that can come later.
Ext.define('Warehouse.view.person.Add', {
    extend: 'Ext.window.Window',
    alias : 'widget.personadd',
    id: "personadder",
    title : 'Add Person',
    layout: 'fit',
    autoShow: true,

    initComponent: function() {
        var me = this;
 
        var states = Ext.create('Ext.data.Store', {
            model: 'Warehouse.model.State',
            proxy: {
                type: 'memory',
                reader: {
                    type: 'array'
                }
            },
            data: 'Warehouse.data.DataSets.states'
        });

        var type = Ext.create('Ext.data.Store', {
            model: 'Warehouse.model.StreetType',
            proxy: {
                type: 'memory',
                reader: {
                    type: 'array'
                }
            },
            data: 'Warehouse.data.DataSets.street_types'
        });        
        
 
        Ext.apply(me, {
            items: {
                xtype: 'form',
                border: false,
                bodyPadding: '5px 10px 5px 10px',
                fieldDefaults: {
                                margin: 5,
                                labelWidth: 100,
                                msgTarget: 'side',
                                autoFitErrors: false
                            },
                items: [
                    { 
                        xtype: 'fieldset',
                        title: 'Personal Details',
                        items: [
                            {
                                xtype: 'textfield',
                                name : 'first_name',
                                fieldLabel: 'First Name',
                                minLength: 2,
                                maxLength: 64
                            }, {
                                xtype: 'textfield',
                                name : 'last_name',
                                fieldLabel: 'Last Name',
                                minLength: 3,
                                maxLength: 64
                            }, {
                                xtype: 'radiogroup',
                                fieldLabel: 'Gender',
                                name: 'gender',
                                columns: 2,
                                vertical: true,
                                items: [
                                    { boxLabel: 'Female', name: 'rb', inputValue: 'Female' },
                                    { boxLabel: 'Male', name: 'rb', inputValue: 'Male' },
                                ]
                            }, {
                                xtype: 'datefield',
                                name : 'dob',
                                fieldLabel: 'Date of Birth'
                            }
                        ]
                    }, { 
                        xtype: 'fieldset',
                        title: 'Contact Information',
                        items: [
                            {
                                xtype: 'textfield',
                                name : 'email',
                                fieldLabel: 'Email'
                            }, {
                                xtype: 'textfield',
                                name: 'mobile',
                                fieldLabel: 'Mobile Phone'
                            }, {
                                xtype: 'textfield',
                                name: 'home_phone',
                                fieldLabel: 'Home Phone'
                            }, {
                                xtype: 'textfield',
                                inputWidth: '40',
                                name: 'unit_no',
                                fieldLabel: 'Unit No'
                            }, {
                                xtype: 'textfield',
                                inputWidth: '40',
                                name: 'streetn_no',
                                fieldLabel: 'Street No'
                            }, {
                                xtype: 'textfield',
                                name: 'street_name',
                                fieldLabel: 'Street Name'
                            }//, {
                             //   xtype: 'streettypecombo',
                             //   name: 'street_type'
                           // }
                        ]
                    }
                ],
                dockedItems: [
                    { 
                        xtype: 'toolbar',
                        dock: 'bottom',
                        ui: 'footer',
                        items: [
                            '->',
                            {
                                text: 'Save',
                                formBind: true,
                                handler: function () { 
                                    var form = this.up('form'),
                                        win = form.up('window'),
                                        store = Ext.data.StoreManager.lookup('personStore'),
                                        values = form.getValues(),
                                        record = form.getRecord(), 
                                        errors = record.validate();
                                    if (errors.isValid()) {
                                        record.set(values);
                                        store.sync({
                                            success: function() { Ext.getCmp("personadder").close(); },
                                            failure: function(batch, options) {
                                                // TODO: Test this. 
                                                // Extract server side validation errors
                                                var errors = new Ext.data.Errors(),
                                                    serverErrors = batch.exceptions[0].error;
                                                Ext.each(serverErrors, function (field) {
                                                    var msg = serverErrors[field].join(",");
                                                    errors.add(undefined, {field: field, message: msg });
                                                });
                                                Ext.getCmp("personadder").down('form').getForm().markInvalid(errors);
                                            }
                                        });
                                    } else {
                                        form.getForm().markInvalid(errors);
                                    }                
                                }
                            }, {
                                text: 'Cancel',
                                handler: function () { Ext.getCmp('personadder').close(); }
                            }
                        ]
                    }
                ]
            }
        });
        me.callParent();
    }
});


// View for Adding a Person. Access from the Add User action on the User Management page.
// will be an extension of a Base view that will be used for Volunteer management but that can come later.
Ext.define('Warehouse.view.user.Add', {
    extend: 'Ext.window.Window',
    alias : 'widget.useradd',
    id: "useradder",
    title : 'Add User',
    layout: 'anchor',
    defaults: { anchor: '100%' },
    autoShow: true,

    initComponent: function() {
        var me = this;
        
        Ext.apply(me, { 
            items: {
                xtype: 'form',
                model: 'Warehouse.model.User',
                border: false,
                bodyPadding: '5',
                width: 400,
                layout: 'anchor',
                default: {
                    anchor: '100%',
                    padding: 5
                },
                fieldDefaults: {
                    margin: '0 5 5 0',
                    msgTarget: 'qtip',
                    labelWidth: 80,
                    labelAlign: 'right'
                },
                items: [
                    {
                        xtype: 'fieldset',
                        title: 'Personal Details',
                        layout: 'anchor',
                        defaults: {
                            anchor: '100%'
                        },
                        items: [
                            {
                                xtype: 'fieldcontainer',
                                fieldLabel: 'Name',
                                layout: 'hbox',
                                margin: 0,
                                combineErrors: true,
                                defaultType: 'textfield',
                                defaults: {
                                    hideLabel: true,
                                    allowBlank: false
                                },
                                items: [
                                    {
                                        name: 'first_name',
                                        flex: 1,
                                        emptyText: 'First Name'
                                    }, {
                                        name: 'last_name',
                                        flex: 1,
                                        emptyText: 'Last Name'
                                    }
                                ]
                            }, {
                                xtype: 'textfield',
                                vtype: 'email',
                                name : 'email',
                                padding: '0 0 5 0',
                                fieldLabel: 'Email',
                                emptyText: 'Email Address'
                            }
                        ]
                    }, {
                        xtype: 'fieldset',
                        title: 'Account Details',
                        layout: 'anchor',
                        defaults: {
                            anchor: '100%'
                        },
                        items: [
                            {
                                xtype: 'fieldcontainer',
                                layout: 'hbox',
                                fieldLabel: 'User',
                                defaults: {
                                    hideLabel: true
                                },
                                items: [
                                    {
                                        xtype: 'textfield',
                                        name : 'username',
                                        flex: 2,
                                        emptyText: 'Username',
                                        fieldLabel: 'User Id',
                                        validator: function (value) {
                                            return value.match(/^[a-z]+[a-z0-9]{2,}$/) ? true : 'Lowercase letters and numbers only. Start with a letter. At least 3 characters.';
                                        }
                                    }, {
                                        xtype: 'checkbox',
                                        name: 'locked',
                                        margin: '0 0 0 10',
                                        flex: 1,
                                        hideLabel: true,
                                        boxLabel: 'Locked'
                                    }
                                ]
                            }, {
                                xtype: 'fieldcontainer',
                                fieldLabel: 'Password',
                                layout: 'hbox',
                                combineErrors: true,
                                defaultType: 'textfield',
                                margin: '0 0 5 0',
                                defaults: {
                                    hideLabel: true,
                                    inputType: 'password'
                                },
                                items: [
                                    {
                                        xtype: 'textfield',
                                        name : 'password',
                                        emptyText: 'Password',
                                        flex: 1,
                                        validator: function (value) {
                                            return (value === "" ? 'value must be present' : true);
                                        }
                                    }, {
                                        xtype: 'textfield',
                                        name : 'confirm_password',
                                        emptyText: 'Confirm password',
                                        flex: 1,
                                        validator: function(value) {
                                            var p1 = me.down('[name=password]');
                                            return (value === p1.getValue()) ? true : 'Passwords do not match.';
                                        }
                                    }       
                                ]
                            }
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
                                var form = this.up('form').getForm(),
                                    win = this.up('window'),
                                    store = Ext.data.StoreManager.lookup('usersStore'),
                                    record = form.getRecord(),
                                    errors;
                                errors = form.updateRecord(record).getRecord().validate();
                                if (errors.isValid()) {
                                    store.add(record);
                                    //TODO: Move sync action into the store definition
                                    store.sync({
                                        success: function() { Ext.getCmp("useradder").close(); },
                                        failure: function(batch, options) {
                                            var jsonData = batch.operations[0].request.scope.reader.jsonData;
                                            Ext.MessageBox.alert("Failed", jsonData["message"]);
                                            store.reload();
                                        }
                                    });
                                } else {
                                    form.markInvalid(errors);
                                }
                            }
                        }, {
                            text: 'Cancel',
                            handler: function () { Ext.getCmp('useradder').close(); }
                        }
                    ]
                }
            ]
            }
        });
        me.callParent();
        var emptyRec = Ext.create('Warehouse.model.User');
        me.down('form').loadRecord(emptyRec);
    }
});


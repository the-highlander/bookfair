Ext.define('Warehouse.view.user.Edit', {
    extend: 'Ext.window.Window',
    alias : 'widget.useredit',
    id: "usereditor",
    title : 'Edit User',
    anchor: '50%',
    autoShow: true,

    initComponent: function() {
        var me = this;
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
                        xtype: 'textfield',
                        name : 'username',
                        fieldLabel: 'User Id',
                        minLength: 3,
                        maxLength: 128
                    }, {
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
                        xtype: 'checkbox',
                        name: 'locked',
                        inputValue: 1,
                        fieldLabel: 'Locked Out'
                    }, {
                        xtype: 'textfield',
                        name : 'password',
                        inputType: 'password',
                        fieldLabel: 'Password',
                        emptyText: 'Password'
                    }, {
                        xtype: 'textfield',
                        name : 'confirm_password',
                        inputType: 'password',
                        padding: '0 0 0 105',
                        emptyText: 'Confirm password',
                        validator  : function(value) {
                            var p1 = me.down('[name=password]');
                            return (value === p1.getValue()) ? true : 'Passwords do not match.';
                        }
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
                                        //record = form.getRecord(),
                                        errors = form.updateRecord().getRecord().validate();
                                   if (errors.isValid()) {
                                        //record.save();
                                        store.sync({
                                            success: function() { 
                                                Ext.getCmp("usereditor").close(); 
                                            },
                                            failure: function(batch, options) {
                                                var jsonData = batch.operations[0].request.scope.reader.jsonData;
                                                Ext.MessageBox.alert("Failed", jsonData["message"]);
                                                store.reload();
                                            }
                                        });
//                                      if (form.down('[name=password]').isDirty()) {
  //                                    }
                                    } else {
                                        console.log("errors", errors);
                                        form.markInvalid(errors);

                                    }
                                }

                            }, {
                                text: 'Cancel',
                                handler: function () { Ext.getCmp('usereditor').close(); }
                            }
                        ]
                    }
                ]
            }
        });
        me.callParent();
    }
});

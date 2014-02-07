Ext.define('Warehouse.view.user.Password', {
    extend: 'Ext.window.Window',
    alias : 'widget.password',
    title : 'Set Password',
    anchor: '50%',
    autoShow: true,
    initComponent: function() {
        var me = this;
        Ext.apply(me, {
            id: 'chgpassword', 
            items: [
                {
                    xtype: 'form',
                    border: false,
                    fieldDefaults: {
                        margin: '5px',
                        labelWidth: 125,
                        msgTarget: 'side',
                        autoFitErrors: false
                    },
                    items: [
                        {
                            xtype: 'textfield',
                            name : 'password',
                            inputType: 'password',
                            emptyText: 'Current password'
                        }, {
                            xtype: 'textfield',
                            name : 'new_password',
                            inputType: 'password',
                            emptyText: 'New password'
                        }, {
                            xtype: 'textfield',
                            name : 'confirm_password',
                            inputType: 'password',
                            emptyText: 'Confirm password',
                            validator  : function(value) {
                                var p1 = me.down('[name=new_password]');
                                return (value === p1.getValue()) ? true : 'Passwords do not match.'
                            }
                        }, {
                            xtype: 'hidden', 
                            msgTarget: 'none',
                            name: 'id'
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
                                    handler: function() {
                                        var form = this.up('form').getForm();
                                        if (form.isValid()) {
                                            form.submit({
                                                clientValidation: true,
                                                submitEmptyText: false,
                                                method: 'PUT',
                                                url: 'users/password',
                                                success: function() { Ext.getCmp("usereditor").close(); },
                                                failure: function (form, action) {
                                                    switch (action.failureType) {
                                                        case Ext.form.action.Action.CLIENT_INVALID:
                                                            Ext.Msg.alert('Failure', 'Form fields may not be submitted with invalid values');
                                                            break;
                                                        case Ext.form.action.Action.CONNECT_FAILURE:
                                                            Ext.Msg.alert('Failure', 'Ajax communication failed');
                                                            break;
                                                        case Ext.form.action.Action.SERVER_INVALID:
                                                           Ext.Msg.alert('Failure', action.result.msg);
                                                   }
                                                }
                                            });
                                        }
                                    }
                                }, {
                                    text: 'Cancel',
                                    handler: function () {
                                        Ext.getCmp("chgpassword").close();
                                    }
                                }
                            ]
                        }
                    ]
                }
            ]
        });
        me.callParent(arguments);
    }
});


//TODO: UI Security based on some client side object representing the user's seurity rights.

Ext.define('Warehouse.view.user.List' ,{
    extend: 'Ext.grid.Panel',
    alias : 'widget.userlist',
    
    initComponent: function() {
        var me = this;
        Ext.apply(me, {
            store:  Ext.create('Warehouse.store.Users', 
                { 
                   storeId: 'usersStore'
                }
            ),
            dockedItems: [
                {   xtype: 'toolbar',
                    dock: 'top',
                    ui: 'footer',
                    items: [
                        {   
                            text: 'Add User',
                            tooltip: 'Add a new User',
                            handler: me.onAddUser
                        }, {
                            text: 'Edit User',
                            id: 'btnEditUser',
                            tooltip: 'Edit the selected User',
                            handler: me.onEditUser                          
                        }, {
                            text: 'Delete User',
                            id: 'btnDeleteUser',
                            tooltip: 'Delete the selected User',
                            handler: me.onDeleteUser
                        }
                    ]                    
                }
            ],
            columns: [
                { header: 'First Name',  dataIndex: 'first_name',  flex: 1 },
                { header: 'Last Name',  dataIndex: 'last_name',  flex: 1 },
                { header: 'User Id', dataIndex: 'username', flex: 1.2 },
                { header: 'Last Login', dataIndex: 'updated_at', xtype: 'datecolumn', format:'D, d M Y h:ia', flex: 1.2 },
                { header: 'Locked', dataIndex: 'locked', flex: 0.5, 
                    renderer: function(val) {
                        var checkedImg = '/bookfair/img/checked.png';
                        var uncheckedImg = '/bookfair/img/unchecked.png';
                        return '<div style="text-align:center;height:13px;overflow:visible">'
                            + '<img style="vertical-align:-3px" src="'
                            + (val ? checkedImg : uncheckedImg)
                            + '" /></div>';
                    }
                }
            ],
            listeners: {
                selectionChange: {
                    fn: me.onSelectionChange
                }
            }
        });
        me.callParent();
    },

    onSelectionChange: function(sel, rec, event) {
    },
            
    onAddUser: function() {
        var view = Ext.widget('useradd');
    },
            
    onEditUser: function () {
        var sm = this.up('userlist').getSelectionModel();
        if (sm.hasSelection()) {
            var record = sm.getSelection()[0],
                view = Ext.widget('useredit');
            view.down('form').loadRecord(record);
        } else {
            Ext.Msg.alert('No User Selected', 'Please select a user from the list and try again.');
        }
    },
   
    onDeleteUser: function() {
        var sm = this.up('userlist').getSelectionModel();
        if (sm.hasSelection()) {
            Ext.Msg.show({
                title: 'Warning',
                icon: Ext.Msg.WARNING,
                msg: 'Are you sure you want to delete this User?',
                buttons: Ext.MessageBox.OKCANCEL,
                fn: function (button) {
                    if (button === 'ok') {
                        var sm = this.up('userlist').getSelectionModel(),
                            record = sm.getSelection()[0],
                            store = sm.getStore();                                        
                        store.remove(record);
                        store.sync({
                            failure: function(batch, options) {
                                var jsonData = batch.operations[0].request.scope.reader.jsonData;
                                Ext.MessageBox.alert("Failed", jsonData["message"]);
                                store.reload();
                            }
                        });                    
                    } 
                },
                scope: this 
            });
        } else {
            Ext.Msg.alert('No User Selected', 'Please select a user from the list and try again.');
        }
    }
  
});

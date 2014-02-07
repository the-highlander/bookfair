//TODO: UI Security based on some client side object representing the user's seurity rights.

Ext.define('Warehouse.view.person.List' ,{
    extend: 'Ext.grid.Panel',
    alias : 'widget.personlist',
    
    initComponent: function() {
        var me = this;
        Ext.apply(me, {
            title : 'All People',
            store:  Ext.create('Warehouse.store.Persons', 
                { 
                   storeId: 'peopleStore'
                }
            ),
            dockedItems: [
                {   xtype: 'toolbar',
                    dock: 'bottom',
                    ui: 'footer',
                    items: [
                        {   
                            text: 'Add Volunteer',
                            id: 'btnAddPerson',
                            tooltip: 'Add a new Person',
                            handler: me.onAddPerson
                        }, {
                            text: 'Edit Volunteer',
                            id: 'btnEditPerson',
                            tooltip: 'Edit the selected Person',
                            handler: me.onEditPerson                          
                        }, {
                            text: 'Delete Person',
                            id: 'btnDeletePerson',
                            tooltip: 'Delete the selected Person',
                            handler: me.onDeletePerson
                        }
                    ]                    
                }
            ],
            columns: [
                { header: 'First Name',  dataIndex: 'first_name',  flex: 1 },
                { header: 'Last Name',  dataIndex: 'last_name',  flex: 1 },
                { header: 'Email',  dataIndex: 'email',  flex: 1 },
                { header: 'Mobile',  dataIndex: 'mobile_phone',  flex: 1 },
                { header: 'Home Phone',  dataIndex: 'home_phone',  flex: 1 },
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
            
    onAddPerson: function() {
        var view = Ext.widget('personadd');
    },
            
    onEditPerson: function () {
        var sm = this.up('userlist').getSelectionModel();
        if (sm.hasSelection()) {
            var record = sm.getSelection()[0],
                view = Ext.widget('personedit');
            view.down('form').loadRecord(record);
        } else {
            Ext.Msg.alert('No User Selected', 'Please select a user from the list and try again.');
        }
    },
   
    onDeletePerson: function() {
        var sm = this.up('personlist').getSelectionModel();
        if (sm.hasSelection()) {
            Ext.Msg.show({
                title: 'Warning',
                icon: Ext.Msg.WARNING,
                msg: 'Are you sure you want to delete this Person?',
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

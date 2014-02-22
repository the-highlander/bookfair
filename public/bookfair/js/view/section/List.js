/*
* File: Warehouse/view/section/List.js
*/
Ext.define('Warehouse.view.section.List' ,{
    extend: 'Ext.grid.Panel',
    alias : 'widget.sectionlist',
    
    initComponent: function() {
        var me = this;
        Ext.apply(me, {
            store: Ext.create('Warehouse.store.Sections', 
                { 
                   storeId: 'sectionStore'
                }
            ),
            plugins: [
				Ext.create('Ext.grid.plugin.RowEditing', {
                    pluginId: 'section-row-editing',
                    autoCancel: true,
                    listeners: {
                        cancelEdit: function(rowEditing, context) {
                            if (context.record.phantom) {
                                // Cancelling edit of a locally added, unsaved record: remove it
                                context.store.remove(context.record);
                            }
                        }
                    }
                })            
            ],
            tbar: [
            	{   
                    text: 'Add',
                    id: 'btnAddSection',
                    tooltip: 'Add a new Section',
                    iconCls: 'icon-add',
                    handler: me.onAddSection                            
                }, '-', {
                    text: 'Edit',
                    id: 'btnEditSection',
                    tooltip: 'Edit the selected Section',
                    iconCls: 'icon-edit',
                    handler: me.onEditSection                
                }, '-', {
                    text: 'Delete',
                    id: 'btnDeleteSection',
                    tooltip: 'Delete the selected Section',
                    iconCls: 'icon-remove',
                    handler: me.onDeleteSection
                }
            ],
            columns: [
            	{
                    header: 'Division',
                    dataIndex: 'division_id',
                    flex: 3,
                    renderer: function(value, metaData, record, rowIndex, colIndex, store, view) {
                        var store = Ext.data.StoreManager.lookup('divisionStore');
                        return store.getById(value).get('name');
                    },
                    editor: {
                        xtype: 'combobox',
                        typeAhead: true,
                        triggerAction: 'all',
                        selectOnTab: true,
                        store: Ext.StoreManager.lookup('divisionStore'),
                        lazyRender: true,
                        listClass: 'x-combo-list-small',
                        displayField: 'name',
                        valueField: 'id',
                        queryMode: 'local',
                        forceSelection: 'true'
                    }
                 }, {
            		header: 'Section',
            		dataIndex: 'name',
            		flex: 4,
            		editor: {
            			xtype: 'textfield'
            		}
            	}, {
                    header: 'Alloc. Tables',
                    dataIndex: 'allocate_tables',
                    renderer: function(value, metaData, record, rowIndex, colIndex, store, view) {
                        return (value) ? 'Yes' : 'No';
                    },
                    editor: new Ext.form.field.ComboBox({
                        typeAhead: true,
                        triggerAction: 'all',
                        selectOnTab: true,
                        store: Warehouse.data.DataSets.yesno,
                        lazyRender: true,
                        listClass: 'x-combo-list-small'
                    })
                }, {
                    header: 'In Use',
                    dataIndex: 'published',
                    hidden: true,
                    width: 60,
                    xtype: 'checkcolumn',
                    stopSelection: false,
                    sortable: false
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


    onAddSection: function () {
        var row, 
            grid = this.up('sectionlist'),
            plugin = grid.getPlugin('section-row-editing'),
            store = grid.getStore();
        plugin.cancelEdit();
        row = Ext.create('Warehouse.model.Section', {
            id: 0,
            published: false,
        });
        row.setDivision(Ext.data.StoreManager.lookup('divisionStore').getAt(0));
        row.phantom = true; // Add phantom property to model and set it true, so this record can be removed if the edit is cancelled.
        console.log("New Model", row);
        store.insert(0,row);
        plugin.startEdit(0,0);    	
    },

    onDeleteSection: function () {
        var grid = this.up('sectionlist'),
            sm = grid.getSelectionModel(),
            record;
        //TODO: Should it prevent deletion if the Section has been used to record actual sales data?
        if (sm.hasSelection()) {
            record = sm.getSelection()[0];
            Ext.Msg.show({
                title: 'Warning',
                icon: Ext.Msg.WARNING,
                msg: Ext.String.format("You are about to delete the <b>{0}</b> Section. Any data associated with this Section " +
                    "will also be removed, including categories, stock allocations, planning data and sales statistics. This operation cannot be undone. <br><br>" +
                    "Delete the {0}?", record.get('name')),
                buttons: Ext.MessageBox.OKCANCEL,
                fn: function (button) {
                    if (button === 'ok') {
                        var sm = this.up('sectionlist').getSelectionModel(),
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
            Ext.Msg.alert('No Section Selected', 'Please select a Section from the list and try again.');
        }

    },

    onEditSection: function () {
        var grid = this.up('sectionlist'),
            sm = grid.getSelectionModel(),
            plugin = grid.getPlugin('section-row-editing'),
            record;
        if (sm.hasSelection()) {
            record = sm.getSelection()[0];
            plugin.startEdit(record.index, 0);            
        } else {
            Ext.Msg.alert('No Section Selected', 'Please select a Section from the list and try again.');
        }    	
    },

    onSelectionChange: function(sel, rec, event) {
    }
            
 });
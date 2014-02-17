/*
* File: Warehouse/view/category/List.js
*/
function ObjectToString(object) {
    var string;
    var name;
console.log(object);
    for (name in object) {
        if (typeof object[name] !== 'function') {
            string += "{\"" + name + "\": \"" + object[name] + "\"}<br />";
        }
    }

    return string;
}

Ext.define('Warehouse.view.category.List' ,{
    extend: 'Ext.grid.Panel',
    alias : 'widget.categorylist',
    requires: [
        'Ext.grid.feature.Grouping',
        'Ext.ux.grid.FiltersFeature'
    ],
    features: [
       {
            ftype: 'grouping',
            groupHeaderTpl: '{[values.groupField == "section_id" ? values.rows[0].data.section_name : values.groupValue]} ({rows.length} Categor{[values.rows.length > 1 ? "ies" : "y"]})' ,
            hideGroupedHeader: true,
            //startCollapsed: true,
            id: 'categoryGrouping'
        }, { 
            ftype: 'filters',
            local: true
        }
    ],
    
    initComponent: function() {
        var me = this,
            sectionStore = Ext.data.StoreManager.get("Warehouse.store.Sections");
        if (!sectionStore) {
            sectionStore = Ext.create("Warehouse.store.Sections");
        }
        Ext.apply(me, {
            store: Ext.create('Warehouse.store.Categories', 
                { 
                   storeId: 'categoryStore'
                }
            ),
            selType: 'rowmodel',
            plugins: [
                Ext.create('Ext.grid.plugin.RowEditing', {
                    pluginId: 'category-row-editing',
                    clicksToEdit: 1,
                    autoCancel: true,
                    listeners: {

                        cancelEdit: function(rowEditing, context) {
                            if (context.record.phantom) {
                                // Cancelling edit of a locally added, unsaved record: remove it
                                Ext.data.StoreManager.lookup('categoryStore').remove(context.record);
                            }
                        },
                        edit: function(rowEditing, context) {
                            console.log("edit event fired");
                            Ext.data.StoreManager.lookup('categoryStore').sync();
                        }

                    }
                })            
            ],
            tbar:[{
                    text: 'Add',
                    id: 'btnAddCategory',
                    tooltip: 'Add a new Category',
                    iconCls:'icon-add',
                    handler: me.onAddCategory
                }, '-', {
                    text: 'Edit',
                    id: 'btnEditCategory',
                    tooltip: 'Edit the selected Category',
                    iconCls:'icon-edit',
                    handler: me.onEditCategory
                },'-',{
                    text: 'Delete',
                    id: 'btnDeleteCategory',
                    tooltip: 'Delete the selected Category',
                    iconCls:'icon-remove',
                    handler: me.onDeleteCategory
                }],
            columns: [{
                    dataIndex: 'section_name',
                    header: 'Section',
                    id: 'secnamecol',
                    flex: 1
            	}, {
            		header: 'Category',
            		dataIndex: 'name',
                    flex: 3,
            		editor: {
            			xtype: 'textfield'
            		}
            	}, {
                    header: 'Label',
                    dataIndex: 'label',
                    editor: {
                        xtype: 'textfield'
                    }
                }, {
                    header: 'Measure',
                    dataIndex: 'measure',
                    renderer: function(value, metaData, record, rowIndex, colIndex, store, view) {
                        switch (value) {
                            case "box": 
                                return "Boxes";
                                break;
                            case "table": 
                                return "Tables";
                                break;
                            case "percent": 
                                return "Percentage";
                                break;
                        }
                    },
                    editor: new Ext.form.field.ComboBox({
                        lazyRender: true,
                        listClass: 'x-combo-list-small',
                        selectOnTab: true,
                        store: Warehouse.data.DataSets.measures,
                        triggerAction: 'all',
                        typeAhead: true,
                        valueField: 'value'
                    })
                }, {
                    header: 'Boxes Per Pallet',
                    dataIndex: 'pallet_loading',
                    editor: {
                        xtype: 'numberfield'
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
        var store = me.getStore(),
            groups = store.getGroups();

        me.groupingFeature = me.view.getFeature('categoryGrouping');
    },


    onAddCategory: function (btn, event) {
        console.log("open a window to Edit the Category")
        /* var row, 
            grid = this.up('categorylist'),
            plugin = grid.getPlugin('category-row-editing'),
            store = grid.getStore();
            // categoryID = Ext.getCmp('categoryCombo').getValue();
       
        plugin.cancelEdit();
        row = Ext.create('Warehouse.model.Category', {
            id: 0,
            allocate_tables: true,
            category_id: 0,
            default_loading: 10,
            default_measure: 'table',
            published: false
        });
        row.phantom = true; // Add phantom property to model and set it true, so this record can be removed if the edit is cancelled.
        store.insert(0,row);
        plugin.startEdit(0,0);
        */
    },

    onDeleteCategory: function () {
        var grid = this.up('categorylist'),
            sm = grid.getSelectionModel(),
            record;
        //TODO: Should it prevent deletion if the Category has been used to record actual sales data?
        if (sm.hasSelection()) {
            record = sm.getSelection()[0];
            Ext.Msg.show({
                title: 'Warning',
                icon: Ext.Msg.WARNING,
                msg: Ext.String.format("You are about to delete <b>{0}</b> Category. Any data associated with this Category " +
                    "will also be removed, including stock allocations, planning data and sales statistics. This operation cannot be undone. <br><br>" +
                    "Delete Category \"<b>{0}</b>\"?", record.get('name')),
                buttons: Ext.MessageBox.OKCANCEL,
                fn: function (button) {
                    if (button === 'ok') {
                        var sm = this.up('categorylist').getSelectionModel(),
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
            Ext.Msg.alert('No Category Selected', 'Please select a Category from the list and try again.');
        }

    },

    onEditCategory: function () {
        var grid = this.up('categorylist'),
            sm = grid.getSelectionModel(),
            plugin = grid.getPlugin('category-row-editing'),
            record;
        if (sm.hasSelection()) {
            record = sm.getSelection()[0];
            plugin.startEdit(record.index, 0);            
        } else {
            Ext.Msg.alert('No Category Selected', 'Please select a Category from the list and try again.');
        }       
    },

    onSelectionChange: function(sel, rec, event) {         
    }
            
 });
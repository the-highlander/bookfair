/*
* File: Warehouse/view/statistics/Targets.js
*/
Ext.define('Warehouse.view.statistics.Targets', {
   extend: 'Ext.grid.Panel',
    alias : 'widget.targets',
    requires: [
        'Warehouse.data.proxy.Restful',
        'Ext.grid.feature.GroupingSummary',
        'Ext.grid.plugin.RowEditing'
    ],
    features: [
        {
            ftype: 'groupingsummary',
            groupHeaderTpl: '{name} ({rows.length} Categor{[values.rows.length > 1 ? "ies" : "y"]})' ,
            id: 'TargetsGrouping',
            enableGroupingMenu: false,
            enableNoGroups: false,
            startCollapsed: true  // 19/1/14 This is not working
        }, { 
            ftype: 'filters',
            local: true
        }
    ],
    initComponent: function() {
        var me = this,
            bookfairStore = Ext.data.StoreManager.lookup('bookfairStore');

        Ext.apply(me, {
            loadMask: true,
            tbar: [
                {
                    text: 'Add',
                    id: 'btnAddCategory',
                    tooltip: 'Add a Category',
                    iconCls: 'icon-add',
                    handler: me.onAddButtonClicked
                }, {
                    text: 'Remove',
                    id: 'btnRemoveCategory',
                    tooltip: 'Remove selected Category',
                    iconCls: 'icon-remove',
                    disabled: true,
                    handler: me.onRemoveButtonClicked
                }, '-', {
                    text: 'Collapse All',
                    id: 'btnCollapseAll',
                    tooltip: 'Collapse all Sections',
                    handler: me.onCollapseButtonClicked
                }
            ],
            store: Ext.create('Warehouse.store.Targets', {
                storeId: 'targetsStore'
            }),
            selType: 'rowmodel',
            //TODO: Ability to edit data depends on security. Add plugins optionally.
            plugins: [
                Ext.create('Ext.grid.plugin.RowEditing', {
                    pluginId: 'stats-row-editing',
                    clicksToEdit: 2,
                    autoCancel: true 
                })            
            ],            
            columns: [
                {
                    header: 'Section',
                    dataIndex: 'section_id',
                    hidden: true,
                    renderer: function (value, metaData, record) {
                        return record.get('section_name');
                    },
                    filter: {
                        type: 'list',
                        labelField: 'name',
                        store:  Ext.create('Ext.data.Store', {
                            fields: ['id', 'name'],
                            proxy: {
                                type: 'ajax',
                                url: 'sections',
                                reader: { type: 'json'}
                            }
                        })
                    }
                }, {
                    header: 'Category',
                    flex: 1,
                    dataIndex: 'name',
                    minWidth: 200,
                    editor: {
                        xtype: 'textfield',
                        allowBlank: false
                    }
                }, {
                    header: 'Label',
                    width: 80,
                    dataIndex: 'label',
                    editor: {
                        xtype: 'textfield'
                    }
                }, {
                    header: 'Measure',
                    width: 80,
                    dataIndex: 'measure',
                    editor: {
                        xtype: 'combo',
                        typeAhead: true,
                        triggerAction: 'all',
                        selectOnTab: true,
                        store: Warehouse.data.DataSets.measures,                            
                        lazyRender: true,
                        listClass: 'x-combo-list-small'                   
                    },
                }, {
                    header: 'Target',
                    dataIndex: 'target',
                    xtype: 'numbercolumn',
                    format: '0,000',
                    editor: {
                        xtype: 'numberfield',
                        minValue: '0'
                    },
                    summaryType: 'sum'
                }, {
                    xtype: 'checkcolumn',
                    header: 'Allocate',
                    dataIndex: 'allocate',
                    width: 60,
                    editor: {
                        xtype: 'checkbox',
                        cls: 'x-grid-checkheader-editor'
                    },
                    stopSelection: false,
                    sortable: false
                }, {
                    xtype: 'checkcolumn',
                    header: 'Track',
                    dataIndex: 'track',
                    width: 60,
                    editor: {
                        xtype: 'checkbox',
                        cls: 'x-grid-checkheader-editor'
                    },
                    stopSelection: false,
                    sortable: false,
                    filter: {
                        type: 'boolean',
                        dataIndex: 'track',
                        active: true,
                        defaultValue: true
                    }

                }
            ],
            listeners: {
                selectionchange: {
                    fn: me.onSelectionChange
                },
                afterrender: function() { 
                    me.filters.createFilters(); // Apply default filters
                }
            }
        });
        me.store.getProxy().setBookfair(this.initialConfig.bookfair.get('id'));
        me.callParent();
        me.groupingFeature = me.view.getFeature('TargetsGrouping');
   },

    onAddButtonClicked: function () {
        console.log("You clicked the add button");
        var view = Ext.widget('statsadd');        
    },

    onCollapseButtonClicked: function () {
        var grid = this.up('targets');
        grid.groupingFeature.collapseAll();
    },

    onSelectionChange: function (sm, recs, event) {
        if (recs.length == 1) {    
            Ext.getCmp('btnRemoveCategory').enable();
        } else {
            Ext.getCmp('btnRemoveCategory').disable();
        }
    },

    onRemoveButtonClicked: function () {
        var grid = this.up('targets'),
            sm = grid.getSelectionModel(),
            record, store = grid.getStore();
        if (sm.hasSelection()) {
            record = sm.getSelection()[0];
            Ext.Msg.show({
                title: 'Warning',
                icon: Ext.Msg.WARNING,
                msg: Ext.String.format("You are about to remove <b>{0}</b> from this Bookfair. Any allocation or sales data recorded against " +
                    "this Category for this Bookfair will be lost. This operation cannot be undone. <br><br>" +
                    "Do you wish to proceed??", record.get('name')),
                buttons: Ext.MessageBox.OKCANCEL,
                fn: function (button) {
                    if (button === 'ok') {
                        store.remove(record);
                        store.sync();
                    }
                },
                scope: this
            });
        }
    }

});
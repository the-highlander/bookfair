/**
 * @file: view/statistics/Targets.js
 * @author Russell Nash
 */
Ext.define('Warehouse.view.statistics.Targets', {
    extend: 'Ext.grid.Panel',
    alias: 'widget.targets',
    requires: [
        'Warehouse.data.proxy.Restful',
        'Ext.grid.feature.GroupingSummary',
        'Ext.grid.plugin.RowEditing'
    ],
    selType : 'rowmodel',
    selModel: { 
        mode: 'MULTI'
    },
    features: [
        {
            ftype: 'groupingsummary',
            groupHeaderTpl: '{name} ({rows.length} Categor{[values.rows.length > 1 ? "ies" : "y"]})',
            id: 'TargetsGrouping',
            enableGroupingMenu: false,
            enableNoGroups: false,
            startCollapsed: false
        }, {
            ftype: 'filters',
            local: true
        }
    ], 
    initComponent: function() {
        var me = this;

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
                    disabled: true,
                    tooltip: 'Collapse all Sections',
                    handler: me.onCollapseButtonClicked
                }
            ],
            store: Ext.create('Warehouse.store.Targets', {
                storeId: 'targetsStore'
            }),
            //TODO: Ability to edit data depends on security. Add plugins optionally.
            plugins: [
                 Ext.create('Ext.grid.plugin.RowEditing', {
                     pluginId: 'target-row-editing',
                     clicksToEdit: 2,
                     clicksToMove: 1,
                     autoCancel: true
                 })
            ],
            columns: [
                {
                    header: 'Section',
                    dataIndex: 'section_id',
                    hidden: true,
                    renderer: function(value, metaData, record) {
                        return record.get('section_name');
                    },
                    filter: {
                        type: 'list',
                        labelField: 'name',
                        store: Ext.create('Ext.data.Store', {
                            fields: ['id', 'name'],
                            proxy: {
                                type: 'ajax',
                                url: 'sections',
                                reader: {type: 'json'}
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
                    }
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
                    header: 'Pallet',
                    dataIndex: 'pallet_id',
                    id: 'palletcol',
                    renderer: function(value, metaData, record, rowIndex, colIndex, store, view) {
                        return record.get('pallet_name');
                    },
                    editor: {
                        xtype: 'combobox',
                        listClass: 'x-combo-list-small',
                        selectOnTab: true,
                        selectOnFocus: true,
                        store: Ext.data.StoreManager.lookup('palletStore'),
                        queryMode: 'local',
                        triggerAction: 'all',
                        typeAhead: true,
                        minChars: 1,
                        autoSelect: true,
                        valueField: 'id',
                        displayField: 'name'
                    }
                }, {
                    header: 'Allocate',
                    dataIndex: 'allocate',
                    width: 60,
                    renderer: function(val) {
                        var checkedImg = '/bookfair/img/checked.gif';
                        var uncheckedImg = '/bookfair/img/unchecked.gif';
                        return '<div style="text-align:center;height:13px;overflow:visible">'
                                + '<img style="vertical-align:-3px" src="'
                                + (val ? checkedImg : uncheckedImg)
                                + '" /></div>';
                    },
                    editor: {
                        xtype: 'checkbox',
                        cls: 'x-grid-checkheader-editor'
                    },
                    sortable: false
                }, {
                    header: 'Track',
                    dataIndex: 'track',
                    width: 60,
                    renderer: function(val) {
                        var checkedImg = '/bookfair/img/checked.gif';
                        var uncheckedImg = '/bookfair/img/unchecked.gif';
                        return '<div style="text-align:center;height:13px;overflow:visible">'
                                + '<img style="vertical-align:-3px" src="'
                                + (val ? checkedImg : uncheckedImg)
                                + '" /></div>';
                    },
                    editor: {
                        xtype: 'checkbox',
                        cls: 'x-grid-checkheader-editor'
                    },
                    sortable: false
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
    },
    onAddButtonClicked: function(btn) {
        var view = Ext.widget('statsadd', { bookfair: btn.up('targets').initialConfig.bookfair });
    },
    onCollapseButtonClicked: function() {
        var grid = this.up('targets');
        grid.groupingFeature.collapseAll();
    },
    onSelectionChange: function(sm, recs, event) {
         if (recs.length === 0) {
            Ext.getCmp('btnRemoveCategory').disable();
        } else {
            Ext.getCmp('btnRemoveCategory').enable();
        }
    },
    onRemoveButtonClicked: function() {
        var grid = this.up('targets'),
                sm = grid.getSelectionModel(),
                records, store = grid.getStore();
        if (sm.hasSelection()) {
            records = sm.getSelection();
            Ext.Msg.show({
                title: 'Warning',
                icon: Ext.Msg.WARNING,
                msg: Ext.String.format("You are about to remove <b>{0}</b> from this Bookfair. Associated packing targets, " +
                        "table allocations and sales data recorded will be lost. This operation cannot be undone. <br><br>" +
                        "Do you wish to proceed?", records.length === 1 ? records[0].get('name') : records.length + ' Categories'),
                buttons: Ext.MessageBox.OKCANCEL,
                fn: function(button) {
                    if (button === 'ok') {
                        store.remove(records);
                    }
                },
                scope: this
            });
        }
    }
});
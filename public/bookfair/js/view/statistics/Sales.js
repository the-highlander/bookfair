/*
* File: Warehouse/view/statistics/Sales.js
*/
Ext.define('Warehouse.view.statistics.Sales', {
   extend: 'Ext.grid.Panel',
    alias : 'widget.sales',
    requires: [
        'Warehouse.data.proxy.Restful',
        'Ext.grid.feature.GroupingSummary',
        'Ext.grid.plugin.RowEditing'
    ],
    features: [
        {
            ftype: 'groupingsummary',
            groupHeaderTpl: '{name} ({rows.length} Categor{[values.rows.length > 1 ? "ies" : "y"]})' ,
            id: 'StatsGrouping',
            enableGroupingMenu: false,
            enableNoGroups: false,
            startCollapsed: true
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
            tbar: [ //TODO: Additional buttons for show/hide columns etc. Whatever is useful.  Stats Sheet for example.
            //TODO: Print Stats sheet from here - show table/box data if already entered.
                {
                    text: 'Collapse All',
                    id: 'btnCollapseAll',
                    tooltip: 'Collapse all Sections',
                    handler: me.onCollapseButtonClicked
                }
            ],
            store: Ext.create('Warehouse.store.Sales', {
                storeId: 'salesStore'
            }),
            //TODO: Ability to edit data depends on security. Add plugins optionally.
            plugins: [
                Ext.create('Ext.grid.plugin.RowEditing', {
                    pluginId: 'stats-row-editing',
                    clicksToEdit: 1,
                    autoCancel: true,
                    listeners: {
                        edit: function(rowEditing, context) { // ** Not needed if storeis set to autosync. Which is better?
                            console.log("edit event fired");
                            Ext.data.StoreManager.lookup('salesStore').sync();
                        }

                    }
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
                    minWidth: 200
                }, {
                    header: 'Label',
                    width: 60,
                    dataIndex: 'label'
                }, {
                    header: 'Measure',
                    width: 70,
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
                    header: 'Delivered',
                    dataIndex: 'delivered',
                    width: 70,
                    align: 'right',
                    xtype: 'numbercolumn',
                    format: '0,000',
                    editor: {
                        xtype: 'numberfield',
                        minValue: '0'
                    },
                    summaryType: 'sum'
                }, {
                    text: 'Start of Bookfair',
                    defaults: {
                        width: 65,
                        xtype: 'numbercolumn',
                        align: 'right',
                        summaryType: 'sum'
                    },
                    columns: [
                        {
                            header: 'Display',
                            dataIndex: 'start_display',
                            editor: {
                                xtype: 'numberfield',
                                minValue: '0'
                            }
                        }, {
                            header: 'Reserve',
                            dataIndex: 'start_reserve',
                            editor: {
                                xtype: 'numberfield',
                                minValue: '0'
                            }
                        }
                    ]
                }, {
                    text: 'Friday',
                    defaults: { 
                        width: 65,
                        align: 'right',
                        xtype: 'numbercolumn',
                        summaryType: 'sum'
                    },
                    columns: [
                        {
                            header: 'Extras',
                            dataIndex: 'fri_extras',
                            hidden: true,
                            editor: {
                                xtype: 'numberfield',
                                minValue: '0'
                            }
                        }, {
                            header: 'Display',
                            dataIndex: 'fri_end_display',
                            editor: {
                                xtype: 'numberfield',
                                minValue: '0'
                            }
                        }, {
                            header: 'Reserve',
                            dataIndex: 'fri_end_reserve',
                            editor: {
                                xtype: 'numberfield',
                                minValue: '0'
                            }
                        }
                    ]
                }, {
                    text: 'Saturday',
                    defaults: { 
                        width: 65,
                        align: 'right',
                        xtype: 'numbercolumn',
                        decimalPrecision: 2,
                        summaryType: 'sum'
                    },
                    columns: [
                        {
                            header: 'Extras',
                            dataIndex: 'sat_extras',
                            hidden: true,
                            editor: {
                                xtype: 'numberfield',
                                minValue: '0'
                            }
                        }, {
                            header: 'Display',
                            dataIndex: 'sat_end_display',
                            editor: {
                                xtype: 'numberfield',
                                minValue: '0'
                            }
                        }, {
                            header: 'Reserve',
                            dataIndex: 'sat_end_reserve',
                            editor: {
                                xtype: 'numberfield',
                                minValue: '0'
                            }
                        }
                    ]
                }, {
                    text: 'Sunday (noon)',
                    defaults: { 
                        width: 65,
                        align: 'right',
                        xtype: 'numbercolumn',
                        summaryType: 'sum'
                    },
                    columns: [
                        {
                            header: 'Extras',
                            dataIndex: 'sun_extras',
                            hidden: true,
                            editor: {
                                xtype: 'numberfield',
                                minValue: '0'
                            }
                        }, {
                            header: 'Display',
                            dataIndex: 'sun_end_display',
                            editor: {
                                xtype: 'numberfield',
                                minValue: '0'
                            }
                        }, {
                            header: 'Reserve',
                            dataIndex: 'sun_end_reserve',
                            editor: {
                                xtype: 'numberfield',
                                minValue: '0'
                            }
                        }
                    ]
                }, {
                    text: 'End of Bookfair',
                    defaults: { 
                        width: 65,
                        align: 'right',
                        xtype: 'numbercolumn',
                        summaryType: 'sum'
                    },
                    columns: [
                        {
                            header: 'Display',
                            dataIndex: 'end_display',
                            editor: {
                                xtype: 'numberfield',
                                minValue: '0'
                            }
                        }, {
                            header: 'Reserve',
                            dataIndex: 'end_reserve',
                            editor: {
                                xtype: 'numberfield',
                                minValue: '0'
                            }
                        }
                    ]
                }
            ],
            listeners: {
                selectionchange: {
                    fn: me.onSelectionChange
                }
            }
        });
        me.store.getProxy().setBookfair(this.initialConfig.bookfair.get('id'));
        me.callParent();
        me.groupingFeature = me.view.getFeature('StatsGrouping');
    },

    onCollapseButtonClicked: function () {
        var grid = this.up('sales');
        grid.groupingFeature.collapseAll();
    },

    onSelectionChange: function (sm, recs, event) {
        if (recs.length == 1) {    
            Ext.getCmp('btnEditSalesData').enable();
        } else {
            Ext.getCmp('btnEditSalesData').disable();
        }
    },

});

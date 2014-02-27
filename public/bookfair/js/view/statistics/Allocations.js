/*
* File: Warehouse/view/statistics/Allocations.js
*/
Ext.define('Warehouse.view.statistics.Allocations', {
    extend: 'Ext.grid.Panel',
    alias : 'widget.allocations',
    requires: [
        'Warehouse.data.proxy.Restful',
        'Ext.grid.feature.GroupingSummary'
    ],
    //TODO: Ability to edit data depends on security. Add plugins optionally.
    plugins: [
       Ext.create('Ext.grid.plugin.CellEditing', {
            clicksToEdit: 1,
            listeners: {
                edit: function(editor, e) {
                    if (e.field === 'packed') {
                        var store = Ext.data.StoreManager.lookup('allocationStore'),
                            group = e.record.get('tablegroup_name'),
                            tablesInGroup = e.record.get('tablegroup_tables'),
                            rows = store.getGroups(group).children,
                            totalBoxes = store.sum('packed', group)[group];
                        Ext.Array.each(rows, function(row, index, groupItSelf) {
                            var suggested = row.get('packed') / totalBoxes * tablesInGroup;
                            row.set('suggested', suggested);
                        });
                    }
                }
            }
        })           
    ],
    features: [
        {
            ftype: 'groupingsummary',
            groupHeaderTpl: Ext.create('Ext.XTemplate', 
                '{name:this.formatName} {children:this.getTableCount}',
                {
                    formatName: function (name) {
                        return (name === "") ? "Ungrouped" : "Group " + name;
                    },
                    getTableCount: function (children) {
                        return (children[0].get('tablegroup_name') === "") ? "" : "(" + children[0].get('tablegroup_tables') + " Tables)";
                    }
                }
            ),
            id: 'allocationGrouping',
            enableGroupingMenu: false,
            enableNoGroups: false
        }, {
            ftype: 'filters',
            local: true
        }
    ],
    selType: 'cellmodel', // supports cell editing (delete if you go back to RowEditing) ,
    columnLines: true,
    initComponent: function() {
        var me = this,
            bookfairStore = Ext.data.StoreManager.lookup('bookfairStore');
        Ext.apply(me, {
            loadMask: true,
            title: 'Allocations',
            tbar: [ //TODO: Additional buttons for show/hide columns etc. Whatever is useful.
                {
                    text: 'Save',
                    id: 'btnSaveAllocationData',
                    tooltip: 'Save changes to allocations',
                    iconCls: 'icon-save',
                    handler: function (btn, event) {
                        var store = Ext.data.StoreManager.lookup('allocationStore');
                        store.suspendEvents();
                        store.sync();
                        store.resumeEvents();
                    }
                }, '-', {
                    text: 'Collapse All',
                    id: 'btnCollapseAll',
                    tooltip: 'Collapse all Sections',
                    handler: me.onCollapseButtonClicked
                }
            ],
            //TODO: Save button should be disabled until the store is dirty  -- are these listeners on the store?
            store: Ext.create('Warehouse.store.Allocations', { 
                storeId: 'allocationStore' 
            }),
            columns: [
                {
                    header: 'Section',
                    dataIndex: 'section_name'
                }, {
                    header: 'Category',
                    flex: 1,
                    dataIndex: 'name',
                    minWidth: 200
                }, {
                    header: 'Label',
                    width: 80,
                    dataIndex: 'label'
                }, {
                    header: 'Table<br/>Group',
                    dataIndex: 'tablegroup_id',
                    id: 'tabgrpcol',
                    renderer: function(value, metaData, record, rowIndex, colIndex, store, view) {
                        return record.get('tablegroup_name');
                    },
                    editor: new Ext.form.field.ComboBox(
                        {
                            lazyRender: true,
                            listClass: 'x-combo-list-small',
                            selectOnTab: true,
                            selectOnFocus: true,
                            store: Ext.data.StoreManager.lookup('tableGroupStore'),
                            queryMode: 'local',
                            triggerAction: 'all',
                            typeAhead: true,
                            minChars: 1,
                            autoSelect: true,
                            valueField: 'id',
                            displayField: 'name'
                        }
                    )
                }, {
                    text: 'Total<br />Boxes',
                    dataIndex: 'packed',
                    summaryType: 'sum',
                    editor: {
                        xtype: 'numberfield', 
                        allowDecimals: false, 
                        allowBlank: false 
                    }
                }, {
                    text: 'Boxes<br />Per Table',
                    dataIndex: 'loading',
                    editor: {
                        xtype: 'numberfield', 
                        allowBlank: false 
                    }
                }, {
                    text: 'Suggested<br />Tables',
                    dataIndex: 'suggested',
                    xtype: 'numbercolumn', 
                    format:'0.00',                    
                    summaryType: 'sum'
                }, { 
                    text: 'Number<br />of Tables',
                    dataIndex: 'allocated',
                    summaryType: 'sum',
                    editor: {
                        xtype: 'numberfield', 
                        allowDecimals: true, 
                        allowBlank: false,
                        minValue: 0.1,
                        maxValue: 11.0,
                        step: 0.25
                    }
                }, {
                    text: 'Boxes<br />On Table',
                    dataIndex: 'display' 
                }, {
                    text: 'Boxes<br />Under Table',
                    dataIndex: 'reserve'
                }, {
                    text: 'Discrepancy',
                    dataIndex: 'discrepancy',
                    renderer: function (value) {
                        return value === 0 ? value : '<span style="color:red; font-weight: bold;">' + Math.abs(value) + '</span>';
                    }
                }
            ]
        });
        me.store.getProxy().setBookfair(this.initialConfig.bookfair.get('id'));
        me.callParent();
        me.groupingFeature = me.view.getFeature('allocationGrouping');
    },

    onCollapseButtonClicked: function () {
        var grid = this.up('allocations');
        grid.groupingFeature.collapseAll();
    }

});


 function ObjectToString(object) {
    var string;
    var name;
    for (name in object) {
        if (typeof object[name] !== 'function') {
            string += "{\"" + name + "\": \"" + object[name] + "\"}<br />";
        }
    }

    return string;
}
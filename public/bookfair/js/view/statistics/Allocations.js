/*
* File: Warehouse/view/statistics/Allocations.js
*/
Ext.define('Warehouse.view.statistics.Allocations', {
    extend: 'Ext.grid.Panel',
    alias : 'widget.allocations',
    requires: [
        'Warehouse.data.proxy.Restful',
        'Ext.grid.feature.GroupingSummary',
        'Ext.grid.plugin.BufferedRenderer'
    ],
    //TODO: Ability to edit data depends on security. Add plugins optionally.
    plugins: [
       Ext.create('Ext.grid.plugin.CellEditing', {
            clicksToEdit: 1
       }),
       { ptype: 'bufferedrenderer' }
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
            tbar: [ //TODO: Additional buttons for show/hide columns etc. Whatever is useful.
                {
                    text: 'Save',
                    id: 'btnSaveAllocationData',
                    tooltip: 'Save changes to allocations',
                    iconCls: 'icon-save',
                    handler: me.onSaveButtonClicked
                }, '-', {
                    text: 'Move Up',
                    id: 'btnMoveUp',
                    disabled: true,
                    tooltip: 'Move toward start of table group',
                    iconCls: 'icon-move-up',
                    handler: me.onMoveUpButtonClicked
                }, {
                    text: 'Move Down',
                    id: 'btnMoveDown',
                    disabled: true,
                    tooltip: 'Move toward end of table group',
                    iconCls: 'icon-move-down',
                    handler: me.onMoveDownButtonClicked
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
                {   header: '#',
                    dataIndex: 'position', sortable: false, width: 20
                }, {
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
                    text: 'Boxes<br />Packed',
                    dataIndex: 'packed',
                    summaryType: 'sum',
                    editor: {
                        xtype: 'numberfield', 
                        allowDecimals: false, 
                        allowBlank: false 
                    }
                }, { 
                    text: '% In<br />This Group',
                    dataIndex: 'portion',
                    renderer: function(value, metaData, record, rowIndex, colIndex, store, view) {
                        return 100 * value + "%";
                    },
                    editor: {
                        xtype: 'numberfield',
                        allowDecimals: true,
                        allowBlank: false,
                        maxValue: 1.00,
                        minValue: 0.01,
                        step: 0.01
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
                    dataIndex: 'tables',
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
                }
            ],
            listeners: {
                selectionchange: {
                    fn: me.onSelectionChange
                },
                edit: {
                    fn: me.onEdit
                }
            }
        });
        me.store.getProxy().setBookfair(this.initialConfig.bookfair.get('id'));
        me.callParent();
        me.groupingFeature = me.view.getFeature('allocationGrouping');
    },

    onCollapseButtonClicked: function () {
        var grid = this.up('allocations');
        grid.groupingFeature.collapseAll();
    },
    
    onEdit: function (editor, e) {
        var store = Ext.data.StoreManager.lookup('allocationStore'),
            recordsInGroup = store.getGroups(e.record.get('tablegroup_name')).children,
            tablesInGroup = e.record.get('tablegroup_tables'),
            boxesInGroup = 0;
        Ext.Array.each(recordsInGroup, function (row, index, rows) {
            boxesInGroup += row.get('packed');
        });
        e.grid.suspendLayout = true;
        if (e.field === 'packed') {
            Ext.Array.each(recordsInGroup, function(row, index, rows) {
                row.set('suggested', Math.round((row.get('packed') / boxesInGroup * tablesInGroup), 2));
            });
        }
        if (e.field === 'tablegroup_id') {
          console.log('need to set position new group", e.record.get('tablegroup_name'));
          store.sort();
        }
        if (e.record.get('tables') === 0) {
            e.record.set({
                'display': 0,
                'reserve': 0
            });
        } else {
            e.record.set({
                'display': Math.min(e.record.get('packed'), Math.floor(e.record.get('tables') * e.record.get('loading'))),
                'reserve': Math.max(0, e.record.get('packed') - e.record.get('display'))
            });
        }
        e.grid.suspendLayout = false;
        e.grid.doLayout();
    },
    
    moveSelectedRow: function (grid, up) {
      var store = grid.getStore(), 
          sm = grid.getSelectionModel(),
          record = sm.getSelection()[0],
          index, adj = 1;
      if (!record) {
          return;
      }
      index = store.indexOf(record);
      if (up) {
          index--;
          if (index < 0) { return; }
          adj = -1;
      } else {
          index++;
          if (index >= store.getCount()) { // Need to handle group!
              return;
          }
      }
      store.getAt(index).set('position', record.get('position'));
      grid.suspendLayout = true;
      record.set('position', record.get('position') + adj);       
      store.remove(record, true);
      store.insert(index, record);
      sm.select(record);
      grid.suspendLayout = false;
      grid.doLayout();
    },
    
    onMoveDownButtonClicked: function () {
        var grid = this.up('allocations');
        grid.moveSelectedRow(grid, false);
    },
    
    onMoveUpButtonClicked: function () {
        var grid = this.up('allocations');
        grid.moveSelectedRow(grid, true);
    },
    
    onSaveButtonClicked: function (btn, event) {
        var store = Ext.data.StoreManager.lookup('allocationStore');
        var grid = this.up('allocations');
        grid.suspendLayout = true;
        store.sync();
        grid.suspendLayout = false;
        grid.doLayout();
    },
    
    onSelectionChange: function(sm, recs, event) {
        if (recs.length === 0) {
            Ext.getCmp('btnMoveUp').disable();
            Ext.getCmp('btnMoveDown').disable();
        } else {
            var store = sm.getStore(),
                pos = recs[0].get('position'),
                //TODO: When category doesn't have a tablegroup this generates an error - group is undefined
                group = store.getGroups(recs[0].get('tablegroup_name'));
            if (pos > 1) {
                Ext.getCmp('btnMoveUp').enable();
            } else {
                Ext.getCmp('btnMoveUp').disable();
            };
            if (pos < group.children.length) {
                Ext.getCmp('btnMoveDown').enable();
            } else {
                Ext.getCmp('btnMoveDown').disable();
            };
        }
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
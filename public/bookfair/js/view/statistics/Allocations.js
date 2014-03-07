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
            clicksToEdit: 1
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
                    text: 'Move Up',
                    id: 'btnMoveUp',
                    disabled: true,
                    tooltip: 'Move toward start of table group',
                    handler: me.onMoveUpButtonClicked
                }, {
                    text: 'Move Down',
                    id: 'btnMoveDown',
                    disabled: true,
                    tooltip: 'Move toward end of table group',
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
                    text: 'Boxes<br />Per Table',
                    dataIndex: 'base_load',
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
                    dataIndex: 'setup_display' 
                }, {
                    text: 'Boxes<br />Under Table',
                    dataIndex: 'setup_reserve'
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
        Ext.each(records, function (record, i, records) {
            record.set('position', record.get('position')+1);
        });
    },
    
    onEdit: function (editor, e) {
        var store = Ext.data.StoreManager.lookup('allocationStore'),
            recordsInGroup = store.getGroups(e.record.get('tablegroup_name')).children,
            tablesInGroup = e.record.get('tablegroup_tables'),
            boxesInGroup = 0;
        Ext.Array.each(recordsInGroup, function (row, index, rows) {
            boxesInGroup += row.get('packed');
        });
        //TODO: if (e.field === 'tablegroup_id' then need to move row and recalculate both affected groups
        if (e.field === 'packed') {
            Ext.Array.each(recordsInGroup, function(row, index, rows) {
                row.set('suggested', Math.round((row.get('packed') / boxesInGroup * tablesInGroup), 2));
            });
        }
        if (e.record.get('allocated') === 0) {
            e.record.set('setup_display', 0);
            e.record.set('setup_reserve', 0);
        } else {
            e.record.set('setup_display', Math.min(e.record.get('packed'), Math.floor(e.record.get('allocated') * e.record.get('base_load'))));
            e.record.set('setup_reserve', Math.max(0, e.record.get('packed') - e.record.get('setup_display')));
        }
    },
    
    moveSelectedRow: function (grid, up) {
      var record = grid.getSelectionModel().getSelection()[0];
      if (!record) {
          return;
      }
      var index = grid.getStore().indexOf(record);
      if (up) {
          index--;
          if (index < 0) { 
              return;
          }
          grid.getStore().getAt(index).set('position', record.get('position'));
          record.set('position', record.get('position')-1);         
      } else {
          index++;
          if (index >= grid.getStore().getCount()) { // Need to handle group!
              return;
          }
          grid.getStore().getAt(index).set('position', record.get('position'));
          record.set('position', record.get('position')+1);
      }
      grid.getStore().remove(record, true);
      grid.getStore().insert(index, record);
      grid.getSelectionModel().select(record);
    },
    
    onMoveDownButtonClicked: function () {
        var grid = this.up('allocations');
        grid.moveSelectedRow(grid, false);
    },
    
    onMoveUpButtonClicked: function () {
        var grid = this.up('allocations');
        grid.moveSelectedRow(grid, true);
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
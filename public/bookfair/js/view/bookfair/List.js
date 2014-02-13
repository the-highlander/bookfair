Ext.define('Warehouse.view.bookfair.List', { 

    extend: 'Ext.grid.Panel',
    alias : 'widget.bookfairlist',
    require: [
        'Warehouse.view.Print', 
        'Warehouse.view.PrintTabs',
        'Ext.grid.plugin.RowEditing'
    ],

    initComponent: function () {
        var me = this;
        Ext.apply(me, {
            loadMask: true,
            store: Ext.create('Warehouse.store.Bookfairs',
                {
                    storeId: 'bookfairStore'
                }),
            tbar: [
                {
                    text: 'Add Bookfair',
                    id: 'btnAddBookfair',
                    tooltip: 'Create a new Bookfair',
                    handler: me.onAddBookfair
                }, {
                    text: 'Edit Bookfair',
                    id: 'btnEditBookfair',
                    tooltip: 'Edit Bookfair properties',
                    handler: me.onEditBookfair,
                    disabled: true
                }, '-', {
                    text: 'Packing',
                    id: 'btnPacking',
                    tooltip: 'Packing Targets & Pallet Assignments',
                    disabled: true,
                    menu: {
                        xtype: 'menu',
                        items: [
                            {
                                text: 'Edit Targets',
                                id: 'mnuEditTargets',
                                iconCls: 'edit',
                                handler: me.onPackingTargets                               
                            }, {
                                text: 'Print Pallet Assignments',
                                id: 'mnuPrintPalletAssignments',
                                iconCls: 'print',
                                handler: me.onPrintPalletAssignments
                            }, {
                                text: 'Print Pallet Tally',
                                id: 'mnuPrintPalletTally',
                                iconCls: 'print',
                                handler: me.onPrintPalletTally
                            }, {
                                text: 'Print Packing Sheets',
                                id: 'mnuPrintPackingSheets',
                                iconCls: 'print',
                                handler: me.onPrintPackingSheets
                            }
                        ]
                    }

                },'-', {
                    text: 'Allocations',
                    id: 'btnAllocations',
                    tooltip: 'Table Allocations',
                    disabled: true,
                    menu: {
                        xtype: 'menu',
                        items: [
                            {
                                text: 'Edit Allocations',
                                id: 'mnuEditAllocations',
                                iconCls: 'edit',
                                handler: me.onAllocateTables
                            }, {
                                text: 'Print Allocations',
                                id: 'mnuPrintAllocations',
                                iconCls: 'print',
                                handler: me.onPrintAllocations
                            }
                        ]
                    }
                }, '-', {
                    text: 'Sales',
                    id: 'btnSales',
                    tooltip: 'Sales Statistics',
                    disabled: true,
                    menu: {
                        xtype: 'menu',
                        items: [
                            {
                                text: 'Print Tally Sheets',
                                id: 'mnuPrintTallySheets',
                                iconCls: 'print',
                                handler: me.onPrintSaleTallySheet
                            }, {
                                text: 'Edit Sales',
                                id: 'mnuEditSAles',
                                iconCls: 'edit',
                                //disabled: true,
                                handler: me.onEnterSalesData
                            }, {
                                text: 'Print Sales Reports',
                                id: 'mnuPrintSales',
                                iconCls: 'graph',
                                handler: me.onPrintSalesReports
                            }
                        ]
                    }
                }
            ],
            columns: [
                {
                    header: 'Year',
                    dataIndex: 'year',
                    format: '0000',
                    flex: 1
                }, {
                    header: 'Season',
                    dataIndex: 'season',
                    flex: 2
                }, {
                    header: 'Days',
                    dataIndex: 'duration',
                    format: '0',
                    flex: 1
                }, {
                    header: 'Start Date',
                    dataIndex: 'start_date',
                    flex: 2,
                    renderer: Ext.util.Format.dateRenderer('d M Y')
                }, {
                    header: 'Venue',
                    dataIndex: 'location',
                    flex: 2
                }, {
                    header: 'Bag Sale',
                    dataIndex: 'bag_sale',
                    width: 60,
                    hidden: true,
                    sortable: false
                }, {
                    header: 'Attendance',
                    dataIndex: 'attendance',
                    xtype: 'numbercolumn',
                    format: '0,000',
                    align: 'right',
                    flex: 1
                }, {
                    header: 'Boxes',
                    dataIndex: 'stock',
                    xtype: 'numbercolumn',
                    format: '0,000',
                    align: 'right',
                    flex: 1
                },{
                    header: 'Sold',
                    dataIndex: 'sold',
                    xtype: 'numbercolumn',
                    format: '0%',
                    align: 'right',
                    flex: 1
                },
                //TODO: Implement locked -- no changes permitted if locked.
                {
                    xtype: 'actioncolumn',
                    width: 30,
                    sortable: false,
                    menuDisabled: false,
                    items: [{
                        icon: '/bookfair/img/delete.png',
                        tooltip: 'Delete Bookfair',
                        handler: me.onDeleteBookfair
                    }]
                }
            ],
            listeners: {
                selectionChange: {
                    fn: me.onSelectionChange
                },
                celldblclick: {
                    fn: me.onCellDoubleClick
                }
            }
        });
        me.callParent();
    },

    onAddBookfair: function () {
        var grid = this.up('bookfairlist'),
            store = grid.getStore(),
            month = ["03","03","03","06","06","06","09","09","09","03","03","03"],
            season = ["Autumn","Autumn","Autumn","Spring","Spring","Spring","Winter","Winter","Winter","Autumn","Autumn","Autumn"],
            venue = ["Exhibition Park","Exhibition Park","Exhibition Park","Exhibition Park","Exhibition Park","Exhibition Park","Vikings Club","Vikings Club","Vikings Club","Exhibition Park","Exhibition Park","Exhibition Park"],
            d = new Date(Ext.Date.now()),
            y = d.getFullYear() + (d.getMonth() > 8 ? 1 : 0),
            open9am = '9:00 AM',
            close6pm = '6:00 PM',
            start = Ext.Date.parse(y + "-" + month[d.getMonth()] + "-15", "Y-m-d"),
            row;
        while (start.getDay() !== 5) {
            start = Ext.Date.add(start, Ext.Date.DAY, 1);
        }
        row = Ext.create('Warehouse.model.Bookfair', {
            id: 0,
            season: season[d.getMonth()],
            year: y,
            start_date: start,
            end_date: Ext.Date.add(start, Ext.Date.DAY, 2),
            location: venue[d.getMonth()],
            fri_open: open9am,
            fri_close: close6pm,
            sat_open: open9am,
            sat_close: close6pm,
            sun_open: open9am,
            sun_close: close6pm,
            bag_sale: true,
            published: false,
            phantom: true
        });
        store.add(row);
        view = Ext.widget('bookfairedit');
        view.down('form').getForm().loadRecord(row);
    },

    onEditBookfair: function () {
        var sm = this.up('bookfairlist').getSelectionModel();
        if (sm.hasSelection()) {
            var row = sm.getSelection()[0],
                view = Ext.widget('bookfairedit');
            view.down('form').getForm().loadRecord(row);
        } else {
            Ext.Msg.alert('No Bookfair Selected', 'Please select a bookfair from the list and try again.');
        }
    },

    onAllocateTables: function () {
        var grid = grid = this.up('bookfairlist'),
            sm = grid.getSelectionModel(),
            bookfair, me, module, win;
        if (sm.hasSelection()) {
            bookfair = sm.getSelection()[0];
            module = myDesktop.getModule('allocations-win');
            module.setBookfair(bookfair);
            win = module && module.createWindow();
            if (win) {
                myDesktop.desktop.restoreWindow(win);
            }
        }
    },

    onDeleteBookfair: function (grid, rowIndex) {
        var store = grid.getStore(),
            record = grid.getStore().getAt(rowIndex);
        Ext.Msg.show({
            title: 'Warning',
            icon: Ext.Msg.WARNING,
            msg: Ext.String.format("You are about to delete the <b>{0} {1}</b> Bookfair. Any data associated with this Bookfair " +
                "will also be removed, including stock allocations, planning data and sales statistics. This operation cannot be undone. <br><br>" +
                "Delete the {0} {1} Bookfair?", record.get('season'), record.get('year')),
            buttons: Ext.MessageBox.OKCANCEL,
            fn: function (button) {
                if (button === 'ok') {
                    store.removeAt(rowIndex);
                    store.sync();
                }
            },
            scope: this
        });
    },

    onPackingTargets: function () {
        var grid = grid = this.up('bookfairlist'),
            sm = grid.getSelectionModel(),
            bookfair, me, module, win;
        if (sm.hasSelection()) {
            bookfair = sm.getSelection()[0];
            module = myDesktop.getModule('targets-win');
            module.setBookfair(bookfair);
            win = module && module.createWindow();
            if (win) {
                myDesktop.desktop.restoreWindow(win);
            }
        }
    },

    onPrintPackingSheets: function () {
        var grid = grid = this.up('bookfairlist'),
            sm = grid.getSelectionModel(),
            bookfair, win;
        if (sm.hasSelection()) {
            bookfair = sm.getSelection()[0];
            win = Ext.create('Warehouse.view.Print', { 
                title: Ext.String.format("Packing Sheets for {0} {1}", bookfair.get('season'), bookfair.get('year')),
                url: 'forms/bookfair/' + bookfair.get('id') + '/packingsheets'
            });
        }
    },

    onPrintPalletAssignments: function () {
        var grid = grid = this.up('bookfairlist'),
            sm = grid.getSelectionModel(),
            bookfair, win;
        if (sm.hasSelection()) {
            bookfair = sm.getSelection()[0];
            win = Ext.create('Warehouse.view.Print', { 
                title: Ext.String.format("Pallet Assignments for {0} {1}", bookfair.get('season'), bookfair.get('year')),
                url: 'forms/bookfair/' + bookfair.get('id') + '/pallet/assignments'
            });
        }
    },

    onPrintPalletTally: function () {
        var grid = grid = this.up('bookfairlist'),
            sm = grid.getSelectionModel(),
            bookfair, win;
        if (sm.hasSelection()) {
            bookfair = sm.getSelection()[0];
            win = Ext.create('Warehouse.view.Print', { 
                title: Ext.String.format("Pallet Tallysheets for  {0} {1}", bookfair.get('season'), bookfair.get('year')),
                url: 'forms/bookfair/' + bookfair.get('id') + '/pallet/tallysheet'
            });
        }
    },

    onSelectionChange: function (sm, recs, event) {
console.log(recs[0]);        
        if (recs.length == 1) {    
            Ext.getCmp('btnEditBookfair').enable();
            Ext.getCmp('btnPacking').enable();
            Ext.getCmp('btnAllocations').enable();
            Ext.getCmp('btnSales').enable();
        } else {
            Ext.getCmp('btnEditBookfair').disable();
            Ext.getCmp('btnTargets').disable();
            Ext.getCmp('btnAllocations').disable();
            Ext.getCmp('btnSales').disable();
        }
   },

   onCellDoubleClick: function (table, td, cellIndex, record, tr, rowIndex, e, eOpts) {
        var view = Ext.widget('bookfairedit');
        view.down('form').getForm().loadRecord(record);
   },

    onPrintAllocations: function () {
    },

    onPrintSaleTallySheet: function () {
        var grid = grid = this.up('bookfairlist'),
            sm = grid.getSelectionModel(),
            bookfair, win;
        if (sm.hasSelection()) {
            bookfair = sm.getSelection()[0];
            win = Ext.create('Warehouse.view.Print', { 
                title: Ext.String.format("Sales Tally Sheet for {0} {1}", bookfair.get('season'), bookfair.get('year')),
                url: 'forms/statistics/capture/bookfair/' + bookfair.get('id')
            });
        }
    },

    onPrintSalesReports: function () {
        var grid = grid = this.up('bookfairlist'),
            sm = grid.getSelectionModel(),
            bookfair, win;
        if (sm.hasSelection()) {
            bookfair = sm.getSelection()[0];
            win = Ext.create('Warehouse.view.PrintTabs', { 
                title: Ext.String.format("Sales Reports for {0} {1}", bookfair.get('season'), bookfair.get('year')),
                tabs: [{ 
                        title: 'Attendance',
                        url: 'forms/statistics/bookfair/' + bookfair.get('id') + '/attendance'
                    }, {
                        title: 'Summary',
                        url: 'forms/statistics/bookfair/' + bookfair.get('id') + '/summary'
                    }, {
                        title: 'Detail',
                        url: 'forms/statistics/bookfair/' + bookfair.get('id') + '/details'
                    }
                ]
            });
        }
    },

    onEnterSalesData: function () {
        var grid = grid = this.up('bookfairlist'),
            sm = grid.getSelectionModel(),
            bookfair, me, module, win;
        if (sm.hasSelection()) {
            bookfair = sm.getSelection()[0];
            module = myDesktop.getModule('sales-win');
            module.setBookfair(bookfair);
            console.log(module.getBookfair());
            win = module && module.createWindow();
            if (win) {
                myDesktop.desktop.restoreWindow(win);
            }
        }
    }

});
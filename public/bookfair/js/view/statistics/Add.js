/*
* @file /view/statistics/Add.js
* @author Russell Nash
*/
Ext.define('Warehouse.view.statistics.Add', {
    extend: 'Ext.window.Window',
    alias : 'widget.statsadd',
    id: 'addstats',
    title : 'Add Categories to Bookfair',
    layout: 'fit',
    modal: true,
    autoShow: true,

    initComponent: function() {
        var me = this;        

        Ext.apply(me, {
            items: {
                xtype: 'form',
                border: false,
                bodyPadding: '5px 10px 5px 10px',
                fieldDefaults: {
                                margin: 5,
                                labelWidth: 100,
                                msgTarget: 'side',
                                autoFitErrors: false
                            },
                items: [
                   { 
                        xtype: 'combo',
                        id: 'sectionCombo',
                        fieldLabel: 'Section',
                        name: 'section',
                        store: Ext.create('Ext.data.Store', {
                            autoLoad: true,
                            fields: ['id', 'name'],
                            proxy: {
                                type: 'ajax',
                                url: 'statistics/bookfair/' + this.bookfair.get('id') + '/freesecs',
                                reader: { type: 'json'}
                            }
                        }),
                        displayField: 'name',
                        queryMode: 'local',
                        typeAhead: true,
                        forceSelection: true,
                        selectOnTab: true,
                        lazyRender: true,
                        listClass: 'x-combo-list-small',
                        listeners: {
                            select: {
                                fn: me.onSectionSelect
                            }
                        }
                    }, { 
                        xtype: 'combo',
                        id: 'categoryCombo',
                        fieldLabel: 'Category',
                        name: 'category',
                        store: Ext.create('Ext.data.Store', {
                            autoLoad: true,
                            fields: ['id', 'label', 'name', 'section_id'],
                            proxy: {
                                type: 'ajax',
                                url: 'statistics/bookfair/' + this.bookfair.get('id') + '/freecats',
                                reader: { type: 'json'}
                            }
                        }),
                        displayField: 'name',
                        queryMode: 'local',
                        typeAhead: true,
                        forceSelection: true,
                        selectOnTab: true,
                        lazyRender: true,
                        listClass: 'x-combo-list-small'
                    }
                ],
                dockedItems: [
                    { 
                        xtype: 'toolbar',
                        dock: 'bottom',
                        ui: 'footer',
                        items: [
                            '->',
                            {
                                text: 'Add',
                                formBind: true,
                                handler: me.onAddCategory
                            }, {
                                text: 'Cancel',
                                handler: function () { Ext.getCmp('addstats').close(); }
                            }
                        ]
                    }
                ]
            }
        });
        me.callParent();
    },

    onAddCategory: function () {
        var combo = Ext.getCmp('categoryCombo'),
            catStore = combo.getStore(),
            cat = catStore.findRecord('name', combo.getValue()),
            targets = Ext.data.StoreManager.lookup('targetsStore');
        targets.add({
            allocate: true,
            category_id: cat.get('id'),
            label: cat.get('label'),
            name: cat.get('name'),
            section_id: cat.get('section_id'),
            track: true
        });
        Ext.getCmp('addstats').close();        
    },
    
    onSectionSelect: function (combo, records, eOpts) {
        var cats = Ext.getCmp('categoryCombo'), secId = records[0].get('id');
        cats.store.clearFilter(true);
        cats.store.filter([{
            filterFn: function (item) {
                return item.get('section_id') === secId;
            }
        }]);
    }

});
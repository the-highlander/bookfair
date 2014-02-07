/*
* File: Warehouse/view/statistics/Add.js
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
                                url: 'sections',
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
                        store:  Ext.create('Ext.data.Store', {
                            autoLoad: true,
                            fields: ['id', 'name'],
                            proxy: {
                                type: 'ajax',
                                url: 'categories',
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
                                handler: function () { 
                                    var form = this.up('form').getForm(),
                                        store = Ext.data.StoreManager.lookup('targetStore'),
                                        errors;
                                    console.log("ready to do something", Ext.getCmp('sectionCombo'), Ext.getCmp('categoryCombo'));
                                    /* form.updateRecord();
                                    form.getRecord().setDirty();
                                    errors = form.getRecord().validate();
                                    console.log(errors);
                                    if (errors.isValid()) {
                                        //record.set(values);
                                        console.log("Form is valid", store);
                                        store.sync({
                                            success: function() { console.log("sync ok"); Ext.getCmp("bookfaireditor").close(); },
                                            failure: function(batch, options) {
                                                // TODO: Test this. 
                                                // Extract server side validation errors
                                                var errors = new Ext.data.Errors(),
                                                    serverErrors = batch.exceptions[0].error;
                                                Ext.each(serverErrors, function (field) {
                                                    var msg = serverErrors[field].join(",");
                                                    errors.add(undefined, {field: field, message: msg });
                                                });
                                                console.log("Sync failed");
                                                Ext.getCmp("bookfaireditor").down('form').getForm().markInvalid(errors);
                                            }
                                        });
                                    } else {
                                        console.log("Form is not valid");
                                        form.markInvalid(errors);
                                    }  
                                    */              
                                }
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

    onSectionSelect: function (combo, records, eOpts) {
        console.log("setion selected.  need to filter the categories store", records);
    }

});
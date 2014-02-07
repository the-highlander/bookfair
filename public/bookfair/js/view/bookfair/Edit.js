// View for Adding and Editing a Bookfair.
Ext.define('Warehouse.view.bookfair.Edit', {
    extend: 'Ext.window.Window',
    alias : 'widget.bookfairedit',
    id: "bookfaireditor",
    title : 'Edit Bookfair Details',
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
                        xtype: 'datefield',
                        name: 'start_date',
                        fieldLabel: 'Start Date',
                        disabledDays: [0, 1, 2, 3, 4],
                        disabledDaysText: 'Bookfairs can only Start on Friday or Saturday',
                        format: 'd M Y'
                    }, { 
                        xtype: 'datefield',
                        name: 'end_date',
                        fieldLabel: 'End Date',
                        disabledDays: [1, 2, 3, 4, 5, 6],
                        disabledDaysText: 'Bookfairs must end on Sunday',
                        format: 'd M Y'
                    }, { 
                        xtype: 'combo',
                        name: 'location',
                        fieldLabel: 'Location',
                        typeAhead: true,
                        forceSelection: true,
                        triggerAction: 'all',
                        selectOnTab: true,
                        store: Warehouse.data.DataSets.venues,
                        lazyRender: true,
                        listClass: 'x-combo-list-small'
                    }, {
                        xtype: 'fieldset',
                        title: 'Friday',
                        items: [
                            {
                                xtype: 'timefield',
                                name : 'fri_open',
                                fieldLabel: 'Opening Time',
                                minValue: '8:00 AM',
                                maxValue: '6:00 PM',
                                increment: 60
                            }, {
                                xtype: 'timefield',
                                name : 'fri_close',
                                fieldLabel: 'Closing Time',
                                minValue: '8:00 AM',
                                maxValue: '6:00 PM',
                                increment: 60
                            }
                        ]
                    }, {
                        xtype: 'fieldset',
                        title: 'Saturday',
                        items: [
                            {
                            }, {
                                xtype: 'timefield',
                                name : 'sat_open',
                                fieldLabel: 'Opening Time',
                                minValue: '8:00 AM',
                                maxValue: '6:00 PM',
                                increment: 60
                            }, {
                                xtype: 'timefield',
                                name : 'sat_close',
                                fieldLabel: 'Closing Time',
                                minValue: '8:00 AM',
                                maxValue: '6:00 PM',
                                increment: 60
                            }
                        ]
                    }, {
                        xtype: 'fieldset',
                        title: 'Sunday',
                        items: [
                            {
                            }, {
                                xtype: 'timefield',
                                name : 'sun_open',
                                fieldLabel: 'Opening Time',
                                minValue: '8:00 AM',
                                maxValue: '6:00 PM',
                                increment: 60
                            }, {
                                xtype: 'timefield',
                                name : 'sun_close',
                                fieldLabel: 'Closing Time',
                                minValue: '8:00 AM',
                                maxValue: '6:00 PM',
                                increment: 60
                            }, {
                                xtype: 'checkbox',
                                name: 'bag_sale',
                                fieldLabel: 'Track Bag Sales',
                                defaultValue: true                                
                            }
                        ]
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
                                text: 'Save',
                                formBind: true,
                                handler: function () { 
                                    var form = this.up('form').getForm(),
                                        store = Ext.data.StoreManager.lookup('bookfairStore'),
                                        errors;
                                    form.updateRecord();
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
console.log("sync called");
                                    } else {
                                        console.log("Form is not valid");
                                        form.markInvalid(errors);
                                    }                
                                }
                            }, {
                                text: 'Cancel',
                                handler: function () { Ext.getCmp('bookfaireditor').close(); }
                            }
                        ]
                    }
                ]
            }
        });
        me.callParent();
    }
});
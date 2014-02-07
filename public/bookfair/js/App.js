Ext.define('Warehouse.App', {
    extend: 'Ext.ux.desktop.Application',
    
    requires: [
        'Ext.ux.desktop.ShortcutModel',
        // TODO: add OTHER Desktop SubApplications here as you add them to desktopconfig
        'Warehouse.UserListWindow',
        'Warehouse.BookfairWindow',
        'Warehouse.SectionWindow',
        'Warehouse.CategoryWindow',
        'Warehouse.AllocationWindow',
        'Warehouse.SalesWindow',
        'Warehouse.TargetsWindow',
        //'Warehouse.StatsWindow', // StatsWindow to be deleted once SalesWindow and AllocationsWindow are working
        'Warehouse.Settings',
        'Warehouse.data.DataSets'
    ], 
    
    init: function() {
        // TODO: add here any custom logic before getXYZ methods get called
        // for example loading a security context and information abou the 
        // current user.
        delete Ext.tip.Tip.prototype.minWidth;
        //TODO: Is division store really needed in pre-load. Wasted if not used.
        this.divisionStore = Ext.create('Warehouse.store.Divisions', 
            { 
               storeId: 'divisionStore'
            });
        this.tableGroupStore = Ext.create('Warehouse.store.TableGroups',
            { 
                storeId: 'tableGroupStore'
            }
        );
        this.callParent();
        // now ready...
    },
    
    requestExceptionProcessor: function(proxy, response, operation, eOpts) { 
        console.log("in requestExceptionProcessor", this, arguments);
        if (response.status === 200) {
           myDesktop.requestMessageProcessor(proxy, response);
        } else {
            rsp = JSON.parse(response.responseText);
            //TODO: Strip the upper part of the directory location from rsp.error.file. Only
            //      show the last 3 directories eg ...\bookfair\controllers\SectionController.php
            Ext.MessageBox.show({
                title: "Status " + response.status + " - " + response.statusText,
                msg: rsp.error.message + "<br>File: " + rsp.error.file + "<br>Line: " + rsp.error.line,
                buttons: Ext.MessageBox.OK,
                icon: Ext.MessageBox.ERROR
            });
        }
    },
                    
    requestMessageProcessor: function (proxy, response) {
        console.log("in myDesktop.requestMessageProcessor", arguments);
        try {
            var responseData = JSON.parse(response.responseText);
            if (responseData.message) {
                if (!responseData.success) {
                    msgDescription = 'Error';
                    msgIcon = Ext.MessageBox.ERROR;
                } else {
                    msgDescription = 'Information';
                    msgIcon = Ext.MessageBox.INFO;                        
                }
                Ext.MessageBox.show({
                    title: msgDescription,
                    msg: responseData.message,
                    buttons: Ext.MessageBox.OK,
                    icon: msgIcon
                });
            }
        }
        catch (err) {
            // Malformed request most likely
            if (console) { 
                console.log(err);
            }
        }
    },

    getModules: function() {
        // Overrides getModules in Application.js
        // TODO: Apply security to this list
        return [
            new Warehouse.BookfairWindow(),
            new Warehouse.SectionWindow(),
            // new Warehouse.StatsWindow(),
            new Warehouse.CategoryWindow(),
            new Warehouse.AllocationWindow(),
            new Warehouse.SalesWindow(),
            new Warehouse.TargetsWindow(),
            // TODO: Add here each desktop module            
            new Warehouse.UserListWindow()

        ];  
    },
            
    getDesktopConfig: function() {
        var me = this, cfg = me.callParent();
        Ext.apply(cfg, {
            contextMenuItems: [
                { text: 'Change Settings', handler: me.onSettings, scope: me }
            ],
            shortcuts: Ext.create('Ext.data.Store', {
                model: 'Ext.ux.desktop.ShortcutModel',
                data: [ 
                    { name: 'Bookfairs', iconCls: 'bookfair-shortcut', module: 'bookfair-win'},
                    { name: 'Sections', iconCls: 'section-shortcut', module: 'section-win' },
                    { name: 'Categories', iconCls: 'category-shortcut', module: 'category-win' },
                    //{ name: 'Allocation & Sales Statistics', iconCls: 'stats-shortcut', module: 'stats-win'},
                    { name: 'User Management', iconCls: 'user-mgmt-shortcut', module: 'user-mgmt-win'}
                ]
            }),
            wallpaper: 'bluedesk.jpg',
            wallpaperPath: '/bookfair/img/wallpapers/',
            wallpaperStretch: false
        });
        return cfg;
    },
    
    getStartConfig: function () {
        var me = this, cfg = me.callParent();
        Ext.apply(cfg, {
            title: 'Current Users Name', // TODO: Need the name of the current user. Where does that come from?
            iconCls: 'user-mgmt-icon',
            height: 300,
            toolConfig: {
                width: 100,
                items: [
                    {
                        text: 'Settings',
                        iconCls: 'settings-icon',
                        handler: me.onSettings,
                        scope: me
                    },
                    '-',
                    {
                        text: 'Logout',
                        iconCls: 'logout-icon', 
                        handler: me.onLogout,
                        scope: me
                    }
                ]
            }
        });
        return cfg;
    },
    
    getTaskbarConfig: function () {
        var me = this, cfg = me.callParent();
        Ext.apply(cfg, {
            quickStart: [
                { name: 'Bookfairs', iconCls: 'bookfair-icon', height: '24px', module: 'bookfair-win' },
                { name: 'Sections', iconCls: 'section-icon', height: '24px', module: 'section-win' },
                { name: 'Categories', iconCls: 'category-icon', height: '24px', module: 'category-win' },
                //{ name: 'Statistics', iconCls: 'stats-icon', height: '24px', module: 'stats-win'},
                { name: 'Users', iconCls: 'user-mgmt-icon', height: '24px', module: 'user-mgmt-win' }
            ], // TODO: Add task bar items here eg stats window
            trayItems: [
                { xtype: 'trayclock', flex: 1 }
            ]
        });
        return cfg;
    },
    
    onLogout: function () {
        var me = this, desktop = me.desktop;
        if (desktop.windows.getCount() > 0) {
          desktop.closeAllWindows();
        }
        desktop.destroy();        
        document.location.replace('logout');
    },
    
    onSettings: function () {
        var dlg = new Warehouse.Settings({
            desktop: this.desktop
        });
        dlg.show();
    }
    
});
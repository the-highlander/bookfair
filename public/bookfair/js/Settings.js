/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
Ext.define('Warehouse.Settings', {
    extend: 'Ext.window.Window',
    uses: [
        'Ext.ux.desktop.Wallpaper',
        'Warehouse.WallpaperModel'
    ],
    
    layout: 'anchor',
    title: 'Change Settings',
    iconCls: 'settings-icon',
    modal: true,
    width: 640,
    height: 480,
    border: false,
    
    initComponent: function () {
        var me = this;
        me.selected = me.desktop.getWallpaper();
        me.stretch = me.desktop.wallpaper.stretch;
        me.preview = Ext.create('widget.wallpaper');
        me.preview.setWallpaper(me.selected, me.desktop.wallpaperPath);
        me.tree = me.createTree();
        me.buttons = [
            { text: 'OK', handler: me.onOK, scope: me },
            { text: 'Cancel', handler: me.close, scope: me }
        ];
        me.items = [
            {
                anchor: '0 -30',
                border: false,
                layout: 'border',
                items: [
                    me.tree,
                    { 
                        xtype: 'panel',
                        title: 'Preview',
                        region: 'center',
                        layout: 'fit',
                        items: [ me.preview ]
                    }
                ]
            },
            {
                xtype: 'checkbox',
                boxLabel: 'Stretch to fit',
                checked: me.stretch,
                listeners: {
                    change: function (comp) {
                        me.stretch = comp.checked;
                    }
                }
            }
        ];
        me.callParent();
    },
    
    createTree: function () {
        var me = this;
        function child(img) { 
            return { img: img, text: me.getTextOfWallpaper(img), iconCls: '', leaf: true };
        }
        var tree = new Ext.tree.Panel({
            title: 'Warehouse Background',
            rootVisible: false,
            lines: false,
            autoScroll: true,
            width: 150,
            region: 'west',
            split: true,
            minWidth: 100,
            listeners: {
                afterrender: { fn: this.setInitialSelection, delay: 100 },
                select: this.onSelect,
                scope: this
            },
            store: new Ext.data.TreeStore({
                model: 'Warehouse.WallpaperModel',
                root: {
                    text: 'Wallpaper',
                    expanded: true,
                    children: [
                        { text: 'None', iconCls: '', leaf: true },
                        child('bluedesk.jpg'),
                        child('cloudwave.jpg'),
                        child('justblue.jpg'),
                        child('bluewaves.jpg')
                    ]
                }
            })
        });
        return tree;
    },
            
    getTextOfWallpaper: function (path) {
        var text = path, slash = path.lastIndexOf('/');
        if (slash >= 0) {
            text = text.substring(slash+1);
        }
        var dot = text.lastIndexOf('.');
        text = Ext.String.capitalize(text.substring(0, dot));
        text = text.replace(/[-]/g, ' ');
        return text;
    },
    
    onOK: function () {
        var me = this;
        if(me.selected) {
            me.desktop.setWallpaper(me.selected, me.desktop.wallpaperPath, me.stretch);
        }
        me.destroy();
    },
    
    onSelect: function (tree, record) {
        var me = this;
        if (record.data.img) {
            me.selected = record.data.img;
        } else {
            me.selected = Ext.BLANK_IMAGE_URL;
        }
        me.preview.setWallpaper(me.selected, me.desktop.wallpaperPath);
    },
    
    setInitialSelection: function () {
        var me = this;
        var s = me.desktop.getWallpaper();
        if (s) {
            var path = "/Wallpaper/" + me.getTextOfWallpaper(s);
            me.tree.selectPath(path, 'text');
        }
    }
    
});


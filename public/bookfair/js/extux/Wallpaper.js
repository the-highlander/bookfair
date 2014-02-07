/*!
 * Ext JS Library 4.0
 * Copyright(c) 2006-2011 Sencha Inc.
 * licensing@sencha.com
 * http://www.sencha.com/license
 */

/**
 * @class Ext.ux.desktop.Wallpaper
 * @extends Ext.Component
 * <p>This component renders an image that stretches to fill the component.</p>
 */
Ext.define('Ext.ux.desktop.Wallpaper', {
    extend: 'Ext.Component',
    alias: 'widget.wallpaper',
    cls: 'ux-wallpaper',
    html: '<img src="'+Ext.BLANK_IMAGE_URL+'">',
    stretch: false,
    path: null,
    wallpaper: null,
    stateful  : true,
    stateId  : 'desk-wallpaper',

    afterRender: function () {
        var me = this;
        me.callParent();
        me.setWallpaper(me.wallpaper, me.path, me.stretch);
    },

    applyState: function () {
        var me = this, old = me.wallpaper;
        me.callParent(arguments);
        if (old !== me.wallpaper) {
            me.setWallpaper(me.wallpaper, me.path, me.stretch);
        }
    },

    getState: function () {
        return this.wallpaper && { wallpaper: this.wallpaper, path: this.path };
    },

    setWallpaper: function (wallpaper, path, stretch) {
        var me = this, imgEl, bkgnd;

        me.stretch = (stretch !== false);
        me.wallpaper = wallpaper;

        if (me.rendered) {
            imgEl = me.el.dom.firstChild;

            if (!wallpaper || wallpaper === Ext.BLANK_IMAGE_URL) {
                Ext.fly(imgEl).hide();
            } else if (me.stretch) {
                imgEl.src = path + wallpaper;
                me.el.removeCls('ux-wallpaper-tiled');
                Ext.fly(imgEl).setStyle({
                    width: '100%',
                    height: '100%'
                }).show();
            } else {
                Ext.fly(imgEl).hide();
                bkgnd = 'url(' + path + wallpaper + ')';
                me.el.addCls('ux-wallpaper-tiled');
            }

            me.el.setStyle({
                backgroundImage: bkgnd || ''
            });
            if(me.stateful) {
                me.saveState();
            }
        }
        return me;
    }
});

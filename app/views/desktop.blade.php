@extends('layouts.master')

@section('styles')
    @parent
    {{ HTML::style('bookfair/css/desktop.css') }}
    {{ HTML::style('vendor/extjs/resources/css/ext-all-debug.css') }}
@stop

@section('scripts') 
    @parent
    {{ HTML::script('vendor/extjs/ext-all-debug.js') }}
     <script>
        // For MVC replace the more tradition onReady event handler shown below with the following 
        // script inclusion for the application object: "http://laravel.local/warehouse/mvc/app.js"
       Ext.Loader.setPath({
                    'Ext.ux': '/vendor/extjs/examples/ux',
                    'Ext.ux.desktop': '/bookfair/js/extux',
                    'Warehouse': '/bookfair/js'
            });
            Ext.require('Warehouse.App');
            var myDesktop;            
            Ext.onReady(function () {
                Ext.QuickTips.init();
                myDesktop = new Warehouse.App();
            });
    </script>
@stop

@section('content')
@stop
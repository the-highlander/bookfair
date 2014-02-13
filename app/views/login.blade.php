@extends('layouts.master')

@section('styles')
    @parent
    {{ HTML::style('vendor/bootstrap/css/bootstrap.css') }}
    {{ HTML::style('vendor/bootstrap/css/bootstrap-responsive.css') }}
@stop

{{-- Content --}}
@section('content')
<br>
<div class="span4 offset4 well">
    <h2 class="form-signin-heading">Please sign in</h2>
    {{ Form::open(array('url'=>'login', 'class'=>'form-signin')) }}
        {{ Form::text('username', Input::old('username'), array('class' => 'input-block-level', 'placeholder' => 'Username', 'autofocus')) }}
        {{ Form::password('password', array('class' => 'input-block-level', 'placeholder' => 'Password')) }}
        {{ Form::submit('Sign in', array('class' => 'btn btn-large btn-primary')) }}
    {{ Form::close() }}
    <!-- check for login errors flash var -->
    @if (Session::has('flash_notice'))
        <div class="alert alert-error fade in">
             <a href="#" class="close" data-dismiss="alert">x</a>
             {{ Session::get('flash_notice') }}
        </div>
    @endif
</div>
@stop

@section('scripts')
    <!-- JQUery? jquery/jquery-1.10.2.min.js')  -->
    {{ HTML::script('vendor/bootstrap/js/bootstrap.min.js') }}
@stop

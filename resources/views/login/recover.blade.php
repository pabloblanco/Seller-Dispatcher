@extends('layouts.login')

@section('customCSS')
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
@stop

@section('content')
	@include('components.messages')

	@if ($errors->any())
	    <div class="alert alert-danger">
	        <ul>
	            @foreach ($errors->all() as $error)
	                <li>{{ $error }}</li>
	            @endforeach
	        </ul>
	    </div>
	@endif

	<div class="login-box">
        <div class="white-box">
            <form class="form-horizontal form-material" id="changePassform" action="{{route('login.changePassword',['hash' => $hash])}}" data-toggle="validator" method="POST">
                <h3 class="box-title m-b-20">Recover Password</h3>
                {{ csrf_field() }}
                <div class="form-group ">
                    <div class="col-xs-12">
                        <input class="form-control" name="email" id="email" type="email" required placeholder="Email">
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-xs-12">
                        <input class="form-control" name="password" id="password" type="password" required placeholder="Nueva contraseña">
                    </div>
                </div>
                <div class="form-group text-center m-t-20">
                    <div class="col-xs-12">
                        <button class="btn btn-primary btn-lg btn-block text-uppercase waves-effect waves-light" type="submit">Cambiar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@stop
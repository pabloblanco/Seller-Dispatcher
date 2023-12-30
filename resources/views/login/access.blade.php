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
            <form class="form-horizontal form-material" id="loginform" method="POST" action="" data-toggle="validator">
            	{{ csrf_field() }}
                <input type="hidden" name="recaptcha" id="recaptcha">
                <h3 class="box-title m-b-20">Iniciar Sesión</h3>
                <div class="form-group ">
                    <div class="col-xs-12">
                        <input class="form-control" type="email" placeholder="Email" id="emailLogin" name="emailLogin" required>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-xs-12">
                        <input class="form-control" type="password" placeholder="Contraseña" id="passLogin" name="passLogin" required>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-12">
                        <a href="javascript:void(0)" id="to-recover" class="text-dark pull-right"><i class="fa fa-lock m-r-5"></i> Olvido su contraseña?</a> 
                    </div>
                </div>
                <div class="form-group text-center m-t-20">
                    <div class="col-xs-12">
                        <button class="btn btn-info btn-lg btn-block text-uppercase waves-effect waves-light" type="submit">Entrar</button>
                    </div>
                </div>
            </form>

            <form class="form-horizontal" id="recoverform" action="{{route('login.resetPassword')}}" method="POST" data-toggle="validator">
            	{{ csrf_field() }}
                <div class="form-group ">
                    <div class="col-xs-12">
                        <h3>Recuperar Contraseña</h3>
                        <p class="text-muted"> Ingrese su email, Se le enviara los pasos ha seguir para restablecer su contraseña. </p>
                    </div>
                </div>
                <div class="form-group ">
                    <div class="col-xs-12">
                        <input class="form-control" id="emailLoginReset" name="emailLoginReset" type="email" required placeholder="Email">
                    </div>
                </div>
                <div class="form-group text-center m-t-20">
                    <div class="col-xs-12">
                        <button class="btn btn-primary btn-lg btn-block text-uppercase waves-effect waves-light" type="submit">Enviar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@stop
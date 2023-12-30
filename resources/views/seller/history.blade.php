{{--vista deprecada--}}

@extends('layouts.admin')

@section('content')
	@include('components.messages')
    @include('components.messagesAjax')

	@if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row bg-title">
        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title"> Historial del vendedor </h4>
        </div>
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
                <li><a href="#">Ventas</a></li>
                <li class="active">Historial</li>
            </ol>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="white-box">
                <div class="row">
                <h3 class="box-title">Ventas</h3>


            </div>
            </div>
        </div>
    </div>
@stop

@section('scriptJS')
    <script src="{{ asset('js/validator.js') }}"></script>
@stop
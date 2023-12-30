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
            <h4 class="page-title"> M&oacute;dulo de ventas </h4>
        </div>
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
                <li><a href="#">Ventas</a></li>
                <li class="active">Venta netwey.</li>
            </ol>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="white-box">
                <h3 class="box-title">Confirmar venta.</h3>

                <form class="form-horizontal" id="Salesclientform" method="POST" action="{{route('seller.doSaleProduct')}}" data-toggle="validator">
                    {{ csrf_field() }}
                    <input type="hidden" name="msisdn" id="msisdn" value="{{$msisdn}}">
                    <div class="row">
                        <div id="showPack">
                            {!! $html !!}
                        </div>
                        <div class="col-md-12 m-t-20">
                            <a href="{{route('seller.onlyProduct')}}" class="btn btn-default waves-effect waves-light">
                                Cancelar
                            </a>

                            <button type="submit" class="btn btn-danger waves-effect waves-light">
                                Confirmar
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div> 
@stop
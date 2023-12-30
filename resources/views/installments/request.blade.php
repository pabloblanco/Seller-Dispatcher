@extends('layouts.admin')

@section('customCSS')
    <link rel="stylesheet" href="{{ asset('plugins/bower_components/typeahead.js-master/dist/typehead-min.css') }}">
@stop

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
            <h4 class="page-title"> Solicitudes de venta en abono </h4>
        </div>
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
                <li><a href="#">venta en abono</a></li>
                <li class="active">Solicitudes de venta en abono.</li>
            </ol>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="white-box">
                <div class="row">
                    <div class="col-md-12 p-b-20" id="list-content">
                        <h3 class="box-title">Lista de solicitudes pendientes</h3>
                        @if(count($req))
                            @foreach($req as $r)
                            <form name="acept-inst-{{$r->id}}" method="POST" action="{{ route('installments.acceptRequest') }}">
                                {{ csrf_field() }}
                                <input type="hidden" name="sale-req" value="{{$r->id}}">
                                <div class="card card-outline-danger text-dark m-b-10">
                                    <div class="card-block">
                                        <div class="col-md-12">
                                            <ul class="list-icons">
                                                <li>
                                                    <i class="ti-angle-right"></i> 
                                                    <strong>Vendedor:</strong> 
                                                    <span>{{ $r->name }} {{ $r->last_name }}</span>
                                                </li>
                                                <li>
                                                    <i class="ti-angle-right"></i> 
                                                    <strong>Cliente:</strong> 
                                                    <span>{{ $r->name_c }} {{ $r->last_name_c }}</span>
                                                </li>
                                                <li>
                                                    <i class="ti-angle-right"></i> 
                                                    <strong>Primera cuota:</strong> 
                                                    <span class="email_seller">${{ $r->first_pay }}</span>
                                                </li>
                                                <li>
                                                    <i class="ti-angle-right"></i> 
                                                    <strong>Total:</strong> 
                                                    <span class="email_seller">${{ $r->amount }}</span>
                                                </li>
                                                <li>
                                                    <i class="ti-angle-right"></i> 
                                                    <strong>Fecha:</strong> 
                                                    <span class="email_seller">{{ date('d-m-Y H:i', strtotime($r->date_reg)) }}</span>
                                                </li>
                                                <li>
                                                    <i class="ti-angle-right"></i> 
                                                    <strong>pack:</strong> 
                                                    <span class="email_seller">{{ $r->pack }}</span>
                                                </li>
                                                <li>
                                                    <i class="ti-angle-right"></i> 
                                                    <strong>Servicio:</strong> 
                                                    <span class="email_seller">{{ $r->service }}</span>
                                                </li>
                                                <li>
                                                    <i class="ti-angle-right"></i> 
                                                    <strong>Art&iacute;culo:</strong> 
                                                    <span class="email_seller">{{ $r->title }}</span>
                                                </li>
                                                <li>
                                                    <i class="ti-angle-right"></i> 
                                                    <strong>msisdn:</strong> 
                                                    <span class="email_seller">{{ $r->msisdn }}</span>
                                                </li>
                                            </ul>
                                            <div class="text-center">
                                                <button type="submit" name="btn-denay" class="btn btn-danger waves-effect waves-light" value="NOT_OK">
                                                    Rechazar
                                                </button>
                                                <button type="submit" name="btn-accept" class="btn btn-success waves-effect waves-light" value="OK">
                                                    Aceptar
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                            @endforeach
                        @else
                            <h3>No tienes solicitudes pendientes.</h3>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('scriptJS')
    <!-- typehead TextBox Search -->
    <script src="{{ asset('plugins/bower_components/typeahead.js-master/dist/typeahead.bundle.min.js') }}"></script>
@stop
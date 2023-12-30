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
                    <div class="col-md-12 p-b-20">
                        <h3 class="box-title">Filtros de solicitudes</h3>

                        <div class="col-md-3 col-sm-12 p-b-10">
                            <button type="buton" data-filter="apro" class="btn btn-success waves-effect waves-light filter">
                                <i class="fa fa-check"></i>
                                Aprobadas
                            </button>
                        </div>
                        <div class="col-md-3 col-sm-12 p-b-10">
                            <button type="buton" data-filter="pen" class="btn btn-warning waves-effect waves-light filter">
                                <i class="fa fa-clock-o"></i>
                                Pendientes
                            </button>
                        </div>
                        <div class="col-md-3 col-sm-12">
                            <button type="buton" data-filter="den" class="btn btn-danger waves-effect waves-light filter">
                                <i class="fa fa-ban"></i>
                                Rechazadas
                            </button>
                        </div>
                        <div class="col-md-3 col-sm-12">
                            <button type="buton" data-filter="all" class="btn btn-default waves-effect waves-light filter">
                                <i class="fa fa-asterisk"></i>
                                Todas
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-12">
            <div class="white-box">
                <div class="row">
                    @if(count($reqA))
                    <div class="col-md-12 p-b-20 request" id="request-apro">
                        <h3 class="box-title">Solicitudes aprobadas</h3>
                        @foreach($reqA as $r)
                            <form name="acept-inst-{{$r->id}}" id="acept-inst-{{$r->id}}" method="POST" action="{{ route('installments.finalStep') }}">
                                {{ csrf_field() }}
                                <input type="hidden" name="sale-req" value="{{$r->id}}">
                                <input type="hidden" id="action" name="action">
                                <div class="card card-outline-success text-dark m-b-10">
                                    <div class="card-block">
                                        <div class="col-md-12">
                                            <ul class="list-icons">
                                                <li>
                                                    <i class="ti-angle-right"></i> 
                                                    <strong>Cliente:</strong> 
                                                    <span>{{ $r->name_c }} {{ $r->last_name_c }}</span>
                                                </li>
                                                <li>
                                                    <i class="ti-angle-right"></i> 
                                                    <strong>Monto primera cuota:</strong> 
                                                    <span class="email_seller">${{ $r->first_pay }}</span>
                                                </li>
                                                <li>
                                                    <i class="ti-angle-right"></i> 
                                                    <strong>Pr&oacute;ximo pago:</strong> 
                                                    <span class="email_seller">
                                                        {{ date('d-m-Y', strtotime('+ '.$r->days_quote.' days', time())) }}
                                                    </span>
                                                </li>
                                                <li>
                                                    <i class="ti-angle-right"></i> 
                                                    <strong>Monto pr&oacute;xima cuota:</strong> 
                                                    <span class="email_seller">${{ ($r->amount - $r->first_pay)/($r->quotes -1) }}</span>
                                                </li>
                                                <li>
                                                    <i class="ti-angle-right"></i> 
                                                    <strong>pack:</strong> 
                                                    <span class="email_seller">{{ $r->pack }}</span>
                                                </li>
                                                <li>
                                                    <i class="ti-angle-right"></i> 
                                                    <strong>msisdn:</strong> 
                                                    <span class="email_seller">{{ $r->msisdn }}</span>
                                                </li>
                                            </ul>
                                            <div class="text-center">
                                                <button type="button" class="btn btn-danger waves-effect waves-light btn-f-r" value="CANCEL" data-form="acept-inst-{{$r->id}}">
                                                    Cancelar
                                                </button>
                                                <button type="button" class="btn btn-success waves-effect waves-light btn-f-r" value="OK" data-form="acept-inst-{{$r->id}}">
                                                    Dar de alta
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        @endforeach
                    </div>
                    @endif

                    @if(count($reqP))
                    <div class="col-md-12 p-b-20 request" id="request-pen">
                        <h3 class="box-title">Solicitudes pendientes</h3>
                        @foreach($reqP as $r)
                            <div class="card card-outline-warning text-dark m-b-10">
                                <div class="card-block">
                                    <div class="col-md-12">
                                        <ul class="list-icons">
                                            <li>
                                                <i class="ti-angle-right"></i> 
                                                <strong>Cliente:</strong> 
                                                <span>{{ $r->name_c }} {{ $r->last_name_c }}</span>
                                            </li>
                                            <li>
                                                <i class="ti-angle-right"></i> 
                                                <strong>Monto primera cuota:</strong> 
                                                <span class="email_seller">${{ $r->first_pay }}</span>
                                            </li>
                                            <li>
                                                <i class="ti-angle-right"></i> 
                                                <strong>pack:</strong> 
                                                <span class="email_seller">{{ $r->pack }}</span>
                                            </li>
                                            <li>
                                                <i class="ti-angle-right"></i> 
                                                <strong>msisdn:</strong> 
                                                <span class="email_seller">{{ $r->msisdn }}</span>
                                            </li>
                                            <li>
                                                <i class="ti-angle-right"></i> 
                                                <strong>Fecha solicitud:</strong> 
                                                <span class="email_seller">{{ date('d-m-Y H:i',strtotime($r->date_update)) }}</span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @endif

                    @if(count($reqD))
                    <div class="col-md-12 p-b-20 request" id="request-den">
                        <h3 class="box-title">Solicitudes rechazadas</h3>
                        @foreach($reqD as $r)
                            <div class="card card-outline-danger text-dark m-b-10">
                                <div class="card-block">
                                    <div class="col-md-12">
                                        <ul class="list-icons">
                                            <li>
                                                <i class="ti-angle-right"></i> 
                                                <strong>Cliente:</strong> 
                                                <span>{{ $r->name_c }} {{ $r->last_name_c }}</span>
                                            </li>
                                            <li>
                                                <i class="ti-angle-right"></i> 
                                                <strong>Monto primera cuota:</strong> 
                                                <span class="email_seller">${{ $r->first_pay }}</span>
                                            </li>
                                            <li>
                                                <i class="ti-angle-right"></i> 
                                                <strong>pack:</strong> 
                                                <span class="email_seller">{{ $r->pack }}</span>
                                            </li>
                                            <li>
                                                <i class="ti-angle-right"></i> 
                                                <strong>msisdn:</strong> 
                                                <span class="email_seller">{{ $r->msisdn }}</span>
                                            </li>
                                            <li>
                                                <i class="ti-angle-right"></i> 
                                                <strong>Fecha rechazo:</strong> 
                                                <span class="email_seller">{{ date('d-m-Y H:i',strtotime($r->date_update)) }}</span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @endif

                    @if(!count($reqA) && !count($reqD) && !count($reqP))
                        <h3>No tienes solicitudes para listar.</h3>
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop

@section('scriptJS')
    <!-- typehead TextBox Search -->
    <script src="{{ asset('plugins/bower_components/typeahead.js-master/dist/typeahead.bundle.min.js') }}"></script>
    <script src="{{ asset('js/sweetalert.min.js') }}"></script>

    <script type="text/javascript">
        $(function () {
            $('.filter').on('click', function(e){
                $('.request').hide();

                $filter = $(e.currentTarget).data('filter');

                if($filter == 'all')
                    $('.request').show();
                else if($filter)
                    $('#request-'+$filter).show();
            });

            $('.btn-f-r').on('click', function(e){
                var form = $(e.currentTarget).data('form'),
                    action = $(e.currentTarget).val();

                if(form && action){
                    $('#action').val(action);

                    if(action == 'OK'){
                        swal({
                            title: "Procesando alta",
                            text: "Por favor no cierre ni refresque el navegador.",
                            icon: "warning",
                            closeOnClickOutside: false,
                            button: {visible: false},
                        });
                    }else{
                        $('.loading-ajax').show();
                    }

                    $('#'+form).submit();
                }
            });
        });
    </script>
@stop
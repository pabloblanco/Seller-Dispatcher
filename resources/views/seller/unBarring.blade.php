@extends('layouts.admin')

@section('customCSS')
    <link rel="stylesheet" href="{{ asset('css/selectize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/selectize.bootstrap.css') }}">
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
                <h3 class="box-title">Consultar MSISDN</h3>

                <form class="form-horizontal" id="Salesclientform" method="POST" action="{{route('seller.confirmSaleProduct')}}" data-toggle="validator">
                    {{ csrf_field() }}

                    <div class="form-group">
                        <label class="control-label">Buscar</label>
                        <select id="msisdn_select" name="msisdn_select" class="form-control">
                            <option value="">Seleccione el msisdn</option>
                            @foreach ($artics as $artic)
                                <option value="{{$artic->msisdn}}">{{$artic->msisdn}}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <div id="showPack">
                            
                        </div>
                        <div class="col-md-12 m-t-20" hidden id="btn-sale">
                            <button type="submit" class="btn btn-success waves-effect waves-light">
                                Vender
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div> 
@stop

@section('scriptJS')
    <script src="{{ asset('js/validator.js') }}"></script>
    <script src="{{ asset('js/selectize.js') }}"></script>
    <script src="{{ asset('js/sweetalert.min.js') }}"></script>

    @if(hasPermit('MAP-DSE'))
    <script src="{{ asset('https://maps.googleapis.com/maps/api/js?key='.env('GOOGLE_KEY').'&libraries=places') }}"></script>
    @endif

    <script type="text/javascript">
        $(function () {
            getPack = function(value){
                $('#btn-sale').attr('hidden', true);
                $('#showPack').html('');
                if(value && value.trim() != ''){
                    $(".preloader").fadeIn();
                    $.ajax({
                        url: '{{route('seller.getPackProduct')}}',
                        type: 'post',
                        data: { _token: "{{ csrf_token() }}", dn: value.trim()},
                        dataType: "json",
                        cache: false,
                        success: function (res) {
                            if(res.error && res.message == 'TOKEN_EXPIRED'){
                                showMessageAjax('alert-danger','Su session a expirado, por favor actualice la página.');
                            }else{
                                if(res.error){
                                    showMessageAjax('alert-danger', res.message ? res.message : 'Ocurrio un error.');
                                }else{
                                    $('#showPack').html(res.html);
                                    $('#btn-sale').attr('hidden', null);
                                }
                            }
                            $(".preloader").fadeOut();
                        },
                        error: function (res) {
                            console.log(res);
                            alert('Hubo un error');
                            $(".preloader").fadeOut();
                        }
                    });
                }
            };

            $('#msisdn_select').selectize({
                onChange: getPack
            });

            $('#Salesclientform').on('submit', function(e){
                var lng = $('#lon').val(),
                    lat = $('#lat').val(),
                    serv = $('#serviciability').val();
                    type_sell = $('#type-sell').val();

                $(".preloader").fadeIn();

                if(type_sell == 'H' && lat.trim() != '' && lng.trim() != '' && serv.trim() == 'NO_OK'){
                    e.preventDefault();
                    $.ajax({
                        url: '{{route('dashboard.serviciability')}}',
                        type: 'post',
                        data: { _token: "{{ csrf_token() }}", lat:lat, lon:lng},
                        dataType: "json",
                        cache: false,
                        success: function (res) {
                            if(res.error && res.message == 'TOKEN_EXPIRED'){
                                showMessageAjax('alert-danger','Su session a expirado, por favor actualice la página.');
                            }else if(res.error){
                                swal({
                                    title: "Venta no exitosa",
                                    text: "Equipo bloqueado.",
                                    icon: "error",
                                    button: "OK",
                                });
                                $(".preloader").fadeOut();
                            }else{
                                $('#serviciability').val('OK');
                                $('#Salesclientform').submit();
                            }
                        },
                        error: function (res){
                            alert('Hubo un error consultando servicialidad');
                            $(".preloader").fadeOut();
                        }
                    }); 
                }
            });

            @if(!empty(session('sale')))
                {{session()->forget('sale')}}
                swal({
                        title: "Venta confirmada",
                        text: "Desbloqueo exitoso.",
                        icon: "success",
                        button: "OK",
                    });
            @endif
        });
    </script>
@stop
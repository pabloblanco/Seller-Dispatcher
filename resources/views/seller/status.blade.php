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
            <h4 class="page-title"> Estatus de la linea </h4>
        </div>
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
                <li><a href="#">Ventas</a></li>
                <li class="active">Estatus</li>
            </ol>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="white-box">
                <div class="row">
                <h3 class="box-title">Número de MSISDN</h3>

                <div class="col-md-12">
                    <div class="col-sm-12 col-md-6 col-lg-4 m-b-20">
                    	<form class="form-horizontal" id="estatusForm" method="POST" action="" data-toggle="validator">
                    		{{ csrf_field() }}
	                        <div class="input-group">
	                            <input type="text" class="form-control" id="statusNumber" name="statusNumber" placeholder="MSISDN">
	                            <span class="input-group-btn">
	                                <button class="btn btn-info" type="submit" id="searchStatus">
	                                    <i class="zmdi zmdi-search zmdi-hc-fw"></i>
	                                </button>
	                            </span>
	                        </div>
	                    </form>
                    </div>
                </div>

                @if(!empty($client))
                    <div class="col-sm-12 col-md-12">
                        <div class="col-md-12">
                            @if(!$client['success'])
                                <div class="alert alert-danger">
                                    <span>{{$client['msg']}}</span>
                                </div>
                            @else
                            <blockquote>
                                <label>Cliente:</label>
                                <p>
                                    {{$client['data']->client->name}} {{$client['data']->client->last_name}}
                                </p>

                                @if(!empty($client['data']->client->serviceability))
                                    <label>Tipo de servicio:</label>
                                    <p>{{$client['data']->client->serviceability}}</p>
                                @endif

                                <label>Estado:</label>
                                <p>{{$client['data']->status_es}}</p>

                                @if(!empty($client['data']->name_last_recharge))
                                <label>Ultima recarga:</label>
                                <p>
                                    ${{$client['data']->amount_last_recharge}} - {{$client['data']->name_last_recharge}}
                                </p>
                                @endif

                                @if($client['data']->type == 'H' || $client['data']->type == 'M' || $client['data']->type == 'MH' || $client['data']->type == 'F')
                                    @if(!empty($client['data']->remaining_gb))
                                    <label>Gigas restantes:</label>
                                    <p>
                                    {{round($client['data']->remaining_gb, 2, PHP_ROUND_HALF_UP)}} GB
                                    </p>
                                    @endif
                                    <label>Fecha de expiraci&oacute;n:</label>
                                    @if(!empty($client['data']->expired_date))
                                    <p>
                                        {{getFormatDate($client['data']->expired_date)}}
                                    </p>
                                    @else
                                    <p>N/A</p>
                                    @endif
                                @endif

                                @if($client['data']->type == 'T' && !empty($client['data']->sort) && count($client['data']->sort))
                                    
                                    <label class="mb-0">Servicios activos</label>
                                    <div>
                                        @foreach($client['data']->sort as $item => $value)
                                            @php
                                                $gbs = false;
                                                $sms = false;
                                                $min = false;
                                                $gbs_total=0;   
                                                $gbs_disponibles=0;
                                                $gbs_inter=0;
                                                $sms_total=0;   
                                                $sms_disponibles=0;
                                                $sms_inter=0;
                                                $min_total=0;   
                                                $min_disponibles=0;
                                                $min_inter=0;
                                            @endphp 

                                            @foreach($value as $dato)

                                                @if(strpos($dato['name'], 'datos') !== false)  
                                                    @php
                                                        $gbs = true;
                                                        $gbs_total=$gbs_total+floatval($dato['total'] );
                                                        $gbs_disponibles=$gbs_disponibles+floatval($dato['remaing']);
                                                    @endphp
                                                    @if(strpos(strtolower($dato['name']), 'internacional') !== false)  
                                                        @php
                                                            $gbs_inter = $gbs_inter+floatval($dato['total']);
                                                        @endphp
                                                    @endif                                                    
                                                @endif

                                                @if(strpos($dato['name'], 'SMS') !== false)    
                                                    @php
                                                        $sms = true;
                                                        $sms_total=$sms_total+floatval($dato['total']);
                                                        $sms_disponibles=$sms_disponibles+floatval($dato['remaing']);
                                                    @endphp
                                                    @if(strpos(strtolower($dato['name']), 'internacional') !== false)  
                                                        @php
                                                            $sms_inter = $sms_inter+floatval($dato['total']);
                                                        @endphp
                                                    @endif
                                                @endif

                                                @if(strpos($dato['name'], 'Minutos') !== false)    
                                                    @php
                                                        $min = true;
                                                        $min_total=$min_total+floatval($dato['total']);
                                                        $min_disponibles=$min_disponibles+floatval($dato['remaing']);
                                                    @endphp
                                                    @if(strpos(strtolower($dato['name']), 'internacional') !== false)  
                                                        @php
                                                            $min_inter = $min_inter+floatval($dato['total']);
                                                        @endphp
                                                    @endif
                                                @endif  
                                            @endforeach

                                            @if($gbs == true)
                                                <label class="mt-4">- Datos</label>
                                                <p class="mb-0">Disponibles: <label class="mb-0">{{$gbs_disponibles}}GB</label></p>
                                                <p>Expira: <label class="mb-0">{{ getFormatDate($item) }}</label> </p>
                                            @endif

                                            @if($min == true)
                                                <label class="mt-4">- Minutos</label>
                                                 <p class="mb-0">Disponibles: <label class="mb-0">{{$min_disponibles}}</label></p>
                                                <p>Expira: <label class="mb-0">{{ getFormatDate($item) }}</label> </p>
                                            @endif

                                            @if($sms == true)
                                                <label class="mt-4">- SMS</label>
                                                <p class="mb-0">Disponibles: <label class="mb-0">{{$sms_disponibles}}</label></p>
                                                <p>Expira: <label class="mb-0">{{ getFormatDate($item) }}</label> </p>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif
                            </blockquote>
                            @endif
                        </div>
                    </div>
                @endif
                </div>
            </div>
        </div>
    </div>
@stop

@section('scriptJS')
    <script type="text/javascript">
        $(function () {
            $('#searchStatus').on('click', function(e){
                $('#searchStatus').attr('disabled',true);
                $('#estatusForm').submit();
            });

        	$('#estatusForm').on('submit', function(e){
        		var num = $('#statusNumber').val().trim(),
        			msg = '',
        			error = false;

        		if(num == ''){
        			error = true;
        			msg = 'Debe escribir el número de teléfono';
        		}else{
        			if(isNaN(parseInt(num))){
        				error = true;
        				msg = 'Debe ser un número';
        			}else{
        				if(num.length < 10 || num.length > 10){
        					error = true;
        					msg = 'El número debe ser de 10 dígitos';
        				}
        			}
        		}

        		if(error){
        			e.preventDefault();
                    $('#searchStatus').attr('disabled',false);
                    showMessageAjax('alert-danger', msg);
        		}else{
                    $('.preloader').show();
                }
        	});
        });
    </script>
@stop
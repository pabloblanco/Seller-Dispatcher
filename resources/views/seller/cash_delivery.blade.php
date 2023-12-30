@extends('layouts.admin')

{{--@section('customCSS')
    {{ Html::style(asset('plugins/bower_components/typeahead.js-master/dist/typehead-min.css')) }}
@stop--}}

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
            <h4 class="page-title"> Entrega de efectivo </h4>
        </div>
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
                <li><a href="#">Dashboard</a></li>
                <li class="active">Entrega de efectivo.</li>
            </ol>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="white-box">
                <h3 class="box-title">Notificar entrega de efectivo</h3>
                @if($sales->count())
		        <div id="sales-list" class="row">
		        	<h3 class="box-title text-center" style="width: 100%;">Tus ventas</h3>
		        	<div class="table-responsive" id="list-article">
			        	<form class="form-horizontal" name="form-reception" id="form-reception" action="" method="POST">
		        			{{ csrf_field() }}
				        	<table class="table table-bordered">
				                <thead>
				                    <tr>
				                        <th></th>
				                        <th>Cliente</th>
				                        <th>Monto</th>
                                        <th>Montivo rechazo</th>
				                        <th class="hidden-xs">Paquete</th>
				                        <th class="hidden-xs">Servicio</th>
				                        <th class="hidden-xs">Producto</th>
				                    </tr>
				                </thead>
				                <tbody>
                                    @php
                                        $total = 0;
                                    @endphp
                                    @foreach($sales as $sale)
                                        @php
                                            $total += $sale->amount;
                                        @endphp
                                        <tr class="item">
                                            <td>
                                                <input class="chk-r" type="checkbox" name="item[]" data-amount="{{ $sale->amount }}" value="{{ $sale->id }}">
                                            </td>
                                            <td>
                                                {{ $sale->name }} {{ $sale->last_name }} ({{ $sale->msisdn }})
                                            </td>
                                            <td>
                                                ${{ number_format($sale->amount,2,'.',',') }}
                                            </td>
                                            <td>
                                                @if(!empty($sale->reason_den))
                                                    {{ $sale->reason_den }}
                                                @else
                                                    N/A
                                                @endif
                                            </td>
                                            <td class="hidden-xs">
                                                {{ $sale->pack }}
                                            </td>
                                            <td class="hidden-xs">
                                                {{ $sale->service }}
                                            </td>
                                            <td class="hidden-xs">
                                                {{ $sale->product }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
				            </table>
				       	 	<div class="col-md-12 text-center">
				       	 		<strong>Total: </strong>
                                <span id="total-amount"> ${{number_format($total,2,'.',',')}}</span>
			                	<button type="button" id="send-noti" class="btn btn-success waves-effect waves-light m-l-10">
			                		Entregar
			                	</button>
			                </div>
		                </form>
		       	 	</div>
		       	</div>
                @endif

                @if($saleIns->count())
                <div class="row p-t-20">
                    <h3 class="box-title text-center" style="width: 100%;">Tus ventas en abono</h3>

                    <div class="table-responsive">
                        <form class="form-horizontal" name="form-reception-q" id="form-reception-q" action="{{ route('installments.payNotification') }}" method="POST">
                            {{ csrf_field() }}
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th></th>
                                        <th>Cliente</th>
                                        <th>Cuota</th>
                                        <th>Monto</th>
                                        <th>Montivo rechazo</th>
                                        <th class="hidden-xs">Paquete</th>
                                        <th class="hidden-xs">Servicio</th>
                                        <th class="hidden-xs">Producto</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $total = 0;
                                    @endphp
                                    @foreach($saleIns as $sale)
                                        @php
                                            $total += $sale->amount;
                                        @endphp
                                        <tr class="item">
                                            <td>
                                                <input class="chk-ri" type="checkbox" name="item[]" data-amount="{{$sale->amount}}" value="{{ $sale->id }}">
                                            </td>
                                            <td>
                                                {{ $sale->name }} {{ $sale->last_name }} ({{ $sale->msisdn }})
                                            </td>
                                            <td>
                                                @if($sale->n_quote == 1)
                                                    Cuota inicial
                                                @else
                                                    {{ $sale->n_quote }}
                                                @endif
                                            </td>
                                            <td>
                                                ${{ number_format($sale->amount,2,'.',',') }}
                                            </td>
                                            <td>
                                                @if(!empty($sale->reason_den))
                                                    {{ $sale->reason_den }}
                                                @else
                                                    N/A
                                                @endif
                                            </td>
                                            <td class="hidden-xs">
                                                {{ $sale->pack }}
                                            </td>
                                            <td class="hidden-xs">
                                                {{ $sale->service }}
                                            </td>
                                            <td class="hidden-xs">
                                                {{ $sale->product }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <div class="col-md-12 text-center">
                                <strong>Total: </strong>
                                <span id="total-amount-i">${{number_format($total,2,'.',',')}}</span>
                                <button type="button" id="send-noti-q" class="btn btn-success waves-effect waves-light m-l-10">
                                    Entregar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
@stop

@section('scriptJS')
    <script type="text/javascript">
        $(function () {
            $('#send-noti').on('click', function(e){
                $('.loading-ajax').show();
                $(this).attr('disabled', true);
                $('#form-reception').submit();
            });

            $('#send-noti-q').on('click', function(e){
                $('.loading-ajax').show();
                $(this).attr('disabled', true);
                $('#form-reception-q').submit();
            });

            $('.chk-r').on('click', function(e){
                let total = 0;
                $('.chk-r').each(function(){
                    if($(this).is(':checked')){
                        total += $(this).data('amount');
                    }
                });

                $('#total-amount').text('$' + total);
            });

            $('.chk-ri').on('click', function(e){
                let total = 0;
                $('.chk-ri').each(function(){
                    if($(this).is(':checked')){
                        total += $(this).data('amount');
                    }
                });

                $('#total-amount-i').text('$' + total);
            })
        });
    </script>
@stop
@if($data->count())
    <h3 class="box-title">Ventas</h3>
    @foreach($data as $sale)
    <div class="card card-outline-danger text-dark m-b-10" id="noti-{{ $sale->id }}">
        <div class="card-block">
            <div class="col-md-12">
                <ul class="list-icons">
                    <li>
                        <i class="ti-angle-right"></i> 
                        <strong>Nombre:</strong> 
                        <span>{{ $sale->name }} {{ $sale->last_name }}</span>
                    </li>
                    <li>
                        <i class="ti-angle-right"></i> 
                        <strong>Email:</strong> 
                        <span class="email_seller">{{ $sale->email }}</span>
                    </li>
                    <li>
                        <i class="ti-angle-right"></i> 
                        <strong>Monto:</strong> 
                        <span class="email_seller">${{ $sale->amount }}</span>
                    </li>
                    <li>
                        <i class="ti-angle-right"></i> 
                        <strong>Fecha:</strong> 
                        <span class="email_seller">{{ $sale->date_reg }}</span>
                    </li>
                    <li>
                        <i class="ti-angle-right"></i> 
                        <strong>DN`s:</strong> 
                        <div>
                            <ul>
                                @foreach($sale->msisdns as $msisdn)
                                    <li>
                                        <i class="icon-phone"></i>
                                        {{ $msisdn }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </li>
                </ul>
                <div class="text-center">
                    <button type="button" class="btn btn-danger waves-effect waves-light den-pay" data-id="{{ $sale->id }}" data-type="N">
                        Rechazar
                    </button>
                    <button type="button" class="btn btn-success waves-effect waves-light acept-pay" data-id="{{ $sale->id }}" data-type="N">
                        Aceptar
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endforeach
@endif

@if(count($dataInstF))
    <h3 class="box-title">Ventas a cuotas</h3>
    @foreach($dataInstF as $sale)
    <div class="card card-outline-danger text-dark m-b-10" id="noti-{{ $sale['transaction'] }}">
        <div class="card-block">
            <div class="col-md-12">
                <ul class="list-icons">
                    <li>
                        <i class="ti-angle-right"></i> 
                        <strong>Nombre:</strong> 
                        <span>{{ $sale['name'] }}</span>
                    </li>
                    <li>
                        <i class="ti-angle-right"></i> 
                        <strong>Email:</strong> 
                        <span class="email_seller">{{ $sale['email'] }}</span>
                    </li>
                    <li>
                        <i class="ti-angle-right"></i> 
                        <strong>Monto:</strong> 
                        <span class="email_seller">${{ $sale['amount'] }}</span>
                    </li>
                    <li>
                        <i class="ti-angle-right"></i> 
                        <strong>Fecha:</strong> 
                        <span class="email_seller">{{ $sale['date'] }}</span>
                    </li>
                    <li>
                        <i class="ti-angle-right"></i> 
                        <strong>DN`s:</strong> 
                        <div>
                            <ul>
                                @foreach($sale['msisdns'] as $msisdn)
                                    <li>
                                        <i class="icon-phone"></i>
                                        {{ $msisdn['dn'] }}
                                        cuota ({{ $msisdn['quote'] }})
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </li>
                </ul>
                <div class="text-center">
                    <button type="button" class="btn btn-danger waves-effect waves-light den-pay" data-id="{{ $sale['transaction'] }}" data-type="I">
                        Rechazar
                    </button>
                    <button type="button" class="btn btn-success waves-effect waves-light acept-pay" data-id="{{ $sale['transaction'] }}" data-type="I">
                        Aceptar
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endforeach
@endif

@if(!$data->count() && !count($dataInstF))
    <div class="row">
        <div class="alert alert-danger">
            <p>No hay recepciones de efectivo pendientes.</p>
        </div>
    </div>
@endif
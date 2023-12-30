@if($data->count())
    <div hidden>
        <form name="listActPag" id="listActPag" method="POST">
            <input type="hidden" name="seller_email" id="seller2" value="{{ $seller_email }}">
            <input type="hidden" name="dateb" id="dateb2" value="{{ !empty($db) ? $db : '' }}">
            <input type="hidden" name="datee" id="datee2" value="{{ !empty($de) ? $de : '' }}">
            <input type="hidden" name="skip" id="skip" value="{{ $skip }}">
        </form>
    </div>

    @php
        $typeS = [
            'H' => 'Internet Hogar',
            'T' => 'Telefonía Celular',
            'M' => 'Internet móvil nacional',
            'MH' => 'Internet móvil huella Altan',
            'F' => 'Fibra'
        ];
    @endphp

    @foreach($data as $sale)
    <div class="card card-outline-danger text-dark m-b-10">
        <div class="card-block">
            <div class="col-md-12">
                <h3 class="box-title">Venta - {{ $sale->unique_transaction }}</h3>
                <ul class="list-icons">
                    <li>
                        <i class="ti-angle-right"></i> 
                        <strong>DN Netwey:</strong> 
                        <span>{{ $sale->msisdn }} {{ $sale->is_migration == 'Y' ? '(Migración)' : '' }}</span>
                    </li>
                    <li>
                        <i class="ti-angle-right"></i> 
                        <strong>Tipo:</strong>
                        <span>{{ !empty($typeS[$sale->sale_type]) ? $typeS[$sale->sale_type] : 'Otro' }}</span>
                    </li>
                    <li>
                        <i class="ti-angle-right"></i> 
                        <strong>Vendedor:</strong> 
                        <span class="phone_seller">
                            {{ $sale->name_seller }} {{ $sale->last_name_seller }}
                        </span>
                    </li>
                    <li>
                        <i class="ti-angle-right"></i> 
                        <strong>Cliente:</strong> 
                        <span class="email_seller">
                            {{ $sale->name }} {{ $sale->last_name }}
                        </span>
                    </li>
                    <li>
                        <i class="ti-angle-right"></i> 
                        <strong>Teléfono1:</strong> 
                        <span class="email_seller">{{ $sale->phone_home }}</span>
                    </li>
                    <li>
                        <i class="ti-angle-right"></i> 
                        <strong>Teléfono2:</strong> 
                        <span class="email_seller">{{ $sale->phone }}</span>
                    </li>
                    <li>
                        <i class="ti-angle-right"></i> 
                        <strong>lat / long:</strong> 
                        <span class="email_seller">
                            @if(!empty($sale->lat))
                                {{ $sale->lat }} / {{ $sale->lng }}
                            @else
                                N/A
                            @endif
                        </span>
                    </li>
                    <li>
                        <i class="ti-angle-right"></i> 
                        <strong>Fecha:</strong> 
                        <span class="email_seller">{{getFormatDate($sale->date_reg)}}</span>
                    </li>
                    <li>
                        <i class="ti-angle-right"></i> 
                        <strong>Monto:</strong> 
                        <span class="email_seller">{{ '$'.number_format($sale->amount,2,'.',',')}}</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    @endforeach

    <div class="text-center">
        <p>Altas totales: <b> {{ $total }} </b></p>
    </div>

    <div class="text-center">
        @if( $skip != 0 )
        <button type="button" class="btn btn-success waves-effect" id="prev">
            Anterior
        </button>
        @endif
        @if( ($total - 5) > $skip )
        <button type="button" class="btn btn-success waves-effect" id="next">
            Siguiente
        </button>
        @endif
    </div>
@else
    <div class="row">
        <div class="alert alert-danger">
            <p>No hay activaciones para la fecha selecionada.</p>
        </div>
    </div>
@endif
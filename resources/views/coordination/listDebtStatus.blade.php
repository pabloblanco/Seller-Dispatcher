@if($data->count())
    @foreach($data as $debt)
    <div class="card card-outline-danger text-dark m-b-10">
        <div class="card-block">
            <div class="col-md-12">
                <h3 class="box-title">Fecha: {{ date_format(date_create($debt->date),'d-m-Y') }}</h3>
                <ul class="list-icons">
                    <li>
                        <i class="ti-angle-right"></i> 
                        <strong>Deuda Inicial:</strong>
                        <span>
                            {{ '$'.number_format($debt->init_debt,2,'.',',') }}
                        </span>
                    </li>
                    {{-- si es coordinador --}}
                    @if($debt->id_profile == '10' || $debt->id_profile == '18')
                        <li>
                            <i class="ti-angle-right"></i>
                            <strong>Deuda Inicial Vendedores:</strong>
                            <span>
                                {{ '$'.number_format($debt->init_debt_sellers,2,'.',',') }}
                            </span>
                        </li>
                    @endif
                    {{-- *************** --}}

                    <li>
                        <i class="ti-angle-right"></i> 
                        <strong>Altas:</strong>
                        <span>
                            @if($debt->ups_debt_day > 0)
                                <a href="#" data-id="{{$debt->id}}" data-type="ups" onclick="detailModalDebts('UPS','{{$debt->id}}','{{ date_format(date_create($debt->date),'d-m-Y') }}','{{($debt->id_profile == '10' || $debt->id_profile == '18')?'C':'V'}}');">{{ '$'.number_format($debt->ups_debt_day,2,'.',',') }}</a>
                            @else
                                <span>{{ '$'.number_format($debt->ups_debt_day,2,'.',',') }}</span>
                            @endif
                        </span>
                    </li>

                    {{-- si es coordinador --}}
                    @if($debt->id_profile == '10' || $debt->id_profile == '18')
                        <li>
                            <i class="ti-angle-right"></i>
                            <strong>Efectivo Recibido:</strong>
                            @if($debt->cash_received > 0)
                                <a href="#" data-id="{{$debt->id}}" data-type="received" onclick="detailModalDebts('REC','{{$debt->id}}','{{ date_format(date_create($debt->date),'d-m-Y') }}','C');">{{ '$'.number_format($debt->cash_received,2,'.',',') }}</a>
                            @else
                                <span>{{ '$'.number_format($debt->cash_received,2,'.',',') }}</span>
                            @endif
                        </li>

                        <li>
                            <i class="ti-angle-right"></i>
                            <strong>Depositos Conciliados:</strong>
                            @if($debt->conciliate_banks_day > 0)
                                <a href="#" data-id="{{$debt->id}}" data-type="received" onclick="detailModalDebts('DEP','{{$debt->id}}','{{ date_format(date_create($debt->date),'d-m-Y') }}','C');">{{ '$'.number_format($debt->conciliate_banks_day,2,'.',',') }}</a>
                            @else
                                <span>{{ '$'.number_format($debt->conciliate_banks_day,2,'.',',') }}</span>
                            @endif
                        </li>
                    @else
                        <li>
                            <i class="ti-angle-right"></i>
                            <strong>Efectivo Entregado:</strong>
                            @if($debt->cash_delivered > 0)
                                <a href="#" data-id="{{$debt->id}}" data-type="delivered" onclick="detailModalDebts('DEL','{{$debt->id}}','{{ date_format(date_create($debt->date),'d-m-Y') }}','V');">{{ '$'.number_format($debt->cash_delivered,2,'.',',') }}</a>
                            @else
                                <span>{{ '$'.number_format($debt->cash_delivered,2,'.',',') }}</span>
                            @endif
                        </li>
                    @endif
                    {{-- *************** --}}


                    <li>
                        <i class="ti-angle-right"></i>
                        <strong>Deuda Final:</strong>
                        <span>
                            {{ '$'.number_format($debt->finish_debt,2,'.',',') }}
                        </span>
                    </li>
                    {{-- si es coordinador --}}
                    @if($debt->id_profile == '10' || $debt->id_profile == '18')
                        <li>
                            <i class="ti-angle-right"></i>
                            <strong>Deuda Final Vendedores:</strong>
                            <span>
                                {{ '$'.number_format($debt->finish_debt_sellers,2,'.',',') }}
                            </span>
                        </li>
                    @endif
                    {{-- *************** --}}

                    {{-- <li>
                        <i class="ti-angle-right"></i> 
                        <strong>Cod. Dep&oacute;sito:</strong> 
                        <span>{{ $conc->id_deposit }}</span>
                    </li>
                    <li>
                        <i class="ti-angle-right"></i> 
                        <strong>Fecha:</strong> 
                        <span>{{ $conc->date_process }}</span>
                    </li>
                    <li>
                        <i class="ti-angle-right"></i> 
                        <strong>Motivo:</strong> 
                        <span>{{ !empty($conc->reason_deposit) ? $conc->reason_deposit : 'N/A' }}</span>
                    </li> --}}
                </ul>
            </div>
        </div>
    </div>
    @endforeach
@else
    <div class="row">
        <div class="alert alert-danger">
            <p>No hay Estados de Deudas registrados en el periodo seleccionado.</p>
        </div>
    </div>
@endif
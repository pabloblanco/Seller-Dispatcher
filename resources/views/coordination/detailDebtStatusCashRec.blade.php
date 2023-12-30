<div class="col-md-12">
    @php
        $total = 0;
    @endphp
    @foreach($sellers as $seller)
    @if(!empty($seller->asigned_sales) && count($seller->asigned_sales))
        <div class="card card-outline-primary text-center text-dark m-b-10">
            <div class="card-block">
                <div class="row">
                    <div class="col-md-12 pull-left">
                        <label class="pull-left">
                            Vendedor: <span> {{$seller->name}} {{$seller->last_name}} </span>
                        </label>
                    </div>
                    <div class="col-md-12 pull-left">
                        <label class="pull-left">
                            Email: <span> {{$seller->email}} </span>
                        </label>
                    </div>
                    <div class="col-md-12 pull-left">
                        <label class="pull-left">
                            Recibido: <span> $ {{$seller->amount}} </span>
                        </label>
                    </div>
                    <div class="col-md-12">
                        <label class="pull-left">
                            <a href="#" class="seeRegisters" data-register="{{$seller->dni}}" data-type='REC'> Ver registros </a>
                        </label>
                    </div>
                    @foreach($seller->asigned_sales as $asigned_sale)
                        <div class="w-100 card card-outline-danger text-dark m-b-10 {{$seller->dni}}" hidden="true">
                            <div class="card-block">
                                <header>Registro de recepción de dinero : <b>#{{$asigned_sale->id}}</b></header>
                                <hr>

                                <div class="row">
                                    <div class="col-md-12 pull-left">
                                        <label class="pull-left">
                                            Fecha recepción: <span> {{ date_format(date_create($asigned_sale->date_received),'d-m-Y H:i:s')}} </span>
                                        </label>
                                    </div>
                                    <div class="col-md-12 pull-left">
                                        <label class="pull-left">
                                            Monto: <span> $ {{$asigned_sale->amount}} </span>
                                        </label>
                                    </div>
                                    <div class="col-md-12 pull-left">
                                        <label class="pull-left">
                                            Ventas conciliadas: <span> {{$asigned_sale->status == 'A'?'Si':'No'}} </span>
                                        </label>
                                    </div>

                                    @if($asigned_sale->status == 'A')
                                        <div class="col-md-12 pull-left">
                                            <label class="pull-left">
                                                Fecha conciliación: <span> {{ date_format(date_create($asigned_sale->date_process),'d-m-Y H:i:s')}} </span>
                                            </label>
                                        </div>
                                        <div class="col-md-12">
                                            <label class="pull-left">
                                                <a href="#" class="seeDetails" data-detail="det-{{$asigned_sale->id}}"> Ver detalles </a>
                                            </label>
                                        </div>

                                        @foreach ($asigned_sale->details as $detail)
                                            <div class="w-100 card card-outline-success text-dark m-b-10 det-{{$asigned_sale->id}}" hidden="true">
                                                <div class="card-block">
                                                    <header>Venta <b>{{$detail->unique_transaction}}</b></header>
                                                <hr>
                                                    <div class="row">
                                                        <div class="col-md-12 pull-left">
                                                            <label class="pull-left">
                                                                DN Netwey: <span> {{$detail->msisdn}} </span>
                                                            </label>
                                                        </div>
                                                        <div class="col-md-12 pull-left">
                                                            <label class="pull-left">
                                                                Producto: <span> {{$detail->arti}} </span>
                                                            </label>
                                                        </div>
                                                        <div class="col-md-12 pull-left">
                                                            <label class="pull-left">
                                                                Tipo de linea: <span> {{$detail->sale_type == 'T' ? 'Telefonía' : ($detail->sale_type == 'M' ? 'MIFI' : 'Internet Hogar')}} </span>
                                                            </label>
                                                        </div>
                                                        <div class="col-md-12 pull-left">
                                                            <label class="pull-left">
                                                                Pack: <span> {{$detail->pack}} </span>
                                                            </label>
                                                        </div>
                                                        <div class="col-md-12 pull-left">
                                                            <label class="pull-left">
                                                                Fecha venta: <span> {{ date_format(date_create($detail->date_reg),'d-m-Y H:i:s')}} </span>
                                                            </label>
                                                        </div>
                                                        <div class="col-md-12 pull-left">
                                                            <label class="pull-left">
                                                                Monto: <span> $ {{$detail->amount}} </span>
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        </div>

                        @php
                            $total += $asigned_sale->amount;
                        @endphp
                    @endforeach
                </div>
            </div>
        </div>
    @endif
    @endforeach

    <div class="col-md-12">
        <label class="pull-right">Total: <span> ${{number_format($total,2,'.',',')}} </span></label>
    </div>
</div>
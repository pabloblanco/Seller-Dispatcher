<div class="col-md-12">
    @php
        $total = 0;
    @endphp
    @foreach($sellers as $seller)
    @if(!empty($seller->sales) && count($seller->sales))
        @if($usertype == 'C')
        <div class="card card-outline-primary text-center text-dark m-b-10">
            <div class="card-block">
                <div class="row">
        {{-- @endif
                    @if($usertype == 'C') --}}
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
                    <div class="col-md-12">
                        <label class="pull-left">
                            <a href="#" class="seeRegisters" data-register="{{$seller->dni}}" data-type='UPS'> Ver ventas </a>
                        </label>
                    </div>
                    @endif
                    @foreach($seller->sales as $sale)
                        <div class="card card-outline-success text-dark m-b-10 {{$seller->dni}}" @if($usertype == 'C') hidden="true" @endif>
                            <div class="card-block">
                                <header>Venta <b>{{$sale->unique_transaction}}</b></header>
                                <hr>

                                <div class="row">
                                    <div class="col-md-12 pull-left">
                                        <label class="pull-left">
                                            DN Netwey: <span> {{$sale->msisdn}} </span>
                                        </label>
                                    </div>
                                    <div class="col-md-12 pull-left">
                                        <label class="pull-left">
                                            Producto: <span> {{$sale->arti}} </span>
                                        </label>
                                    </div>
                                    <div class="col-md-12 pull-left">
                                        <label class="pull-left">
                                            Tipo de linea: <span> {{$sale->sale_type == 'T' ? 'Telefonía' : ($sale->sale_type == 'M' ? 'MIFI' : 'Internet Hogar')}} </span>
                                        </label>
                                    </div>
                                    <div class="col-md-12 pull-left">
                                        <label class="pull-left">
                                            Pack: <span> {{$sale->pack}} </span>
                                        </label>
                                    </div>
                                    <div class="col-md-12 pull-left">
                                        <label class="pull-left">
                                            Fecha venta: <span> {{ date_format(date_create($sale->date_reg),'d-m-Y H:i:s')}} </span>
                                        </label>
                                    </div>
                                    <div class="col-md-12 pull-left">
                                        <label class="pull-left">
                                            Monto: <span> $ {{$sale->amount}} </span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @php
                            $total += $sale->amount;
                        @endphp
                    @endforeach
        @if($usertype == 'C')
                </div>
            </div>
        </div>
        @endif
    @endif
    @endforeach

    <div class="col-md-12">
        <label class="pull-right">Total: <span> ${{number_format($total,2,'.',',')}} </span></label>
    </div>
</div>
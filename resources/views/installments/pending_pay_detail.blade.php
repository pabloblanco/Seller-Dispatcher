<h3 class="box-title">Pagos pendientes</h3>
@if(!empty($orderSales['expired']) && count($orderSales['expired']))
    @foreach($orderSales['expired'] as $sale)
        <div class="card card-outline-danger text-dark m-b-10" id="noti-1">
            <div class="card-block">
                <div class="col-md-12">
                    <ul class="list-icons">
                        <li>
                            <span class="label label-danger">Vencido</span>
                        </li>
                        <li>
                            <i class="ti-angle-right"></i> 
                            <strong>Vendedor:</strong> 
                            <span>
                                {{ $sale->name }} {{ $sale->last_name }}
                                @if($sale->seller == session('user') && session('user_type') != 'vendor')
                                    (Tú)
                                @endif
                            </span>
                        </li>
                        <li>
                            <i class="ti-angle-right"></i> 
                            <strong>Cliente:</strong> 
                            <span class="name-{{$sale->id}}">
                                {{ $sale->name_c }} {{ $sale->last_name_c }}
                            </span>
                        </li>
                        <li>
                            <i class="ti-angle-right"></i> 
                            <strong>cuotas restantes:</strong> 
                            <span class="quote-{{$sale->id}}">{{ $sale->quotes_rest }}</span>
                        </li>
                        <li>
                            <i class="ti-angle-right"></i> 
                            <strong>Monto a pagar:</strong> 
                            <span class="amount-{{$sale->id}}">${{ $sale->quote_amount }}</span>
                        </li>
                        <li>
                            <i class="ti-angle-right"></i> 
                            <strong>Fecha de vencimiento:</strong> 
                            <span>{{ $sale->date_expired }}</span>
                        </li>
                        <li>
                            <i class="ti-angle-right"></i> 
                            <strong>msisdn:</strong> 
                            <span class="dn-{{$sale->id}}">{{ $sale->msisdn }}</span>
                        </li>
                        <li>
                            <i class="ti-angle-right"></i> 
                            <strong>Direcci&oacute;n:</strong> 
                            <span>{{ !empty($sale->address)? $sale->address : 'N/A' }}</span>
                        </li>
                    </ul>
                    @if($sale->seller == session('user'))
                    <form name="do-pay" id="form-pay-{{$sale->id}}" method="POST" action="{{ route('installments.doPay') }}">
                        {{ csrf_field() }}
                        <input type="hidden" name="sale" value="{{$sale->id}}">
                        <div class="text-center">
                            <button type="button" data-sale="{{$sale->id}}" class="btn btn-success waves-effect waves-light acept-pay">
                                Pagar
                            </button>
                        </div>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    @endforeach
@endif

@if(!empty($orderSales['uptodate']) && count($orderSales['uptodate']))
    @foreach($orderSales['uptodate'] as $sale)
        <div class="card card-outline-success text-dark m-b-10" id="noti-1">
            <div class="card-block">
                <div class="col-md-12">
                    <ul class="list-icons">
                        <li>
                            <span class="label label-success">Al dia</span>
                        </li>
                        <li>
                            <i class="ti-angle-right"></i> 
                            <strong>Vendedor:</strong> 
                            <span>
                                {{ $sale->name }} {{ $sale->last_name }}
                                @if($sale->seller == session('user') && session('user_type') != 'vendor')
                                    (Tú)
                                @endif
                            </span>
                        </li>
                        <li>
                            <i class="ti-angle-right"></i> 
                            <strong>Cliente:</strong> 
                            <span class="name-{{$sale->id}}">
                                {{ $sale->name_c }} {{ $sale->last_name_c }}
                            </span>
                        </li>
                        <li>
                            <i class="ti-angle-right"></i> 
                            <strong>cuotas restantes:</strong> 
                            <span class="quote-{{$sale->id}}">{{ $sale->quotes_rest }}</span>
                        </li>
                        <li>
                            <i class="ti-angle-right"></i> 
                            <strong>Monto a pagar:</strong> 
                            <span class="amount-{{$sale->id}}">${{ $sale->quote_amount }}</span>
                        </li>
                        <li>
                            <i class="ti-angle-right"></i> 
                            <strong>Fecha de vencimiento:</strong> 
                            <span>{{ $sale->date_expired }}</span>
                        </li>
                        <li>
                            <i class="ti-angle-right"></i> 
                            <strong>msisdn:</strong> 
                            <span class="dn-{{$sale->id}}">{{ $sale->msisdn }}</span>
                        </li>
                        <li>
                            <i class="ti-angle-right"></i> 
                            <strong>Direcci&oacute;n:</strong> 
                            <span>{{ !empty($sale->address)? $sale->address : 'N/A' }}</span>
                        </li>
                    </ul>
                    @if($sale->seller == session('user'))
                    <form name="do-pay" id="form-pay-{{$sale->id}}" method="POST" action="{{ route('installments.doPay') }}">
                        {{ csrf_field() }}
                        <input type="hidden" name="sale" value="{{$sale->id}}">
                        <div class="text-center">
                            <button type="button" data-sale="{{$sale->id}}" class="btn btn-success waves-effect waves-light acept-pay">
                                Pagar
                            </button>
                        </div>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    @endforeach
@endif

<div id="pay-confirm" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog" style="top: 140px;">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title">Confirmación de Pago</h4>
            </div>
            <div class="modal-body">
                <div class="col-sm-12 col-md-6">
                    <label>Nombre / Apellido:</label>
                    <p class="name-client"></p>
                </div>
                <div class="col-sm-12 col-md-6">
                    <label>MSISDN:</label>
                    <p class="dn-client"></p>
                </div>
                <div class="col-sm-12 col-md-6">
                    <label>Cuota:</label>
                    <p class="quote-client"></p>
                </div>
                <div class="col-sm-12 col-md-6">
                    <label>Monto:</label>
                    <p class="amount-client"></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default waves-effect" data-dismiss="modal">Cancelar</button>
                <button type="button" id="conf-pay-btn" class="btn btn-danger waves-effect waves-light">
                    Confirmar
                </button>
            </div>
        </div>
    </div>
</div>

@if((empty($orderSales['uptodate']) || !count($orderSales['uptodate'])) && (empty($orderSales['expired']) || !count($orderSales['expired'])))
    <h3>No tienes pagos pendientes.</h3>
@endif

<script type="text/javascript">
    $(function () {
        $('.acept-pay').on('click', function(e){
            e.preventDefault();

            var sale = $(e.currentTarget).data('sale');
            
            if(sale){
                $('#pay-confirm .name-client').text($('.name-'+sale).text());
                $('#pay-confirm .dn-client').text($('.dn-'+sale).text());
                $('#pay-confirm .quote-client').text($('.quote-'+sale).text());
                $('#pay-confirm .amount-client').text($('.amount-'+sale).text());
                $('#conf-pay-btn').data('sale', sale);

                $('#pay-confirm').modal('show');
            }else{
                showMessageAjax('alert-danger', 'No se puede procesar el pago.');
            }
            
            return false;
        });

        $('#conf-pay-btn').on('click', function(e){
            var btn = $(e.currentTarget).data('sale');

            if(btn && btn != ''){
                $(e.currentTarget).attr('disabled', true);

                $('form[name="do-pay"]').find('button[type="submit"]').attr('disabled', true);

                $('.loading-ajax').show();

                $('#form-pay-' + btn).submit();
            }else{
                showMessageAjax('alert-danger', 'No se puede procesar el pago.');
            }
        });

        $('#pay-confirm').on('hide.bs.modal', function (event) {
            $('#pay-confirm .name-client').text('');
            $('#pay-confirm .dn-client').text('');
            $('#pay-confirm .quote-client').text('');
            $('#pay-confirm .amount-client').text('');
            $('#conf-pay-btn').data('sale', 0);
        });
    });
</script>
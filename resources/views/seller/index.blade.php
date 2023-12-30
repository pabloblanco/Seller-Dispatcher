@extends('layouts.admin')

@section('content')
    @include('components.messages')
    @include('components.messagesAjax')

    @if ($errors->any())
<div class="alert alert-danger">
  <ul>
    @foreach ($errors->all() as $error)
    <li>
      {{ $error }}
    </li>
    @endforeach
  </ul>
</div>
@endif
<div class="row bg-title">
  <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
    <h4 class="page-title">
      Módulo de ventas
    </h4>
  </div>
  <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12 d-flex justify-content-end">
    <ol class="breadcrumb">
      <li>
        <a href="#">
          Ventas
        </a>
      </li>
      <li class="active">
        Venta más activación netwey.
      </li>
    </ol>
  </div>
</div>
<div class="row">
  @if(!empty($lock) && $lock->is_locked == 'Y')
  <div class="col-md-12">
    <div class="white-box">
      <div class="alert alert-danger">
        <p>
          <b>
            Has sido bloqueado
          </b>
          , por favor comunicate con tu supervisor.
        </p>
      </div>
    </div>
  </div>
  @else
  <div class="col-md-12">
    @php
    $rut = route("seller.showClientN");
    @endphp
    @include('seller.searchClient', ['urlRouteViewClient' => $rut ])
  </div>
  <div aria-hidden="true" aria-labelledby="myModalLabel" class="modal fade" id="sale-confirmation" role="dialog" style="display: none;" tabindex="-1">
    <div class="modal-dialog" style="max-height:95%;  margin-top: 180px;">
      <div class="modal-content">
        <div class="modal-header">
          <button aria-hidden="true" class="close" data-dismiss="modal" type="button">
            ×
          </button>
          <h4 class="modal-title">
            Confirmación del Alta
            <span id="type-sell-txt">
            </span>
          </h4>
        </div>
        <div class="modal-body">
          <div class="col-sm-12 col-md-6">
            <label>
              Nombre / Apellido:
            </label>
            <p class="name-client">
            </p>
          </div>
          <div class="col-sm-12 col-md-6">
            <label>
              INE:
            </label>
            <p class="ine-client">
            </p>
          </div>
          <div class="col-sm-12 col-md-6">
            <label>
              Tipo de compra:
            </label>
            <p class="type-client">
            </p>
          </div>
          <div class="col-sm-12 col-md-6">
            <label>
              Plan:
            </label>
            <p class="plan-client">
            </p>
          </div>
          <div class="col-sm-12 col-md-6">
            <label>
              MSISDN:
            </label>
            <p class="msisdn-client">
            </p>
          </div>
          <div class="col-sm-12 col-md-6 type-pay-n">
            <label>
              Precio total:
            </label>
            <p class="price-client">
            </p>
          </div>
          <div class="col-sm-12 col-md-6 type-pay-q" hidden="">
            <label>
              Cuotas:
            </label>
            <p class="quotes-client">
            </p>
          </div>
          <div class="col-sm-12 col-md-6 type-pay-q" hidden="">
            <label>
              Monto abonado:
            </label>
            <p class="price-client">
            </p>
          </div>
          <div class="col-sm-12 col-md-6 type-pay-q" hidden="">
            <label>
              Monto cuota(s) restante(s):
            </label>
            <p class="quotes-price">
            </p>
          </div>
          <div class="col-md-12" hidden="true" id="info-refered-content">
            <label>
              Referido por:
            </label>
            <p class="refered-by">
            </p>
          </div>
          <div class="col-md-12" hidden="true" id="info-payjoy-content">
            <div class="alert alert-danger alert-dismissable">
              <button aria-hidden="true" class="close" data-dismiss="alert" type="button">
                ×
              </button>
              <strong>
                Recordatorio:
              </strong>
              Si la venta es con financiamiento
              <i>
                'PayJoy' ó 'Paguitos'
              </i>
              , al finalizar el registro en la plataforma de financiamiento debes hacer la asociación en la opción correspondiente del menú ventas.
              <b>
                Si no realizas la asociación debes pagar el 100% de la deuda
              </b>
              .
            </div>
          </div>
                    {{--

          @if(hasPermit('SEL-TLP'))
          <div class="col-md-12" hidden="true" id="info-telmovpay-content">
            <div class="alert text-white label-red alert-dismissable">
              <button aria-hidden="true" class="close" data-dismiss="alert" type="button">
                ×
              </button>
              <strong>
                Recordatorio:
              </strong>
              Al finalizar debes ir al menú ventas a la opción de
              <i>
                'Ventas / TelmovPay / Asociar financiamiento'
              </i>
              para procesar la financiación.
              <b>
                Si no realizas la asociación debes pagar el 100% de la deuda
              </b>
              .
            </div>
          </div>
          @endif          --}}

        </div>
        <div class="modal-footer">
          <button class="btn btn-default waves-effect" data-dismiss="modal" type="button">
            Cancelar
          </button>
          <button class="btn btn-danger waves-effect waves-light btnBuy" hidden="" type="button">
            Procesar
          </button>
          <button class="btn btn-danger waves-effect waves-light btnBuy-coppel" hidden="" type="button">
            Pagar
          </button>
        </div>
      </div>
    </div>
  </div>
  @endif
</div>
@stop

@if(!empty($lock) && $lock->is_locked == 'N')

{{-- https://1000hz.github.io/bootstrap-validator/#validator-usage --}}
{{--
<script src="{{ asset('plugins/bower_components/dropify/dist/js/dropify.min.js') }}">
</script>
--}}
<script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_KEY') }}&libraries=places">
</script>
<script src="https://sdk.coppelpay.com/coppelpaysdk/CoppelPay.js">
</script>
@endif

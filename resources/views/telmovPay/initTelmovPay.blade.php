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
      Iniciar proceso de financimiento TelmovPay.
    </h4>
  </div>
  <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12 d-flex justify-content-end">
    <ol class="breadcrumb">
      <li>
        <a href="#">
          TelmovPay
        </a>
      </li>
      <li class="active">
        Iniciar financimiento
      </li>
    </ol>
  </div>
</div>
<div class="row">
  <div class="col-md-12">
    <div class="alert alert-info alert-dismissable">
      <button aria-hidden="true" class="close" data-dismiss="alert" type="button">
        ×
      </button>
      <strong>
        Pasos:
      </strong>
      <br/>
      <li>
        Crear el contrato de financiamiento de telmovPay para conocer si procede el financiamiento
      </li>
      <li>
        Realizar la venta desde la opción
        <i>
          <a href="{{route('seller.index')}}">
            <strong>
              "venta + activación"
            </strong>
          </a>
        </i>
        en la plataforma de ventas Netwey y seleccionar la opción tipo de pago
        <i>
          "TelmovPay"
        </i>
      </li>
      {{--<li>
        Al finalizar debes ir al menú a la opción
        <i>
          <strong>
            'Ventas / TelmovPay / Asociar financiamiento'
          </strong>
        </i>
        para procesar la financiación.
        <b>
          Si no realizas la asociación debes pagar el 100% de la deuda
        </b>
        .
      </li>--}}
    </div>
    <div class="alert text-white label-red alert-dismissable">
      <button aria-hidden="true" class="close" data-dismiss="alert" type="button">
        ×
      </button>
      <strong>
        Nota:
      </strong>
      <br/>
      <li>
        No cierre ni refresque la ventana mientras se este configurando el proceso con telmovPay
      </li>
      <li>
        Solo puedes tener un (1) proceso activo a la vez con telmovPay
      </li>
      <li>
        Se debe disponer de un celular con camara frontal y posterior.
      </li>
      <li>
        El cliente debe tener a la mano el
        <strong>
          CURP vigente
        </strong>
        y un
        <strong>
          correo electronico.
        </strong>
      </li>
      <li>
        <strong>
          NO ENCENDER
        </strong>
        el equipo celular hasta que se le indique para realizar una correcta configuración. Caso contrario debes realizar un reseteo de fabrica completa del equipo.
      </li>
      <li>
        Se debe contar con red Wifi al momento de encender el dispositivo
      </li>
    </div>
  </div>
  <div class="col-md-12 white-box">
    @php
    $rut = route("telmovpay.step1InitFinace");
    @endphp
    @include('seller.searchClient', ['urlRouteViewClient' => $rut ])
  </div>
</div>
@stop
@section('scriptJS')
@stop

@extends('layouts.admin')

@section('customCSS')
<link href="{{ asset('plugins/bower_components/typeahead.js-master/dist/typehead-min.css') }}" rel="stylesheet"/>
<link href="{{ asset('plugins/bower_components/dropify/dist/css/dropify.min.css') }}" rel="stylesheet"/>
<link href="{{ asset('css/selectize.css') }}" rel="stylesheet"/>
<link href="{{ asset('css/selectize.bootstrap.css') }}" rel="stylesheet"/>
@stop

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
<div class="row py-3 bg-title">
  <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
    <h4 class="page-title">
      Solicitud de baja
    </h4>
  </div>
  <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
    <ol class="breadcrumb float-right">
      <li>
        <a href="#">
          Bajas
        </a>
      </li>
      <li class="active">
        Solicitud de baja.
      </li>
    </ol>
  </div>
</div>
<div class="row">
  @if($lock->is_locked == 'Y')
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
    <div class="white-box">
      <h3 class="box-title">
        Buscar vendedor a dar de baja
      </h3>
      <form action="" class="form-horizontal" data-toggle="validator" id="Salesclientform" method="POST">
        {{ csrf_field() }}
        <div class="form-group">
          <div class="col-md-12">
            <div id="scrollable-dropdown-menu">
              <select class="form-control" id="list-users" name="list-users">
              </select>
            </div>
          </div>
        </div>
        <div class="alert alert-danger" hidden="true" id="alert-content-inw">
          <p class="font-weight-bold">
            El usuario:
            <span id="user_selected">
            </span>
            ya se encuentra con solicitud de baja en espera de ser procesada.
          </p>
        </div>
      </form>
      <div class="row hidden py-3" id="data-seller">
        <h3 class="box-title">
          Datos del vendedor
        </h3>
        <div class="col-12">
          <ul class="list-icons">
            <li>
              <i class="ti-angle-right">
              </i>
              <strong>
                Nombre:
              </strong>
              <span class="name_seller">
              </span>
            </li>
            <li>
              <i class="ti-angle-right">
              </i>
              <strong>
                Teléfono:
              </strong>
              <span class="phone_seller">
              </span>
            </li>
            <li>
              <i class="ti-angle-right">
              </i>
              <strong>
                Email:
              </strong>
              <span class="email_seller">
              </span>
            </li>
          </ul>
        </div>
      </div>
      <div class="row py-3 hidden" id="deuda-seller">
        <div class="col-12">
          <hr/>
        </div>
        <h3 class="box-title">
          Deuda del vendedor
        </h3>
        <div class="col-12">
        </div>
        <div class="row w-100 justify-content-center hidden" id="block-deuda">
          <div class="col-12 box-title">
            > Deuda en efectivo
          </div>
          <div class="col-md-4 col-sm-6 col-12">
            <div class="white-box text-center bg-info" style="padding: 14px !important;">
              <h1 class="text-white counter">
                <span id="mountDeuda">
                  $ 0
                </span>
              </h1>
              <p class="text-white">
                Deuda en efectivo
              </p>
            </div>
          </div>
          <div class="col-md-4 col-sm-6 col-12">
            <div class="white-box text-center bg-info" style="padding: 14px !important;">
              <h1 class="text-white counter">
                <span id="dayDeuda">
                  0
                </span>
              </h1>
              <p class="text-white">
                Días de deuda
              </p>
            </div>
          </div>
          <div class="col-md-4 col-sm-6 col-12">
            <div class="white-box text-center bg-info" style="padding: 14px !important;">
              <h1 class="text-white counter">
                <span id="cantDeuda">
                  0
                </span>
              </h1>
              <p class="text-white">
                Cantidad de ventas
              </p>
            </div>
          </div>
        </div>
        <div class="row w-100 justify-content-center hidden" id="block-equipos">
          <div class="col-12 box-title">
            > Deuda en equipos
          </div>
          <div class="col-md-4 col-sm-6 col-12">
            <div class="white-box text-center bg-info" style="padding: 14px !important;">
              <h1 class="text-white counter">
                <span id="cash_hbb">
                  $ 0
                </span>
              </h1>
              <p class="text-white">
                HBB (
                <span id="cant_hbb">
                  0
                </span>
                )
              </p>
            </div>
          </div>
          <div class="col-md-4 col-sm-6 col-12">
            <div class="white-box text-center bg-info" style="padding: 14px !important;">
              <h1 class="text-white counter">
                <span id="cash_telf">
                  $ 0
                </span>
              </h1>
              <p class="text-white">
                Telefonia (
                <span id="cant_telf">
                  0
                </span>
                )
              </p>
            </div>
          </div>
          <div class="col-md-4 col-sm-6 col-12">
            <div class="white-box text-center bg-info" style="padding: 14px !important;">
              <h1 class="text-white counter">
                <span id="cash_mifi">
                  $ 0
                </span>
              </h1>
              <p class="text-white">
                Mifi  (
                <span id="cant_mifi">
                  0
                </span>
                )
              </p>
            </div>
          </div>
          <div class="col-md-4 col-sm-6 col-12">
            <div class="white-box text-center bg-info" style="padding: 14px !important;">
              <h1 class="text-white counter">
                <span id="cash_fibra">
                  $ 0
                </span>
              </h1>
              <p class="text-white">
                Fibra  (
                <span id="cant_fibra">
                  0
                </span>
                )
              </p>
            </div>
          </div>
        </div>
        {{--
        <div class="row w-100 justify-content-center" id="block-abonos">
          <div class="col-12 box-title">
            > Deuda en abonos
          </div>
          <div class="col-md-4 col-sm-6 col-12">
            <div class="white-box text-center bg-info" style="padding: 14px !important;">
              <h1 class="text-white counter">
                <span id="cash_abonos">
                  $ 0
                </span>
              </h1>
              <p class="text-white">
                Deuda ventas en abonos
              </p>
            </div>
          </div>
          <div class="col-md-4 col-sm-6 col-12">
            <div class="white-box text-center bg-info" style="padding: 14px !important;">
              <h1 class="text-white counter">
                <span id="count_abonos">
                  0
                </span>
              </h1>
              <p class="text-white">
                Cantidad ventas en abonos
              </p>
            </div>
          </div>
        </div>
        --}}
        <div class="row w-100 justify-content-center hidden" id="deuda_total">
          <div class="col-12 box-title">
            > Deuda total
          </div>
          <div class="col-md-4 pb-md-0 pb-4 col-12 text-center d-flex align-items-center justify-content-center">
            <div class="icon-dashboard">
              <i class="fa fa-dollar icon">
              </i>
            </div>
            <p class="total-debt" id="mountTotal">
              $ 0
            </p>
          </div>
        </div>
        <div class="col-12 hidden d-flex justify-content-center" id="without-deuda">
          <div class="alert alert-danger">
            <p class="font-weight-bold">
              Vendedor sin deuda.
            </p>
          </div>
        </div>
      </div>
      <div class="row py-3 hidden" id="info-seller">
        <div class="col-12">
          <hr/>
        </div>
        <h3 class="box-title">
          Inventario del vendedor
        </h3>
        <div class="col-12 hidden d-flex justify-content-center text-center" id="without-device">
          <div class="alert alert-success">
            <p class="font-weight-bold">
              Por favor retira el inventario que posee asignado, tienes 15 dias para realizar esta accion.
              <span class="name_seller">
              </span>
              .
            </p>
          </div>
        </div>
        <div class="col-12 hidden d-flex justify-content-center" id="without-article">
          <div class="alert alert-danger">
            <p class="font-weight-bold">
              Vendedor sin inventario.
            </p>
          </div>
        </div>
        <div class="table-responsive hidden" id="device-content">
        </div>
      </div>
      <div class="row pt-5 hidden" id="low-motive">
        <div class="col-12">
          <hr/>
        </div>
        <div class="col-12 form-group">
          <h3 class="box-title">
            Motivo de la baja
          </h3>
          <select class="form-control" id="reason" name="reason" required="">
            <option value="">
              Seleccione un motivo
            </option>
            @foreach ($Reason as $motivo)
            <option value="{{$motivo['id']}}">
              {{$motivo['reason']}}
            </option>
            @endforeach
          </select>
        </div>
      </div>
      <div class="row py-3 hidden" id="block-sales">
        <div class="col-12">
          <hr/>
        </div>
        <div class="col-12">
          <h3 class="box-title">
            Ventas realizadas ultimas dos(2) semanas
          </h3>
          <span id="dateSales">
          </span>
        </div>
        <div class="row w-100 justify-content-center hidden" id="detail-sales">
          <div class="col-lg-3 col-md-5 col-sm-12">
            <div class="white-box text-center bg-info" style="padding: 14px !important;">
              <h1 class="text-white counter">
                <span id="cantSale">
                </span>
              </h1>
              <p class="text-white">
                Cantidad de ventas
              </p>
            </div>
          </div>
          <div class="col-lg-3 col-md-5 col-sm-12">
            <div class="white-box text-center bg-info" style="padding: 14px !important;">
              <h1 class="text-white counter">
                <span id="mountSale">
                  $
                </span>
              </h1>
              <p class="text-white">
                Dinero generado en ventas
              </p>
            </div>
          </div>
          <div class="col-12 d-flex justify-content-center">
            <button class="btn btn-success" id="btnPlusDetailSales" name="btnPlusDetailSales" type="button">
              <i class="zmdi zmdi-eye">
              </i>
              Ver detalles
            </button>
          </div>
          <div class="col-12 table-responsive pt-5 hidden" id="device-sales">
          </div>
        </div>
        <div class="col-12 hidden d-flex justify-content-center" id="without-sales">
          <div class="alert alert-danger">
            <p class="font-weight-bold">
              Vendor sin ventas.
            </p>
          </div>
        </div>
      </div>
      <div class="row py-3 hidden" id="low-evidence">
        <div class="col-12">
          <hr/>
        </div>
        <div class="col-12 form-group">
          <h3 class="box-title">
            Adjuntar evidencia de la baja
          </h3>
          <div class="pt-4" id="block-evidence">
          </div>
          {{--
          <input accept=".jpg, .png, image/jpeg, image/png" id="photo" multiple="" name="photo" type="file"/>
          --}}
          <button class="btn btn-success" id="btnEvidence" name="btnEvidence" type="button">
            <i class="zmdi zmdi-file-plus">
            </i>
            Agregar evidencia
          </button>
        </div>
      </div>
      @if($lock->is_locked == 'N')
      <div class="col-12 text-center pt-5 hidden" id="low-btnSend">
        <button class="btn btn-success" id="btnsend" name="btnsend" type="button">
          <i class="zmdi zmdi-cloud-upload">
          </i>
          Solicitar baja
        </button>
      </div>
      @endif
    </div>
  </div>
  @endif
</div>
@stop

@section('scriptJS')
@if($lock->is_locked == 'N')
<!-- typehead TextBox Search -->
<script src="{{ asset('plugins/bower_components/typeahead.js-master/dist/typeahead.bundle.min.js') }}">
</script>
<script src="{{ asset('plugins/bower_components/dropify/dist/js/dropify.min.js') }}">
</script>
<script src="{{ asset('js/sweetalert.min.js') }}">
</script>
<script src="{{ asset('js/selectize.js')}}">
</script>
<script src="{{ asset('js/low/newRequest.JS').'?v=0.1'}}">
</script>
@endif
@stop

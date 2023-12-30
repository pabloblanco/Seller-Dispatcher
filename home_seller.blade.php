@extends('layouts.admin')

@php
    $typeDev = [
        'H' => 'Internet Hogar',
        'T' => 'Telefonía',
        'MH' => 'Internet móvil huella altan',
        'M' => 'Internet móvil nacional',
        'F' => 'Fibra'
    ]
@endphp

@section('customCSS')
<link href="{{ asset('plugins/bower_components/bootstrap-daterangepicker/daterangepicker.css') }}" rel="stylesheet"/>
<link href="{{ asset('plugins/bower_components/bootstrap-datepicker/bootstrap-datepicker.min.css') }}" rel="stylesheet"/>
<link href="{{ asset('css/selectize.css') }}" rel="stylesheet"/>
<link href="{{ asset('css/selectize.bootstrap.css') }}" rel="stylesheet"/>
<link href="{{ asset('plugins/bower_components/clockpicker/dist/jquery-clockpicker.min.css') }}" rel="stylesheet"/>
@stop

@section('content')
    @include('components.messages')
    @include('components.messagesAjax')
<div class="row bg-title">
  <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
    <h4 class="page-title">
      Dashboard
    </h4>
  </div>
  <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
    <ol class="breadcrumb">
      <li>
        <a href="#">
          Dashboard
        </a>
      </li>
      <li class="active">
        Home
      </li>
    </ol>
  </div>
</div>
<div class="row">
  @if(!empty($data['cod_dep']))
  <div class="col-md-12">
    <div class="white-box">
      <div class="row">
        <h3 class="box-title">
          Código de depósito
        </h3>
        <div class="col-md-12">
          <p>
            @if(session('user_type') == 'vendor')
               Código de depósito de tú coordinador:
            @else
               Tu código de depósito:
            @endif
            <b>
              {{ $data['cod_dep']->id_deposit }}
            </b>
          </p>
        </div>
      </div>
    </div>
  </div>
  @endif

        @if(session('org_type') != 'R' && !hasPermit('SEL-INF'))
  <div class="col-md-12">
    <div class="white-box">
      <div class="row">
        <h3 class="box-title">
          Serviciabilidad por ubicación.
        </h3>
        <div class="col-md-12">
          <div class="form-group">
            <div class="col-lg-4 col-xs-12 col-md-12 col-sm-12">
              <input class="form-control lat-loc" name="lat" placeholder="Latitud" required="" type="text"/>
              <div class="help-block with-errors">
              </div>
            </div>
            <div class="col-lg-4 col-xs-12 col-md-12 col-sm-12">
              <input class="form-control lon-loc" name="lon" placeholder="longitud" required="" type="text"/>
              <div class="help-block with-errors">
              </div>
            </div>
            <div class="col-lg-4 col-xs-12 col-md-4 col-sm-12">
              <button class="btn btn-success waves-effect waves-light m-r-10 btnGeo" data-btn="loc" name="btnGeo" type="button">
                Ubicar
              </button>
            </div>
          </div>
        </div>
        <div class="col-md-12 p-t-20 hidden" id="serv-c-loc">
          <div class="card card-outline-secondary text-center text-dark">
            <div class="card-block">
              <p class="m-0 font-18 serviciability-loc">
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  @endif

  @if(hasPermit('FIB-VLD'))
  <div class="col-md-12">
    <div class="white-box">
      <div class="row">
        <h3 class="box-title">
          Comprobar dispatcher 2.
        </h3>
      </div>
    </div>
  </div>
  @endif

        @if(hasPermit('SEL-MOV'))
  <div class="col-md-12">
    <div class="white-box">
      <div class="row">
        <h3 class="box-title">
          Comprobar compatibilidad (Telefonía).
        </h3>
        <div class="col-md-12">
          <div class="alert alert-info alert-dismissable">
            <button aria-hidden="true" class="close" data-dismiss="alert" type="button">
              ×
            </button>
            Para obtener el número de IMEI de tu teléfono celular marca *#06#.
          </div>
          <div class="input-group">
            <input class="form-control" id="imei" name="imei" placeholder="IMEI" type="number"/>
            <span class="input-group-btn">
              <button class="btn btn-success" id="valid-imei" type="button">
                Consultar
              </button>
            </span>
          </div>
          <div class="help-block with-errors" id="imei-error">
          </div>
          <div class="alert" id="alert-comp">
          </div>
        </div>
      </div>
    </div>
  </div>
  @endif

        @if(session('org_type') == 'R' || hasPermit('MAP-SEL'))
  <div class="col-md-12">
    <div class="white-box">
      <div class="row">
        <h3 class="box-title">
          Serviciabilidad por dirección.
        </h3>
        <div class="col-md-12">
          {{--
          <label class="col-md-12">
            Dirección
          </label>
          --}}
          <input class="form-control form-control-sm" id="address" name="address" placeholder="Escribe la dirección donde estara el Netwey*" type="text"/>
          <div class="col-md-12 col-sm-12 map-container" id="map" style="height: 50vh;min-height: 300px; padding-top: 20px;">
            <div id="map-content" style="width: 100%;height: 100%;">
            </div>
          </div>
        </div>
        <div class="col-md-12 p-t-20">
          <div class="col-lg-4 col-xs-12 col-md-12 col-sm-12">
            <label>
              Latitud:
            </label>
            <input class="form-control lat-map" name="lat" type="text"/>
            <div class="help-block with-errors">
            </div>
          </div>
          <div class="col-lg-4 col-xs-12 col-md-12 col-sm-12">
            <label>
              longitud:
            </label>
            <input class="form-control lon-map" name="lon" type="text"/>
            <div class="help-block with-errors">
            </div>
          </div>
          <div class="col-lg-4 col-xs-12 col-md-4 col-sm-12">
            <button class="btn btn-success waves-effect waves-light m-r-10 btnGeo" data-btn="map" name="btnMap" style="margin-top: 26px;" type="button">
              Consultar
            </button>
          </div>
        </div>
        <div class="col-md-12 p-t-20 hidden" id="serv-c-map">
          <div class="card card-outline-secondary text-center text-dark">
            <div class="card-block">
              <p class="m-0 font-18 serviciability-map">
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  @endif

        @if($data['balanceUser']>0)
  <div class="col-md-12">
    <div class="white-box">
      <h3 class="box-title">
        Saldo de recargas.
      </h3>
      <div class="row">
        <div class="col-lg-2 col-md-3 col-xs-12 col-sm-12 text-center">
          <div class="icon-dashboard">
            <i class="fa fa-credit-card-alt icon">
            </i>
          </div>
        </div>
        <div class="col-lg-3 col-md-4 col-xs-12 col-sm-12">
          <div class="white-box text-center bg-theme-dark">
            <h1 class="text-white">
              $
              <span class="counter">
                {{number_format($data['balanceUser'],2,'.',',')}}
              </span>
            </h1>
            <p class="text-white font-18">
              Monto para recargas
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
  @endif

  @if(session('user_type') != 'vendor')
  <div class="col-md-12">
    <div class="white-box">
      <h3 class="box-title">
        Deuda vendedores activos.
      </h3>
      <div class="row">
        <div class="col-lg-2 col-md-3 col-xs-12 col-sm-12 text-center">
          <div class="icon-dashboard">
            <i class="fa fa-dollar icon">
            </i>
          </div>
          @if(!empty($data['sellersHasDebt']) && $data['sellersHasDebt'])
          <p class="total-debt">
            ${{$data['sellers']->sum('debtamount')}}
          </p>
          @endif
        </div>
        <div class="col-lg-10 col-md-9 col-xs-12 col-sm-12 table-responsive">
          @if(!empty($data['sellersHasDebt']) && $data['sellersHasDebt'])
          <div class="table-responsive">
            <table class="table table-bordered">
              <thead>
                <tr>
                  <th class="text-nowrap">
                    N°
                  </th>
                  <th>
                    Vendedor
                  </th>
                  <th>
                    Días de deuda
                  </th>
                  <th>
                    N° Ventas
                  </th>
                  <th>
                    Monto
                  </th>
                  <th>
                    Tipo
                  </th>
                </tr>
              </thead>
              <tbody>
                @php($i=0)
                @foreach($data['sellers'] as $seller)
                  @if($seller->debtcount)
                    @php($i++)
                <tr>
                  <td class="text-nowrap">
                    {{$i}}
                  </td>
                  <td>
                    {{$seller->name}} {{$seller->last_name}}
                  </td>
                  <td class="@if($seller->debtdays >= 5) text-red @endif">
                    {{$seller->debtdays}}
                  </td>
                  <td>
                    {{$seller->debtcount}}
                  </td>
                  <td>
                    ${{$seller->debtamount}}
                  </td>
                  <td>
                    {{ str_replace(' ', '', $seller->name_profile) }}
                  </td>
                </tr>
                @endif
                                    @endforeach
              </tbody>
            </table>
          </div>
          @else
          <p>
            Tus vendedores no tienen deuda.
          </p>
          @endif
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-12">
    <div class="white-box">
      <h3 class="box-title">
        Deuda vendedores en proceso de baja.
      </h3>
      <div class="row">
        <div class="col-lg-2 col-md-3 col-xs-12 col-sm-12 text-center">
          <div class="icon-dashboard">
            <i class="fa fa-dollar icon">
            </i>
          </div>
          @if(!empty($data['sellersLowHasDebt']) && $data['sellersLowHasDebt'])
          <p class="total-debt">
            ${{$data['lowSellers']->sum('debtamount')}}
          </p>
          @endif
        </div>
        <div class="col-lg-10 col-md-9 col-xs-12 col-sm-12 table-responsive">
          @if(!empty($data['sellersLowHasDebt']) && $data['sellersLowHasDebt'])
          <div class="table-responsive">
            <table class="table table-bordered">
              <thead>
                <tr>
                  <th class="text-nowrap">
                    N°
                  </th>
                  <th>
                    Vendedor
                  </th>
                  <th>
                    Días de deuda
                  </th>
                  <th>
                    N° Ventas
                  </th>
                  <th>
                    Monto
                  </th>
                  <th>
                    Tipo
                  </th>
                </tr>
              </thead>
              <tbody>
                @php($i=0)
                @foreach($data['lowSellers'] as $seller)
                  @if($seller->debtcount)
                    @php($i++)
                <tr>
                  <td class="text-nowrap">
                    {{$i}}
                  </td>
                  <td>
                    {{$seller->name}} {{$seller->last_name}}
                  </td>
                  <td class="@if($seller->debtdays >= 5) text-red @endif">
                    {{$seller->debtdays}}
                  </td>
                  <td>
                    {{$seller->debtcount}}
                  </td>
                  <td>
                    ${{$seller->debtamount}}
                  </td>
                  <td>
                    {{ str_replace(' ', '', $seller->name_profile) }}
                  </td>
                </tr>
                @endif
                                    @endforeach
              </tbody>
            </table>
          </div>
          @else
          <p>
            Tus vendedores no tienen deuda.
          </p>
          @endif
        </div>
      </div>
    </div>
  </div>
  @endif

        @if(session('user_type') != 'vendor')
  <div class="col-md-12">
    <div class="white-box">
      <h3 class="box-title">
        Seleccione usuario.
      </h3>
      <div class="row">
        <div class="col-lg-2 col-md-3 col-xs-12 col-sm-12 text-center">
          <div class="icon-dashboard">
            <i class="fa fa-user icon">
            </i>
          </div>
        </div>
        <div class="col-lg-10 col-md-9 col-xs-12 col-sm-12">
          <select class="form-control" id="list-seller" name="list-seller">
            <option selected="true" value="{{!empty($data['select']) ? $data['select'] : session('user')}}">
              Yo
            </option>
          </select>
        </div>
      </div>
    </div>
  </div>
  @elseif(session('user_type') == 'vendor')
  <input id="list-seller" name="list-seller" type="hidden" value="{{session('user')}}"/>
  @endif

        @if(session('org_type') != 'R')
        @if(count($data['sales_inst']) && !empty($data['sales_inst']['expired']))
  <div class="col-md-12">
    <div class="white-box">
      <h3 class="box-title">
        Pagos vencidos (Abonos).
      </h3>
      <div class="row">
        <div class="col-lg-2 col-md-3 col-xs-12 col-sm-12 text-center">
          <div class="icon-dashboard">
            <i class="ti-face-sad icon">
            </i>
          </div>
        </div>
        <div class="col-lg-10 col-md-9 col-xs-12 col-sm-12 table-responsive">
          <table class="table table-bordered">
            <thead>
              <tr>
                <th>
                  Cliente
                </th>
                @if(session('user_type') == 'vendor')
                <th>
                  Fecha exp.
                </th>
                @else
                <th>
                  Vendedor
                </th>
                @endif
                <th>
                  Detalle
                </th>
              </tr>
            </thead>
            <tbody>
              @foreach($data['sales_inst']['expired'] as $sale)
              <tr>
                <td>
                  {{ $sale->name_c }} {{ $sale->last_name_c }}
                </td>
                @if(session('user_type') == 'vendor')
                <td>
                  {{ date('d/m/y', strtotime($sale->date_expired)) }}
                </td>
                @else
                <td>
                  {{ $sale->name }} {{ $sale->last_name }} @if(session('user') == $sale->seller) (tú) @endif
                </td>
                @endif
                <td>
                  @if(session('user_type') == 'vendor')
                  <a href="{{ route('installments.pendingPaySeller', ['saleid' => $sale->id]) }}">
                    Ver
                  </a>
                  @else
                  <a href="{{ route('installments.pendingPay', ['saleid' => $sale->id]) }}">
                    Ver
                  </a>
                  @endif
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
  @endif

        @if(count($data['sales_inst']) && !empty($data['sales_inst']['nextExp']))
  <div class="col-md-12">
    <div class="white-box">
      <h3 class="box-title">
        Pagos pendientes (Abonos).
      </h3>
      <div class="row">
        <div class="col-lg-2 col-md-3 col-xs-12 col-sm-12 text-center">
          <div class="icon-dashboard">
            <i class="ti-marker icon">
            </i>
          </div>
        </div>
        <div class="col-lg-10 col-md-9 col-xs-12 col-sm-12 table-responsive">
          <table class="table table-bordered">
            <thead>
              <tr>
                <th>
                  Cliente
                </th>
                @if(session('user_type') == 'vendor')
                <th>
                  Fecha exp.
                </th>
                @else
                <th>
                  Vendedor
                </th>
                @endif
                <th>
                  Detalle
                </th>
              </tr>
            </thead>
            <tbody>
              @foreach($data['sales_inst']['nextExp'] as $sale)
              <tr>
                <td>
                  {{ $sale->name_c }} {{ $sale->last_name_c }}
                </td>
                @if(session('user_type') == 'vendor')
                <td>
                  {{ date('d/m/y', strtotime($sale->date_expired)) }}
                </td>
                @else
                <td>
                  {{ $sale->name }} {{ $sale->last_name }} @if(session('user') == $sale->seller) (tú) @endif
                </td>
                @endif
                <td>
                  @if(session('user_type') == 'vendor')
                  <a href="{{ route('installments.pendingPaySeller', ['saleid' => $sale->id]) }}">
                    Ver
                  </a>
                  @else
                  <a href="{{ route('installments.pendingPay', ['saleid' => $sale->id]) }}">
                    Ver
                  </a>
                  @endif
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
  @endif

        {{-- @if(showMenu(['SEL-FIB']))
  <div class="col-md-12">
    <div class="white-box">
      <h3 class="box-title">
        Instalaciones pendientes por cobrar.
      </h3>
      <div class="row">
        <div class="col-lg-2 col-md-3 col-xs-12 col-sm-12 text-center">
          <div class="icon-dashboard">
            <i class="ti-signal icon">
            </i>
          </div>
        </div>
        <div class="col-lg-10 col-md-9 col-xs-12 col-sm-12 table-responsive">
          @if(count($data['instalationsNotPaid']))
          <div class="table-responsive">
            <table class="table table-bordered">
              <thead>
                <tr>
                  <th>
                    Cliente
                  </th>
                  <th>
                    Teléfono
                  </th>
                  <th>
                    Dirección
                  </th>
                  <th>
                    Fecha de instalación
                  </th>
                  <th>
                    Ver
                  </th>
                </tr>
              </thead>
              <tbody id="row-detail-content">
                @include('dashboard.pending_paid',['instalations' => $data['instalationsNotPaid']])
              </tbody>
            </table>
          </div>
          @else
          <p>
            No hay citas de instalación pendientes.
          </p>
          @endif
        </div>
      </div>
    </div>
  </div>
  @endif
        --}}
  <div class="col-md-12">
    <div class="white-box">
      <h3 class="box-title">
        Inventario.
      </h3>
      <div class="row">
        <div class="col-lg-2 col-md-3 col-xs-12 col-sm-12 text-center">
          <div class="icon-dashboard">
            <i class="zmdi zmdi-codepen zmdi-hc-fw icon">
            </i>
          </div>
        </div>
        <div class="col-lg-10 col-md-9 col-xs-12 col-sm-12 table-responsive">
          @if(count($data['articles']) || count($data['articlesTe']) || count($data['articlesMI']) || count($data['articlesF']))
            @if(hasPermit('DCI-DSE'))
          <div class="col-md-12">
            <button class="btn btn-success m-b-20" id="exportCsv" type="button">
              Exportar CSV
            </button>
            <a href="#" id="downloadfile" style="display: none;">
            </a>
          </div>
          @endif
          <div class="table-responsive">
            <table class="table table-bordered">
              <thead>
                <tr>
                  <th class="text-nowrap">
                    N°
                  </th>
                  <th>
                    Equipo
                  </th>
                  <th>
                    MSISDN
                  </th>
                  <th>
                    ICCID
                  </th>
                  <th>
                    IMEI/MAC
                  </th>
                  <th>
                    Fecha Asignación
                  </th>
                </tr>
              </thead>
              <tbody>
                @include('dashboard.inventory',['articles' => $data['articles'], 'i' => 0, 'type' => 'H'])

                  @include('dashboard.inventory',['articles' => $data['articlesTe'], 'i' => count($data['articles']), 'type' => 'T'])

                  @include('dashboard.inventory',['articles' => $data['articlesMI'], 'i' => (count($data['articlesTe']) + count($data['articles'])), 'type' => 'M'])

                  @include('dashboard.inventory',['articles' => $data['articlesF'], 'i' => (count($data['articlesTe']) + count($data['articles']) + count($data['articlesMI'])), 'type' => 'F'])
              </tbody>
            </table>
          </div>
          @else
          <p>
            Sin equipos asignados.
          </p>
          @endif
        </div>
      </div>
    </div>
  </div>

  {{--Jefe instaladores--}}

  @if(hasPermit('FIB-VAC'))
  <div class="col-md-12" id="agendaInstalations">
    <div class="white-box">
      <h3 class="box-title">
        Citas por asignar.
      </h3>
      <div class="row justify-content-center">
        <div class="col-lg-2 col-md-3 col-xs-12 col-sm-12 text-center">
          <div class="icon-dashboard">
            <i class="ti-agenda icon">
            </i>
          </div>
          <div class="py-3 text-left">
            Cantidad de citas: <span class="counter font-weight-bold">{{count($data['agendaInstalations'])}}</span>
          </div>
          @if(count($data['agendaInstalations']))
            <div class="py-2 text-left">
              Citas Hoy: <span class="counter font-weight-bold" id="cant_citas_hoy">0</span>
            </div>
          @endif
          @if($data['caduce_agenda'])
            <div class="py-2 text-left">
              Citas vencidas ayer: <span class="counter font-weight-bold" id="cant_citas_vencida">{{ $data['caduce_agenda'] }}</span>
            </div>
          @endif
        </div>
        <div class="col-lg-10 col-md-9 col-xs-12 col-sm-12 table-responsive d-flex justify-content-center align-items-center">
          @if(count($data['agendaInstalations']))

            @include('dashboard.datesInstall',['dates' => $data['agendaInstalations'], 'type' => 'installerAgenda'])

          @if($data['showNextInstalations'])
          <div class="col-md-12">
            <button class="btn btn-success waves-effect" data-date="{{$data['showNextInstalations']}}" data-dateb="{{$data['datesInstalationsB']}}" id="load-more-dates" type="button">
              Cargar más
            </button>
          </div>
          @endif
          @else
          <p>
            No hay citas pendientes por asignar.
          </p>
          @endif
        </div>
      </div>
    </div>
  </div>
  @endif

  @if(hasPermit('FIB-VLC'))
  <div class="col-md-12" id="asigneInstalations">
    <div class="white-box">
      <h3 class="box-title">
        Citas asignadas a instaladores.
      </h3>
      <div class="row justify-content-center">
        <div class="col-lg-2 col-md-3 col-xs-12 col-sm-12 text-center">
          <div class="icon-dashboard">
            <i class="fa fa-calendar icon">
              {{--fa fa-list--}}
            </i>
          </div>
          <div class="py-3 text-left">
            Cantidad de asignaciones: <span class="counter font-weight-bold">{{count($data['asigneInstalations'])}}</span>
          </div>
          @if(count($data['asigneInstalations']))
            <div class="py-2 text-left">
              Instalaciones Hoy: <span class="counter font-weight-bold" id="cant_asignaciones_hoy">0</span>
            </div>
          @endif
          @if($data['caduce_asigne'])
            <div class="py-2 text-left">
              Instalaciones vencidas ayer: <span class="counter font-weight-bold" id="cant_asignaciones_vencida">{{ $data['caduce_asigne'] }}</span>
            </div>
          @endif
        </div>
        <div class="col-lg-10 col-md-9 col-xs-12 col-sm-12 table-responsive d-flex justify-content-center align-items-center">
          @if(count($data['asigneInstalations']))

            @include('dashboard.datesInstall',['dates' => $data['asigneInstalations'], 'type' => 'installerAsigne'])

          @if($data['showNextInstalations'])
          <div class="col-md-12">
            <button class="btn btn-success waves-effect" data-date="{{$data['showNextInstalations']}}" data-dateb="{{$data['datesInstalationsB']}}" id="load-more-dates" type="button">
              Cargar más
            </button>
          </div>
          @endif
          @else
          <p>
            No hay citas asignas a instaladores.
          </p>
          @endif
        </div>
      </div>
    </div>
  </div>
  @endif
{{--End Jefe instaladores--}}

  @if(hasPermit('SEL-INF'))
  <div class="col-md-12">
    <div class="white-box">
      <h3 class="box-title">
        Instalaciones por realizar.
      </h3>
      <div class="row justify-content-center">
        <div class="col-lg-2 col-md-3 col-xs-12 col-sm-12 text-center">
          <div class="icon-dashboard">
            <i class="ti-signal icon">
            </i>
          </div>
          <div class="py-3 text-left">
            Cantidad de instalaciones: <span class="counter font-weight-bold">{{count($data['datesInstalations'])}}</span>
          </div>
          @if(count($data['datesInstalations']))
            <div class="py-2 text-left">
              Instalaciones Hoy: <span class="counter font-weight-bold" id="cant_instalation_hoy">0</span>
            </div>
          @endif
          @if($data['caduce_instalations'])
            <div class="py-2 text-left">
              Instalaciones vencidas ayer: <span class="counter font-weight-bold" id="cant_instalation_vencida">{{ $data['caduce_instalations'] }}</span>
            </div>
          @endif
        </div>
        <div class="col-lg-10 col-md-9 col-xs-12 col-sm-12 table-responsive d-flex justify-content-center align-items-center">
          @if(count($data['datesInstalations']))

            @include('dashboard.datesInstall',['dates' => $data['datesInstalations'], 'type' => 'installer'])

          @if($data['showNextInstalations'])
          <div class="col-md-12">
            <button class="btn btn-success waves-effect" data-date="{{$data['showNextInstalations']}}" data-dateb="{{$data['datesInstalationsB']}}" id="load-more-dates" type="button">
              Cargar más
            </button>
          </div>
          @endif
          @else
          <p>
            No hay instalación pendientes por realizar.
          </p>
          @endif
        </div>
      </div>
    </div>
  </div>
  @endif

  @if(hasPermit('FIB-VLD'))
  <div class="col-md-12" id="agendaSupports">
    <div class="white-box">
      <h3 class="box-title">
        Listado de soportes pendientes.
      </h3>
      <div class="row justify-content-center">
        <div class="col-lg-2 col-md-3 col-xs-12 col-sm-12 text-center">
          <div class="icon-dashboard">
            <i class="ti-agenda icon">
            </i>
          </div>
          <div class="py-3 text-left">
            Cantidad de servicios: <span class="counter font-weight-bold">{{count($data['agendaSupports'])}}</span>
          </div>
          @if(count($data['agendaSupports']))
            <div class="py-2 text-left">
              Citas Hoy: <span class="counter font-weight-bold" id="cant_citas_soporte_hoy">0</span>
            </div>
          @endif
          @if($data['caduce_support_agenda'])
            <div class="py-2 text-left">
              Citas vencidas ayer: <span class="counter font-weight-bold" id="cant_citas_vencida">{{ $data['caduce_support_agenda'] }}</span>
            </div>
          @endif
        </div>
        <div class="col-lg-10 col-md-9 col-xs-12 col-sm-12 table-responsive d-flex justify-content-center align-items-center">
          @if(count($data['agendaSupports']))

            @include('dashboard.datesSupport',['dates' => $data['agendaSupports'], 'type' => 'supportAgenda'])

          @if($data['showNextSupports'])
          <div class="col-md-12">
            <button class="btn btn-success waves-effect" data-date="{{$data['showNextSupports']}}" data-dateb="{{$data['showNextSupportsB']}}" id="load-more-support-dates" type="button">
              Cargar más
            </button>
          </div>
          @endif
          @else
          <p>
            No hay citas de soporte pendientes por asignar.
          </p>
          @endif
        </div>
      </div>
    </div>
  </div>
  @endif

  @if(hasPermit('FIB-ASS'))
  <div class="col-md-12" id="agendaInstalations">
    <div class="white-box">
      <h3 class="box-title">
        Citas de soporte por asignar.
      </h3>
      <div class="row justify-content-center">
        <div class="col-lg-2 col-md-3 col-xs-12 col-sm-12 text-center">
          <div class="icon-dashboard">
            <i class="ti-support icon">
            </i>
          </div>
          <div class="py-3 text-left">
            Cantidad de citas: <span class="counter font-weight-bold">{{count($data['agendaInstalations'])}}</span>
          </div>
          @if(count($data['agendaInstalations']))
            <div class="py-2 text-left">
              Citas Hoy: <span class="counter font-weight-bold" id="cant_citas_hoy">0</span>
            </div>
          @endif
          @if($data['caduce_agenda'])
            <div class="py-2 text-left">
              Citas vencidas ayer: <span class="counter font-weight-bold" id="cant_citas_vencida">{{ $data['caduce_agenda'] }}</span>
            </div>
          @endif
        </div>
        <div class="col-lg-10 col-md-9 col-xs-12 col-sm-12 table-responsive d-flex justify-content-center align-items-center">
          @if(count($data['agendaInstalations']))

            @include('dashboard.datesInstall',['dates' => $data['agendaInstalations'], 'type' => 'installerAgenda'])

          @if($data['showNextInstalations'])
          <div class="col-md-12">
            <button class="btn btn-success waves-effect" data-date="{{$data['showNextInstalations']}}" data-dateb="{{$data['datesInstalationsB']}}" id="load-more-dates" type="button">
              Cargar más
            </button>
          </div>
          @endif
          @else
          <p>
            No hay citas de soporte por asignar.
          </p>
          @endif
        </div>
      </div>
    </div>
  </div>
  @endif

  @if(hasPermit('FIB-ASS'))
  <div class="col-md-12" id="agendaInstalations">
    <div class="white-box">
      <h3 class="box-title">
        Citas de soporte asignadas pendientes de realizar.
      </h3>
      <div class="row justify-content-center">
        <div class="col-lg-2 col-md-3 col-xs-12 col-sm-12 text-center">
          <div class="icon-dashboard">
            <i class="ti-calendar icon">
            </i>
          </div>
          <div class="py-3 text-left">
            Cantidad de citas: <span class="counter font-weight-bold">{{count($data['agendaInstalations'])}}</span>
          </div>
          @if(count($data['agendaInstalations']))
            <div class="py-2 text-left">
              Citas Hoy: <span class="counter font-weight-bold" id="cant_citas_hoy">0</span>
            </div>
          @endif
          @if($data['caduce_agenda'])
            <div class="py-2 text-left">
              Citas vencidas ayer: <span class="counter font-weight-bold" id="cant_citas_vencida">{{ $data['caduce_agenda'] }}</span>
            </div>
          @endif
        </div>
        <div class="col-lg-10 col-md-9 col-xs-12 col-sm-12 table-responsive d-flex justify-content-center align-items-center">
          @if(count($data['agendaInstalations']))

            @include('dashboard.datesInstall',['dates' => $data['agendaInstalations'], 'type' => 'installerAgenda'])

          @if($data['showNextInstalations'])
          <div class="col-md-12">
            <button class="btn btn-success waves-effect" data-date="{{$data['showNextInstalations']}}" data-dateb="{{$data['datesInstalationsB']}}" id="load-more-dates" type="button">
              Cargar más
            </button>
          </div>
          @endif
          @else
          <p>
            No hay citas de soporte asignadas pendientes de realizar.
          </p>
          @endif
        </div>
      </div>
    </div>
  </div>
  @endif

        @if(!hasPermit('SEL-INF'))
        @include(
            'dashboard.balances',
            [
                'title' => 'Efectivo.',
                'amount' => number_format((
                                $data['total_mount_e'] +
                                $data['total_mount_e_tel'] +
                                $data['total_mount_e_mi'] +
                                $data['total_mount_e_mih'] +
                                $data['total_mount_e_f'] +
                                $data['due_coord'] +
                                $data['due_coordTE'] +
                                $data['due_coordMI'] +
                                $data['due_coordMIH'] +
                                $data['due_coordF']
                                )
                                ,2,'.',','),
                'detailCash' => ($data['detailCash'] || $data['detailCashFSeller'] || $data['detailCashInstFSeller'])
            ]
        )

        @if($data['typeuser'] != 'vendor')
            @include(
                'dashboard.balances',
                [
                    'title' => 'Saldo a favor.',
                    'amount' => number_format($data['resAmount'],2,'.',',')
                ]
            )
        @endif

        @include(
            'dashboard.balances',
            [
                'title' => 'Deuda Total.',
                'amount' => number_format((
                                $data['due'] +
                                $data['total_mount_e'] +
                                $data['due_coord'] +
                                $data['dueTE'] +
                                $data['total_mount_e_tel'] +
                                $data['total_mount_e_mi'] +
                                $data['total_mount_e_mih'] +
                                $data['total_mount_e_f'] +
                                $data['due_coordTE'] +
                                $data['due_coordMI'] +
                                $data['due_coordMIH'] +
                                $data['due_coordF']
                                )
                                ,2,'.',',')
            ]
        )


        @include(
            'dashboard.balances',
            [
                'title' => 'Deuda Internet Hogar.',
                'amount' => number_format(($data['due'] + $data['total_mount_e'] + $data['due_coord']),2,'.',',')
            ]
        )

        @include(
            'dashboard.balances',
            [
                'title' => 'Deuda Telefonía.',
                'amount' => number_format(($data['dueTE'] + $data['total_mount_e_tel'] + $data['due_coordTE']),2,'.',',')
            ]
        )

        @include(
            'dashboard.balances',
            [
                'title' => 'Deuda Internet móvil nacional.',
                'amount' => number_format(($data['dueMI'] + $data['total_mount_e_mi'] + $data['due_coordMI']),2,'.',',')
            ]
        )

        @include(
            'dashboard.balances',
            [
                'title' => 'Deuda Internet huella Altan.',
                'amount' => number_format(($data['dueMI'] + $data['total_mount_e_mih'] + $data['due_coordMIH']),2,'.',',')
            ]
        )
        @endif {{--Fin el if politica de instalación fibra--}}

        @if(hasPermit('SEL-INF'))
        @include(
            'dashboard.balances',
            [
                'title' => 'Deuda Fibra.',
                'amount' => number_format(($data['dueF'] + $data['total_mount_e_f'] + $data['due_coordF']),2,'.',',')
            ]
        )
        @endif

        @endif {{-- Fin del if != retail --}}

        @if(!hasPermit('SEL-INF'))
        @include(
            'dashboard.sales',
            [
                'title' => 'Ventas.',
                'type' => 'h',
                'totalSales' => $data['total_sales'],
                'dateRange' => date('d-m-Y', strtotime($data['dateSaleB'])).'/'.date('d-m-Y', strtotime($data['dateSaleE'])),
                'salesDetail' => $data['salesDetail']
            ]
        )

        @include(
            'dashboard.sales',
            [
                'title' => 'Ventas Telefonía.',
                'type' => 't',
                'totalSales' => $data['total_sales_t'],
                'dateRange' => date('d-m-Y', strtotime($data['dateSaleB'])).'/'.date('d-m-Y', strtotime($data['dateSaleE'])),
                'salesDetail' => $data['salesDetailT']
            ]
        )

        @include(
            'dashboard.sales',
            [
                'title' => 'Ventas Internet móvil nacional.',
                'type' => 'mi',
                'totalSales' => $data['total_sales_mi'],
                'dateRange' => date('d-m-Y', strtotime($data['dateSaleB'])).'/'.date('d-m-Y', strtotime($data['dateSaleE'])),
                'salesDetail' => $data['salesDetailMI']
            ]
        )

        @include(
            'dashboard.sales',
            [
                'title' => 'Ventas Internet móvil huella Altan.',
                'type' => 'mih',
                'totalSales' => $data['total_sales_mih'],
                'dateRange' => date('d-m-Y', strtotime($data['dateSaleB'])).'/'.date('d-m-Y', strtotime($data['dateSaleE'])),
                'salesDetail' => $data['salesDetailMIH']
            ]
        )

        @if(showMenu(['SEL-PSI']) || showMenu(['DSI-DSE']))
        @include(
            'dashboard.sales',
            [
                'title' => 'Ventas en abono.',
                'type' => 'inst',
                'totalSales' => $data['total_sales_inst'],
                'dateRange' => date('d-m-Y', strtotime($data['dateSaleB'])).'/'.date('d-m-Y', strtotime($data['dateSaleE'])),
                'salesDetail' => $data['salesDetail_inst']
            ]
        )
        @endif
        @endif {{--Fin el if politica de instalación fibra--}}

        @if(showMenu(['SEL-INF']))
        @include(
            'dashboard.sales',
            [
                'title' => 'Ventas Fibra.',
                'type' => 'f',
                'totalSales' => $data['total_sales_f'],
                'dateRange' => date('d-m-Y', strtotime($data['dateSaleB'])).'/'.date('d-m-Y', strtotime($data['dateSaleE'])),
                'salesDetail' => $data['salesDetailF']
            ]
        )
        @endif

        @if($data['detailCash'] || $data['detailCashFSeller'] || $data['detailCashInstFSeller'])
  <div aria-hidden="true" aria-labelledby="myModalLabel" class="modal fade" id="detail-modal" role="dialog" style="display: none;" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <button aria-hidden="true" class="close" data-dismiss="modal" type="button">
            ×
          </button>
          <h4 class="modal-title">
            Detalle de ventas
          </h4>
        </div>
        <div class="modal-body">
          <div class="table-responsive">
            <table class="table table-striped">
              <thead>
                <tr>
                  <th>
                    Cliente
                  </th>
                  <th>
                    MSISDN
                  </th>
                  <th>
                    Paquete
                  </th>
                  <th>
                    Monto
                  </th>
                  <th>
                    Tipo
                  </th>
                  <th>
                    Fecha
                  </th>
                </tr>
              </thead>
              <tbody>
                @if($data['detailCash'])
                  @foreach($data['detailCash'] as $sale)
                <tr>
                  <td>
                    {{$sale->name}} {{$sale->last_name}}
                  </td>
                  <td>
                    {{$sale->msisdn}}
                  </td>
                  <td>
                    {{$sale->pack}}
                  </td>
                  <td>
                    {{$sale->amount}}
                  </td>
                  <td>
                    {{!empty($typeDev[$sale->sale_type]) ? $typeDev[$sale->sale_type] : 'Otro'}}
                  </td>
                  <td>
                    {{getFormatDate($sale->date_reg)}}
                  </td>
                </tr>
                @endforeach
                 @endif

                 @if($data['detailCashFSeller'])
                  @foreach($data['detailCashFSeller'] as $sale2)
                <tr>
                  <td>
                    {{$sale2->name}} {{$sale2->last_name}}
                  </td>
                  <td>
                    {{$sale2->msisdn}}
                  </td>
                  <td>
                    {{$sale2->pack}}
                  </td>
                  <td>
                    {{$sale2->amount}}
                  </td>
                  <td>
                    {{!empty($typeDev[$sale2->sale_type]) ? $typeDev[$sale2->sale_type] : 'Otro'}}
                  </td>
                  <td>
                    {{getFormatDate($sale2->date_reg)}}
                  </td>
                </tr>
                @endforeach
                 @endif

                 @if($data['detailCashInstFSeller'])
                  @foreach($data['detailCashInstFSeller'] as $sale3)
                <tr>
                  <td>
                    {{$sale3->name}} {{$sale3->last_name}}
                  </td>
                  <td>
                    {{$sale3->msisdn}}
                  </td>
                  <td>
                    {{$sale3->pack}}
                  </td>
                  <td>
                    {{$sale3->amount}}(cuota {{$sale3->n_quote}})
                  </td>
                  <td>
                    {{!empty($typeDev[$sale3->sale_type]) ? $typeDev[$sale3->sale_type] : 'Otro'}}
                  </td>
                  <td>
                    {{getFormatDate($sale3->date_reg)}}
                  </td>
                </tr>
                @endforeach
                 @endif
              </tbody>
            </table>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-default waves-effect" data-dismiss="modal" type="button">
            Close
          </button>
        </div>
      </div>
    </div>
  </div>
  @endif

  @if(count($data['datesInstalations']) || count($data['asigneInstalations']) || count($data['agendaInstalations']))
  <div aria-hidden="true" aria-labelledby="myModalLabel" class="modal fade" id="detail-install-modal" role="dialog" style="display: none;" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <button aria-hidden="true" class="close" data-dismiss="modal" type="button">
            ×
          </button>
          <h4 class="modal-title">
            Detalle de la cita de instalación
          </h4>
        </div>
        <div class="modal-body" id="detail-install-content">
        </div>
        <div class="modal-footer">
          <button class="btn btn-default waves-effect" data-dismiss="modal" id="close-modal-detail-inst" type="button">
            Cerrar
          </button>
          <button class="btn btn-success waves-effect" data-vtype="" id="go-to-install" type="button">
            ***
          </button>
        </div>
      </div>
    </div>
  </div>
  @endif

  @if(count($data['instalationsNotPaid']))
  <div aria-hidden="true" aria-labelledby="myModalLabel" class="modal fade" id="detail-pending-pay-modal" role="dialog" style="display: none;" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <button aria-hidden="true" class="close" data-dismiss="modal" type="button">
            ×
          </button>
          <h4 class="modal-title">
            Detalle de la instalación
          </h4>
        </div>
        <div class="modal-body" id="detail-pending-paid--content">
        </div>
        <div class="modal-footer">
          <button class="btn btn-default waves-effect" data-dismiss="modal" id="close-modal-paid-detail-inst" type="button">
            Cerrar
          </button>
          <button class="btn btn-success waves-effect" id="mark-as-paid" type="button">
            Marcar como pagada
          </button>
        </div>
      </div>
    </div>
  </div>
  @endif
</div>
<!-- Button trigger modal -->
<!--<button type="button" class="btn btn-primary btn-lg" data-toggle="modal" data-target="#modalDeleteCita">
      Launch demo modal
    </button>-->
<!-- Modal eliminar cita-->
<div aria-labelledby="myModalLabel" class="modal fade" id="modalDeleteCita" role="dialog" tabindex="-1">
  <div class="modal-dialog" role="document" style="width: 400px;">
    <div class="modal-content">
      <div class="modal-header" style="text-align: center;">
        <button aria-label="Close" class="close close_modal_delete_app" data-dismiss="modal" type="button">
          <span aria-hidden="true">
            ×
          </span>
        </button>
        <strong>
          <span>
            <i class="glyphicon glyphicon-exclamation-sign" style="font-size: 50px; color: #f68e6b;">
            </i>
          </span>
          <h4 class="modal-title" id="myModalLabel">
            ¿Seguro que desea eliminar la cita?
          </h4>
          <h5>
            Esta acción no tiene reverso.
          </h5>
        </strong>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label class="col-sm-12 control-label" for="inputPassword3" style="text-align: center;">
            Razon de la eliminacion?
          </label>
          <div class="col-sm-12">
            <textarea class="form-control" id="reason_delete" name="reason_delete" rows="3">
            </textarea>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-default close_modal_delete_app" data-dismiss="modal" type="button">
          Cancelar
        </button>
        <button class="btn btn-danger" id="delete-appointment" style="background-color: #e64942;" type="button">
          Eliminar
        </button>
      </div>
    </div>
  </div>
</div>
@if(hasPermit('FIB-SMC'))
<div aria-labelledby="myModalLabel" class="modal fade" id="modalCancelCita" role="dialog" tabindex="-1">
  <div class="modal-dialog" role="document" style="width: 450px;">
    <div class="modal-content">
      <div class="modal-header" style="text-align: center;">
        <button aria-label="Close" class="close close_modal_cancel_app" data-dismiss="modal" type="button">
          <span aria-hidden="true">
            ×
          </span>
        </button>
        <strong>
          <span>
            <i class="glyphicon glyphicon-exclamation-sign" style="font-size: 50px; color: #f68e6b;">
            </i>
          </span>
          <h4 class="modal-title" id="myModalLabel">
            ¿Seguro deseas cancelar la cita?
          </h4>
          <h5>
            Esta acción será procesada por mesa de control para reagendar o descartar la cita.
          </h5>
        </strong>
      </div>
      <div class="modal-body" id="blockTipification">

      </div>
      <div class="modal-footer">
        <button class="btn btn-default close_modal_cancel_app" data-dismiss="modal" type="button">
          Cancelar
        </button>
        <button class="btn btn-danger" id="cancel_cita" style="background-color: #e64942;" type="button">
          Enviar
        </button>
      </div>
    </div>
  </div>
</div>

@endif

@if(hasPermit('FIB-VSP'))
  @if(count($data['datesSupports']) || count($data['asigneSupports']) || count($data['agendaSupports']))
  <div aria-hidden="true" aria-labelledby="myModalLabel" class="modal fade" id="detail-support-modal" role="dialog" style="display: none;" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <button aria-hidden="true" class="close" data-dismiss="modal" type="button">
            ×
          </button>
          <h4 class="modal-title">
            Detalle de la cita de soporte
          </h4>
        </div>
        <div class="modal-body" id="detail-support-content">
        </div>
        <div class="modal-footer">
          <button class="btn btn-default waves-effect" data-dismiss="modal" id="close-modal-detail-supp" type="button">
            Cerrar
          </button>
          <button class="btn btn-success waves-effect" data-vtype="" id="go-to-support" type="button">
            ***
          </button>
        </div>
      </div>
    </div>
  </div>
  @endif
@endif

@stop

@if(session('org_type') == 'R' || hasPermit('MAP-SEL'))
<script type="text/javascript">
  function initPlaces(){
            var input = document.getElementById('address'),
                autocomplete = new google.maps.places.Autocomplete(input),
                geocoder = new google.maps.Geocoder,
                lat = 19.39068,
                lng = -99.2836963;

            center = new google.maps.LatLng(lat, lng),
            map = new google.maps.Map(document.getElementById('map-content'), {
                            center,
                            zoom: 5
                        });

            marker = new google.maps.Marker({
                        map: map,
                        draggable: true,
                        animation: google.maps.Animation.DROP,
                        position: {lat: lat, lng: lng}
                    });

            map.addListener('click', function(e) {
                marker.setPosition(e.latLng);
                map.panTo(e.latLng);

                lat = marker.getPosition().lat();
                lng = marker.getPosition().lng();

                geocodeLatLng(lat, lng, geocoder, map, marker, true);
            });

            marker.addListener('dragend', function (event){
                lat = marker.getPosition().lat();
                lng = marker.getPosition().lng();

                geocodeLatLng(lat, lng, geocoder, map, marker, true);
            });

            google.maps.event.addListener(autocomplete, 'place_changed', function() {
                var place = autocomplete.getPlace();

                if(place.geometry){
                    lat = place.geometry.location.lat();
                    lng = place.geometry.location.lng();

                    geocodeLatLng(lat, lng, geocoder, map, marker, true);
                }
            });

            $('#address').on('keypress', function(e){
                if(e.which == 10 || e.which == 13){
                    var firstA = $('.pac-container').first().find('.pac-item-query').first().text();
                    firstA += ' ' + $('.pac-container').first().find('.pac-item-query').first().next().text();

                    $('#address').val(firstA);

                    request = {
                        query: firstA,
                        fields: ['geometry']
                    }

                    placeService = new google.maps.places.PlacesService(map);

                    placeService.findPlaceFromQuery(request, function(results, status){
                        if (status == google.maps.places.PlacesServiceStatus.OK){
                            if(results && results[0]){
                                lat = results[0].geometry.location.lat();
                                lng = results[0].geometry.location.lng();

                                geocodeLatLng(lat, lng, geocoder, map, marker, true);
                            }
                        }
                    });
                    e.preventDefault();
                }
            });

            $('.lon-map').on('blur', function(e){
                if($('.lon-map').val().trim() != '' && $('.lat-map').val().trim() != '' && !isNaN(parseFloat($('.lat-map').val())) && !isNaN(parseFloat($('.lon-map').val()))){
                    geocodeLatLng(parseFloat($('.lat-map').val()), parseFloat($('.lon-map').val()), geocoder, map, marker, true);
                }
            });

            $('.lat-map').on('blur', function(e){
                if($('.lon-map').val().trim() != '' && $('.lat-map').val().trim() != '' && !isNaN(parseFloat($('.lat-map').val())) && !isNaN(parseFloat($('.lon-map').val()))){
                    geocodeLatLng(parseFloat($('.lat-map').val()), parseFloat($('.lon-map').val()), geocoder, map, marker, true);
                }
            });
        }

        function centerMap(map, marker, lat, lng){
            if(lat != '' && lng != '' && !isNaN(parseFloat(lat)) && !isNaN(parseFloat(lng))){
                var latlng = {lat: parseFloat(lat), lng: parseFloat(lng)};

                map.setCenter(latlng);
                marker.setPosition(latlng);
                map.setZoom(16);

                return true;
            }
            return false;
        }

        function geocodeLatLng(lat,lng, geocoder, map, marker, ban) {
            var latlng = {lat:lat, lng: lng};

            geocoder.geocode({'location': latlng}, function(results, status) {
                $('#preloader').hide();
                if (status === 'OK') {
                    if (results[0]){
                        if(ban){
                            $('#address').val(results[0].formatted_address);
                            $('.lon-map').val(lng),
                            $('.lat-map').val(lat);
                        }
                        centerMap(map, marker, lat, lng);
                    }else{
                        showMessageAjax('alert-danger','No se encontro la dirección del punto marcado.');
                    }
                }else{
                    showMessageAjax('alert-danger','Ocurrio un error cargando la dirección, por favor intente mas tarde.');
                    console.log(status);
                }
            });
        }
</script>
@endif

@section('scriptJS')
<script src="{{ asset('plugins/bower_components/waypoints/lib/jquery.waypoints.js') }}">
</script>
{{-- <script src="{{ asset('plugins/bower_components/counterup/jquery.counterup.min.js') }}">
</script> --}}
<script src="{{ asset('plugins/bower_components/moment/moment.js') }}">
</script>
<script src="{{ asset('plugins/bower_components/bootstrap-daterangepicker/daterangepicker.js') }}">
</script>
<script src="{{ asset('plugins/bower_components/bootstrap-datepicker/bootstrap-datepicker.min.js') }}">
</script>
<script src="{{ asset('plugins/bower_components/clockpicker/dist/jquery-clockpicker.min.js') }}">
</script>
<script src="{{ asset('js/sweetalert.min.js') }}">
</script>
<script src="{{ asset('js/selectize.js')}}">
</script>
<script src="{{ asset('js/validator.js') }}">
</script>
<script src="{{ asset('js/detect.min.js') }}">
</script>
@if(session('org_type') == 'R' || hasPermit('MAP-SEL'))
<script src="{{ asset('https://maps.googleapis.com/maps/api/js?key='.env('GOOGLE_KEY').'&libraries=places&callback=initPlaces') }}">
</script>
@elseif(hasPermit('SEL-INF'))
<script src="{{ asset('https://maps.googleapis.com/maps/api/js?key='.env('GOOGLE_KEY').'&libraries=places') }}">
</script>
@endif
<script type="text/javascript">
  @if(hasPermit('FIB-SMC'))
  $('#modalCancelCita').on('shown.bs.modal', function(event) {
    let citaId = $('#btnCancelCita').data('cita');
    let typeBtn = $('#btnCancelCita').data('type');
    $('#detail-install-modal').modal('hide');
    $('.loading-ajax').show();
    doPostAjax(
      '{{ route('sellerFiber.getTypification') }}',
      function(res){
        $('.loading-ajax').hide();
        $('#blockTipification').html(res.html);
        $('body').addClass('modal-open');
      },
      {
        cita: citaId,
        type: typeBtn
      },
      $('meta[name="csrf-token"]').attr('content')
    );
  });

  $('#cancel_cita').on('click', function(e){
    var typificationField = document.getElementById('typification');
    if(typeof typificationField !== 'undefined'){
      if($("#typification").val()==''){
        $('#error-typification').text("Debes seleccionar una tipificación para continuar");
      }else{
        if($("#typification").val()=='13'){
          if($("#msgTypi").val()==''){
            $('#error-msgTypi').text("Debes ingresar el detalle de la causa de cancelación para continuar");
            return 0;
          }
        }
        doPostAjax(
          '{{ route('sellerFiber.cancelInstalation') }}',
          function(res){
            $('#modalCancelCita').modal('hide');
            swal({
                  title: res.title,
                  text: res.msg,
                  icon: res.icon,
                  button: {
                    text: "OK"
                  }
            }).then((value) => {
              window.location.href = '{{route('dashboard')}}'+'/';
            });
          },
          {
            cita: $('#typification').data('cita'),
            typification: $('#typification').val(),
            msgTypi: $('#msgTypi').val(),
          },
          $('meta[name="csrf-token"]').attr('content')
        );
      }
    }else{
      setTimeout(() => {
        $('body').addClass('modal-open');
      }, 1500);
      $('#returnStep').trigger('click');
    }
  });

  $('.close_modal_cancel_app').on('click', function(e){
    // $('#modalCancelCita').modal('hide');
    // $('.modal-backdrop').remove();
    setTimeout(() => {
        $('body').addClass('modal-open');
      }, 1500);
    $('#returnStep').trigger('click');
  });

  @endif
  @if($data['redStatusAlert'])
    swal({
        title: "¡Atención!",
        text: "Se te ha devuelto inventario de tus vendedores por estatus de color rojo",
        icon: "warning",
        button: {text: "OK"},
    })
    .then(() => {
        window.location.href = '{{ route('inventory.listDNOOR') }}';
    });
  @endif

  @if($data['preAssinedRejectAlert'])
    swal({
        title: "¡Atención!",
        text: "Algunos de tus vendedores han rechazado el inventario pre-asignado",
        icon: "warning",
        button: {text: "OK"},
    })
    .then(() => {
        window.location.href = '{{ route('inventory.preassignedStatus') }}';
    });
  @endif

  @if($data['preAssinedAlert'])
      swal({
          title: "¡Atención!",
          text: "Tienes inventario pre-asignado que debes aceptar o rechazar",
          icon: "warning",
          button: {text: "OK"},
      })
      .then(() => {
          window.location.href = '{{ route('inventory.preassigned') }}';
      });
  @endif

  @if(hasPermit('FIB-VAC') && count($data['agendaInstalations']))
      swal({
          title: "¡Atención!",
          text: "Tienes citas de instalación de fibra pendiente por asignar a instaladores",
          icon: "warning",
          button: {text: "OK"},
      })
      .then(() => {
          var posicion = $('#agendaInstalations').offset().top - 50;
              $("html, body").animate({
                  scrollTop: posicion
              }, 1000);
      });
  @endif


  callServ = function(lat, lon, btn){
      if(btn == 'map'){
          if(map && marker){
              centerMap(map, marker, lat, lon);
          }
      }

      $.ajax({
          type: 'POST',
          url: "{{route('dashboard.serviciability')}}",
          data: { _token: "{{ csrf_token() }}",lat:lat, lon:lon},
          dataType: 'json',
          success: function(serv){
              $('.loading-ajax').hide();

              if(serv.error && serv.message == 'TOKEN_EXPIRED'){
                  showMessageAjax('alert-danger','Su session a expirado, por favor actualice la página.');
              }else{
                  $('.serviciability-'+btn).text(serv.message);
                  $('#serv-c-'+btn).removeClass('hidden');
              }
          },
          error: function(){
              $('.loading-ajax').hide();
              showMessageAjax('alert-danger','No se pudo consultar la servicialidad.');
          }
      });
  }

  getReportSales = function(type = ''){
      if($('#daterange'+type).length && $('#daterange'+type).val().trim() != ''){
          $('.loading-ajax').show();
          $.ajax({
              type: 'POST',
              url: "{{route('getTotalSalesByDate')}}",
              data: { _token: "{{ csrf_token() }}", user:$('#list-seller').val(), date: $('#daterange'+type).val().trim(), type: type},
              dataType: "json",
              success: function(serv){
                  if(serv.error && serv.message == 'TOKEN_EXPIRED'){
                      showMessageAjax('alert-danger','Su session a expirado, por favor actualice la página.');
                  }else{
                      if(serv.error)
                          showMessageAjax('alert-danger', serv.msg);
                      else{
                          $('#totalSales'+serv.type).text(serv.data.total_sales);
                          $('#totalSalesDates'+serv.type).text(serv.dates);
                          $('#table-detail-sales'+serv.type).html(serv.detail);
                      }
                  }
                  $('.loading-ajax').hide();
              },
              error: function(){
                  showMessageAjax('alert-danger', 'No se pudo realizar la consulta.');
                  $('.loading-ajax').hide();
              }
          });
      }
  }

  function registerUserConect(){
    var register=false;
    if(sessionStorage.getItem('lastUserLogin') == "undefined" || sessionStorage.getItem('lastUserLogin') == "null"){
      sessionStorage.setItem('lastUserLogin', "{{session('user')}}");
      register=true;

    }else{
      if(sessionStorage.getItem('lastUserLogin')!="{{session('user')}}"){
        sessionStorage.setItem('lastUserLogin', "{{session('user')}}");
        register=true;
      }
    }
    if(register){
      /**
       * [ua https://github.com/darcyclarke/Detect.js]
       * @type {[type]}
       */
      var ua = detect.parse(navigator.userAgent);
      var type_device="";
      var device="";
      var os="";
      var browser="";

     // console.log('01 ',ua.browser.family); // "Mobile Safari"
     // console.log('02 ',ua.browser.name); // "Mobile Safari 4.0.5"
     // console.log('03 ',ua.browser.version); // "4.0.5"
      ////console.log('04 ',ua.browser.major); // 4
      ////console.log('05 ',ua.browser.minor); // 0
      ////console.log('06 ',ua.browser.patch); // 5
      browser = ">family: "+ua.browser.family+" >name: "+ua.browser.name+" >version: "+ua.browser.version;

     // console.log('13 ',ua.device.type); // "Mobile"
     //console.log('14 ',ua.device.manufacturer); // "Apple"
    //  console.log('07 ',ua.device.family); // "iPhone"
    //  console.log('08 ',ua.device.name); // "iPhone"
    //  console.log('09 ',ua.device.version); // ""
      ////console.log('10 ',ua.device.major); // null
      ////console.log('11 ',ua.device.minor); // null
      ////console.log('12 ',ua.device.patch); // null
      type_device = ua.device.type;
      device = ">manufacturer: "+ua.device.manufacturer+" >family: "+ua.device.family+" >version: "+ua.device.version;

    //  console.log('15 ',ua.os.family); // "iOS"
    //  console.log('16 ',ua.os.name); // "iOS 4"
    //  console.log('17 ',ua.os.version); // "4"
      ////console.log('18 ',ua.os.major); // 4
      ////console.log('19 ',ua.os.minor); // 0
      ////console.log('20 ',ua.os.patch); // null
      os = ">family: "+ua.os.family+" >name: "+ua.os.name+" >version: "+ua.os.version;

       doPostAjax(
          '{{ route('dashboard.regconex') }}',
          function(res){
            $('.loading-ajax').fadeOut();

            showMessageAjax('alert-success',res.msg);
          },
          {
            browser: browser,
            type_device: type_device,
            device: device,
            os: os
        },
          $('meta[name="csrf-token"]').attr('content')
        );
      /**
       * other option
       * https://github.com/hgoebl/mobile-detect.js
       * https://hgoebl.github.io/mobile-detect.js/
       * [document https://hgoebl.github.io/mobile-detect.js/doc/MobileDetect.html]
       * @type {MobileDetect}
       */
    }else{
      console.log('Ya se registro ',sessionStorage.getItem('lastUserLogin'));
    }
  }

  $(function () {
            // $(".counter").counterUp({
            //     delay: 100,
            //     time: 1200
            // });

    let configDatePicker = {
        autoUpdateInput: false,
        alwaysShowCalendars: true,
        parentEl: "calendar-content",
        buttonClasses: ['btn', 'btn-sm'],
        applyClass: 'btn-danger',
        cancelClass: 'btn-inverse',
        maxDate: new Date(),
        locale: {
            format: "YYYY/MM/DD",
            separator: "/",
            applyLabel: "Aceptar",
            cancelLabel: 'Cancelar',
            toLabel: "A",
        }
    };

    $('.datepicker-input').daterangepicker(configDatePicker);

    $('.datepicker-input').on('apply.daterangepicker', function(ev, picker) {
        let type = $(ev.currentTarget).data('type');

        $(this).val(picker.startDate.format('YYYY-MM-DD') + ' / ' + picker.endDate.format('YYYY-MM-DD'));
        $('#calendar-content').addClass('hidden');
        getReportSales(type);
    });

    $('.datepicker-input').on('cancel.daterangepicker', function(ev, picker) {
        $(this).val('');
    });

    $('.show-calendar').on('click', function(e){
        let type = $(e.currentTarget).data('type');

        $('#calendar-content-'+type).removeClass('hidden');
        $('#daterange'+type).trigger('click')
    });

    function handleError(err) {
        $('.loading-ajax').hide();
        console.warn('ERROR(' + err.code + '): ' + err.message);
    };

    function getServicability(lat, lon, btn){
        $('.loading-ajax').show();

        if(lon != '', lat != ''){
            callServ(lat, lon, btn);
        }else{
            if (navigator.geolocation){
                navigator.geolocation.getCurrentPosition(function (funcExito){
                    var lon = funcExito.coords.longitude;
                    var lat = funcExito.coords.latitude;
                    $('.lat-'+btn).val(lat);
                    $('.lon-'+btn).val(lon);
                    callServ(lat, lon, btn);
                },handleError, {maximumAge:0});
            }else{
                $('.loading-ajax').hide();

                showMessageAjax('alert-danger', 'No se puede obtener la Geolocalización.');
            }
        }
    }

    $('.btnGeo').on('click', function(event){
        var btn = $(event.currentTarget).data('btn'),
            lon = $('.lon-'+btn).val().trim(),
            lat = $('.lat-'+btn).val().trim();

        getServicability(lat, lon, btn)
    });

    $('#list-seller').on('change', function(e){
        var data = $('#list-seller').val().trim();
        if(data){
            $('.loading-ajax').show();
            location.href = "{{route('dashboard')}}/"+data;
        }
    });

    @if(hasPermit('DCI-DSE'))
      $('#exportCsv').on('click', function(e){
          $.ajax({
              url: "{{ route('dashboard.downloadInv') }}",
              type: 'POST',
              data:{
                  user: $('#list-seller').val(),
                  _token: "{{ csrf_token() }}"
              },
              dataType: 'text',
              success: function(result){
                  var uri = 'data:application/csv;charset=UTF-8,' + encodeURIComponent(result);
                  var download = document.getElementById('downloadfile');
                  download.setAttribute('href', uri);
                  download.setAttribute('download','inv_asig_'+'{{ date('Ymd') }}'+'.csv');
                  download.click();
              }
          });
      });
    @endif

    @if(session('user_type') != 'vendor')
      $('#list-seller').selectize({
          valueField: 'email',
          searchField: 'name',
          labelField: 'name',
          options: [
              {email: '{{ session('user') }}', name: 'Yo', type: '{{ session('profile') }}'},
              @if($data['showSellers'] && !empty($data['sellers']))
              @foreach($data['sellers'] as $seller)
                  {email: '{{$seller->email}}', name: '{{$seller->name_profile.': '.$seller->name}} {{$seller->last_name}}', type: '{{ str_replace(' ', '', $seller->name_profile) }}'},
              @endforeach
              @endif
          ],
          render: {
              option: function(item, escape) {
                  {{-- class="item-seller '+escape(item.type)+'" --}}
                  return '<p>'+escape(item.name)+'</p>';
              }
          }
      });
    @endif

            {{-- @if(hasPermit('SEL-FIB'))
            $('#detail-pending-pay-modal').on('hide.bs.modal', function(event) {
                $('#detail-pending-paid--content').html('');
            });

            $('#detail-pending-pay-modal').on('shown.bs.modal', function(event) {
                let idInstall = $(event.relatedTarget).attr('data-id');

                $('.loading-ajax').show();

                doPostAjax(
                    '{{ route('sellerFiber.detailPendingPaidInsModal') }}',
                    function(res){
                        $('.loading-ajax').fadeOut();

                        if(!res.error){
                            $('#detail-pending-paid--content').html(res.html);
                        }else{
                            if(res.message == 'TOKEN_EXPIRED'){
                                showMessageAjax('alert-danger','Su session a expirado, por favor actualice la página.');
                            }else{
                                showMessageAjax('alert-danger', res.message);
                                $('#close-modal-paid-detail-inst').trigger('click');
                            }
                        }
                    },
                    {id: idInstall},
                    $('meta[name="csrf-token"]').attr('content')
                );
            });

            $('#mark-as-paid').on('click', function(){
                swal({
                    title: "¿Seguro que desea marcar esta instalación como pagada?",
                    text: "Esta acción no tiene reverso.",
                    icon: "warning",
                    dangerMode: true,
                    buttons: {
                        cancel: {
                            text: 'Cancelar',
                            visible: true,
                            value: 'cancelar'
                        },
                        confirm: {
                            text: 'Aceptar',
                            visible: true,
                            value: 'ok'
                        }
                    }
                })
                .then((value) => {
                    if(value == 'ok'){
                        $('.loading-ajax').fadeIn();

                        let idInstall = $('#form-paid-install').attr('data-id');

                        doPostAjax(
                            '{{ route('sellerFiber.markAsPaidInstall') }}',
                            function(res){
                                $('#close-modal-paid-detail-inst').trigger('click');
                                $('.loading-ajax').fadeOut();

                                if(!res.error){
                                    $('#row-date-paid-'+res.id).remove();
                                    showMessageAjax('alert-success', res.message);
                                }else{
                                    $('#close-modal-paid-detail-inst').trigger('click');
                                    if(res.message == 'TOKEN_EXPIRED'){
                                        showMessageAjax('alert-danger','Su session a expirado, por favor actualice la página.');
                                    }else{
                                        showMessageAjax('alert-danger', res.message);
                                    }
                                }
                            },
                            {
                                id: idInstall
                            },
                            $('meta[name="csrf-token"]').attr('content')
                        );
                    }
                });
            });
            @endif --}}

    @if(hasPermit('SEL-INF'))
      let addEventsToModal = function(){
        let $installer = $('#installer').selectize({
            valueField: 'email',
            searchField: ['name', 'last_name', 'email', 'info'],
            labelField: 'info',
            render: {
                option: function(item, escape) {
                return '<p>'+escape(item.info)+'</p>';
                }
            },
            load: function(query, callback) {
            if (!query.length) return callback();

            doPostAjax(
                '{{ route('seller.findInstaller') }}',
                function(res){
                    if(!res.error){
                        callback(res);
                    }else{
                        callback();
                    }
                },
                {
                    search: query,
                    pack_id: $("#pack_id").val(),
                    install_id: $("#install_id").val()
                },
                $('meta[name="csrf-token"]').attr('content')
            );
            }
        });

        //Cambio de fecha cita instalación
        $('#calendar').datepicker({
            language: 'es',
            todayHighlight: true,
            format: 'dd-mm-yyyy',
            startDate: new Date()
            //daysOfWeekDisabled:[0,6]
        }).on('changeDate', function (selected) {
            var date = selected.date,
                month = ((date.getMonth() + 1) < 10) ? ('0' + (date.getMonth() + 1 )) : (date.getMonth() + 1 ),
                day = (date.getDate() < 10) ? ('0' + date.getDate()) : date.getDate(),
                fecha = date ? date.getFullYear() + '-' + month + '-' + day : '';

            $('#dateCalendar').val(fecha);
            $('#error-calendar').text('');
        });

        {{-- $('.clockpicker').clockpicker({
            donetext: 'Seleccionar',
            default: 'now',
            placement: 'bottom',
            autoclose: true
        }); --}}

        {{-- $('#hour').selectize(); --}}

        //Eliminando eventos previamente asignados
        {{--$('#edit-date').unbind('click');--}}
        @if (hasPermit('FIB-AIC'))
          $('#change-install').unbind('click');
        @endif
        $('#delete-date').unbind('click');
        $('#form-edit-install').validator().unbind('submit');

        $('#delete-date').on('click', function(){
          swal({
              title: "¿Seguro que desea eliminar la cita?",
              text: "Esta acción no tiene reverso.",
              icon: "warning",
              dangerMode: true,
              buttons: {
                  cancel: {
                      text: 'Cancelar',
                      visible: true,
                      value: 'cancelar'
                  },
                  confirm: {
                      text: 'Eliminar',
                      visible: true,
                      value: 'ok'
                  }
              }
          })
          .then((value) => {
              if(value == 'ok'){
                  $('.loading-ajax').show();
                  let idInstall = $('#form-edit-install').attr('data-id');
                  doPostAjax(
                      '{{ route('sellerFiber.deleteInstall') }}',
                      function(res){
                          $('#close-modal-detail-inst').trigger('click');
                          $('.loading-ajax').fadeOut();

                          if(!res.error){
                              $('#row-date-'+res.id).remove();
                              showMessageAjax('alert-success', res.message);
                          }else{
                              $('#close-modal-detail-inst').trigger('click');
                              if(res.message == 'TOKEN_EXPIRED'){
                                  showMessageAjax('alert-danger','Su session a expirado, por favor actualice la página.');
                              }else{
                                  showMessageAjax('alert-danger', res.message);
                              }
                          }
                      },
                      {
                          id: idInstall
                      },
                      $('meta[name="csrf-token"]').attr('content')
                  );
              }
          });
        });

        $('.close_modal_delete_app').on('click', function(){
            $('#reason_delete').val("");
        });

        $('#delete-appointment').on('click', function(){
          $('.loading-ajax').show();
          let idInstall = $('#form-edit-install').attr('data-id');
          let reason_delete = $('#reason_delete').val();

          $.ajax({
              headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              },
              url: '{{ route('sellerFiber.deleteInstall') }}',
              data: {
                  id: idInstall,
                  reason_delete: reason_delete,
              },
              type: 'post',
              dataType: "json",
              success: function (res) {
                  $('.loading-ajax').fadeOut();
                  $('.close_modal_delete_app').click();
                  $('#close-modal-detail-inst').click();

                  if ( res.error) {

                      swal({
                          title: "Error!",
                          text: res.message,
                          icon: "error",
                          button: {text: "OK"},
                      });

                  }
                  else {
                      $('#row-date-'+idInstall).remove();

                      swal({
                          title: "Bien!",
                          text: res.message,
                          icon: "success",
                          button: {text: "OK"},
                      });
                  }
              },
              error: function (res) {
                  $('.loading-ajax').fadeOut();
                  $('.close_modal_delete_app').click();
                  $('#close-modal-detail-inst').click();

                  swal({
                      title: "Error!",
                      text: 'Ocurrio un error al intentar eliminar la cita.',
                      icon: "error",
                      button: {text: "OK"},
                  });
              }
          });

                    /*doPostAjax(
                        '{{ route('sellerFiber.deleteInstall') }}',
                        function(res){
                            $('#close-modal-detail-inst').trigger('click');
                            $('.loading-ajax').fadeOut();

                            if(!res.error){
                                $('#row-date-'+res.id).remove();
                                showMessageAjax('alert-success', res.message);
                            }else{
                                $('#close-modal-detail-inst').trigger('click');
                                if(res.message == 'TOKEN_EXPIRED'){
                                    showMessageAjax('alert-danger','Su session a expirado, por favor actualice la página.');
                                }else{
                                    showMessageAjax('alert-danger', res.message);
                                }
                            }
                        },
                        {
                            id: idInstall,
                            reason_delete: reason_delete,
                        },
                        $('meta[name="csrf-token"]').attr('content')
                    );*/
        });
        @if (hasPermit('FIB-AIC'))
          //Cambio de instalador hecho por un jefe
          $('#change-install').on('click', function(){
             // $('#dateCalendar').val('');
              $('#cal-content').attr('hidden', true);
              $('#insta-content').attr('hidden', null);
          });
        @endif
                //Cambio de fecha de la cita
                {{--$('#edit-date').on('click', function(e){
                    $installer[0].selectize.clearOptions();
                    $('#insta-content').attr('hidden', true);
                    $('#cal-content').attr('hidden', null);
                });--}}

        $('#form-edit-install').validator().on('submit', function(e){
          if(!e.isDefaultPrevented()){
              e.preventDefault();
              let idInstall = $('#form-edit-install').attr('data-id');
              if($('#dateCalendar').val() != '' || !$('#cal-content').is(':visible')){
                  $('.loading-ajax').show();

                  doPostAjax(
                      '{{ route('sellerFiber.saveDetailInsModal') }}',
                      function(res){
                          $('.loading-ajax').fadeOut();

                          if(!res.error){
                              if(res.date){
                                  $('#form-edit-install').attr('data-id', res.new_id);
                                  $('#row-date-'+res.prev_id+' td:eq(4)').find('button').attr('data-id', res.new_id);
                                  $('#row-date-'+res.prev_id+' td:eq(2)').text(res.date);
                                  $('#row-date-'+res.prev_id+' td:eq(3)').text(res.schedule);
                                  $('#row-date-'+res.prev_id).attr('id', 'row-date-'+res.new_id);
                                  $('#date-inst-label').text(res.date+' / '+res.schedule);
                                  $('#cal-content').attr('hidden', true);
                              }else{
                                  if(res.delete){
                                      $('#row-date-'+res.prev_id).remove();
                                  }
                                  $('#close-modal-detail-inst').trigger('click');
                                  showMessageAjax('alert-success', res.message);
                              }
                          }else{
                              $('#close-modal-detail-inst').trigger('click');
                              if(res.message == 'TOKEN_EXPIRED'){
                                  showMessageAjax('alert-danger','Su session a expirado, por favor actualice la página.');
                              }else{
                                  showMessageAjax('alert-danger', res.message);
                              }
                          }
                      },
                      {
                          id: idInstall,
                          date: $('#dateCalendar').val().trim(),
                          hour: $('#hour').val().trim(),
                          installer: $('#installer').val().trim()
                      },
                      $('meta[name="csrf-token"]').attr('content')
                  );
              }else{
                  $('#error-calendar').text('Debe seleccionar una fecha.');
              }
          }
        });
      }

      $('#detail-install-modal').on('hide.bs.modal', function(event) {
          $('#detail-install-content').html('');
      });

      $('#detail-install-modal').on('shown.bs.modal', function(event) {
          let idInstall = $(event.relatedTarget).attr('data-id');
          let typeInstall = $(event.relatedTarget).attr('data-type');

          $('.loading-ajax').show();

          doPostAjax(
              '{{ route('sellerFiber.detailInsModal') }}',
              function(res){
                  $('.loading-ajax').fadeOut();

                  if(!res.error){
                      $('#detail-install-content').html(res.html);
                      addEventsToModal();
                      $('#calendar').datepicker('setDate', new Date(res.date_ins));
                  }else{
                      if(res.message == 'TOKEN_EXPIRED'){
                          showMessageAjax('alert-danger','Su session a expirado, por favor actualice la página.');
                      }else{
                          showMessageAjax('alert-danger', res.message);
                          $('#close-modal-detail-inst').trigger('click');
                      }
                  }
              },
              {
                id: idInstall,
                type: typeInstall
              },
              $('meta[name="csrf-token"]').attr('content')
          );
      });

      //Carga mas citas
      if($('#load-more-dates').is(':visible')){
          $('#load-more-dates').on('click', function(){
              $('.loading-ajax').show();

              let date = $(this).data('date')
                  dateb = $(this).data('dateb');

              doPostAjax(
                  '{{ route('sellerFiber.loadMoredetailInsModal') }}',
                  function(res){
                      $('.loading-ajax').fadeOut();

                      if(!res.error){
                          if(res.showNextInstalations){
                              $('#load-more-dates').data('dateb', res.datesInstalationsB);
                              $('#load-more-dates').data('date', res.showNextInstalations);
                          }else{
                              $('#load-more-dates').attr('hidden', true);
                          }

                          $('#row-detail-content').append(res.html);
                      }else{
                          if(res.message == 'TOKEN_EXPIRED'){
                              showMessageAjax('alert-danger','Su session a expirado, por favor actualice la página.');
                          }else{
                              showMessageAjax('alert-danger', res.message);
                              $('#close-modal-detail-inst').trigger('click');
                          }
                      }
                  },
                  {
                      dateB: dateb,
                      date: date
                  },
                  $('meta[name="csrf-token"]').attr('content')
              );
          });
      }

      function verifyAction(action){
        @if(hasPermit('FIB-AIC'))
            //Que permita asignar instalador a cita
          let wrapper = document.createElement('div');
          let tex = "<p class='text-left'>";
          tex += "<strong> > RESUMEN:</strong></br> ";
          tex += "<strong> Fecha de instalación: </strong>" + $('#date-inst-label').text() + "</br>";
          tex += "<strong> Dirección: </strong> Estado: " + $('#date-state-label').text() +", Ciudad: "+ $('#date-city-label').text() +", Municipio: "+ $('#date-municipality-label').text() +", Colonia: "+ $('#date-colony-label').text() +", Ruta: "+ $('#date-route-label').text() +", Casa: "+ $('#date-house_number-label').text() +", Referencia: "+ $('#date-reference-label').text() +"</br>";
          tex += "<strong> Instalador: </strong>" + $('#installer').text() + "</br>";
          tex += "</p>";
          wrapper.innerHTML = tex;
          let el = wrapper.firstChild;

          swal({
            title: "Confirmas la asignación de cita?",
            content: el,
            dangerMode: true,
            closeOnClickOutside: false,
            icon: "info",
            buttons: {
              cancel: {
                text: "Cancelar",
                value: 'cancel',
                visible: true,
                className: "",
                closeModal: true,
              },
              confirm: {
                text: "Procesar",
                value: 'save',
                visible: true,
                className: "",
                closeModal: true
              },
            },
          }).then((value) => {
            if (value == 'save') {
              $('.loading-ajax').fadeIn();
              doPostAjax(
                '{{ route('sellerFiber.saveDetailInsModal') }}',
                function(res){
                  if($('.loading-ajax').is(':visible')){
                    $('.loading-ajax').fadeOut();
                  }
                    //console.log('2- ',res);
                  if(res.success){

                    if(res.msg.length){
                      swal(res.msg, {
                        icon: res.icon,
                      }).then((value) => {

                        window.location.href = '{{route('dashboard')}}'+'/';
                      });
                    }else{
                      window.location.href = '{{route('dashboard')}}'+'/';
                    }
                  }else{
                    if(res.message == 'TOKEN_EXPIRED'){
                      showMessageAjax('alert-danger','Su session a expirado, por favor actualice la página.');
                    }
                    if(res.msg.length){
                      swal(res.msg, {
                          icon: res.icon,
                      });
                    }
                  }
                },
                {
                  cita: $('#install_id').val(),
                  installer: $('#installer').val(),
                  vtype: $('#go-to-install').data('vtype')
                },
                $('meta[name="csrf-token"]').attr('content')
              );
            }
          });
        @endif
      }

      //Redirige a la instalación o a guardar el instalador
      $('#go-to-install').on('click', function(){

        let type = $(this).data('vtype');
        switch(type){
          case "installer":
            //Instalaciones por realizar.
            $('.loading-ajax').fadeIn();
            let idInstall = $('#form-edit-install').attr('data-id');
            window.location.href = '{{route('sellerFiber.doInstall')}}'+'/'+idInstall
            break;
          case "installerAgenda":
            //Citas por asignar. Guardo instalador
            verifyAction("installerAgenda");
            break;
          case "installerAsigne":
            //Citas asignadas a instaladores. Actualizo instalador
            verifyAction("installerAsigne");
            break;
          default:
            console.log('Opcion '+option+' no definida');
        }
      });
    @endif

    //Registro el dispositivo con el cual hacen uso
    //
    registerUserConect();
  });
</script>
@if(hasPermit('SEL-MOV'))
<script type="text/javascript">
  let validImei = function(res){
    $('.loading-ajax').fadeOut();
    $('#alert-comp').removeClass('alert-danger').removeClass('alert-warning');
    $('#alert-comp').removeClass('text-white').removeClass('text-dark');

    if(!res.error){
      $('#alert-comp').addClass('alert-success');
      if(res.data.volteCapable == 'no'){
        $('#alert-comp').text('Equipo compatible con VozApp');
      }else{
        $('#alert-comp').text('Equipo es compatible con la red VoLTE');
      }
      $('#alert-comp').show();
    }else{
      if(res.message == 'TOKEN_EXPIRED'){
        showMessageAjax('alert-danger','Su session a expirado, por favor actualice la página.');
      }else{
        $('#alert-comp').addClass('alert-danger');
        $('#alert-comp').text('Equipo no compatible');
        $('#alert-comp').show();
      }
    }
  }

  $('#valid-imei').on('click', function(e){
      let imei = $('#imei').val();

      $('#imei-error').text('');

      if(imei != '' && String(imei).length == 15 && !isNaN(parseInt(imei))){
          $('.loading-ajax').show();

          doPostAjax(
              '{{ route('seller.validImei') }}',
              validImei,
              {imei: imei, _token: '{{ csrf_token() }}'}
          );
      }else{
          $('#imei-error').text('Imei no válido');
      }
  });
</script>
@endif

@if(hasPermit('FIB-VSP'))
<script type="text/javascript">

  $('#detail-support-modal').on('hide.bs.modal', function(event) {

    $('#detail-support-content').html('');

  });

  $('#detail-support-modal').on('shown.bs.modal', function(event) {
    let idSupport = $(event.relatedTarget).attr('data-id');
    let typeSupport = $(event.relatedTarget).attr('data-type');

    $('.loading-ajax').show();

    doPostAjax(
      '{{ route('sellerFiber.detailSupModal') }}',
      function(res){
        $('.loading-ajax').fadeOut();

        if(!res.error){
          $('#detail-support-content').html(res.html);
          addEventsToModal();
          $('#calendar').datepicker('setDate', new Date(res.date_ins));
        }else{
          if(res.message == 'TOKEN_EXPIRED'){
            showMessageAjax('alert-danger','Su session a expirado, por favor actualice la página.');
          }else{
            showMessageAjax('alert-danger', res.message);
            $('#close-modal-detail-supp').trigger('click');
          }
        }
      },
      {
        id: idSupport,
        type: typeSupport
      },
      $('meta[name="csrf-token"]').attr('content')
      );
  });

      //Carga mas citas
      if($('#load-more-support-dates').is(':visible')){
        $('#load-more-support-dates').on('click', function(){
          $('.loading-ajax').show();

          let date = $(this).data('date')
          dateb = $(this).data('dateb');

          doPostAjax(
            '{{ route('sellerFiber.loadMoredetailSupModal') }}',
            function(res){
              $('.loading-ajax').fadeOut();

              if(!res.error){
                if(res.showNextSupports){
                  $('#load-more-support-dates').data('dateb', res.datesSupportsB);
                  $('#load-more-support-dates').data('date', res.showNextSupports);
                }else{
                  $('#load-more-support-dates').attr('hidden', true);
                }

                $('#row-detail-content').append(res.html);
              }else{
                if(res.message == 'TOKEN_EXPIRED'){
                  showMessageAjax('alert-danger','Su session a expirado, por favor actualice la página.');
                }else{
                  showMessageAjax('alert-danger', res.message);
                  $('#close-modal-detail-supp').trigger('click');
                }
              }
            },
            {
              dateB: dateb,
              date: date
            },
            $('meta[name="csrf-token"]').attr('content')
            );
        });
      }

  //Redirige a la instalación o a guardar el instalador
  $('#go-to-support').on('click', function(){

    let type = $(this).data('vtype');
    switch(type){
      case "installer":
        //Instalaciones por realizar.
        $('.loading-ajax').fadeIn();
        let idInstall = $('#form-edit-install').attr('data-id');
        window.location.href = '{{route('sellerFiber.doInstall')}}'+'/'+idInstall
        break;
      case "installerAgenda":
        //Citas por asignar. Guardo instalador
        verifyAction("installerAgenda");
        break;
      case "installerAsigne":
        //Citas asignadas a instaladores. Actualizo instalador
        verifyAction("installerAsigne");
        break;
      default:
        console.log('Opcion '+option+' no definida');
    }
  });

</script>
@endif

@stop

@extends('layouts.admin')

@section('customCSS')

@stop

@section('content')
@include('components.messages')
@include('components.messagesAjax')

@php
$types = [
  'H' => 'Internet Hogar',
  'T' => 'Telefonía',
  'M' => 'Mifi',
  'F' => 'Fibra'
];

$status = [
  'P' => 'Pendiente',
  'A' => 'Aceptado',
  'R' => 'Rechazado'
];
@endphp

@if ($errors->any())
<div class="alert alert-danger">
  <ul>
    @foreach ($errors->all() as $error)
    <li>{{ $error }}</li>
    @endforeach
  </ul>
</div>
@endif

<div class="row bg-title">
  <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
    <h4 class="page-title"> Estatus de Inventario Preasignado</h4>
  </div>
  <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
    <ol class="breadcrumb">
      <li><a href="#">Dashboard</a></li>
      <li class="active">Estatus de Inventario Preasignado</li>
    </ol>
  </div>
</div>
<div class="row">
  <div class="col-md-12">
    <div class="white-box">
      <h3 class="box-title">Lista Articulos Pre-asignados</h3>
      @if($articles->count())
      <div id="sales-list" class="row">
        <div class="table-responsive" id="list-article">
          <table class="table table-bordered table-dn-noty">
            <thead>
              <tr>
                <th>Vendedor</th>
                <th>MSISDN</th>
                <th>Equipo</th>
                <th>EMEI</th>
                <th>Tipo</th>
                <th>Fecha de PreAsignación</th>
                <th>Estatus</th>
                <th>Motivo de rechazo</th>
              </tr>
            </thead>
            <tbody>
              @foreach($articles as $article)
              <tr class="item">
                <td>
                  {{ $article->vendor }}
                </td>

                <td>
                  {{ $article->msisdn }}
                </td>

                <td>
                  {{ $article->title }}
                </td>

                <td>
                  {{ $article->imei }}
                </td>

                <td>
                  {{ !empty($types[$article->type]) ? $types[$article->type] : 'Otro' }}
                </td>

                <td>
                  {{ date('d-m-Y', strtotime($article->date_reg)) }}
                </td>

                <td style="font-weight: bold;" class="@if($article->status == 'P') text-warning @elseif($article->status == 'A') text-success @elseif($article->status == 'R') text-danger @endif">
                  {{ !empty($status[$article->status]) ? $status[$article->status] : 'Otro' }}
                </td>

                <td>
                  {{ !empty($article->reason_reject) ? $article->reason_reject : 'N/A' }}
                </td>

              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
      @else
      <div class="row">
        <div class="alert alert-danger">
          <p>No se consiguio inventario pre-asignado</p>
        </div>
      </div>
      @endif
    </div>
  </div>
</div>
@stop

@section('scriptJS')
<script src="{{ asset('js/sweetalert.min.js') }}"></script>
<script type="text/javascript">
  $(function() {


  });

</script>
@stop

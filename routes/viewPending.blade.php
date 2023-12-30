@extends('layouts.admin')

@section('customCSS')
    <link rel="stylesheet" href="{{ asset('plugins/bower_components/bootstrap-datepicker/bootstrap-datepicker.min.css') }}">
@stop

@section('content')

    <div class="row bg-title">
        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title"> Reporte de instalaciones</h4>
        </div>
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
                <li><a href="#">Reportes</a></li>
                <li class="active">Reporte de instalaciones.</li>
            </ol>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="white-box">
                <h3 class="box-title">Instalaciones pendientes</h3>

                @if(count($data))                        
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Cliente</th>
                                    <th>Días desde la venta</th>
                                    <th>Dirección</th>
                                    <th>Colonia</th>
                                    <th>Zona</th>
                                    <th>Re-programaciones</th>
                                    <th>Fecha/Hora instalación</th>
                                </tr>
                            </thead>
                            <tbody id="row-detail-content">
                                @include('reports.fiber.pendingInstall',['data' => $data])
                            </tbody>
                        </table>
                    </div>
                @if($data)
                    <div class="col-md-12">
                        {{-- <button type="button" class="btn btn-success waves-effect" id="load-more-dates" data-dateb="{{$data['datesInstalationsB']}}" data-date="{{$data['showNextInstalations']}}">Cargar mas</button> --}}
                    </div>
                @endif
                @else
                    <p>No hay instalaciones pendientes.</p>
                @endif

            </div>
        </div>
    </div>
@stop


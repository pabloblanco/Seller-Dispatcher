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
      Solicitudes de baja en proceso
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
        Solicitudes de baja en proceso
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
      <div id='filters-container'>
        <h3 class="box-title" id='filters-title'>Filtros</h3>
        <form id="filters-form" method="POST" data-toggle="validator">
            {{ csrf_field() }}
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group" id="rs-content">
                      <label class="col-md-12">Estatus de baja</label>
                      <div class="col-md-12">
                          <select id="status" name="status" class="form-control">
                              <option value="" selected>Seleccione</option>
                              <option value="R">Solicitada</option>
                              <option value="P">En Proceso</option>
                              <option value="D">Rechazada</option>
                          </select>
                      </div>
                  </div>
                </div>

                <div class="col-md-8">
                    <label class="col-md-12">Vendedor</label>
                    <div class="form-group">
                      <div class="col-md-12">
                        <div id="scrollable-dropdown-menu">
                          <select class="form-control" id="list-users" name="list-users">
                          </select>
                        </div>
                      </div>
                    </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-12 text-center p-b-20 mt-5">
                    <button type="button" id="do-search" class="btn btn-success waves-effect">
                        Filtrar
                    </button>
                </div>
            </div>
        </form>
      </div>

      <div id="list-request">
        {!! $html_list !!}
      </div>
    </div>
  </div>
  @endif
</div>
@stop

@section('scriptJS')
@if($lock->is_locked == 'N')
<!-- typehead TextBox Search -->
<script src="{{ asset('plugins/bower_components/typeahead.js-master/dist/typeahead.bundle.min.js') }}"></script>
<script src="{{ asset('plugins/bower_components/dropify/dist/js/dropify.min.js') }}"></script>
<script src="{{ asset('js/sweetalert.min.js') }}"></script>
<script src="{{ asset('js/selectize.js')}}"></script>
<script src="{{ asset('js/low/viewRequests.js')}}"></script>
@endif
@stop

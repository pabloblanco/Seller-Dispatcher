@extends('layouts.admin')

@section('customCSS')
    <link rel="stylesheet" href="{{ asset('plugins/bower_components/bootstrap-datepicker/bootstrap-datepicker.min.css') }}">
@stop

@section('content')
    @include('components.messages')

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
            <h4 class="page-title"> Editar Prospecto </h4>
        </div>
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
                <li><a href="#">Prospectos</a></li>
                <li><a href="{{route('client.list')}}">Listado de prospectos</a></li>
                <li class="active">Editar Prospecto.</li>
            </ol>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="white-box">
                <h3 class="box-title">Datos del prospecto</h3>
                <form class="form-horizontal" id="registerclientform" method="POST" action="" data-toggle="validator">
                    {{ csrf_field() }}
                    <div class="form-group">
                        <label class="col-md-12">Nombre</label>
                        <div class="col-md-12">
                            <input type="text" class="form-control" id="name" name="name" placeholder="Nombre del prospecto" value="{{$data->name}}" required>
                            <div class="help-block with-errors"></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-12">Apellido</label>
                        <div class="col-md-12">
                            <input type="text" class="form-control" id="last_name" name="last_name" placeholder="Apellido del prospecto" value="{{$data->last_name}}" required>
                            <div class="help-block with-errors"></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-12">INE</label>
                        <div class="col-md-12">
                            <input type="text" class="form-control" id="dni" name="dni" placeholder="Identificación del prospecto" value="{{$data->dni}}" readonly="true">
                            <div class="help-block with-errors"></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-12">Direcci&oacute;n</label>
                        <div class="col-md-12">
                            <input type="text" class="form-control" id="direction" name="direction" value="{{$data->address}}">
                            <div class="help-block with-errors"></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-12">Cumpleaños</label>
                        <div class="col-md-12">
                            <input type="text" class="form-control" id="birthday" name="birthday" placeholder="dd/mm/yyyy" value="{{getFormatDate($data->birthday, 'd-m-Y')}}">
                            <div class="help-block with-errors"></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-12">Email</label>
                        <div class="col-md-12">
                            <input class="form-control" id="email" name="email" type="email" placeholder="correo@servidor.com" value="{{$data->email}}" data-error="Dirección de email no válida">
                            <div class="help-block with-errors"></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-12">Teléfono principal</label>
                        <div class="col-md-12">
                            <input class="form-control" id="phone" name="phone" type="text" minlength="10" maxlength="10" required value="{{$data->phone_home}}">
                            <div class="help-block with-errors"></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-12">Télefono secundario</label>
                        <div class="col-md-12">
                            <input class="form-control" id="phone2" name="phone2" type="text" minlength="10" maxlength="10" value="{{$data->phone}}">
                            <div class="help-block with-errors"></div>
                        </div>
                    </div>
                    {{-- <div class="form-group">
                        <label class="col-md-12">Prospecto redes sociales</label>
                        <div class="col-md-12">
                            <label class="custom-control custom-radio">
                                <input name="social" type="radio" class="custom-control-input" value="N" @if($data->social == 'N')checked @endif>
                                <span class="custom-control-indicator"></span>
                                <span class="custom-control-description">No</span>
                            </label>
                            <label class="custom-control custom-radio">
                                <input name="social" type="radio" class="custom-control-input" value="S" @if($data->social == 'S')checked @endif>
                                <span class="custom-control-indicator"></span>
                                <span class="custom-control-description">Si</span>
                            </label>
                        </div>
                    </div> --}}
                    <div class="form-group">
                        <label class="col-md-12">Pr&oacute;ximo contacto</label>
                        <div class="col-md-12">
                            <input type="text" class="form-control" id="nextC" name="nextC" placeholder="dd-mm-yyyy" value="{{getFormatDate($data->contact_date, 'd-m-Y')}}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-12">Notas</label>
                        <div class="col-md-12">
                            <textarea class="form-control" rows="4" id="note" name="note">{{$data->note}}</textarea>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-success waves-effect waves-light m-r-10">Guardar</button>
                </form>
            </div>
        </div>
    </div>
@stop

@section('scriptJS')
    <script src="{{ asset('plugins/bower_components/bootstrap-datepicker/bootstrap-datepicker.min.js') }}"></script>
    <script src="{{ asset('js/validator.js') }}"></script>

    <script type="text/javascript">
        $(function () {
            var now = new Date(),
                start = new Date(new Date().setFullYear(now.getFullYear() - 18));

            $('#birthday').datepicker({
                language: 'es',
                autoclose: true,
                format: 'dd-mm-yyyy',
                endDate: start
            });

            $('#nextC').datepicker({
                language: 'es',
                autoclose: true,
                format: 'dd-mm-yyyy',
                startDate: now
            });
        });
    </script>
@stop

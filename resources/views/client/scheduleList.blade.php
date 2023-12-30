@extends('layouts.admin')

@section('customCSS')
    <link rel="stylesheet" href="{{ asset('plugins/bower_components/bootstrap-datepicker/bootstrap-datepicker.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/bower_components/clockpicker/dist/jquery-clockpicker.min.css') }}">
@stop

@section('content')
    @include('components.messages')
    @include('components.messagesAjax')

    <div class="row bg-title">
        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title"> Agenda </h4>
        </div>
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
                <li><a href="#">Dashboard</a></li>
                <li class="active">Agenda</li>
            </ol>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="white-box">
                <div class="row">
                    <div class="col-md-12">
                        <h3 class="box-title">Citas agendadas</h3>
                        <center>
                            <div id="calendar"></div>
                        </center>
                        <hr>
                        <div class="col-md-12 p-t-20" id="allSchedule">
                            @forelse($dates as $date)
                                <div class="col-md-12 content-date m-b-20">
                                    @if(session('user_type') != 'vendor')
                                    <div class="col-md-12">
                                        <h3>Vendedor</h3>
                                        <p>{{$date->name_seller}} {{$date->last_name_seller}}</p>
                                    </div>
                                    <div class="col-md-12">
                                        <hr>
                                        <h3>Propecto</h3>
                                    </div>
                                    @endif
                                    <div class="col-sm-12 col-md-6">
                                        <label>Nombre:</label>
                                        <p>{{$date->name}}</p>
                                    </div>
                                    <div class="col-sm-12 col-md-6">
                                        <label>Tel&eacute;fono:</label>
                                        <p>{{$date->phone_home}}</p>
                                    </div>
                                    <div class="col-sm-12 col-md-6">
                                        <label>Direcci&oacute;n:</label>
                                        <p>{{!empty($date->address) ? $date->address : 'S/I'}}</p>
                                    </div>
                                    <div class="col-sm-12 col-md-6">
                                        <label>Fecha de la cita:</label>
                                        <p>{{$date->date_schedules}}</p>
                                    </div>
                                    @if(hasPermit('ECV-DSE'))
                                    <div class="col-sm-12 col-md-12">
                                        <a class="btn btn-danger waves-effect waves-light m-t-10" href="{{$date->delay}}">Editar</a>
                                    </div>
                                    @endif
                                </div>
                            @empty
                            <div class="col-md-12">
                                <blockquote>
                                    <p>No hay citas registradas.</p>
                                </blockquote>
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('scriptJS')
    <script src="{{ asset('plugins/bower_components/bootstrap-datepicker/bootstrap-datepicker.min.js') }}"></script>
    <script src="{{ asset('plugins/bower_components/clockpicker/dist/jquery-clockpicker.min.js') }}"></script>
    <script src="{{ asset('js/validator.js') }}"></script>

    <script type="text/javascript">
        $(function () {
            getSchedule = function(date = false){
                var route = date ? "{{route('client.getSchedule')}}/"+date : "{{route('client.getSchedule')}}";
                $.get(route, function(data){
                    var html = '';
                    if(!data.error && data.data){
                        data.data.forEach(function(ele){
                            html += '<div class="col-md-12 content-date m-b-20">';
                            if(data.notSeller){
                                html += '<div class="col-md-12"> <h3>Vendedor</h3> <p>' +ele.name_seller +' '+ele.last_name_seller + '</p> <hr> <h3>Prospecto</h3> </div>';
                            }
                            html += '<div class="col-sm-12 col-md-6"> <label>Nombre:</label> <p>'+ele.name+'</p> </div>';
                            html += '<div class="col-sm-12 col-md-6"> <label>Teléfono:</label> <p>'+ele.phone_home+'</p> </div>';
                            html += '<div class="col-sm-12 col-md-6"> <label>Dirección:</label> <p>'+(ele.address ? ele.address : 'S/I')+'</p> </div>';
                            html += '<div class="col-sm-12 col-md-6"> <label>Fecha de la cita:</label> <p>'+ele.date_schedules+'</p> </div>';
                            if(data.hold)
                                html += '<div class="col-sm-12 col-md-12"> <a class="btn btn-danger waves-effect waves-light m-t-10" href="'+ele.delay+'">Editar</a></div>';
                            html += '</div>';
                        });

                        $('#allSchedule').html(html);
                    }else if(data.code == 'NOT_DATA'){
                        html += '<blockquote>';
                        html += '<p>No hay citas registradas.</p>';
                        html += '</blockquote>';
                        $('#allSchedule').html(html);
                    }else{
                        $('#msgAjax').addClass('alert-danger').show();
                        $('#txtMsg').text('No se pudo actualizar la lista de citas.');
                        setTimeout(function(){$('#msgAjax').removeClass('alert-success').removeClass('alert-danger').hide(300);},3000);
                    }
                    $(".preloader").fadeOut();
                }, 'json');
            }

            $('#calendar').datepicker({
                language: 'es',
                todayHighlight: true,
                format: 'dd-mm-yyyy',
                startDate: new Date()
            }).on('changeDate', function (selected) {
                $(".preloader").fadeIn();
                var date = selected.date,
                    month = ((date.getMonth() + 1) < 10) ? ('0' + (date.getMonth() + 1 )) : (date.getMonth() + 1 );
                    day = (date.getDate() < 10) ? ('0' + date.getDate()) : date.getDate();
                    fecha = date ? date.getFullYear() + '-' + month + '-' + day : '';

                getSchedule(fecha);
            });

            $('.clockpicker').clockpicker({
                donetext: 'Seleccionar',
                default: 'now',
                placement: 'bottom',
                autoclose: true
            });
        });
    </script>
@stop
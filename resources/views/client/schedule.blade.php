{{--Vista deprecada--}}

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
            <!--<a href="https://themeforest.net/item/elite-admin-responsive-dashboard-web-app-kit-/16750820" target="_blank" class="btn btn-danger pull-right m-l-20 btn-rounded btn-outline hidden-xs hidden-sm waves-effect waves-light">Buy Now</a>-->
            <ol class="breadcrumb">
                <li><a href="#">Dashboard</a></li>
                <li class="active">Agenda</li>
            </ol>
        </div>
        <!-- /.col-lg-12 -->
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="white-box">
                <div class="row">
                    <div class="col-md-12">
                        @if(!empty($client))
                        <div class="col-md-12" id="newDate">
                            @if(!empty($sche))
                                <h3 class="box-title">Actualizar fecha de cita</h3>
                            @else
                                <h3 class="box-title">Agendar cita nueva</h3>
                            @endif
                            
                            <div class="col-md-12 content-date">
                                {{csrf_field()}}
                                <input type="hidden" name="client" id="client" value="{{base64_encode($client->dni)}}">
                                @if(!empty($sche))
                                    <input type="hidden" name="sche" id="sche" value="{{$sche}}">
                                @endif
                                <div class="col-sm-12 col-md-12 text-center m-b-20">
                                    <h4>Tienes <span id="CountD"> {{$client->countDate}} </span> citas para el <span id="CountDD">{{date('d-m-Y')}}</span> </h4>
                                </div>
                                <div class="col-sm-12 col-md-6">
                                    <label>Nombre:</label>
                                    <p>{{$client->name}} {{$client->last_name}}</p>
                                </div>
                                <div class="col-sm-12 col-md-6">
                                    <label>Tel&eacute;fono:</label>
                                    <p>{{$client->phone_home}}</p>
                                </div>
                                <div class="col-sm-12 col-md-6">
                                    <label>Direcci&oacute;n:</label>
                                    <p>{{$client->address}}</p>
                                </div>
                                <div class="col-sm-12 col-md-6">
                                    <label>Hora de la cita:</label>
                                    <div class="input-group clockpicker">
                                        <input type="text" id="hour" class="form-control">
                                        <span class="input-group-addon"> <span class="glyphicon glyphicon-time"></span> </span>
                                    </div>
                                </div>
                                <div class="col-sm-12 col-md-6">
                                    <label>Fecha de la cita:</label>
                                    <div id="calendar"></div>
                                </div>
                                <div class="col-sm-12 col-md-12">
                                    @if(!empty($sche))
                                        @if(hasPermit('RCC-MV3'))
                                        <button type="button" class="btn btn-success waves-effect waves-light m-t-10 activate-loader" id="updateDate">Actualizar</button>
                                        @endif
                                    @else
                                        <button type="button" class="btn btn-success waves-effect waves-light m-t-10 activate-loader" id="saveDate">Agendar</button>
                                    @endif
                                </div>
                            </div>
                        </div>

                        @else
                            <h3 class="box-title">Citas agendadas</h3>
                            <center>
                                <div id="calendar"></div>
                            </center>
                            <hr>
                            <div class="col-md-12 p-t-20" id="allSchedule">
                                @forelse($dates as $date)
                                    <div class="col-md-12 content-date m-b-20">
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
                                            <p>{{$date->address}}</p>
                                        </div>
                                        <div class="col-sm-12 col-md-6">
                                            <label>Fecha de la cita:</label>
                                            <p>{{getFormatDate($date->date_schedules, 'd-m-Y H:i')}}</p>
                                        </div>
                                        @if(hasPermit('ECV-MV3'))
                                        <div class="col-sm-12 col-md-12">
                                            <a class="btn btn-danger waves-effect waves-light m-t-10" href="{{route('client.schedule',['dni' => base64_encode($date->client_dni),'sche' => base64_encode($date->id)])}}">Posponer</a>
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
                        @endif
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
                            html += '<div class="col-sm-12 col-md-6"> <label>Nombre:</label> <p>'+ele.name+'</p> </div>';
                            html += '<div class="col-sm-12 col-md-6"> <label>Teléfono:</label> <p>'+ele.phone_home+'</p> </div>';
                            html += '<div class="col-sm-12 col-md-6"> <label>Dirección:</label> <p>'+ele.address+'</p> </div>';
                            html += '<div class="col-sm-12 col-md-6"> <label>Fecha de la cita:</label> <p>'+ele.date_schedules+'</p> </div>';
                            if(data.hold)
                                html += '<div class="col-sm-12 col-md-12"> <a class="btn btn-danger waves-effect waves-light m-t-10" href="'+ele.delay+'">Posponer</a></div>';
                            html += '</div>';
                        });
                        $('#allSchedule').html(html);
                        bindEvents();
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

            getCountSchedule = function(date = false){
                if(date){
                    $.get("{{route('client.countchedule')}}/"+date, function(data){
                        if(!data.error){
                            $('#CountDD').text(data.date);
                            $('#CountD').text(data.count);
                        }else{
                            $('#msgAjax').addClass('alert-danger').show();
                            $('#txtMsg').text('No se pudo obtener la contidad de citas.');
                            setTimeout(function(){$('#msgAjax').removeClass('alert-success').removeClass('alert-danger').hide(300);},3000);
                        }
                        $(".preloader").fadeOut();
                    }, 'json');
                }
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
                if($('#allSchedule').is(':visible'))
                    getSchedule(fecha);
                if($('#newDate').is(':visible'))
                    getCountSchedule(fecha);
            });

            $('.clockpicker').clockpicker({
                donetext: 'Seleccionar',
                default: 'now',
                placement: 'bottom',
                autoclose: true
            });

            bindEvents();
        });
        
        function bindEvents(){
            $('.deleteDate').on('click', function(event){
                var data = $(event.currentTarget).data('sche');

                if(data && data != ''){
                    $.get("{{route('client.deleteSchedule')}}/"+data, function(data){
                        if(!data.error){
                            $('#msgAjax').addClass('alert-danger').show();
                            $('#txtMsg').text('Se borro la cita.');
                            var date = $('#calendar').datepicker('getDate')
                            if(date){
                                var month = ((date.getMonth() + 1) < 10) ? ('0' + (date.getMonth() + 1 )) : (date.getMonth() + 1 ),
                                    day = (date.getDate() < 10) ? ('0' + date.getDate()) : date.getDate()
                                    fecha = date ? date.getFullYear() + '-' + month + '-' + day : '';
                                getSchedule(fecha);
                            }else getSchedule();
                        }else{
                            $('#msgAjax').addClass('alert-danger').show();
                            $('#txtMsg').text('No se pudo borrar la cita.');
                        }
                        setTimeout(function(){$('#msgAjax').removeClass('alert-success').removeClass('alert-danger').hide(300);},3000);
                    }, 'json')
                        .fail(function() {
                            $('#msgAjax').addClass('alert-danger').show();
                            $('#txtMsg').text('Ocurrio un error inesperado.');
                            setTimeout(function(){$('#msgAjax').removeClass('alert-success').removeClass('alert-danger').hide(300);},3000);
                        });
                }else{
                    $('#msgAjax').addClass('alert-danger').show();
                    $('#txtMsg').text('No se pudo borrar la cita.');
                    setTimeout(function(){$('#msgAjax').removeClass('alert-success').removeClass('alert-danger').hide(300);},3000);
                }
            });

            $('#saveDate').on('click', function(event){
                var date = $('#calendar').datepicker('getDate'),
                    hour = $('#hour').val();

                if(!date && hour == ''){
                    $('#msgAjax').addClass('alert-danger').show();
                    $('#txtMsg').text('Debe seleccionar la fecha y hora de la cita.');
                }else{
                    if(!date){
                        $('#msgAjax').addClass('alert-danger').show();
                        $('#txtMsg').text('Debe seleccionar la fecha de la cita.');
                    }else{
                        if(!hour){
                            $('#msgAjax').addClass('alert-danger').show();
                            $('#txtMsg').text('Debe seleccionar la hora de la cita.');
                        }
                    }
                }

                if($('#msgAjax').is(':visible')){
                    setTimeout(function(){$('#msgAjax').removeClass('alert-success').removeClass('alert-danger').hide(300);},3000);
                }else{
                    var month = ((date.getMonth() + 1) < 10) ? ('0' + (date.getMonth() + 1 )) : (date.getMonth() + 1 ),
                        day = (date.getDate() < 10) ? ('0' + date.getDate()) : date.getDate();
                        fecha = date.getFullYear() + '-' + month + '-'+ day,
                        sendData = {
                            date: fecha,
                            hour: hour,
                            _token: $('input[name=_token]').val(),
                            client: $('#client').val()
                        };

                    $.post("{{route('client.saveSchedule')}}", sendData, function( data ){
                        if(!data.error){
                            $('#msgAjax').addClass('alert-success').show();
                            $('#txtMsg').text('Se agendo la cita exitosamente.');
                            setTimeout(function(){window.location = "{{route('client.list')}}";},2000);
                        }else{
                            $('#msgAjax').addClass('alert-danger').show();
                            $('#txtMsg').text('No se pudo agendar la cita.');
                            setTimeout(function(){$('#msgAjax').removeClass('alert-success').removeClass('alert-danger').hide(300);},3000);
                        }
                        $(".preloader").fadeOut();
                    }, "json");
                }
            });

            $('#updateDate').on('click', function(event){
                var date = $('#calendar').datepicker('getDate'),
                    hour = $('#hour').val();

                if(!date && hour == ''){
                    $('#msgAjax').addClass('alert-danger').show();
                    $('#txtMsg').text('Debe seleccionar la fecha y hora de la cita.');
                }else{
                    if(!date){
                        $('#msgAjax').addClass('alert-danger').show();
                        $('#txtMsg').text('Debe seleccionar la fecha de la cita.');
                    }else{
                        if(!hour){
                            $('#msgAjax').addClass('alert-danger').show();
                            $('#txtMsg').text('Debe seleccionar la hora de la cita.');
                        }
                    }
                }

                if($('#msgAjax').is(':visible')){
                    setTimeout(function(){$('#msgAjax').removeClass('alert-success').removeClass('alert-danger').hide(300);},3000);
                }else{
                    var month = ((date.getMonth() + 1) < 10) ? ('0' + (date.getMonth() + 1 )) : (date.getMonth() + 1 ),
                        day = (date.getDate() < 10) ? ('0' + date.getDate()) : date.getDate();
                        fecha = date.getFullYear() + '-' + month + '-'+ day,
                        sendData = {
                            date: fecha,
                            hour: hour,
                            sche: $('#sche').val(),
                            _token: $('input[name=_token]').val(),
                            client: $('#client').val()
                        };

                    $.post("{{route('client.updateSchedule')}}", sendData, function( data ){
                        if(!data.error){
                            $('#msgAjax').addClass('alert-success').show();
                            $('#txtMsg').text('Se actualizo la cita exitosamente.');
                            setTimeout(function(){window.location = "{{route('client.schedule')}}";},2000);
                        }else{
                            $('#msgAjax').addClass('alert-danger').show();
                            $('#txtMsg').text('No se pudo actualizar la cita.');
                            setTimeout(function(){$('#msgAjax').removeClass('alert-success').removeClass('alert-danger').hide(300);},3000);
                        }
                        $(".preloader").fadeOut();
                    }, "json");
                }
            });
        }
    </script>
@stop
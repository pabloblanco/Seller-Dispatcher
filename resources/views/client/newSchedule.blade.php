@extends('layouts.admin')

@section('customCSS')
    <link rel="stylesheet" href="{{ asset('plugins/bower_components/bootstrap-datepicker/bootstrap-datepicker.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/bower_components/clockpicker/dist/jquery-clockpicker.min.css') }}">
    <link href="{{ asset('css/selectize.css') }}" rel="stylesheet">
    <link href="{{ asset('css/selectize.bootstrap.css') }}" rel="stylesheet">
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
                        <div class="col-md-12" id="newDate">
                            @if($data['editschedule'])
                                <h3 class="box-title">Editar cita</h3>
                            @else
                                <h3 class="box-title">Agendar cita nueva</h3>
                            @endif
                            <form class="form-horizontal" id="formSaveDate" method="POST" action="">
                            <div class="col-md-12 content-date">
                                {{csrf_field()}}
                                <div class="col-md-6 col-sm-12">
                                    @if($data['showSellers'])
                                        <div class="col-md-12 m-b-10">
                                            <label>Vendedor:</label>
                                            <select class="form-control" id="list-seller" name="sellerEmail">
                                                @if($data['hasSeller'])
                                                    <option value="{{$data['SellerEmail']}}">{{$data['sellerName']}}</option>
                                                @else
                                                    <option value="">Seleccione un vendedor</option>
                                                @endif
                                                {{--@foreach($data['sellers'] as $seller)
                                                    @if($data['hasSeller'])
                                                        @if($seller->email != $data['SellerEmail'])
                                                            <option value="{{$seller->email}}">{{$seller->name}} {{$seller->last_name}}</option>
                                                        @endif
                                                    @else
                                                        <option value="{{$seller->email}}">{{$seller->name}} {{$seller->last_name}}</option>
                                                    @endif
                                                @endforeach --}}
                                            </select>
                                        </div>
                                    @else
                                        <input type="hidden" name="sellerEmail" id="list-seller" value="{{session('user')}}">
                                    @endif

                                    <div class="col-md-12">
                                        <label>Nombre:</label>
                                        <p>{{$data['client']->name}} {{$data['client']->last_name}}</p>
                                    </div>

                                    <div class="col-md-12">
                                        <label>Tel&eacute;fono:</label>
                                        <p>{{$data['client']->phone_home}}</p>
                                    </div>

                                    <div class="col-md-12">
                                        <label>Direcci&oacute;n:</label>
                                        <p>{{!empty($data['client']->address) ? $data['client']->address : 'S/I'}}</p>
                                    </div>

                                    <div class="col-md-12">
                                        <label>Fecha de la cita:</label>
                                        <div id="calendar"></div>
                                        <input type="hidden" name="dateCalendar" id="dateCalendar" value="">
                                    </div>
                                </div>
                                <div class="col-md-6 col-sm-12">
                                    <div class="col-md-12">
                                        <label>Hora de la cita:</label>
                                        <div class="input-group clockpicker">
                                            <input type="text" name="hour" id="hour" class="form-control">
                                            <span class="input-group-addon"> <span class="glyphicon glyphicon-time"></span> </span>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-12 m-t-10" id="showDates">
                                        @if(!empty($data['dates']) && count($data['dates']))
                                            <label>Citas del vendedor para el <span id="CountDD">{{$data['date']}}</span></label>
                                            <ul>
                                                @foreach($data['dates'] as $date)
                                                    <li>
                                                        {{$date->name}} {{$date->last_name}} ({{getFormatDate($date->date_schedules, 'H:i')}})
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    </div>
                                    
                                </div>

                                <div class="col-sm-12 col-md-12">
                                    @if($data['editschedule'])
                                        <button type="submit" class="btn btn-success waves-effect waves-light m-t-10" id="saveDate">Editar</button>
                                    @else
                                        <button type="submit" class="btn btn-success waves-effect waves-light m-t-10" id="saveDate">Agendar</button>
                                    @endif
                                </div>
                            </div>
                            </form>
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
    <script src="{{ asset('js/selectize.js')}}"></script>

    <script type="text/javascript">
        $(function () {
            getListDates = function(fecha){
                var email = $('#list-seller').val(),
                route = "{{route('call.listDate')}}";
                $('#showDates').html('');

                if(email != ''){
                    $(".preloader").fadeIn();

                    var sendData = {
                        date: fecha,
                        _token: $('input[name=_token]').val().trim(),
                        email: email.trim()
                    };

                    $.post(route, sendData, function(data){
                        if(!data.error){
                            if(data.dates){
                                var list = '<label>Citas del vendedor para el <span id="CountDD">' + data.date + '</span></label>';
                                list += '<ul>';
                                data.dates.forEach(function(ele){
                                    list += '<li> '+ ele.name + ' ' + ele.last_name + '</li>';
                                });
                                list += '</ul>';
                                $('#showDates').html(list);
                            }
                        }else{
                            if(data.message == 'TOKEN_EXPIRED'){
                                showMessageAjax('alert-danger','Su session a expirado, por favor actualice la página.');
                            }else{
                                showMessageAjax('alert-danger','No se pueden mostrar las citas del vendedor.');
                            }
                        }
                        $(".preloader").fadeOut();
                    }, "json");
                }
            }

            $('#calendar').datepicker({
                language: 'es',
                todayHighlight: true,
                format: 'dd-mm-yyyy',
                startDate: new Date()
            }).on('changeDate', function (selected) {
                var date = selected.date,
                    month = ((date.getMonth() + 1) < 10) ? ('0' + (date.getMonth() + 1 )) : (date.getMonth() + 1 ),
                    day = (date.getDate() < 10) ? ('0' + date.getDate()) : date.getDate(),
                    fecha = date ? date.getFullYear() + '-' + month + '-' + day : '';
                $('#dateCalendar').val(fecha);
                getListDates(fecha);
            });

            $('.clockpicker').clockpicker({
                donetext: 'Seleccionar',
                default: 'now',
                placement: 'bottom',
                autoclose: true
            });

            $('#list-seller').on('change', function(e){
                $('#list-seller').val($('#list-seller').val());
                var date = $('#calendar').datepicker('getDate');
                date = (date == undefined) ? new Date() : date;
                var month = ((date.getMonth() + 1) < 10) ? ('0' + (date.getMonth() + 1 )) : (date.getMonth() + 1 ),
                    day = (date.getDate() < 10) ? ('0' + date.getDate()) : date.getDate(),
                    fecha = date ? date.getFullYear() + '-' + month + '-' + day : '';

                getListDates(fecha);
            });

            $('#formSaveDate').on('submit', function(e){
                var seller = $('#list-seller').val().trim(),
                    date = $('#dateCalendar').val().trim(),
                    hour = $('#hour').val().trim();

                if(date == '' || hour == ''){
                    e.preventDefault();
                    showMessageAjax('alert-danger','Debe seleccionar la fecha y hora de la cita.');
                }else{
                    if(seller == ''){
                        e.preventDefault();
                        showMessageAjax('alert-danger','Debe seleccionar un vendedor.');
                    }else{
                        $(".preloader").fadeIn();
                    }
                }
            });

            $('#list-seller').selectize({
                valueField: 'email',
                searchField: 'name',
                labelField: 'name',
                options: [
                    @if($data['hasSeller'])
                        {email: '{{$data['SellerEmail']}}', name: '{{$data['sellerName']}}'},
                    @else
                        {email: '', name: 'Seleccione un vendedor'},
                    @endif

                    @if(!empty($data['sellers']) && count($data['sellers']))
                    @foreach($data['sellers'] as $seller)
                        @if($data['hasSeller'] && $seller->email == $data['SellerEmail'])
                            @continue
                        @endif
                        {email: '{{$seller->email}}', name: '{{$seller->name}} {{$seller->last_name}}'},
                    @endforeach
                    @endif
                ],
                render: {
                    option: function(item, escape) {
                        return '<p>'+escape(item.name)+'</p>';
                    }
                }
            });
        });
    </script>
@stop
@extends('layouts.admin')

@section('customCSS')
    <link rel="stylesheet" href="{{ asset('plugins/bower_components/bootstrap-datepicker/bootstrap-datepicker.min.css') }}">
    <link href="{{ asset('css/selectize.css') }}" rel="stylesheet">
    <link href="{{ asset('css/selectize.bootstrap.css') }}" rel="stylesheet">
@stop

@section('content')
    @include('components.messages')
    @include('components.messagesAjax')

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
            <h4 class="page-title"> Reporte de activaciones </h4>
        </div>
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
                <li><a href="#">Coordinaci&oacute;n</a></li>
                <li class="active">Reporte de activaciones.</li>
            </ol>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="white-box">
                <h3 class="box-title">Filtros</h3>
                <form id="Salesclientform" method="POST" data-toggle="validator">
                    {{ csrf_field() }}
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label">Usuario</label>
                                <div id="scrollable-dropdown-menu">
                                    <select class="form-control" id="seller" name="seller">
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label">Fecha desde</label>
                                <input type="text" name="dateb" id="dateb" class="form-control" placeholder="dd-mm-yyyy">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label">Fecha hasta</label>
                                <input type="text" name="datee" id="datee" class="form-control" placeholder="dd-mm-yyyy">
                            </div>
                        </div>

                        <div class="col-md-12 text-center p-b-20">
                            <button type="button" id="do-search" class="btn btn-success waves-effect">
                                Filtrar
                            </button>
                        </div>
                    </div>
                </form>
                <div class="row">
                    <div class="col-md-12 p-b-20" id="list-activation" hidden="">
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('scriptJS')
    <script src="{{ asset('plugins/bower_components/bootstrap-datepicker/bootstrap-datepicker.min.js') }}"></script>
    <script src="{{ asset('js/selectize.js')}}"></script>

    <script type="text/javascript">
        $(function () {
            $('#seller').selectize({
                valueField: 'email',
                searchField: 'name',
                labelField: 'name',
                render: {
                    option: function(item, escape) {
                        return '<p>'+escape(item.name_profile)+': '+escape(item.name)+' '+escape(item.last_name)+'</p>';
                    }
                },
                load: function(query, callback) {
                    if (!query.length) return callback();
                    $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        url: '{{ route('findRelationUsers') }}',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            q: query,
                            me: true
                        },
                        error: function() {
                            callback();
                        },
                        success: function(res){
                            if(!res.error){
                                callback(res.users);
                            }else{
                                callback();
                            }
                        }
                    });
                }
            });

            $('#seller').on('change', function(e){
                let email = $(this).val();

                if(email != '' && email.trim() != ''){
                    $('#seller_email').remove();
                    
                    let hid = $('<input>',{
                        'type': 'hidden',
                        'value': email,
                        'id': 'seller_email',
                        'name': 'seller_email'
                    });
                    
                    $('#Salesclientform').append(hid);
                }{{-- else{
                    showMessageAjax('alert-danger', 'Error seleccionando vendedor.');
                } --}}
            });

            var config = {
                autoclose: true,
                format: 'dd-mm-yyyy',
                todayHighlight: true,
                endDate: new Date()
            }

            $('#dateb').datepicker(config);

            $('#datee').datepicker(config);

            function loadList(form = '', action = ''){
                //$(".preloader").fadeIn();
                $('.loading-ajax').show();

                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': "{{ csrf_token() }}"
                    },
                    async: true,
                    url: "{{route('coordination.getReportActivarions')}}",
                    method: 'POST',
                    data: $('#'+form).serialize()+'&action='+action,
                    dataType: 'json',
                    success: function (res) {
                        $('.loading-ajax').hide();

                        if(res.success){
                            if($('#seller').val() == ''){
                                $('#seller_email').remove();
                            }

                            $('#list-activation').html(res.html);
                            $('#list-activation').attr('hidden', null);
                            $('#next').bind('click', next);
                            $('#prev').bind('click', prev);
                        }else{
                            showMessageAjax('alert-danger', res.msg);
                        }
                    },
                    error: function (res) {
                        $('.loading-ajax').hide();
                        showMessageAjax('alert-danger', 'No se pudieron cargar las activaciones.');
                    }
                });
            }

            $('#do-search').on('click', function(e){
                if($('#seller').val() == ''){
                    $('#seller_email').remove();
                }

                loadList('Salesclientform');
            });

            prev = function(e){
                loadList('listActPag', 'prev');
            }

            next = function(e){
                loadList('listActPag', 'next');
            }

            loadList('Salesclientform');
        });
    </script>
@stop
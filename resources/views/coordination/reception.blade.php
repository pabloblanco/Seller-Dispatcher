@extends('layouts.admin')

@section('customCSS')
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
            <h4 class="page-title"> Recepci&oacute;n de efectivo </h4>
        </div>
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
                <li><a href="#">Coordinaci&oacute;n</a></li>
                <li class="active">Recepci&oacute;n efectivo.</li>
            </ol>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="white-box">
                <h3 class="box-title">Buscar Usuario</h3>
                <form class="form-horizontal" id="Salesclientform" method="POST" action="">
                    {{ csrf_field() }}
                    <div class="form-group">
                        <div class="col-md-12">
                            <div id="scrollable-dropdown-menu">
                                <select class="form-control" id="user" name="user"></select>
                            </div>
                        </div>
                    </div>
                </form>
                <div class="row">
                    <div class="col-md-12 p-b-20" hidden id="list-content"></div>
                </div>
            </div>
        </div>
    </div>

    <div id="den-pay" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog" style="top: 140px;">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    <h4 class="modal-title">Rechazar recepci&oacute;n de efectivo</h4>
                </div>
                <div class="modal-body">
                    <div class="col-sm-12">
                        <label>Motivo por el cual rechaza la recepci&oacute;n de efectivo:</label>
                        <textarea class="form-control" rows="4" id="reason-den"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default waves-effect" data-dismiss="modal">Cancelar</button>
                    <button type="button" id="do-den" class="btn btn-danger waves-effect waves-light btnBuy">Rechazar</button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('scriptJS')
    <script src="{{ asset('js/selectize.js')}}"></script>

    <script type="text/javascript">
        $(function () {
            $('#user').selectize({
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
                            me: false,
                            dismissal: true
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

            $('#user').on('change', function(e){
                let email = $(this).val();

                if(email != '' && email.trim() != ''){
                    loadList(email);
                }else{
                    loadList();
                }
            });

            function deleteRecption(e){
                let id = $(e.currentTarget).data('id'),
                    type = $(e.currentTarget).data('type');

                if(id && type){
                    $('#do-den').data('id', id);
                    $('#do-den').data('type', type);

                    $('#den-pay').modal('show');
                }else{
                   showMessageAjax('alert-danger', 'No se pudo rechazar la recepción de efectivo.');
                }
            }

            $('#do-den').on('click', function(e){
                let id = $(e.currentTarget).data('id'),
                    type = $(e.currentTarget).data('type')
                    reason = $('#reason-den').val();

                if(id && type && reason && reason.trim() != '')
                    setStatus('D', id, type, reason);
                else if(!reason || reason.trim() == '')
                   showMessageAjax('alert-danger', 'Debe escribir el motivo del rechazo.');
                else
                   showMessageAjax('alert-danger', 'No se pudo rechazar la recepción de efectivo.');
            });

            function aceptRecption(e){
                var id = $(e.currentTarget).data('id'),
                    type = $(e.currentTarget).data('type');
                    
                if(id && type)
                    setStatus('A', id, type, '');
                else
                   showMessageAjax('alert-danger', 'No se pudo aceptar la recepción de efectivo.');
            }

            function setStatus(status, id, type, reason = ''){
                if(reason != ''){
                    $('#den-pay').modal('hide');
                }

                $('.loading-ajax').show();

                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': "{{ csrf_token() }}"
                    },
                    async: true,
                    url: "{{route('coordination.receptionStatus')}}",
                    method: 'POST',
                    data: {status: status, id: id, type: type, reason: reason},
                    dataType: 'json',
                    success: function (res) {
                        $('.loading-ajax').hide();

                        if(res.success){
                            loadList(false, res.msg);
                        }else{
                            showMessageAjax('alert-danger', res.msg);
                        }
                    },
                    error: function (res) {
                        $('.loading-ajax').hide();
                        showMessageAjax('alert-danger', 'No se pudo cargar la lista recepción de efectivo.');
                    }
                });
            }

            function loadList(email = false, msg = false){
                $('.loading-ajax').show();

                let filter = (email != false) ? '/'+email.trim() : '';

                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': "{{ csrf_token() }}"
                    },
                    async: true,
                    url: "{{route('coordination.receptionList')}}" + filter,
                    method: 'POST',
                    dataType: 'json',
                    success: function (res){
                        if(res.success){
                            $('#list-content').html(res.html);
                            $('.den-pay').bind('click', deleteRecption);
                            $('.acept-pay').bind('click', aceptRecption);
                            $('#list-content').attr('hidden', null);

                            if(res.count == 0){
                                $('#icon-notification').attr('hidden', true);
                                $('#icon-rf .waves-effect').removeClass('v-it').addClass('h-it');
                            }

                            if(msg){
                                showMessageAjax('alert-success', msg);
                            }
                        }else{
                            showMessageAjax('alert-danger', res.msg);
                        }

                        $('.loading-ajax').hide();
                    },
                    error: function (res) {
                        $('.loading-ajax').hide();
                        showMessageAjax('alert-danger', 'No se pudo cargar la lista recepción de efectivo.');
                    }
                });
            }

            loadList("{{ !empty($email)? $email : false }}");
        });
    </script>
@stop
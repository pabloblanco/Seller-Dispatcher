@extends('layouts.admin')

@section('customCSS')
    <link rel="stylesheet" href="{{ asset('plugins/bower_components/typeahead.js-master/dist/typehead-min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/selectize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/selectize.bootstrap.css') }}">
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
            <h4 class="page-title"> Pagos de venta en abono </h4>
        </div>
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
                <li><a href="#">venta en abono</a></li>
                <li class="active">Pagos.</li>
            </ol>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="white-box">
                <div class="row p-b-20">
                    <div class="col-md-12 p-b-10">
                        <h3 class="box-title">Buscar</h3>
                        <label class="form-check-label p-l-0">
                            <input name="radio" type="radio" class="type-f" value="cl" checked> cliente
                        </label>

                        <label class="form-check-label">
                            <input name="radio" type="radio" class="type-f" value="se"> vendedor
                        </label>
                    </div>
                    <div class="col-md-12">
                        <select id="find" name="find" class="form-control">
                            <option value="">Escribe el nombre</option>
                        </select>
                    </div>
                    <div class="col-md-12" id="reset-filter" hidden>
                        <a href="{{ route('installments.pendingPay') }}">Reiniciar filtro</a>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12 p-b-20" id="pending-content">
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('scriptJS')
    <!-- typehead TextBox Search -->
    <script src="{{ asset('plugins/bower_components/typeahead.js-master/dist/typeahead.bundle.min.js') }}"></script>
    <script src="{{ asset('js/selectize.js') }}"></script>

    <script type="text/javascript">
        $(function () {
            var configSelect = {
                valueField: 'email',
                searchField: ['name', 'last_name'],
                options: [],
                create: false,
                render: {
                    option: function(item, escape) {
                        return '<p class="m-0">'+item.name+' '+item.last_name+'</p>';
                    },
                    item: function(item, escape) {
                        return '<p class="m-0">'+item.name+' '+item.last_name+'</p>';
                    }
                },
                load: function(query, callback) {
                    if (!query.length) return callback();
                    $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': "{{ csrf_token() }}"
                        },
                        url: "{{ route('installments.findClient') }}",
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            t: $('.type-f:checked').val(),
                            q: query
                        },
                        error: function() {
                            callback();
                        },
                        success: function(res){
                            if(!res.error)
                                callback(res.results);
                            else{
                                if(data.message == 'TOKEN_EXPIRED'){
                                    showMessageAjax(
                                        'alert-danger',
                                        'Su session a expirado, por favor actualice la página.'
                                        );
                                }
                            }
                        }
                    });
                },
                onChange: function(value){
                    if(value){
                        getFilterData(value);
                        $('#reset-filter').attr('hidden', null);
                    }
                }
            };

            function getFilterData(value, detail = false){
                $('.loading-ajax').show();

                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': "{{ csrf_token() }}"
                    },
                    url: "{{ route('installments.getPendingPay') }}",
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        type: $('.type-f:checked').val(),
                        value: value,
                        detail: detail? detail : null
                    },
                    error: function() {
                        showMessageAjax(
                            'alert-danger',
                            'Ocurrio un error consultando los pagos pendientes.'
                        );

                        $('.loading-ajax').hide();
                    },
                    success: function(res){
                        $('.loading-ajax').hide();

                        if(!res.error){
                            $('#pending-content').html(res.html);
                        }else{
                            if(data.message == 'TOKEN_EXPIRED'){
                                showMessageAjax(
                                    'alert-danger',
                                    'Su session a expirado, por favor actualice la página.'
                                );
                            }
                        }
                    }
                });
            }

            $('.type-f').on('click', function(e){
                findObj.clearOptions();
            })

            var find = $('#find').selectize(configSelect),
                findObj = find[0].selectize;

            getFilterData('ALL', '{{ $saleid }}');

        });
    </script>
@stop
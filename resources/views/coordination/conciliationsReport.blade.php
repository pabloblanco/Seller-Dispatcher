@extends('layouts.admin')

@section('customCSS')
    <link rel="stylesheet" href="{{ asset('plugins/bower_components/bootstrap-datepicker/bootstrap-datepicker.min.css') }}">
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
            <h4 class="page-title"> Reporte de conciliaciones </h4>
        </div>
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
                <li><a href="#">Coordinaci&oacute;n</a></li>
                <li class="active">Reporte de conciliaciones.</li>
            </ol>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="white-box">
                <h3 class="box-title">Filtros</h3>
                <form id="concilations-form" method="POST" data-toggle="validator">
                    {{ csrf_field() }}
                    <div class="row">
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
                <div class="row" id="concilations-content" hidden="">
                    <div>
                        <button class="btn btn-success m-b-20" id="exportCsv" type="button">
                            Exportar CSV
                        </button>
                        <a href="#" style="display: none;" id="downloadfile"></a>
                    </div>
                    <div class="col-md-12 p-b-20" id="list-concilations">
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('scriptJS')
    <script src="{{ asset('plugins/bower_components/bootstrap-datepicker/bootstrap-datepicker.min.js') }}"></script>

    <script type="text/javascript">
        $(function () {
            var config = {
                autoclose: true,
                format: 'dd-mm-yyyy',
                language: 'es',
                todayHighlight: true,
                orientation: 'bottom',
                endDate: new Date()
            }

            $('#dateb').datepicker(config);

            $('#datee').datepicker(config);

            function loadList(form = '', action = ''){
                $(".preloader").fadeIn();

                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': "{{ csrf_token() }}"
                    },
                    async: true,
                    url: "{{route('coordination.reportgetConcilations')}}",
                    method: 'POST',
                    data: $('#'+form).serialize(),
                    dataType: 'json',
                    success: function (res) {
                        $(".preloader").fadeOut();

                        if(res.success){
                            if($('#seller').val() == '')
                                $('#seller_email').remove();
                            $('#list-concilations').html(res.html);
                            $('#concilations-content').attr('hidden', null);
                        }else{
                            showMessageAjax('alert-danger', res.msg);
                        }
                    },
                    error: function (res) {
                        $(".preloader").fadeOut();
                        showMessageAjax('alert-danger', 'No se pudieron cargar las activaciones.');
                    }
                });
            }

            $('#do-search').on('click', function(e){
                if($('#seller').val() == '')
                    $('#seller_email').remove();
                loadList('concilations-form');
            });

            $('#exportCsv').on('click', function(e){
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': "{{ csrf_token() }}"
                    },
                    url: "{{ route('coordination.downloadReportConc') }}",
                    type: 'POST',
                    data:$('#concilations-form').serialize(),
                    dataType: 'text',
                    success: function(result){
                        var uri = 'data:application/csv;charset=UTF-8,' + encodeURIComponent(result);
                        var download = document.getElementById('downloadfile');
                        download.setAttribute('href', uri);
                        download.setAttribute('download','rep_conciliacion_'+'{{ date('Ymd') }}'+'.csv');
                        download.click();
                    }
                });
            });

            loadList('concilations-form');
        });
    </script>
@stop
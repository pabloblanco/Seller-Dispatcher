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
            <h4 class="page-title"> Registrar Prospecto </h4>
        </div>
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
                <li><a href="#">Prospectos</a></li>
                <li class="active">Registrar Prospecto.</li>
            </ol>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="white-box">
                <h3 class="box-title">Datos del prospecto</h3>
                @include('client.formRegisterProspect')
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

            function handleError(err) {
                console.warn('ERROR(' + err.code + '): ' + err.message);
            }

            $('#btnGeo').on('click', function(event){
                $('.loading-ajax').show();
                var lon = $('#lon').val().trim();
                var lat = $('#lat').val().trim();
                if (navigator.geolocation){
                    navigator.geolocation.getCurrentPosition(function (funcExito){
                        var lon = funcExito.coords.longitude;
                        var lat = funcExito.coords.latitude;
                        $('#lat').val(lat.toFixed(7));
                        $('#lon').val(lon.toFixed(7));
                        $('.loading-ajax').hide();
                    },handleError, {maximumAge:0});
                }else{
                    $('.loading-ajax').hide();
                    $('#msgAjax').addClass('alert-danger').show();
                    $('#txtMsg').text('No se puede obtener la Geolocalización.');
                    setTimeout(function(){$('#msgAjax').removeClass('alert-success').removeClass('alert-danger').hide(300);},3000);
                }
                
            });

            $('#registerclientform').validator().on('submit', function(e){
                if(e.isDefaultPrevented()){
                    $('.preloader').fadeOut();
                }
            });

            $('#rs-content').hide();

            $('.social').on('click', function(e){
                let s = $(this).val();
                $('#campaign option').attr("selected",false);
                if(s == 'S'){
                    $('#campaign option[value="Campaña al Whatsapp"]').attr("selected",true);
                    $('#rs-content').show();
                }else{
                    $('#rs-content').hide();
                }
            });
        });
    </script>
@stop
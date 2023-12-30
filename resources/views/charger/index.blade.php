@extends('layouts.admin')

@section('content')
    @include('components.messages')
    @include('components.messagesAjax')
    
    @if($service->status == false)
        <div class="alert alert-danger">
            <ul>
                <li>{{ $service->message }}</li>
            </ul>
        </div>  
    @endif
@if($service->charger == true)
        <div id="myCharger" class="modal fade" role="dialog" >
          <div class="modal-dialog" style="margin-top: 180px !important;">
            <!-- Modal content-->
            <div class="modal-content">
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Recarga Exitosa</h4>
              </div>
              <div class="modal-body">
                <div class="row" style="margin-left: 10px;">
                    <div class="m-t-20" >
                        <div class="row">
                            <div class="col-lg-12">
                                <h5>Fecha:</h5> <b>{{$service->date}}</b>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <h5>Nº Transacción:</h5> <b>{{$service->transaction}}</b>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <h5>MSISDN:</h5> <b>{{$service->msisdn}}</b>
                            </div>
                        </div>
                    </div>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal" onclick="location.replace('{{route('charger.index')}}')">Cerrar</button>
              </div>
            </div>

          </div>
        </div>
@endif
    <div class="row bg-title">
        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title"> Recargas de servicios </h4>
        </div>
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
                <li><a href="#">Recargas</a></li>
                <li class="active">Home</li>
            </ol>
        </div>
        <!-- /.col-lg-12 -->
    </div>
    <form name="frmCharger" id="frmCharger" method="POST" action="" id="frmCharger">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <input type="hidden" name="lat" id="lat" value="">
        <input type="hidden" name="lng" id="lng" value="">
    <div class="row">
        <div class="col-md-12">
            <div class="white-box">
                <h3 class="box-title">1.- Verificar msisdn</h3>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="input-group">
                            <input type="text"  name="txtmsisdn" id="txtmsisdn" value="" class="form-control" maxlength="10" placeholder="MSISDN A RECARGAR..." required="true">
                            <span class="input-group-btn">
                                <button class="btn btn-info" id="btnConsulta" type="button">Consultar!!!</button>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="row m-t-20" id="showServices">
                </div>
            </div>
        </div>
    </div>
    </form>
@stop
@section('scriptJS')
    <script src="{{ asset('js/validator.js') }}"></script>

    <script type="text/javascript"> 
        $(function () {
            // Attach initialized event to it
            $(window).load(function(){        
                $('#myCharger').modal('show');
            });

            function handleError(err) {
                doFirstStep({
                    _token: "{{ csrf_token() }}",
                    msisdn: $("#txtmsisdn").val()
                });
            };

            $('#frmCharger').on('submit', function(e){
                if($("#showServices").html().trim() == ''){
                    e.preventDefault();
                    $("#btnConsulta").trigger('click');
                }else{
                    $('#btncharger').attr('disabled', true);
                    $('.loading-ajax').show();
                }
            });

            $("#btnConsulta").click(function(evt, data){
                $('.loading-ajax').show();
                if($("#txtmsisdn").val() != ""){
                    if (navigator.geolocation){
                        navigator.geolocation.getCurrentPosition(function (funcExito){
                            var lon = funcExito.coords.longitude;
                            var lat = funcExito.coords.latitude;
                            $('#lat').val(lat);
                            $('#lng').val(lon);

                            doFirstStep({
                                _token: "{{ csrf_token() }}",
                                msisdn: $("#txtmsisdn").val(),
                                lat:lat,
                                lon:lon
                            });
                        },handleError, {maximumAge:0, timeout:5000});
                    }else{
                        doFirstStep({
                            _token: "{{ csrf_token() }}",
                            msisdn: $("#txtmsisdn").val()
                        });
                    }
                }else{
                    $('.loading-ajax').hide();

                    showMessageAjax('alert-danger', 'Debe ingresar un MSISDN correcto.');
                }
            });

            function doFirstStep(data){
                $.ajax({
                    url: "{{route('charger.find')}}",
                    type: 'POST',
                    data: data,
                    success: function(step1){
                        $('#txtmsisdn').attr('readonly', true);
                        $("#showServices").html(step1);
                        $('.loading-ajax').hide();
                    }
                });
            }
        });
    </script>
@stop
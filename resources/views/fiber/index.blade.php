@extends('layouts.admin')

@section('customCSS')
<link href="{{ asset('plugins/bower_components/bootstrap-datepicker/bootstrap-datepicker.min.css') }}" rel="stylesheet"/>
<link href="{{ asset('plugins/bower_components/clockpicker/dist/jquery-clockpicker.min.css') }}" rel="stylesheet"/>
{{--
<link href="{{ asset('plugins/bower_components/typeahead.js-master/dist/typehead-min.css') }}" rel="stylesheet"/>
--}}
<link href="{{ asset('plugins/bower_components/dropify/dist/css/dropify.min.css') }}" rel="stylesheet"/>
<link href="{{ asset('css/selectize.css') }}" rel="stylesheet"/>
<link href="{{ asset('css/selectize.bootstrap.css') }}" rel="stylesheet"/>
<link href="{{ asset('plugins/bower_components/bootstrap4-toggle/bootstrap4-toggle.min.css') }}" rel="stylesheet"/>
<link href="{{ asset('css/flash.min.css') }}" rel="stylesheet"/>

@stop

@section('content')
  @include('components.messages')
  @include('components.messagesAjax')

  @if ($errors->any())
<div class="alert alert-danger">
  <ul>
    @foreach ($errors->all() as $error)
    <li>
      {{ $error }}
    </li>
    @endforeach
  </ul>
</div>
@endif
<div class="row bg-title">
  <div class="col-lg-3 col-md-4 col-sm-4 col-12 text-sm-left text-center">
    <h4 class="page-title">
      Módulo de ventas
    </h4>
  </div>
  <div class="col-lg-9 col-md-8 col-sm-8 col-12 d-flex justify-content-sm-end justify-content-center">
    <ol class="breadcrumb">
      <li>
        <a href="#">
          Ventas
        </a>
      </li>
      <li class="active">
        Venta fibra.
      </li>
    </ol>
  </div>
</div>
<div class="row">
  @if($lock->is_locked == 'Y')
  <div class="col-md-12">
    <div class="white-box">
      <div class="alert alert-danger">
        <p>
          <b>
            Has sido bloqueado
          </b>
          , por favor comunicate con tu supervisor.
        </p>
      </div>
    </div>
  </div>
  @else
  <div class="col-md-12">
    <div class="white-box">
      @if(hasPermit('RCL-DSE'))
      <div class="text-right p-b-10">
        <a class="btn btn-success waves-effect waves-light" data-target="#register-modal" data-toggle="modal" href="#">
          Registrar Prospecto
        </a>
      </div>
      @endif
      <h3 class="box-title">
        Consultar Prospecto
      </h3>
      <form class="form-horizontal" data-toggle="validator" id="form-reg-inst">
        {{ csrf_field() }}
        <div class="form-group">
          <label class="col-md-12">
            Buscar
          </label>
          <div class="col-md-12">
            <select class="form-control" id="buscar" name="buscar" placeholder="Escribe el Nombre o DN del Prospecto" required="">
            </select>
            <div class="help-block with-errors">
            </div>
          </div>
        </div>
        <div class="form-group" id="showClient">
        </div>
        <div hidden="true" id="installer-content">
          {{--
          <h3 class="box-title">
            Asignar instalador
          </h3>
          <div class="form-group" hidden="true" id="insta-content">
            <label class="col-md-12">
              Buscar
            </label>
            <div class="col-md-12">
              <select class="form-control" id="installer" name="installer" placeholder="Escribe el Nombre o correo del instalador." required="">
              </select>
              <div class="help-block with-errors">
              </div>
            </div>
          </div>
          --}}
          <div class="form-group" hidden="true" id="cal-content">

          </div>
          {{--
          <div class="form-group" hidden="true" id="pay-content">
            <label>
              ¿Pago realizado?
            </label>
            <div class="col-md-12">
              <label class="custom-control custom-radio">
                <input checked="" class="custom-control-input" name="paycheck" type="radio" value="N"/>
                <span class="custom-control-indicator">
                </span>
                <span class="custom-control-description">
                  No
                </span>
              </label>
              <label class="custom-control custom-radio">
                <input class="custom-control-input" name="paycheck" type="radio" value="Y"/>
                <span class="custom-control-indicator">
                </span>
                <span class="custom-control-description">
                  Si
                </span>
              </label>
            </div>
          </div>
          --}}
          <div class="form-group" hidden="true" id="photo-content">
            <label class="col-md-12">
              Foto de referencia del domicilio
            </label>
            <input accept="image/png, image/jpeg, image/jpg" class="dropify" data-allowed-file-extensions="png jpg jpeg" data-max-file-size="3M" data-show-loader="true" id="photo" name="photo" type="file"/>
            <div class="help-block with-errors">
            </div>
          </div>
          <div class="form-group" hidden="true" id="forzoso-content">
            <div class="alert alert-danger alert-dismissable">
              <button aria-hidden="true" class="close" data-dismiss="alert" type="button">
                ×
              </button>
              <span>
                <h4>
                  <strong>
                    Importante:
                  </strong>
                </h4>
                  &#128679; Antes de continuar debe contar con:
              </span>
              <br>
              <li>
                <strong>1 -</strong> Factura o recibo donde refleje la dirección donde sera instalado el servicio
              </li>
              <li>
                <strong>2 -</strong> Un documento de identidad (Ine, Pasaporte, Curp) u otro documento de identificación oficial
              </li>
              <li>
                <strong>3 -</strong> Contar con un telefono celular para la recepción del enlace de contrato de adhesión.
              </li>
              <li>
                <strong>4 -</strong> Leer contrato de adhesión.
              </li>
            </div>
            @handheld
            <div class="alert alert-primary alert-dismissable">
              <button aria-hidden="true" class="close" data-dismiss="alert" type="button">
                ×
              </button>
              <span>
                <h4>
                  <strong>
                    Atención:
                  </strong>
                </h4>
                  &#128706; A continuación debes sacar unas fotografías, por favor selecciona la cámara de tu dispositivo que sea más acorde para obtener la mejor calidad.
              </span>
            </div>
            @endhandheld

            <div class="row justify-content-center pb-5 pt-1 @handheld d-block @elsehandheld d-none @endhandheld">
              <select id="audioSource" hidden></select>
              <label>Seleccione la camara</label>
              <select class="form-control" id="video_source" required></select>
            </div>
            <div class="row justify-content-center text-center" hidden id="block_recibo">
              <label class="col-md-12">1 - Recibo de un servicio &#128209;</label>
              <div class="col-md-8">
                @include('fiber.camphoto', ['current'=> 'recibo', 'next'=>'identiF'])
              </div>
            </div>
            <div id="block_document" hidden="true">
              <label>
                2 - Documento de identidad &#128706;
              </label>
              <div class="row justify-content-center text-center">
                <div class="col-md-8 col-12 text-center" id="block_phot_identiF" >
                  <label>Vista frontal</label>
                  <div class="col-md-12">
                  @include('fiber.camphoto', ['current'=> 'identiF', 'next'=>'identiP'])
                  </div>
                </div>
                <div class="col-md-8 col-12 text-center" id="block_phot_identiP" hidden="true">
                  <label>Vista posterior</label>
                  <div class="col-md-12">
                  @include('fiber.camphoto', ['current'=> 'identiP', 'next'=>'end'])
                  </div>
                </div>
                <div class="col-md-12 py-5" id="block_number_identi" hidden="true">

                </div>
              </div>
            </div>
            <div class="row justify-content-center" id="block_tyc" hidden="true">
              <label class="col-md-12">3 - Contrato de adhesión</label>

              <div class="col-md-8 text-center py-4" id="btnqr_tyc">
                <button class="btn btn-success waves-effect waves-light" id="requestQr_tyc" type="button">
                Generar contrato de adhesión
                </button>
              </div>
              {{--Si esta verificado se envia el SMS--}}
              {{--Si no esta verificado se debe compartir el QR--}}

              <div class="col-md-8 text-center py-4" id="qr_tyc">
              </div>
              <div class="col-md-12 text-right" id="block_share_tyc">
              </div>

              <input type="hidden" name="tyc" id="tyc" value="">

              <div class="col-md-8 text-center py-4" hidden id="btnqr_tyc_resend">
                <div id="txt_resend"> Tiempo de espera para solicitar reenvio de contrato <span id="hms_resend"> </span>
                </div>
                <button class="btn btn-success waves-effect waves-light" hidden id="requestQr_tyc_resend" type="button" title="Reenvio de SMS con el contrato de adhesion">
                Reenviar SMS contrato de adhesión
                </button>
              </div>
            </div>
          </div>
          <div class="refered-container row mb-5">
            <div class="col-md-12">
              <label>
                Venta por Referido:
              </label>
              <div class="form-group px-3">
                <label class="custom-control custom-radio">
                  <input checked="" class="custom-control-input" name="refopt" id="refN" type="radio" value="N"/>
                  <span class="custom-control-indicator">
                  </span>
                  <span class="custom-control-description">
                    No
                  </span>
                </label>
                <label class="custom-control custom-radio">
                  <input class="custom-control-input" name="refopt" id="refY" type="radio" value="Y"/>
                  <span class="custom-control-indicator">
                  </span>
                  <span class="custom-control-description">
                    Si
                  </span>
                </label>
              </div>
            </div>
            <div class="col-md-12 form-group refered-container-data d-none">
              <div class="row">
                <div class="col-12 col-md-6">
                  <label for="">
                    MSISDN de referencia
                  </label>
                  <input class="form-control msisdn-ref" id="msisdn-ori-ref" minlength="10" maxlength="10" placeholder="msisdn de referencia" type="number"/>
                </div>
                <div class="col-12 col-md-6">
                  <label for="">
                    Confirma MSISDN de referencia
                  </label>
                  <input class="form-control msisdn-ref" id="msisdn-rep-ref" minlength="10" maxlength="10" placeholder="confirma el msisdn de referencia" type="number"/>
                </div>
              </div>
            </div>
            <div class="col-12 refered-client-data d-none">
              <input id="msisdn_refered" name="msisdn_refered" type="hidden"/>
              <label for="">
                Referido por:
              </label>
              <p id="name_ref">
              </p>
              <p id="email_ref">
              </p>
            </div>
          </div>
          <div class="form-group">
            <div class="col-md-12 m-t-20">
              <button class="btn btn-success waves-effect waves-light" disabled="true" id="reg-install" type="submit">
                Registrar solicitud de instalación
              </button>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
  @endif
</div>
@if($lock->is_locked == 'N')
<div aria-hidden="true" aria-labelledby="myModalLabel" class="modal fade" id="register-modal" role="dialog" style="display: none;" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button aria-hidden="true" class="close" data-dismiss="modal" type="button">
          ×
        </button>
        <h4 class="modal-title">
          Registrar prospecto.
        </h4>
      </div>
      <div class="modal-body">
        @include('client.formRegisterProspect')
      </div>
      <div class="modal-footer">
        <button class="btn btn-default waves-effect" data-dismiss="modal" type="button">
          Cerrar
        </button>
      </div>
    </div>
  </div>
</div>
<div aria-hidden="true" aria-labelledby="myModalLabel" class="modal fade my-4" id="install-confirmation" role="dialog" style="display: none;" tabindex="-1">
  <div class="modal-dialog py-5" @mobile style="top: 330px;" @else style="top: 230px;" @endmobile >
    <div class="modal-content">
      <div class="modal-header">
        <button aria-hidden="true" class="close" data-dismiss="modal" type="button">
          ×
        </button>
        <h4 class="modal-title">
          Confirmar solicitud de instalación
        </h4>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-12 ">
            <label>
              Datos del Cliente.
            </label>
          </div>
        </div>
        <div class="row px-3">
          <div class="col-12 col-md-6 ">
            <label>
              Nombre:
            </label>
            <p class="name-client">
            </p>
          </div>
          <div class="col-12 col-md-6 ">
            <label>
              ID del cliente:
            </label>
            <p class="ine-client">
            </p>
          </div>
        </div>
        <div class="row">
          <div class="col-12 ">
            <hr class="mb-4 mt-0">
              <label>
                Paquete de Instalación.
              </label>
            </hr>
          </div>
        </div>
        <div class="row px-3">
          <div class="col-12 col-md-6 ">
            <label>
              Plan:
            </label>
            <p class="plan-client">
            </p>
          </div>
          <div class="col-12 col-md-6 ">
            <label>
              Descripción:
            </label>
            <p class="plan-description-client">
            </p>
          </div>
        </div>

        @include('fiber.detailResumenAgenda',['type'=>'F','title'=>'Fibra'])
        @include('fiber.detailResumenAgenda',['type'=>'H','title'=>'Hogar'])
        @include('fiber.detailResumenAgenda',['type'=>'M','title'=>'Mifi'])
        @include('fiber.detailResumenAgenda',['type'=>'MH','title'=>'Mifi Huella'])
        @include('fiber.detailResumenAgenda',['type'=>'T','title'=>'Telefonia'])

        <div class="row px-3" id="totalPay" hidden>
          <label><strong></strong></label>
        </div>

        <div class="row px-3" id="block_all">
          <div class="col-12 col-md-6 ">
            <label>
              Producto:
            </label>
            <p class="product-client_">
            </p>
          </div>
          <div class="col-12 col-md-6">
            <label>
              Servicio:
            </label>
            <p class="service-client_">
            </p>
          </div>
          <div class="col-12 col-md-6 ">
            <label>
              Precio:
            </label>
            <p class="price-client_">
            </p>
          </div>
        </div>
        <hr>
        <div class="row px-3">
          <div class="col-12 col-md-6 ">
            <label>
              Fecha de instalación:
            </label>
            <p class="date-client">
            </p>
          </div>
          <div class="col-12 col-md-6 ">
            <label>
              Horario de instalación:
            </label>
            <p class="schedule-client">
            </p>
          </div>
        </div>
        <div class="row">
          <div class="col-12 ">
            <hr class="mb-4 mt-0">
              <label>
                Dirección de Instalación.
              </label>
            </hr>
          </div>
        </div>
        <div class="row px-3">
          <div class="col-12 col-md-6">
            <label>
              Estado:
            </label>
            <p class="state-install">
            </p>
          </div>
          <div class="col-12 col-md-6 ">
            <label>
              Municipio:
            </label>
            <p class="muni-install">
            </p>
          </div>
          <div class="col-12 col-md-6">
            <label>
              Ciudad:
            </label>
            <p class="city-install">
            </p>
          </div>
          <div class="col-12 col-md-6 ">
            <label>
              Colonia:
            </label>
            <p class="colony-install">
            </p>
          </div>
          <div class="col-12 col-md-6 ">
            <label>
              Calle:
            </label>
            <p class="route-install">
            </p>
          </div>
          <div class="col-12 col-md-6 ">
            <label>
              Número de casa:
            </label>
            <p class="number-install">
            </p>
          </div>
          <div class="col-12 col-md-6 ">
            <label>
              Referencia:
            </label>
            <p class="reference-install">
            </p>
          </div>
          {{--
          <div class="col-md-12">
            <label>
              Dirección de instalación
            </label>
            --}}
            {{--
            <p class="address-client">
            </p>
            --}}
          {{--
          </div>
          --}}
        </div>
        <div class="row">
          <div class="col-12 ">
            <hr class="mb-4 mt-0">
              <label>
                Información de conexión.
              </label>
            </hr>
          </div>
          <div class="col-12 col-md-6 px-4">
            <label>
              Olt de fibra:
            </label>
            <p class="olt-install">
            </p>
          </div>
         {{-- <div class="col-12 col-md-6 px-3">
            <label>
              Nodo de conexión:
            </label>
            <p class="node-install">
            </p>
          </div>--}}
        </div>
        <div class="row" hidden="true" id="info-refered-content">
          <div class="col-12 px-3">
            <hr class="mb-4 mt-0">
              <label>
                Referido por:
              </label>
            </hr>
          </div>
          <div class="col-12">
            <p class="refered-name">
            </p>
            <p class="refered-email">
            </p>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-default waves-effect" data-dismiss="modal" type="button">
          Cancelar
        </button>
        <button class="btn btn-danger waves-effect waves-light" id="pro-install" type="button">
          Procesar
        </button>
      </div>
    </div>
  </div>
</div>
@endif
@stop

@section('scriptJS')
@if($lock->is_locked == 'N')
<script src="{{ asset('plugins/bower_components/bootstrap-datepicker/bootstrap-datepicker.min.js') }}">
</script>
{{--
<script src="{{ asset('plugins/bower_components/clockpicker/dist/jquery-clockpicker.min.js') }}">
</script>
--}}
<script src="{{ asset('js/validator.js') }}">
</script>
<script src="{{ asset('js/sweetalert.min.js') }}">
</script>
<script src="{{ asset('plugins/bower_components/dropify/dist/js/dropify.min.js') }}">
</script>
<script src="{{ asset('js/selectize.js')}}">
</script>
<script src="{{ asset('https://maps.googleapis.com/maps/api/js?key='.env('GOOGLE_KEY').'&libraries=places') }}">
</script>
<script src="{{ asset('plugins/bower_components/bootstrap4-toggle/bootstrap4-toggle.min.js') }}">
</script>
<script async src="{{ asset('js/camFiber_v2.js') }}">
</script>
<script type="text/javascript">
/*  function resetInstaladores(){
    $('#installer').val('');
    $('#installer').data('selectize').setValue("");
    $('#installer').data('selectize').clearOptions();
  }*/
  function getClient(dni){
    $('.loading-ajax').show();
    doPostAjax(
      '{{ route('sellerFiber.showClient') }}',
        function(res){
          client(res);
        },
      {dni: dni, _token: '{{ csrf_token() }}'}
    );
  }
  function client(res){
    $('.loading-ajax').fadeOut();

    resetForm();

    if(!res.error){

      $("#showClient").html('');
      $("#showClient").html(res.html);
      ResetViewFiberCite('plan');

    }else{
      if(res.message == 'TOKEN_EXPIRED'){
          showMessageAjax('alert-danger','Su session a expirado, por favor actualice la página.');
      }else{
          showMessageAjax('alert-danger', 'No se consiguio el prospecto.');
      }
    }
  }
  function resetForm(ignoreShow=true){
        //ocultando secciones
        $('#installer-content').attr('hidden', true);
        //$('#insta-content').attr('hidden', true);
        $('#cal-content').attr('hidden', true);
        $('#cal-content').html('');
        //$('#pay-content').attr('hidden', true);
        $('#reg-install').attr('disabled', true);
        $('#photo-content').attr('hidden', true);
        //Reseteo la foto
        var drEvent = $('#photo').dropify();
        drEvent = drEvent.data('dropify');
        drEvent.resetPreview();
        drEvent.clearElement();
        if(ignoreShow){
          $('#showClient').html('');
        }
        //limpieza del selector de instaladores
      // resetInstaladores();
      }

    function runElement(elemt, textMsg=false, movimiento=50){
      if(textMsg){
        //Ir a la posicion y alertar
        swal({
          title: "Verifica por favor la información del registro de cita",
          text: textMsg,
          icon: "warning",
          dangerMode: true,
          buttons: {
            confirm: {
              text: 'OK',
              visible: true,
              value: 'ok'
            }
          }
        }).then((value) => {
            if(value == 'ok'){
              var posicion = $(elemt).offset().top - movimiento;
              $("html, body").animate({
                  scrollTop: posicion
              }, 1000);
            }
          });
      }else{
        //Solo ir a la posicion
        var posicion = $(elemt).offset().top - movimiento;
          $("html, body").animate({
            scrollTop: posicion
        }, 1000);
      }
    }

    function verifyQrClient(){
      $('.loading-ajax').show();
      return new Promise((resolve, reject) => {
        doPostAjax(
          '{{ route('sellerFiber.verifyQr') }}',
          function(res){
            //$('.loading-ajax').fadeOut();
            return resolve(res);

           /* if(!res.success){
              swal({
                title: res.title,
                text: res.msg,
                icon: res.icon
              });
              return 0;
            }*/
          },
          {
            dni: $('#buscar').val(),
            tyc: $('#tyc').val()
          },
          $('meta[name="csrf-token"]').attr('content')
        );
      });
    }

    function changerViewBlockPlan(view, obj){
      $('#install-confirmation .product-client_'+obj).text($('#txt-product_'+obj).text().trim());
      $('#install-confirmation .service-client_'+obj).text($('#txt-service_'+obj).text().trim());
      $('#install-confirmation .price-client_'+obj).text($('#txt-price_'+obj).text().trim());
      var element = (obj==='')?'all':obj;
      $('#block_'+element).attr('hidden', (view)? true : null);
    }

    function hidePlanType(){
      $('#block_H').attr('hidden', true);
      $('#block_M').attr('hidden', true);
      $('#block_MH').attr('hidden', true);
      $('#block_T').attr('hidden', true);
      $('#block_F').attr('hidden', true);
      $('#block_all').attr('hidden', true);
      $('#totalPay').attr('hidden', true);
    }

    function loadConfirmPlanNormal(){
      hidePlanType();
      changerViewBlockPlan(false, '');
    }

    function loadConfirmPlanBundle(){
      hidePlanType();

      if($('#title_plan_H').length){
        changerViewBlockPlan(false, 'H');
      }
      if($('#title_plan_M').length){
        changerViewBlockPlan(false, 'M');
      }
      if($('#title_plan_MH').length){
        changerViewBlockPlan(false, 'MH');
      }
      if($('#title_plan_T').length){
        changerViewBlockPlan(false, 'T');
      }
      if($('#title_plan_F').length){
        changerViewBlockPlan(false, 'F');
      }

      $('#install-confirmation #totalPay strong').text($('#bundlepay').text().trim());
      $('#totalPay').attr('hidden', null);
    }

    function Phone_Verify_Autorized(){
      //la funcion cantToken en el campo code puedo conocer el status que tiene el telefono junto al cliente si esta autorizado o si esta verificado el celular para agendar la cita
      //
      $('.loading-ajax').show();
      return new Promise((resolve, reject) => {

      $.ajax({
        type: 'POST',
        url: "{{route('sellerFiber.cantToken')}}",
        dataType: "json",
        data: {
          _token: "{{ csrf_token() }}",
          phoneVal: $("#msisdn-contact").val(),
          clientId: $(".client-dni").text().trim()
        },
        success: function(res) {
          if(res.success){
            return resolve(res.code);
          }else{
            $('.loading-ajax').hide();
            @handheld
              swal({
                title: res.title,
                text: res.msg,
                icon: 'warning'
              });
            @elsehandheld
              var tipy="alert-danger";
              showMessageAjax(tipy, res.msg);
            @endhandheld
          }
          return resolve(0);
        },
        error: function() {
          $('.loading-ajax').hide();
          var textmsg = "Intenta nuevamente para conocer el status del telefono de contacto";
          @handheld
            swal({
              title: "Ups Ocurrio un error",
              text: textmsg,
              icon: 'warning'
            });
          @elsehandheld
            var tipy="alert-danger";
            showMessageAjax(tipy, textmsg);
          @endhandheld
          return resolve(0);
        }
      });
    });
      //return "AUTHORIZED";
      //return "VERIFIED";
    }

  function mostrarQrContract(code){
    //console.log("code: "+code);
    if(code == "AUTHORIZED" || code == "VERIFIED"){

      var titleSwal='', bodySwal='';
      if(code == "AUTHORIZED"){
        titleSwal = "Seguro de continuar?";
        bodySwal = "A continuación se generara el contrato de adhesión del servicio de fibra, una vez generado no se pueden editar ni cambiar la información de las fotografías que acabas de adjuntar"
      }else{
        titleSwal = "Se hara uso de SMS para enviar el contrato!";
        bodySwal = "A continuación se generara el contrato de adhesión del servicio de fibra y sera enviado por medio de SMS al telefono del cliente que se verifico al inicio del proceso de venta, una vez generado no se pueden editar ni cambiar la información de las fotografías que acabas de adjuntar"
      }

      swal({
        title: titleSwal,
        text: bodySwal,
        icon: "info",

        buttons: {
          cancel: {
            text: "Cancelar",
            value: 'cancel',
            visible: true,
            className: "btn btn-primary",
            closeModal: true,
            botonesStyling: false
          },
          ok: {
            text: "continuar",
            value: 'ok',
            visible: true,
            className: "btn btn-danger",
            closeModal: true,
            botonesStyling: false
          },
        },
        dangerMode: true,
     }).then((result) => {
        //alert(result);
        if (result == 'ok') {
          disableinputPhotos(true);
          generateQr(code);
        }
      });
    }else{
      swal({
        title: "No se puede continuar con el agendamiento",
        text: "El telefono de contacto del cliente no se ha verificado ni autorizado para proseguir",
        icon: "info"
      });
    }
  }

  $(function () {
      //Foto de referencia
      let messagesDF = {
        default: 'Click o arrastre una imágen para subirla',
        replace: 'Reemplazar imágen',
        remove: 'Borrar',
        error: 'Error al cargar imágen'
      };

      var df = $('#photo').dropify({
        messages: {
          default: 'Click o arrastre una imágen para subirla',
          replace: 'Reemplazar imágen',
          remove: 'Borrar',
          error: 'Error al cargar imágen'
        }
      });

      df.on('dropify.errors', function(event, element) {
        return swal({
          title: "Error en Evidencia",
          text: "Hubo un problema al cargar la evidencia. Verifica que el tamano no exceda los 3mb y sea un archivo de extension (png, jpg, o jpeg)",
          icon: "warning"
        });
      });

      /*let client = function(res){
        $('.loading-ajax').fadeOut();
        //FUNCION DEPRECADA
        resetForm();

        if(!res.error){
          $("#showClient").html(res.html);
          ResetViewFiberCite('states');

        }else{
          if(res.message == 'TOKEN_EXPIRED'){
              showMessageAjax('alert-danger','Su session a expirado, por favor actualice la página.');
          }else{
              showMessageAjax('alert-danger', 'No se consiguio el prospecto.');
          }
        }
      }*/

      //Agenda
     {{-- $('#calendar').datepicker({
          firstDay: 1,
          language: 'es',
          todayHighlight: true,
          format: 'dd-mm-yyyy',
          startDate: new Date()
          //daysOfWeekDisabled:[0,6]
      }).on('changeDate', function (selected) {
          var date = selected.date,
              month = ((date.getMonth() + 1) < 10) ? ('0' + (date.getMonth() + 1 )) : (date.getMonth() + 1 ),
              day = (date.getDate() < 10) ? ('0' + date.getDate()) : date.getDate(),
              fecha = date ? date.getFullYear() + '-' + month + '-' + day : '';

          $('#dateCalendar').val(fecha);
          $('#pay-content').attr('hidden', null);
          $('#reg-install').attr('disabled', null);
          $('#photo-content').attr('hidden', null);
          $('#error-calendar').text('');
      });--}}

      {{-- $('.clockpicker').clockpicker({
          donetext: 'Seleccionar',
          default: 'now',
          placement: 'bottom',
          autoclose: true
      }); --}}

      {{--$('#hour').selectize();--}}

      //Busqueda de instaladores
      /*let $installer = $('#installer').selectize({
        valueField: 'email',
        searchField: ['name', 'last_name', 'email', 'info'],
        labelField: 'info',
        render: {
            option: function(item, escape) {
              return '<p>'+escape(item.info)+'</p>';
            }
        },
        load: function(query, callback) {
          if (!query.length) return callback();

          doPostAjax(
            '{ route('seller.findInstaller') }}',
            function(res){
              if(!res.error){
                callback(res);
              }else{
                callback();
              }
            },
            {
              search: query,
              pack_id: $('#plan').val()
            },
            $('meta[name="csrf-token"]').attr('content')
          );
        }
      });*/
      /*
      $('#installer').on('change', function(e){
        let val = $(this).val();

        if(val && val != ''){
          $('#cal-content').attr('hidden', null);
        }
      });*/

      {{--@handheld
      $('#video_source').on('change', function(e){
        let val = $(this).val();
        if(val && val != ''){

        }
      });
      @endhandheld--}}

      $(".refered-container input:radio[name=refopt]").on('change',function(){
        if($(this).val()=='Y'){
          $('.refered-container-data').removeClass('d-none');
        }
        else{
          $('.refered-container-data').addClass('d-none');
          $('.refered-client-data').addClass('d-none');
          $('#msisdn-ori-ref').val("");
          $('#msisdn-rep-ref').val("");
          $('#msisdn_refered').val("");
          $('#name_ref').html("");
          $('#email_ref').html("");
        }
      });

      $('.msisdn-ref').on('blur', function(e) {
        // console.log('1-> '+$('#msisdn-ori-ref').val());
        // console.log('2-> '+$('#msisdn-rep-ref').val());

        if($('#msisdn-ori-ref').val().trim().length == 10 && $('#msisdn-rep-ref').val().trim().length == 10 && $('#msisdn-ori-ref').val().trim() == $('#msisdn-rep-ref').val().trim()){

          $('.loading-ajax').show();

          $.ajax({
            type: 'POST',
            url: "{{route('client.getByDn')}}",
            dataType: "json",
            data: {
              _token: "{{ csrf_token() }}",
              msisdn: $('#msisdn-ori-ref').val(),
            },
            success: function(res) {
              $('.loading-ajax').hide();
              if(res.error){
                var textmsg = 'No se conseguieron registros para el MSISDN: '+($('#msisdn-ori-ref').val());
                @handheld
                  swal({
                    title: "Hay un problema con el msisdn a referir",
                    text: textmsg,
                    icon: 'warning'
                  });
                @elsehandheld
                    var tipy="alert-danger";
                    showMessageAjax(tipy, textmsg);
                @endhandheld
              }
              else{
                $('.refered-container-data').addClass('d-none');
                $('.refered-client-data').removeClass('d-none');
                $('#msisdn_refered').val($('#msisdn-ori-ref').val());
                $('#name_ref').html('<strong>Cliente:</strong> '+ res.client.name+" "+res.client.last_name);
                $('#email_ref').html('<strong>Email:</strong> '+ res.client.email);
              }
            },
            error: function() {
              $('.loading-ajax').hide();
              showMessageAjax('alert-danger', 'Ups Ocurrio un error, por favor intenta de nuevo.');
            }
          });
        }
      });


      //Monstrando confirmación de solicitud de cita previo a ser enviado a mesa
      //validator().on
      $('#form-reg-inst').on('submit', function(e){
        if(!e.isDefaultPrevented()){
          e.preventDefault();

          var failData = false;
          var textMsg = '';
          var elemt = '';
          if($('#reference').val()==''){
            elemt = '#reference';
            failData = true;
            textMsg="Debe proporcionar una referencia a la direccion suministrada.";
          }
          if($('#typePlan_bundle').length){
            //si existe se verifica
            if( $('#typePlan_bundle').is(':checked')){
              if($('#imei').val() != $('#imei_copy').val()){
                 elemt = '#imei';
                failData = true;
                textMsg = "Se debe validar el nuevo IMEI ingresado.";
              }
            }
          }
          if($('#dateCalendar').val()=='' && !failData){
            elemt = '#calendar';
            failData = true;
            textMsg="Debe seleccionar una fecha para agendar la cita.";
            $('#error-calendar').text(textMsg);
          }
          if($('#hour').val()=='' && !failData){
            elemt = '#hour';
            failData = true;
            textMsg="Debes indicar la hora de agendamiento";
            $('#error-hours').text(textMsg);
          }

          if($('input[name=refopt]:checked').val()=='Y'){
            if($('#msisdn-ori-ref').val()=='') {
              elemt = '#msisdn-ori-ref';
              failData = true;
              textMsg="Debes indicar MSISDN de referencia";
            }
            $("#info-refered-content").attr("hidden",null);
          }

          if(failData){
            runElement(elemt, textMsg);
            return 0;
          }

          if($('#olt').val() != '' && $('#city').length){
            $('#install-confirmation .name-client').text($('.client-name-full').text().trim());
            $('#install-confirmation .ine-client').text($('.client-dni').text().trim());
            $('#install-confirmation .plan-client').text($('#plan').data('description'));
            $('#install-confirmation .plan-description-client').text($('#txt-plan-description_').text().trim());

            if($('#typePlan_bundle').length){
                //si existe se verifica
                if( $('#typePlan_bundle').is(':checked')){
                  //paquete bundle
                  loadConfirmPlanBundle();
                }else{
                  loadConfirmPlanNormal();
                }
            }else{
              loadConfirmPlanNormal();
            }
            //$('#install-confirmation .address-client').text($('#address').val().trim());
            $('#install-confirmation .route-install').text($('#route').val().trim() != '' ? $('#route').val().trim() : 'S/I');
            $('#install-confirmation .number-install').text($('#numberhouse').val().trim() ? $('#numberhouse').val().trim() : 'S/I');
            $('#install-confirmation .colony-install').text($('#colony').val().trim() ? $('#colony').val().trim() : 'S/I');
            $('#install-confirmation .city-install').text($('#city option:selected').text().trim() ? $('#city option:selected').text().trim() : 'S/I');
            $('#install-confirmation .olt-install').text($('#olt option:selected').text().trim() ? $('#olt option:selected').text().trim() : 'S/I');
            /*$('#install-confirmation .node-install').text($('#nodo_red option:selected').text().trim() ? $('#nodo_red option:selected').text().trim() : 'Por establecer...');*/
            $('#install-confirmation .muni-install').text($('#muni').val().trim() ? $('#muni').val().trim() : 'S/I');
            $('#install-confirmation .state-install').text($('#stateF option:selected').text().trim() ? $('#stateF option:selected').text().trim() : 'S/I');
            $('#install-confirmation .reference-install').text($('#reference').val().trim() ? $('#reference').val().trim() : 'S/I');
            $('#install-confirmation .refered-name').text($('#name_ref').text());
            $('#install-confirmation .refered-email').text($('#email_ref').text());

            //fecha de agendamiento
            //let dateobj = $('#calendar').datepicker('getDate');
            //let date = dateobj.getDate()+'-'+(dateobj.getMonth() + 1)+'-'+dateobj.getFullYear();
            //atob
            $('.date-client').text($('#dateCalendar').val());
            $('.schedule-client').text($('#hour option:selected').text().trim());

            //Se evalua si es un plan forzoso y si el cliente acepto los terminos y condiciones para continuar
            if($('#typePlan').length){
              if($('#typePlan').is(':checked')){
                //forzoso
                let isAceptClient = verifyQrClient();
                isAceptClient.then(function(promesa_tyc) {
                  $('.loading-ajax').fadeOut();
                  if(!promesa_tyc['success']){
                    swal({
                      title: promesa_tyc['title'],
                      text: promesa_tyc['msg'],
                      icon: promesa_tyc['icon']
                    });
                    return false;
                  }else{
                    $('#install-confirmation').modal('show');
                  }
                });
              }else{
                $('#install-confirmation').modal('show');
              }
            }else{
              $('#install-confirmation').modal('show');
            }
            //End de evaluacion de plan forzoso
          }else{
            console.log('Faltan datos de la OLT');
          }
        }
      });

      $('#requestQr_tyc').on('click', function(e){
        if($('#typePlan').is(':checked')){
          //Planes forzosos
          var  failData = false;
          if(sessionStorage.getItem('photo-recibo')=='false'){
            elemt = '#block_recibo';
            failData = true;
            textMsg="Fotografia de recibo requerido";
            $('#error-photo-recibo').text(textMsg);
          }
          if(sessionStorage.getItem('photo-identiF')=='false' && !failData){
            elemt = '#block_phot_identiF';
            failData = true;
            textMsg="Fotografia frontal del documento de identidad es requerido";
            $('#error-photo-identiF').text(textMsg);

          }
          if(sessionStorage.getItem('photo-identiP')=='false' && !failData){
            elemt = '#block_phot_identiP';
            failData = true;
            textMsg="Fotografia posterior del documento de identidad es requerido";
            $('#error-photo-identiP').text(textMsg);
          }
          if($('#typeDocument').val()=='' && !failData){
            elemt = '#typeDocument';
            failData = true;
            textMsg="Debes indicar el tipo de documento de identidad adjuntado";
            $('#error-typeDocument').text(textMsg);
          }

          var valid = validateDocument($('#identification').val().trim(), $('#typeDocument').val());
           // console.log(valid);
          if(!valid['success']){
            elemt = '#identification';
            failData = true;
            textMsg = valid['msg'];
            $('#error-identification').text(textMsg);
          }

          if(failData){
            runElement(elemt, textMsg);
            return 0;
          }else{
            //Se evalua si esta verificado o si esta autorizado

            let isValidPhone = Phone_Verify_Autorized();
            isValidPhone.then(function(promesa_phone) {
              $('.loading-ajax').hide();
              mostrarQrContract(promesa_phone);
            });
          }
        }
      });

      //Enviando formulario de registro de cita a mesa de control
      $('#pro-install').on('click', function(e){
        $('#install-confirmation').modal('hide');

        $('.loading-ajax').fadeIn();

        let data = new FormData();
        data.append('client', $('#buscar').val().trim());
        //data.append('adress', $('#address').val().trim());
        data.append('pack', $('#plan').val().trim());
       // data.append('installer', $('#installer').val().trim());
        data.append('date', $('#dateCalendar').val().trim());
        data.append('hour', $('#hour').val());
        data.append('pay', 'N');//$('input[name=paycheck]:checked').val().trim()
        data.append('route', $('#route').val().trim());
        data.append('numberhouse', $('#numberhouse').val().trim());
        data.append('colony', $('#colony').val().trim());
        data.append('city', $('#city option:selected').text().trim());
        data.append('city_id', $('#city').val().trim());
        data.append('zone_id', $('#olt').val().trim());
        //data.append('node_red_name', $('#nodo_red option:selected').text().trim());
        //data.append('node_red', $('#nodo_red').val().trim());
        data.append('muni', $('#muni').val().trim());
        data.append('state', $('#stateF option:selected').text().trim());
        data.append('state_id', $('#stateF').val().trim());
        data.append('reference', $('#reference').val().trim());
        data.append('lat', $('#lat_OK').val());
        data.append('lng', $('#lng_OK').val());

        if(document.getElementById('photo').files[0]){
          data.append('photo', document.getElementById('photo').files[0]);
        }

        if($('#typePlan').length){
          if($('#typePlan').is(':checked')){
            let blob_recibo = dataURLtoBlob($('#canvas-recibo')[0].toDataURL('image/png'));
            data.append('photo_recibo', blob_recibo, 'recibo.png');
            data.append('tyc_start', $('#tyc').val());
          }else{
            data.append('tyc_start', '');
            data.append('photo_recibo', '');
          }
        }else{
          data.append('tyc_start', '');
          data.append('photo_recibo', '');
        }

        if($('#typePlan_bundle').length){

          if($('#typePlan_bundle').is(':checked')){
            data.append('isbundle', 'Y');
            if($('#imei_copy').val()!=''){
              data.append('imeiPhone', $('#imei_copy').val());
              data.append('imeiBrand', $('#imei_brand').val());
              data.append('imeiModel', $('#imei_model').val());
              data.append('isBandTE', $("#is-band-te").val());
            }else{
              data.append('imeiPhone', '');
            }
          }else{
            data.append('imeiPhone', '');
            data.append('isbundle', 'N');
          }
        }else{
          data.append('imeiPhone', '');
          data.append('isbundle', 'N');
        }

        {{-- if($('input[name=migrationcheck]').length){
          data.append('isMigration', $('input[name=migrationcheck]:checked').val().trim());
        } --}}

        if($('input[name=refopt]').length){
          data.append('isReferred', $("#msisdn-ori-ref").val());
        }

        doPostAjaxForm(
          '{{ route('sellerFiber.regInstall') }}',
          function(res){
            $('.loading-ajax').fadeOut();
            res = JSON.parse(res);

            if(res.success){
              resetForm();

              $clientsearch[0].selectize.clearOptions();

              swal({
                title: "Solicitud registrada!",
                text: "En la brevedad posible Mesa de Control Netwey procesará la información suministrada y contactará al cliente para confirmar la cita",
                icon: "success",
                button: {
                  text: "OK"
                }
              });
            }else{
              swal({
                title: "No se pudo registrar la solicitud",
                text: res.msg,
                icon: "error",
                button: {
                  confirm: {
                    text: "Continuar",
                    value: 'ok',
                    visible: true,
                    className: "",
                    closeModal: true
                  }
                }
              });
              if(res.code == 'ERR_DATE'){
                ResetViewFiberCite('plan');
              }
            }
          },
          data,
          $('meta[name="csrf-token"]').attr('content')
        );
      });

      $('#install-confirmation').on('hide.bs.modal', function(event) {
        $('#install-confirmation .name-client').text('');
        $('#install-confirmation .ine-client').text('');
        $('#install-confirmation .plan-client').text('');
        $('#install-confirmation .price-client').text('');
        $('#install-confirmation .address-client').text('');
        $('#install-confirmation .date-client').text('');
        $('.schedule-client').text('');
      });

      //Busqueda de prospectos
      let $clientsearch = $('#buscar').selectize({
        valueField: 'dni',
        searchField: ['msisdn', 'name', 'last_name', 'info'],
        labelField: 'info',
        render: {
          option: function(item, escape) {
            return '<p>'+escape(item.info)+'</p>';
          }
        },
        load: function(query, callback) {
          if (!query.length) return callback();

          doPostAjax(
            '{{ route('seller.findClient') }}',
            function(res){
              if(!res.error){
                callback(res);
              }else{
                callback();
              }
            },
            {search: query},
            $('meta[name="csrf-token"]').attr('content')
          );
        }
      });

      $('#buscar').on('change', function(e){
        let dni = $(this).val();

        if(dni){
          getClient(dni);
        }
      });

      /*let getClient = function(dni){
        //FUNCION DEPRECADA

        $('.loading-ajax').show();
        doPostAjax(
          '{{ route('sellerFiber.showClient') }}',
          client,
          {dni: dni, _token: '{{ csrf_token() }}'}
        );
      }*/

      //Formulario prospecto
      let now = new Date(),
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

      let register = function(res){
        $('#register-modal').modal('hide');

        if(!res.error){
          //$('.loading-ajax').show();

          $clientsearch[0].selectize.addOption({
            info: res.name+' '+res.last_name,
            dni: res.dni
          });

          $clientsearch[0].selectize.setValue(res.dni);

          /*doPostAjax(
            '{{ route('sellerFiber.showClient') }}',
            client,
            {dni: res.dni, _token: '{{ csrf_token() }}'}
          );*/
        }else{
          $('.loading-ajax').fadeOut();
          if(res.message == 'TOKEN_EXPIRED'){
              showMessageAjax('alert-danger','Su session a expirado, por favor actualice la página.');
          }else{
              if(res.message){
                showMessageAjax('alert-danger', res.message);
              }else{
                showMessageAjax('alert-danger', 'No se pudo registrar el prospecto.');
              }
          }
        }
      }

      $('#registerclientform button[type=submit]').unbind('click');

      $('#registerclientform').validator().on('submit', function(e){
        if(!e.isDefaultPrevented()){
          e.preventDefault();

          $('.loading-ajax').show();

          doPostAjax(
            '{{ route('client.registerAjax') }}',
            register,
            $(this).serialize()
          );
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

      $('#requestQr_tyc_resend').on('click', function(e){
        $('.loading-ajax').show();
        $.ajax({
          type: 'POST',
          url: "{{route('sellerFiber.reSendForceURL')}}",
          dataType: "json",
          data: {
            _token: "{{ csrf_token() }}",
            phoneVal: $("#msisdn-contact").val(),
          },
          success: function(res) {
            $('.loading-ajax').hide();
            if(res.success){
              //Contador regresivo
              $('#requestQr_tyc_resend').attr('hidden', true);
              $('#txt_resend').attr('hidden', null);
              $('#hms_resend').attr('hidden', null);
              $('#hms_resend').html("00:03:00");
              decremented_timer('hms_resend','SEND_URL');

            }else{
              @handheld
                swal({
                  title: res.title,
                  text: res.msg,
                  icon: 'warning'
                });
              @elsehandheld
                var tipy="alert-danger";
                showMessageAjax(tipy, res.msg);
              @endhandheld
            }
          },
          error: function() {
            $('.loading-ajax').hide();
            var textmsg = "Intenta nuevamente para reenviar el contrato de adhesion del servicio de fibra a contratar";
            @handheld
              swal({
                title: "Ups Ocurrio un error",
                text: textmsg,
                icon: 'warning'
              });
            @elsehandheld
              var tipy="alert-danger";
              showMessageAjax(tipy, textmsg);
            @endhandheld
          }
        });
      });

    });

function disableinputPhotos(visible){
  $('#typeDocument').attr('disabled', visible);
  $('#identification').attr('disabled', visible);
  $('#btnDesPic-recibo').attr('disabled', visible);
  $('#btnDesPic-identiF').attr('disabled', visible);
  $('#btnDesPic-identiP').attr('disabled', visible);
}

function generateQr(code){

  const dataQR = new FormData();
  let blob_identiF = dataURLtoBlob($('#canvas-identiF')[0].toDataURL('image/png'));
  dataQR.append('photo_identiF', blob_identiF, 'identiF.png');
  let blob_identiP = dataURLtoBlob($('#canvas-identiP')[0].toDataURL('image/png'));
  dataQR.append('photo_identiP', blob_identiP, 'identiP.png');

  dataQR.append('dni', $('#dni').val());
  dataQR.append('pack', $('#plan').val());
  dataQR.append('isBundle', $('#plan option:selected').data('bundle'));
  dataQR.append('date_instalation', $('#dateCalendar').val());
  dataQR.append('schedule', $('#hour').val());
  dataQR.append('identity', $('#identification').val());
  dataQR.append('typeIdentity', $('#typeDocument').val());

  //data para generacion de Contrato
  if($('#typePlan').is(':checked')){
    dataQR.append('route', $('#route').val().trim());
    dataQR.append('numberhouse', $('#numberhouse').val().trim());
    dataQR.append('colony', $('#colony').val().trim());
    dataQR.append('city', $('#city option:selected').text().trim());
    dataQR.append('city_id', $('#city').val().trim());
    dataQR.append('muni', $('#muni').val().trim());
    dataQR.append('state', $('#stateF option:selected').text().trim());
    dataQR.append('reference', $('#reference').val().trim());
  }
  dataQR.append('codePhoneContact', code);

  $('.loading-ajax').show();
    doPostAjaxForm(
      "{{ route('sellerFiber.getQrForce') }}", function(res) {
          $('.loading-ajax').fadeOut();
          res = JSON.parse(res);
          if (res.success) {
            $('#btnqr_tyc').attr('hidden', true);
            $('#reg-install').attr('disabled', null);
            $('#reg-install').attr('hidden', null);
            $('#tyc').val(res.tyc);

            if(res.code == "AUTHORIZED"){
              $('#qr_tyc').html(res.html);
              $('#qr_tyc svg').attr('width', '300px');
              $('#qr_tyc svg').attr('height', '300px');
              $('#block_share_tyc').html(res.htmlShare);
            }else{
              //Se envio el mensaje
              var icono = '<div class="col-auto px-3">'+res.icon+'</div>';
              var texto = '<div class="col-auto">'+res.msg+'</div>';
              $('#qr_tyc').html(icono+texto);
              $('#btnqr_tyc_resend').attr('hidden', null);
              $('#txt_resend').attr('hidden', null);
              $('#hms_resend').attr('hidden', null);
              $('#hms_resend').html("00:03:00");
              decremented_timer('hms_resend','SEND_URL');
            }
          } else {
            @handheld
              swal({
                title: "Hubo un problema!",
                text: res.msg,
                icon: res.icon
              });
            @elsehandheld
              var tipy="alert-danger";
              showMessageAjax(tipy, res.msg);
            @endhandheld
          }
        },
      dataQR,
      $('meta[name="csrf-token"]').attr('content')
    );

}

function validateDocument(value, type){
  var resul = {'success': false, 'msg': "Verificar el numero de identificacion del documento"};

  if(type==''){
    textMsg = "Se debe especificar el tipo de documento";
    $('#error-typeDocument').text(textMsg);
    return {'success': false, 'msg': textMsg+" para continuar"};
  }

  switch (type) {
    case 'CURP':
      var regex = /^[a-zA-Z]{4}[0-9]{2}[0-1][0-9][0-3][0-9][a-zA-Z]{6}[a-zA-Z0-9]{2}$/;
      var result = regex.test(value);
      if(result){
        //ABCS980939ABCDEFZ9
        resul = {'success': true, 'msg': "OK"};
      }else{
        resul = {'success': false, 'msg': "El documento CURP(Clave Única de Registro de Población) ingresado es invalido, recuerda es un código alfanumérico de 18 elementos"};
      }
    break;
    case 'INE':
      var regex = /^[0-9]{13}$/;
      var result = regex.test(value);
      if(result){
        //0458095299349
        resul = {'success': true, 'msg': "OK"};
      }else{
        resul = {'success': false, 'msg': "El documento INE(Instituto Nacional Electoral) ingresado es invalido, este se encuentran en la primera línea de la parte inferior después de los símbolos “<<”. El número identificador está compuesto por 13 dígitos"};
      }
    break;
    //RFC es el Registro Federal de Contribuyentes

    default:
      if(value.length >= 9 && value.length <= 25){
        resul = {'success': true, 'msg': "OK"};
      }else{
        resul = {'success': false, 'msg': "El documento "+type+" ingresado tiene un tamano invalido ("+value.length+")"};
      }
    break;
  }

  return resul;
}

function ResetViewFiberCite(level){

    if(level=='states'){
      $('#blockCity').addClass('d-none');
      $('#blockCity').html('');
    }
    if(level=='states' || level=='city'){
      if($('#blockOlt').html()!=''){
        $('#blockOlt').addClass('d-none');
        $('#blockOlt').html('');
      }
    }
    if(level=='states' || level=='city' || level=='olt'){
      if($('#blockNodes').html()!=''){
        $('#blockNodes').addClass('d-none');
        $('#blockNodes').html('');
      }
      $('#blockTipeAddress').html('');

      $('#blockMap').addClass('d-none');
      $('#blockPlan').html('');
    }
    if(level=='states' || level=='city' || level=='olt' || level=='offcoverage'){
      $('#locality').val('');
      $('#typeAddress').prop('checked', false).change();
      if(level != 'offcoverage'){
        $('#blockGPS').addClass('d-none');
      }
      $('#blockAddressExtra').addClass('d-none');
      $('#muni').val('');
      $('#colony').val('');
      $('#route').val('');
      $('#numberhouse').val('');
      $('#reference').val('');

    }
    if(level=='states' || level=='city' || level=='olt' || level=='offcoverage' || level=='plan'){

      if(level=='plan' || level=='offcoverage'){
        $('#plan').val("");
       // $('#plan').data('selectize').setValue("");
      }
      /**  setTimeout(() => {
          showMessageAjax('alert-danger', 'Debes volver a verificar servicialidad de fibra para continuar');
        }, 3000);
      **/

      if($('#plan-content').html('')!=''){
          $('#plan-content').html('');
          resetForm(false);
      }
      $('#blockTipeAddress').addClass('d-none');

      $('#forzoso-content').attr('hidden', true);
      $('#error-photo-recibo').text('');
      $('#error-photo-identiF').text('');
      $('#error-photo-identiP').text('');
      if(sessionStorage.getItem('photo-recibo')!='null'){
        sessionStorage.setItem('photo-recibo', null);
        if($('#video-recibo')[0].srcObject !== null){
          btnDesPic('recibo', false, false);
          for(var i = 0; i < $('#video-recibo')[0].srcObject.getTracks().length; i++){
            $('#video-recibo')[0].srcObject.getTracks()[i].stop();
          }
          $('#block_document').attr('hidden', true);
        }
      }
      if(sessionStorage.getItem('photo-identiF')!='null'){
        sessionStorage.setItem('photo-identiF', null);
        if($('#video-identiF')[0].srcObject !== null){
          btnDesPic('identiF', false, false);
          for(var i = 0; i < $('#video-identiF')[0].srcObject.getTracks().length; i++){
            $('#video-identiF')[0].srcObject.getTracks()[i].stop();
          }
          $('#block_phot_identiP').attr('hidden', true);
        }
      }
      if(sessionStorage.getItem('photo-identiP')!='null'){
        sessionStorage.setItem('photo-identiP', null);
        if($('#video-identiP')[0].srcObject !== null){
          btnDesPic('identiP', false, false);
          for(var i = 0; i < $('#video-identiP')[0].srcObject.getTracks().length; i++){
            $('#video-identiP')[0].srcObject.getTracks()[i].stop();
          }
          $('#block_tyc').attr('hidden', true);
        }
      }
      disableinputPhotos(null);
      $('#btnqr_tyc').attr('hidden', true);
      $('#btnqr_tyc_resend').attr('hidden', true);
      $('#qr_tyc').html('');
      $('#block_share_tyc').html('');
      $('#block_number_identi').attr('hidden', true);
      $('#typeDocument').val('');
      $('#error-typeDocument').text('');
      $('#identification').val('');
      $('#error-identification').text('');
    }
   // resetInstaladores();
  }
</script>
@endif
@stop

@extends('layouts.admin')

@section('customCSS')
<link href="{{ asset('css/selectize.css') }}" rel="stylesheet"/>
<link href="{{ asset('css/selectize.bootstrap.css') }}" rel="stylesheet"/>
<link href="{{ asset('plugins/bower_components/bootstrap4-toggle/bootstrap4-toggle.min.css') }}" rel="stylesheet"/>
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
  <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
    <h4 class="page-title">
      Módulo de ventas
    </h4>
  </div>
  <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
    <ol class="breadcrumb">
      <li>
        <a href="#">
          Ventas
        </a>
      </li>
      <li class="active">
        Alta de cliente fibra. <span>#{{$data->id}}</span>
      </li>
    </ol>
  </div>
</div>
@if(!empty($data))
  @if($data->status=='A')

<div class="row">
  <div class="col-md-12">
    <div class="white-box">
      @include('fiber.infoClientFiber', ['data' => $data ])
      <div class="row">
        <h3 class="box-title col-md-12 p-t-10">
          Datos de la instalación
        </h3>

        @include('fiber.plan', ['plan' => $plan, 'bundle' => $bundle])

        <input type="hidden" id="cita" value="{{$data->id}}">

      {{--
      No se permite subscripciones con bundle de momento hasta estar el api de cobro
      --}}

        @if(!empty($htmlPack) && (($data->owner=='N' && $countDns) || $data->owner=='V') /*&& empty($bundle)*/)

        <div class="col-md-12" id="notify_suscription">
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
                &#128179; Para activar el pago recurrente con tarjeta al cliente:
            </span>
            <br>
            <li>
              <strong>1 -</strong> Da click en "Ofrecer un plan con pago recurrente" y selecciona el plan que desea el cliente.
            </li>
            <li>
              <strong>2 -</strong> Proporciona al cliente el enlace de pago para registrar el pago con su tarjeta.
            </li>
            <li>
              <strong>3 -</strong> Activa el servicio del cliente luego que se tenga confirmación del pago en su pantalla.
            </li>
            <li>
              <strong>4 -</strong> Si el pago con tarjeta no procede, realiza el cobro al cliente en efectivo e informa que puede activar el pago recurrente después de la instalación con su DN a travéz del sitio web de Netwey al momento de recargar.
              {{--OJO: https://netwey-contract.s3.amazonaws.com/contracts/5ba42308e092820230209172526.pdf--}}
            </li>
          </div>
        </div>
        <div class="col-md-12 pt-4 pb-5" id="block_changer_subscrip">
          <div class="col-md-12 text-center">
            <button class="btn btn-success waves-effect waves-light" id="request_subscrip" type="button">
              Ofrecer un plan con pago recurrente
            </button>
          </div>
          <h3 class="box-title col-md-12 p-t-10 text-left" id="label_new_pack" hidden>
            Nuevo plan con pago recurrente a ser ofrecido:
          </h3>
          <div hidden id="listPacks">
            {!!$htmlPack!!}
          </div>
          <div class="col-md-12 mt-4 pt-5" style="border-style: dashed dotted; display: flex; border-width: 2px; padding-left: 3%; padding-right: 3%; background: #edf1f5;" id="plan-content" hidden></div>
          <div class="col-md-12" id="block_payment_subscrip"></div>
          <div class="col-md-12 text-center my-3">
            <button class="btn btn-success waves-effect waves-light" id="btn_cancel_packToCS" hidden type="button">
              Descartar cambio de plan
            </button>
          </div>
        </div>
        @else
          @if(!empty($htmlPackSelect) && (($data->owner=='N' && $countDns) || $data->owner=='V') /*&& empty($bundle)*/)
            <h3 class="box-title col-md-12 p-t-10 text-left" id="label_new_pack" >
              Nuevo plan con pago recurrente a ser ofrecido:
            </h3>
            <div class="col-md-12 mb-4 py-5" style="border-style: dashed dotted; display: flex; border-width: 2px; padding-left: 3%; padding-right: 3%; background: #edf1f5;" id="plan-content">
              {!!$htmlPackSelect!!}
            </div>
            <div class="col-md-12 {{$QrPaymentClass}} " id="block_qr_subscrip">
              {!!$QrPayment!!}
            </div>
            <div class="col-md-12 text-center my-3">
              <button class="btn btn-success waves-effect waves-light" id="btn_cancel_packToCS" type="button">
                Descartar cambio de plan
              </button>
            </div>
          @else
            @if((!empty($htmlPack) || !empty($htmlPackSelect)) && (($data->owner=='N' && $countDns) || $data->owner=='V') /*&& empty($bundle)*/)
              <div class="alert alert-danger">
                <p>
                  No posee inventario disponibles.
                </p>
              </div>
            @endif
          @endif
        @endif

        <div class="col-md-6">
          <label>
            Dirección de instalación
          </label>
          <p>
            {{$data->address_instalation}}
          </p>
        </div>
        <div class="col-md-6">
          <label>
            Fecha de instalación
          </label>
          <p>
            {{$data->date_instalation}} / {{$data->schedule}}
          </p>
        </div>
        @if((!empty($QrPayment) && empty($htmlPack) && empty($htmlPackSelect)) && (($data->owner=='N' && $countDns) || $data->owner=='V'))
          {{--Se cambio a efectivo y debe elegir un plan sin pago recurrente(recarga manual)--}}
          <h3 class="box-title col-md-12 p-t-10 text-left" id="label_new_pack" hidden >
              Nuevo plan con recargas manuales:
          </h3>
          <div class="col-md-12 py-4" hidden id="listPacks">
          </div>
          <div class="col-md-12 mt-4 pt-5" style="border-style: dashed dotted; display: flex; border-width: 2px; padding-left: 3%; padding-right: 3%; background: #edf1f5;" id="plan-content" hidden></div>
          <div class="col-md-12 py-4 text-center" id="blockBtnChangerPack" hidden>
            <button class="btn btn-primary waves-effect waves-light my-2" id="btn_changer_packToSS"  disabled type="button">
              Aceptar cambio de plan
            </button>
            <button class="btn btn-success waves-effect waves-light my-2" id="btn_changer_packToCS" type="button">
              Descartar cambio de plan
            </button>
          </div>
          <div class="col-md-12 {{$QrPaymentClass}}" id="block_qr_subscrip">
            {!!$QrPayment!!}
          </div>

        @endif
        <div class="col-md-12">
          @include('fiber.infoOltConex', ['data' => $data, 'view' => false, 'NodoRed' => $NodoRed])
        </div>
        <h3 class="box-title col-md-12 p-t-10">
          Datos del equipo de fibra a instalar
        </h3>
        <div class="col-md-12">
          <div class="pt-2">
            <label>
              Ingrese la dirección Mac del equipo de Fibra
            </label>
          </div>
          @if($data->owner=='N')
            {{--interno Netwey--}}
          <form class="form-horizontal" data-toggle="validator" id="form-reg-inst">
            <div class="p-t-10">
              <div class="form-group">
                @if($countDns)
                <select class="form-control" id="mac_select" name="mac_select" placeholder="Ingresa la Mac" required="">
                </select>
                <div class="help-block with-errors" id="error-mac">
                </div>
                @else
                <div class="alert alert-danger">
                  <p>
                    No tienes inventario asignado para realizar la instalación.
                  </p>
                </div>
                @endif
              </div>
            </div>
            @if($countDns)
              {{--interno Net--}}
            <div class="row pt-3" hidden="true" id="item-selected-content">
              <div class="col-sm-12 col-md-6">
                <label>
                  Msisdn:
                </label>
                <strong>
                  <p id="msisdn-selected">
                  </p>
                </strong>
                <input id="msisdn" name="msisdn" type="hidden">
                </input>
              </div>
              <div class="col-sm-12 col-md-6">
                <label>
                  MAC:
                </label>
                <p id="mac-selected">
                </p>
              </div>
              <div class="col-sm-12 col-md-6">
                <label>
                  Serial:
                </label>
                <p id="serial-selected">
                </p>
              </div>
            </div>
              @php
                $Btn_id = "reg-install";
                $Btn_label = "Dar de alta";
                $Btn_habilite = true;
              @endphp
              {{-- end interno Net--}}
            @endif
          </form>
          @else
            {{--externo Netwey--}}
          <form class="form-horizontal" data-toggle="validator" id="form-request-dn">
            <input autocomplete="off" class="form-control" id="mac_input" maxlength="17" minlength="17" name="mac_input" pattern="^([0-9A-Fa-f]{2}[:]){5}([0-9A-Fa-f]{2})$" placeholder="Escribe la Mac" required="">
            </input>
            <div class="help-block with-errors" id="error-mac">
            </div>
            <div class="row pt-3" hidden="true" id="item-create-content">
              <div class="col-sm-12 col-md-6">
                <label>
                  MAC a usar:
                </label>
                <p id="mac-selected">
                </p>
              </div>
              <div class="col-sm-12 col-md-6">
                <label>
                  Serial del equipo de fibra:
                </label>
                <input autocomplete="off" class="w-100" id="serial" name="serial" minlength="5" required="">
                </input>
                <div class="help-block with-errors-serial">
                </div>
              </div>
              <div>
                <input autocomplete="off" id="chk_ve" name="chk_ve" required="" type="hidden">
                </input>
              </div>
              <div class="col-sm-12 col-md-6" hidden id="loadindDN">
                <label>
                  Msisdn de fibra generado:
                </label>
                <strong>
                  <p id="msisdn-selected">
                  </p>
                </strong>
                <input id="msisdn" name="msisdn" type="hidden">
                </input>
              </div>
            </div>
            @php
              $Btn_id = "install-getDN";
              $Btn_label = "Solicitar alta";
              $Btn_habilite = true;
              if(!$arti_install_zone['success']){
                $Btn_habilite = false;
              }
            @endphp
          </form>
            {{--end externo Netwey--}}
          @endif

          @if($habilityPortability)
            <div class="row">
              <div class="col-md-12 px-0">
                <h3 class="box-title col-md-12">Datos del equipo de teléfonia a entregar
                </h3>
              </div>
              @if(isset($obj_bundle->children_T->imei ))
                <div class="col-md-6 col-sm-12 py-3">
                  <label class="col-md-12">
                    IMEI del celular del cliente:
                  </label>
                  <p class="px-2" id="text-imei_phone">
                    {{$obj_bundle->children_T->imei }}
                  </p>
                  <div class="alert alert-primary">
                    El imei descrito fue el usado para verfificar compatibilidad de la red de netwey.
                  </div>
                </div>
              @endif
              @if(count($disposeInvT)>0)

              <div class="col-md-6 col-sm-12">
                <label class="col-md-12 ">
                  Msisdn de teléfonia:
                </label>
                <select autocomplete="off" class="form-control" id="dn_phone" maxlength="10" minlength="10" name="dn_phone" pattern="^([0-9]{10})$" placeholder="Escribe el MSISDN de telefonia" required="">
                </select>
                <div class="help-block with-errors" id="error-dn_phone"></div>
              </div>
              <div class="col-md-12 pt-3">
                <label class="col-md-12">
                  Tipo de bundle de teléfonia
                </label>
                <input data-off="Sin portabilidad" data-on="Con portabilidad" data-toggle="toggle" data-width="150" data-height="55" id="typePort" type="checkbox" title="Alta de telefonia con o sin portabilidad"/>
              </div>
              @include('seller.formPortability',['banJs' => 'fiber'])
              <div class="col-md-12 pt-3" hidden id="block_dnTrans">
                <label class="col-md-12">
                  Msisdn transitorio:
                </label>
                <p id="txt-dn_transt"></p>
                <div class="alert alert-success alert-dismissable">
                  <button aria-hidden="true" class="close" data-dismiss="alert" type="button">
                    ×
                  </button>
                  <span>
                    <h4>
                      <strong>
                        Atención:
                      </strong>
                    </h4>
                      &#128679; Consideraciones de la portabilidad:
                  </span>
                  <br>
                  <li>
                    <strong>1 -</strong> La portabilidad suele tardar aproximadamente 3 días hábiles
                  </li>
                  <li>
                    <strong>2 -</strong> Durante el tiempo que tarda en hacerse efectiva la portación su número de teléfono sera el msisdn transitorio descrito anteriormente
                  </li>
                </div>
              </div>
              @else
                <div class="col-md-6 col-sm-12 d-flex justify-content-center align-items-center alert text-white label-red">
                  Inventario no disponible para dar el alta.
                </div>
            @endif
            </div>

          @endif

          @if((($Btn_habilite && count($disposeInvT)>0) || ($QrPaymentClass == "" && $Btn_habilite )
            )
             && !empty($plan)
          )
          {{--
          boton de envio cuando se ingresa portabilidad, mac y serial
          --}}
            <div class="col-md-12 m-t-20 text-center">
              <div class="form-group">
                <button class="btn btn-success waves-effect waves-light" id="{{$Btn_id}}" type="button">
                  {{$Btn_label}}
                </button>
              </div>
            </div>
          @endif

        </div>
      </div>
    </div>
  </div>
</div>

<div aria-hidden="true" aria-labelledby="myModalLabel" class="modal fade" id="install-confirmation" role="dialog" style="display: none;" tabindex="-1">
  <div class="modal-dialog" style="top: 140px;">
    <div class="modal-content">
      <div class="modal-header">
        <button aria-hidden="true" class="close" data-dismiss="modal" type="button">
          ×
        </button>
        <h4 class="modal-title">
          Confirmar datos del alta
        </h4>
      </div>
      <div class="modal-body">
        <div class="col-sm-12">
          <label>
            Nombre / Apellido:
          </label>
          <p id="name-client-conf">
          </p>
        </div>
        <div class="row px-0" hidden id="blockDetailNornal">
          <div class="col-sm-12 col-md-6">
            <label>
              Plan:
            </label>
            <p id="plan-conf">
            </p>
          </div>
          <div class="col-sm-12 col-md-6">
            <label>
              Servicio:
            </label>
            <p id="service-conf">
            </p>
          </div>
          <div class="col-sm-12 col-md-6">
            <label>
              Msisdn de fibra:
            </label>
            <strong>
              <p id="msisdn-conf">
              </p>
            </strong>
          </div>
          <div class="col-sm-12 col-md-6">
            <label>
              Nodo de red:
            </label>
            <p id="nodo-conf">
            </p>
          </div>

        </div>
        <div class="row px-0" hidden id="blockDetailBundle">
          <div class="col-sm-12 col-md-6">
            <label>
              Paquete bundle:
            </label>
            <p id="plan-conf">
            </p>
          </div>
          @include('fiber.detailplanResumen',['type' => 'F', 'title'=> "Fibra", 'line' => true])
          @include('fiber.detailplanResumen',['type' => 'H', 'title'=> "Hogar", 'line' => true])
          @include('fiber.detailplanResumen',['type' => 'M', 'title'=> "Mifi", 'line' => true])
          @include('fiber.detailplanResumen',['type' => 'MH', 'title'=> "Mifi Huella", 'line' => true])
          @include('fiber.detailplanResumen',['type' => 'T', 'title'=> "Telefonia", 'line' => false])

        </div>
        <div class="col-sm-12 col-md-6">
          <label>
            Precio total:
          </label>
          <strong>
            <p>
              $ <span id="amount-conf"></span>
            </p>
          </strong>
        </div>
        <div class="col-sm-12 col-md-6">
          <label>
            Observación del pago:
          </label>
          <i><u>
            <p id="payment-conf">
            </p>
          </u></i>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-default waves-effect" data-dismiss="modal" id="close-mod-conf" type="button">
          Cancelar
        </button>
        <button class="btn btn-danger waves-effect waves-light" data-inst="{{$data->id}}" id="pro-install" type="button">
          Procesar
        </button>
      </div>
    </div>
  </div>
</div>

<div aria-hidden="true" aria-labelledby="myModalLabel" class="modal fade" data-backdrop="static" data-keyboard="false" id="modal-email-mp" role="dialog" style="display: none;" tabindex="-1">
  <div class="modal-dialog" style="top: 140px;">
    <div class="modal-content">
      <div class="modal-header">
        <button aria-hidden="true" class="close" data-dismiss="modal" type="button">
          ×
        </button>
        <h4 class="modal-title">
          Correo electrónico para pago recurrente
        </h4>
      </div>
      <div class="modal-body">
        <div class="col-sm-12">
          <label>
            Nombre / Apellido:
          </label>
          <span id="name-client-mp">
          </span>
        </div>
        <div class="col-sm-12">
          <label>
            Plan:
          </label>
          <span id="plan-mp">
          </span>
        </div>
        <div class="col-sm-12">
          <label>
            Servicio:
          </label>
          <span id="service-mp">
          </span>
        </div>
        <div class="col-sm-12">
          <label>
            Monto:
          </label>
          <span id="amount-mp">
          </span>
        </div>
        <div class="col-sm-12">
          <label>
           &#9993; Email registrado en Netwey:
          </label>
          <span id="email-mp">
          </span>
        </div>
        <div class="col-sm-12">
          <label>
           &#129309; Email a usar en proceso de pago:
          </label>
          <input class="form-control" id="newemail_mp" name="newemail_mp" type="text" placeholder="email@dominio.com" onselectstart="return alerta()" onpaste="return alerta()" onCopy="return alerta()" onCut="return alerta()" onDrag="return alerta()" onDrop="return alerta()" autocomplete="off" data-netmail="" required />
          <label>
          Confirmar email del proceso de pago:
          </label>
          <input class="form-control" id="newemail_mp_copy" name="newemail_mp_copy" type="text" placeholder="email@dominio.com" onselectstart="return alerta()" onpaste="return alerta()" onCopy="return alerta()" onCut="return alerta()" onDrag="return alerta()" onDrop="return alerta()" autocomplete="off" data-netmail="" required />
          <div class="help-block email-errors">
          </div>
        </div>
        <div class="form-check col-sm-12">
          <input class="form-check-input" type="checkbox" value="" id="equals_mp_mail" name="equals_mp_mail" >
          <label class="form-check-label" for="equals_mp_mail">&nbsp;Usar el email del proceso de pago como email en Netwey</label>
        </div>
        <div class="form-check col-sm-12">
          <h6><strong>Nota:</strong> Si no tienes correo registrado en netwey es recomendable que indiques que deseas usar el mismo correo del proceso de pago</h6>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-default waves-effect" data-dismiss="modal" id="close-mod-conf" type="button">
          Cancelar
        </button>
        <button class="btn btn-danger waves-effect waves-light" data-inst="{{$data->id}}" id="btn_mp_mail" type="button">
          Enviar
        </button>
      </div>
    </div>
  </div>
</div>

  @else
    <div class="alert alert-danger">
      Instalación no activa para ser instalada.
    </div>
  @endif
@else
<div class="alert alert-danger">
    Instalación no disponible.
</div>
@endif

@stop

@if(!empty($data))
  @if($data->status=='A')

@section('scriptJS')
<script src="{{ asset('js/validator.js') }}">
</script>
<script src="{{ asset('js/sweetalert.min.js') }}">
</script>
<script src="{{ asset('js/selectize.js')}}">
</script>
<script src="{{ asset('plugins/bower_components/bootstrap4-toggle/bootstrap4-toggle.min.js') }}"></script>


<script defer type="text/javascript">

  function alerta(){
    swal({
      title: 'Acción no permitida',
      text: "Debes escribir el email",
      icon: "warning",
      button: {
        text: "OK"
      }
    });
  }
  @if($data->owner=='V')
    //////// process MAC
    {{--externo Netwey--}}

    function resetFormMac(){
      $('#item-create-content').attr('hidden', true);
      $('#serial').val('');
    }

    function blockFormArtSerial(type=false){
      //console.log('block serial-model');
      document.getElementById("serial").disabled = type;
    }

    function valid_Mac( valor, IDcontenedor = 'mac_input') {
      let regex = /^([0-9A-Fa-f]{2}[:]){5}([0-9A-Fa-f]{2})$/;
      let tag   = document.getElementById(IDcontenedor);
      if( regex.test( valor ) ) {
        return true;
      }
      return false;
    }

    function cheking_mac(deviceMac){
      doPostAjax(
        "{{ route('sellerFiber.chekingMac') }}",
        function(res){
          if(!res.success){
            resetFormMac();
            swal({
              title: 'Problemas con la dirección MAC',
              text: res.msg,
              icon: "warning",
              button: {
                text: "OK"
              }
            });
          }else{
            $('#item-create-content').attr('hidden', null);
            $('#mac-selected').text(deviceMac);
            $('#chk_ve').val('');

            if(res.code=='A'){
              //console.log('ACTIVO');
              $('#serial').val(res.infoArt.serial);
              $('#msisdn').val(res.infoArt.msisdn);
              blockFormArtSerial(true);
              $('#chk_ve').val(res.infoArt.serial);
              $('#msisdn-selected').text(res.infoArt.msisdn);
              $('#loadindDN').attr('hidden', null);
            }else{
              //console.log('NUEVO');
              $('#serial').val('');
              blockFormArtSerial();
              $('#loadindDN').attr('hidden', true);
              $('#msisdn').val('');
            }
            $('.with-errors-serial').text('');
            $('.with-errors-serial').removeClass('alert').removeClass('alert-danger');
          }
        },
        {
          mac: deviceMac
        },
        $('meta[name="csrf-token"]').attr('content')
      );
    }

    function formatMacAddress(userInput) {
      var macAddress = userInput || null;

      if (macAddress !== null) {
        var deviceMac = macAddress.value;
        deviceMac = deviceMac.toUpperCase();

        if (deviceMac.length >= 3 && deviceMac.length <= 16) {
          deviceMac = deviceMac.replace(/\W/ig, '');
          deviceMac = deviceMac.replace(/(.{2})/g, "$1:");

        }else{
          if(deviceMac.length == 17){
            if(valid_Mac(deviceMac)){
              cheking_mac(deviceMac);
            }else{
              $('#error-mac').html('Mac invalida');
              $('#error-mac').addClass('alert').addClass('alert-danger');
            }
          }
        }
        document.getElementById(macAddress.id).value = deviceMac;
      }
    }
  @endif

  function actionChangerMail(plan=false){
    $('.loading-ajax').fadeIn();
      doPostAjax(
        "{{ route('sellerFiber.getMailPayment') }}",
        function(res){
          $('.loading-ajax').fadeOut();
          if(res.success){
            $('#name-client-mp').text(res.client);
            $('#plan-mp').text(res.plan);
            $('#service-mp').text(res.service);
            $('#amount-mp').text('$'+res.mount);
            $('#email-mp').text(res.email);
            $('#newemail_mp').data('netmail',res.email);
            $('#newemail_mp').val('');
            $('#newemail_mp_copy').val('');
            $('#btn_mp_mail').data('packprice', res.packprice)
            $('#modal-email-mp').modal('show');
          }else{
            if(res.message == 'TOKEN_EXPIRED'){
                showMessageAjax('alert-danger','Su session a expirado, por favor actualice la página.');
              }else{
                @handheld
                  swal({
                    title: res.title,
                    text: res.msg,
                    icon: res.icon
                  });
                @elsehandheld
                    var tipy="alert-danger";
                    showMessageAjax(tipy, res.msg);
                @endhandheld
              }
            }
        },
        {
          id: "{{$data->id}}",
          packNew: plan,
          isBundle: "{{isset($obj_bundle)?'Y':'N'}}"
        },
        $('meta[name="csrf-token"]').attr('content')
      );
  }

  function verifyIsPayment(){
    $('.loading-ajax').fadeIn();
      return new Promise((resolve, reject) => {
        doPostAjax(
          "{{ route('sellerFiber.verifyPayment') }}",
          function(res){
            $('.loading-ajax').fadeOut();
            return resolve(res);
          },
          {
            id: {!!$data->id!!}
          },
          $('meta[name="csrf-token"]').attr('content')
        );
      });
  }

  function validateInstall(){
    //true:indica que falta algo para continuar
    return new Promise((resolve, reject) => {

      let error = false;
      if($('#nodo_red option:selected').val()==''){
        $('#error-nodo').text('Debe seleccionar un nodo de conexion.');
        $('#error-nodo').addClass('alert').addClass('alert-danger');
        error=true;
      }
      @if($data->owner=='V')
      //Control de velocom
        if($('#mac_input').val() == ''){
          $('#error-mac').text('Debe ingresar la mac del equipo a conectar.');
          $('#error-mac').addClass('alert').addClass('alert-danger');
          error=true;
        }
        if($('#mac_input').val().length != 17 && !error){
          $('#error-mac').text('Debe ingresar una mac valida.');
          $('#error-mac').addClass('alert').addClass('alert-danger');
          error=true;
        }
        if($('#serial').val()=='' || $('#serial').val().length < 5){
          $('.with-errors-serial').text('Debe ingresar el serial del equipo a conectar.');
          $('.with-errors-serial').addClass('alert').addClass('alert-danger');
          error=true;
        }
      @else
      //Control de netwey
        if($('#mac_select option:selected').val() == ''){
          $('#error-mac').text('Debe ingresar la mac del equipo a conectar.');
          $('#error-mac').addClass('alert').addClass('alert-danger');
          error=true;
        }
      @endif

      @if($habilityPortability)
        if($('#dn_phone').val() == ''){
          $('#error-dn_phone').text('Debe informar el msisdn de telefonia a entregar.');
          $('#error-dn_phone').addClass('alert').addClass('alert-danger');
          error=true;
        }
        if($('#typePort').is(':checked') && $('#btn-form-port').data('verify')=='N'){
          $('#error-verifyPort').text('Debes verificar los datos de la portabilidad.');
          $('#error-verifyPort').addClass('alert').addClass('alert-danger');
          error=true;
        }
      @endif

      if(error){
        return resolve(error);
      }

      let isRecibPayment = verifyIsPayment();
      isRecibPayment.then(function(promesa_is_payment) {
        //Se verifica si esta pagado
        $('.loading-ajax').fadeOut();
        if(promesa_is_payment['code']!="SIN_SUBS"){
          swal({
            title: promesa_is_payment['title'],
            text: promesa_is_payment['msg'],
            icon: promesa_is_payment['icon']
          });
        }
        if(!promesa_is_payment['success']){
          return resolve(true);
        }else{
          return resolve(error);
        }
      });
    });
  }

  function infoConfirInstall(resg=false){
    $('.loading-ajax').fadeIn();
    doPostAjax(
      "{{ route('sellerFiber.getInstallerCharges') }}",
      function(res){
        $('.loading-ajax').fadeOut();
        $('#name-client-conf').text("{{$data->name}} {{$data->last_name}}");
        if(res.success){
          if(!res.infoBundle){
            $('#blockDetailNornal #plan-conf').text(res.pack);//'{{$data->pack}}'
            $('#blockDetailNornal #service-conf').text(res.service);//'{{$data->service}}'
            if(resg){
              if(resg.code=="OK_DN"){
                $('#blockDetailNornal #msisdn-conf').text(resg.msisdn);
                $('#msisdn').val(resg.msisdn);
              }
            }else{
              $('#blockDetailNornal #msisdn-conf').text($('#msisdn').val().trim());
            }
            $('#nodo-conf').text($('#nodo_red option:selected').val()? $('#nodo_red option:selected').text().trim()+'('+$('#nodo_red').val()+')' : 'S/I');
            $('#amount-conf').text(res.price);//'${{$data->price}}'
            $('#blockDetailBundle').attr('hidden', true);
            $('#blockDetailNornal').attr('hidden', null);
          }else{
            $('#blockDetailBundle #plan-conf').text(res.infoBundle.pack_title);
           // console.log(res.infoBundle);
            if(res.infoBundle.pack_F != null){
              $('#block_F #plan-conf').text(res.infoBundle.pack_F);
              if(resg){
                //Es del proceso de creacion de inventario de fibra
                $('#block_F #msisdn-conf').text(resg.msisdn);
                $('#msisdn').val(resg.msisdn);
              }else{
                $('#block_F #msisdn-conf').text($('#msisdn').val().trim());
              }
              $('#block_F #nodo-conf').text($('#nodo_red option:selected').val()? $('#nodo_red option:selected').text().trim()+'('+$('#nodo_red').val()+')' : 'S/I');
              $('#block_F').attr('hidden', null);
            }else{
              $('#block_F').attr('hidden', true);
            }

            if(res.infoBundle.pack_H != null){
              $('#block_H #plan-conf').text(res.infoBundle.pack_H);
              $('#block_H #msisdn-conf').text("S/N");
              $('#block_H').attr('hidden', null);
            }else{
              $('#block_H').attr('hidden', true);
            }

            if(res.infoBundle.pack_M != null){
              $('#block_M #plan-conf').text(res.infoBundle.pack_M);
              $('#block_M #msisdn-conf').text("S/N");
              $('#block_M').attr('hidden', null);
            }else{
              $('#block_M').attr('hidden', true);
            }

            if(res.infoBundle.pack_MH != null){
              $('#block_MH #plan-conf').text(res.infoBundle.pack_MH);
              $('#block_MH #msisdn-conf').text("S/N");
              $('#block_MH').attr('hidden', null);
            }else{
              $('#block_MH').attr('hidden', true);
            }

            if(res.infoBundle.pack_T != null){
              $('#block_T #plan-conf').text(res.infoBundle.pack_T);
              $('#block_T #msisdn-conf').text($('#txt-dn_transt').text().trim());
              $('#block_T').attr('hidden', null);
            }else{
              $('#block_T').attr('hidden', true);
            }

            $('#amount-conf').text(res.infoBundle.price);
            $('#pro-install').attr('disabled', false);
            $('#blockDetailBundle').attr('hidden', null);
            $('#blockDetailNornal').attr('hidden', true);

          }
          $('#payment-conf').text(res.title);

        }else{
          $('#plan-conf').text('FALLO BUSQUEDA');//
          $('#service-conf').text('FALLO BUSQUEDA');//
          $('#amount-conf').text('FALLO BUSQUEDA');//
          $('#payment-conf').text('FALLO BUSQUEDA');
          showMessageAjax('alert-danger',res.msg);
          $('#pro-install').attr('disabled', true);
        }

        $('#install-confirmation').modal('show');
      },
      {
        id: {!!$data->id!!}
      },
      $('meta[name="csrf-token"]').attr('content')
    );
  }

  @if(!empty($bundle))
   function valid_DN( valor, IDcontenedor) {
      let regex = /^([0-9]{10})$/;
      let tag   = document.getElementById(IDcontenedor);
      if( regex.test( valor ) ) {
        return true;
      }
      return false;
    }
  @endif

  $(function () {
    $('#plan').val('');
    @if($habilityPortability)
      $('#error-verifyPort').text('');
      $('#error-verifyPort').removeClass('alert').removeClass('alert-danger');
      $('#error-dn_phone').text('');
      $('#error-dn_phone').removeClass('alert').removeClass('alert-danger');
      $('#typePort').bootstrapToggle();
      $('#btn-form-port').data('verify','N');
      $('#typePort').on('change',function(){
        if($('#typePort').is(':checked')){
          //console.log("VER");
          console.log($('#btn-form-port').data('verify'));
          $('#form-port-content').attr('hidden', null);
        }else{
         // console.log("CERRAR");
          $('#form-port-content').attr('hidden', true);
          $("#block_dnTrans").attr('hidden', true);
          resetFormPort();
        }
      });

      $('#dn_phone').on('change',function(){
        $('#error-dn_phone').text('');
        $('#error-dn_phone').removeClass('alert').removeClass('alert-danger');
      });

      $('#btn-form-port').on('click', function(e){

        valid = fromValidPort();
        if(!valid){
           showMessageAjax('alert-danger', 'Por favor revisa los datos para la portabilidad.');
        }else{
          //Se revisa si el DN a portar no es cliente existen de netwey
          if($("#dn_phone option:selected").text() != $('#dn_port').val() && $('#dn_port').val() != '' ){
            verifyInfoPort();
          }else{
            showMessageAjax('alert-danger', 'Por favor revisa el Dn que se desea portar a netwey');
            swal({
              title: 'Verifique los datos de portación',
              text: "El dn a portar no es valido",
              icon: "warning",
              button: {
                text: "OK"
              }
            });
          }
        }
      });

      $('#dn_phone').selectize({
        maxItems: 1,
        valueField: 'id',
        searchField: ['msisdn'],
        labelField: 'msisdn',
        render: {
          item: function (item, escape) {
            $("#txt-dn_transt").text(item.msisdn);
            $("#block_dnTrans").attr('hidden', true);
            if($('#typePort').is(':checked')){
              $("#block_dnTrans").attr('hidden', null);
            }
            opt = "<div>";
            opt += "<span>" + escape(item.msisdn) + "</span></br>";
            opt += "</div>";
            return opt;
          },
          option: function(item, escape) {

            let tipoArt="";
            switch (item.artic_type) {
              case 'T':
                tipoArt="Telefonia"
                break;
              case 'H':
                tipoArt="Hogar"
                break;
              case 'M':
                tipoArt="Mifi"
                break;
              case 'MH':
                tipoArt="Mifi Huella"
                break;
              case 'F':
                tipoArt="Fibra"
                break;
              default:
                tipoArt="No definido";
                break;
            }
            opt = "<div class='row'>";
            opt += '<div class="col-12"><span style="color:#666; opacity:0.75; font-weight:600;"> Msisdn: </span><span> ' + escape(item.msisdn) + '</span></div>';
            opt += '<div class="col-12"><span class="aai_description mb-0" style="color:#666; opacity:0.75; font-weight:600;"> Tipo de producto: </span><span>' + escape(tipoArt) + '</span></div>';
            opt += '<div class="col-12"><span class="aai_description mb-0" style="color:#666; opacity:0.85; font-weight:700;"> Articulo: </span><span>' + escape(item.product_name) + '</span></div>';
            opt += "</div>";
            return opt;
          }
        },
        load: function(query, callback) {
          //if (!query.length) return callback();
          //Debe escribir 7 digitos para que termine de autocompletar el Msisdn que entrega el instalador
          if (query.length<7) return callback();

          doPostAjax(
            '{{ route('sellerFiber.findInventoryAsigned') }}',
            function(res){
              if(!res.error){
                callback(res);
              }else{
                callback();
              }
            },
            {
              search: query,
              type: "T"
            },
            $('meta[name="csrf-token"]').attr('content')
          );
        }
      });

      function verifyInfoPort(){
        //console.log("INFO VALIDO");
        $('.loading-ajax').fadeIn();
        doPostAjax(
          "{{ route('sellerFiber.verifyInfoPort') }}",
          function(res){
            $('.loading-ajax').fadeOut();
            showMessageAjax(res.icon, res.msg);
            if(res.success){
              $('#btn-form-port').data('verify','Y');
              $("{{$Btn_id}}").attr('disabled', false);
            }else{
              $('#btn-form-port').data('verify','N');
              $("{{$Btn_id}}").attr('disabled', true);
            }
          },
          {
            msisdn: $('#dn_port').val()
          },
          $('meta[name="csrf-token"]').attr('content')
        );
      }

    @endif
    $('#blockBtnChangerPack').attr('hidden', true);
    $('#btn_changer_packToSS').attr('disabled', true);
    @if($QrPaymentCode==="EMP_MAI")
    //Significa que no tengo correo en netwey y aun no se generado url de pago
     // window.onload=actionChangerMail;
      @if($bundle=='Y' /*&& isset($obj_bundle)*/)
        actionChangerMail('{{ $data->bundle_id }}');
      @else
        actionChangerMail();
      @endif
    @endif


    @if($data->owner=='V')
      {{--externo Netwey--}}

      var macAddressField = document.getElementById('mac_input');
        // Make sure field object exists
      if (typeof macAddressField !== 'undefined') {

        // Attache event listner
        macAddressField.addEventListener('keyup', function() {
            MAC = this.value;
            ItemMAC = MAC.substr(-1);
            let regex = /^([a-f]|[0-9]|[A-F])$/;

            if(MAC.length < 17){
              //hidden the imputs y clear alert error
              $('#error-mac').html('');
              $('#error-mac').removeClass('alert').removeClass('alert-danger');
              resetFormMac();
            }
            // Allow user to use the backspace key
            if (event.keyCode !== 8 && regex.test( ItemMAC )) {
                // Format field value
                formatMacAddress(this);
            }else{
              //input invalid removed last
             MAC = MAC.substr(0, MAC.length - 1);
            document.getElementById('mac_input').value = MAC;
            }
        }, false);

        /*macAddressField.addEventListener('change', function() {
          //hidden the imputs y clear alert error
          $('#error-mac').html('');
          $('#error-mac').removeClass('alert').removeClass('alert-danger');
          resetFormMac();
        }, false);*/

      }
      //////// END process MAC

      $("#serial").bind("keydown change", function(){
        if($("#serial").val().length >= 5){
          $('.with-errors-serial').html('');
          $('.with-errors-serial').removeClass('alert').removeClass('alert-danger');
        }
      });

      //install-getDN
      //$('#form-request-dn').validator().on('submit', function(e){
      $('#install-getDN').on('click', function(e){
        e.preventDefault();

        let validate = validateInstall();
        validate.then(function(promesa_is_validate, error){
          console.log('validate A ',promesa_is_validate);

          if(promesa_is_validate){
            return 0;
          }else{
            if($('#mac_input').val()!='' && $('#nodo_red').val()!=''){

              /*var combo = document.getElementById("equipo");
              var selectedArt = combo.options[combo.selectedIndex].text;*/

              let wrapper = document.createElement('div');
              let tex = "<p class='text-left'>";
              tex += "<strong> > RESUMEN </strong></br> ";
              tex += "<strong> >> Equipo de fibra: (inserción) </strong></br> ";
              tex += "<strong> MAC: </strong>" + $('#mac_input').val() + "</br>";
              tex += "<strong> Serial: </strong>" + $('#serial').val() + "</br>";
              tex += "<strong> Articulo agendado: </strong>" + '{{$arti_install_zone['description']}}' + "</br>";
              @if($habilityPortability)
                tex += "</br><strong> >> Equipo de Telefonia: (movimiento)</strong></br> ";
                tex += "<strong> Msisdn: </strong>" + $('#txt-dn_transt').text()  +"</br>";
                tex += "<strong> Articulo: </strong>" + $('#txt-product_T').text()+"</br>";
              @endif
              tex += "</br>Verifica la información antes de continuar, la ejecución no se puede revertir</br>";
              tex += "</p>";
              wrapper.innerHTML = tex;
              let el = wrapper.firstChild;

              swal({
                title: "Movimiento de inventario",
                content: el,
                dangerMode: true,
                closeOnClickOutside: false,
                icon: "info",
                buttons: {
                  cancel: {
                    text: "Cancelar",
                    value: 'cancel',
                    visible: true,
                    className: "",
                    closeModal: true,
                  },
                  confirm: {
                    text: "Continuar",
                    value: 'save',
                    visible: true,
                    className: "",
                    closeModal: true
                  },
                },
              }).then((option) => {
                if (option == 'save') {
                  blockFormArtSerial(true);
                  doPostAjax(
                    "{{ route('sellerFiber.getMSISDNGenerate') }}",
                    function(resg){
                      if(resg.success){
                        infoConfirInstall(resg);
                      }else{
                        if(resg.code=='SERIAL'){
                          $('.with-errors-serial').html(resg.msg);
                          blockFormArtSerial(false);
                          $('.with-errors-serial').addClass('alert').addClass('alert-danger');
                        }else{
                          swal({
                            title: 'Hubo un problema asociado con la creación del articulo de fibra',
                            text: resg.msg,
                            icon: "warning",
                            button: {
                              text: "OK"
                            }
                          });
                        }
                      }
                    },
                    {
                      mac: $('#mac_input').val(),
                      serial: $('#serial').val(),
                      chk_ve: $('#chk_ve').val(),
                      idArtInstall: '{{$data->inv_article_id}}'
                    },
                    $('meta[name="csrf-token"]').attr('content')
                  );
                }
              });
            }
          }
        });
      });

      $('#close-mod-conf').on('click', function(){
          $('#chk_ve').val( $('#serial').val() );
          blockFormArtSerial(true);
      });
      {{--END externo Netwey--}}
    @else
      {{--interno Netwey--}}

      function resetListMac(){
        $('#mac_select').val('');
        $('#mac_select').data('selectize').setValue("");
        $('#mac_select').data('selectize').clearOptions();
      }

      let $dns = $('#mac_select').selectize({
        valueField: 'imei',
        searchField: 'imei',
        labelField: 'imei',
        render: {
            option: function(item, escape) {
              return '<p>'+escape(item.imei)+'</p>';
            }
        },
        load: function(query, callback) {
          if (!query.length){
            return callback();
          }
          doPostAjax(
            "{{ route('sellerFiber.getMSISDNSFiber') }}",
            function(res){
              if(!res.error){
                callback(res);
              }else{
                callback();
              }
            },
            {
              search: query,
              id: {{$data->id}}
            },
            $('meta[name="csrf-token"]').attr('content')
          );
        },
        onChange: function(value){
          if(value !== ''){
            $('#error-mac').text('');
            $('#error-mac').removeClass('alert').removeClass('alert-danger');
            let data = $dns[0].selectize.options[value];

            $('#item-selected-content').attr('hidden', null);
            $('#serial-selected').text(data.serial ? data.serial : 'S/I');
            $('#mac-selected').text(data.imei);
            $('#msisdn-selected').text(data.msisdn);
            $('#msisdn').val(data.msisdn);
          }
        }
      });

      //reg-install
      //$('#form-reg-inst').validator().on('submit', function(e){
      $('#reg-install').on('click', function(e){
        e.preventDefault();
        let validate = validateInstall();
        validate.then(function(promesa_is_validate, error){
            console.log('validate B ',promesa_is_validate);

            if(promesa_is_validate){
              return 0;
            }else{
              if($('#msisdn').val()!=''  &&
                $('#nodo_red').val()!=''){
                infoConfirInstall();
              }
            }
        });
      });
      {{--END interno Netwey--}}

    @endif

    $('#newemail_mp, #newemail_mp_copy').bind("keyup change", function (e) {
      $('.email-errors').text("");
      $('.email-errors').removeClass('alert').removeClass('alert-danger');
    });

    $('#btn_mp_mail').on('click', function(){
      var regex = /^[a-zA-Z0-9_\-\.~]{2,}@[a-zA-Z0-9_\-\.~]{2,}\.[a-zA-Z]{2,4}$/;
      var result = regex.test($('#newemail_mp').val());
      var iquals = ($('#newemail_mp').val() === $('#newemail_mp_copy').val())?true:false;

      if(result && iquals){
        $('.loading-ajax').fadeIn();
        doPostAjax(
          "{{ route('sellerFiber.setMailPayment') }}",
          function(res){
            $('.loading-ajax').fadeOut();
            if(res.success){
              $('#modal-email-mp').modal('hide');
              swal({
                title: res.title,
                text: res.msg,
                icon: res.icon
              }).then((value) => {
                $('.loading-ajax').fadeIn();
                window.location.reload();
              });
            }else{
              if(res.message == 'TOKEN_EXPIRED'){
                showMessageAjax('alert-danger','Su session a expirado, por favor actualice la página.');
              }else{
                showMessageAjax('alert-danger', res.msg);
              }
              swal({
                title: res.title,
                text: res.msg,
                icon: res.icon
              });
            }
          },
          {
            id: "{{$data->id}}",
            use_netwey: $('#equals_mp_mail').is(':checked')?true:false,
            newmail: $("#newemail_mp").val(),
            packprices: $('#btn_mp_mail').data('packprice'),
            isBundle: "{{isset($obj_bundle)?'Y':'N'}}"
          },
          $('meta[name="csrf-token"]').attr('content')
        );
      }else{
        if(!result){
          $('.email-errors').text("Email invalido");
        }else{
          $('.email-errors').text("Debes confirmar el email");
        }
        $('.email-errors').addClass('alert').addClass('alert-danger');
      }
    });

    $('#btn_changer_packToCS').on('click', function(){
      $('.loading-ajax').show();
      setTimeout(() => {
        window.location.reload();
      }, 500);
    });

    $('#btn_changer_packToSS').on('click', function(){
      $('.loading-ajax').show();
        doPostAjax(
          "{{ route('sellerFiber.setChangerPack') }}",
          function(res){
            $('.loading-ajax').hide();
            if(res.success){
              swal({
                title: res.title,
                text: res.msg,
                icon: res.icon,
                dangerMode: true,
              }).then((value) => {
                $('.loading-ajax').show();
                setTimeout(() => {
                  window.location.reload();
                }, 500);
              });
            }else{
              showMessageAjax('alert-danger',res.msg);
            }
          },
          {
            id: "{{$data->id}}",
            newpack: $(this).data('newpack'),
            is_bundle: "{{!empty($bundle)? $bundle: 'N'}}"
          },
          $('meta[name="csrf-token"]').attr('content')
        );
    });

    $('#btn_cancel_packToCS').on('click', function(){
      $('.loading-ajax').show();
        doPostAjax(
          "{{ route('sellerFiber.cancelQrPayment') }}",
          function(res){

            if(res.success || res.code == "EMP_QR"){
              window.location.reload();
            }else{
              $('.loading-ajax').fadeOut();
              showMessageAjax('alert-danger',res.msg);
            }
          },
          {id: "{{$data->id}}"},
          $('meta[name="csrf-token"]').attr('content')
        );
    });

    $('#pro-install').on('click', function(){
      $('.loading-ajax').fadeIn();
      let id = $(this).data('inst');

      swal({
        title: 'Procesando alta',
        text: "Por favor no cierre ni refresque el navegador.",
        icon: "warning",
        closeOnClickOutside: false,
        button: {
          visible: false
        }
      });

      doPostAjax(
        "{{ route('sellerFiber.doRegister') }}",
        function(res){
            $('#close-mod-conf').trigger('click');
            $('.loading-ajax').fadeOut();
            swal.close();

            if(!res.error){
              @if($data->owner=='N')
                resetListMac();
              @endif
              swal({
                title: res.title,
                text: res.message,
                icon: res.icon,
                button: {
                  text: "OK"
                }
              })
              .then((value) => {
                $('.loading-ajax').fadeIn();
                window.location.href = `{{ route('sellerFiber.installerSurvey') }}/`+id;
              });
            }else{
              @if($data->owner=='N')
                $dns[0].selectize.clearOptions();
              @endif
                if(res.message == 'TOKEN_EXPIRED'){
                    showMessageAjax('alert-danger','Su session a expirado, por favor actualice la página.');
                }else{
                  showMessageAjax('alert-danger',res.message);
                  //if(res.code=='FAI_FOR'){
                    swal({
                      title: res.title,
                      text: res.message,
                      icon: res.icon,
                      dangerMode: true,
                    });
                  //}
                }
            }
        },
        {
          id: id,
          msisdn: $('#msisdn').val(),
          nodo: $('#nodo_red').val(),
          nodo_name: $('#nodo_red option:selected').text().trim(),
          inv_bundle_T1: (document.querySelector('#dn_phone')!==null)? $('#dn_phone').val().trim():'',
          isPort: $('#typePort').is(':checked'),
          port_dn: (document.querySelector("#dn_port")!== null)? $('#dn_port').val().trim() : '',
          port_nip: (document.querySelector('#nip')!==null)? $('#nip').val().trim() : '',
          port_supplier_id: (document.querySelector("#port-prov")!== null)? $('#port-prov').val().trim() : '',
          is_bundle: "{{!empty($bundle)? $bundle: 'N'}}"
        },
        $('meta[name="csrf-token"]').attr('content')
      );
    });

    $('#request_subscrip').on('click', function(e){
      $('#request_subscrip').attr('hidden', true);
      $('#label_new_pack').attr('hidden', null);
      $('#listPacks').attr('hidden', null);
      $('#btn_cancel_packToCS').attr('hidden', null);
    });
  });
</script>

@stop

@endif
@endif

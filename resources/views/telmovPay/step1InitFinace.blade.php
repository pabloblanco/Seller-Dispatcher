<div class="col-md-12">
  @include('seller.InfoClient', ['client' => $client ])
</div>
<div class="col-md-12">
  <hr style="width: 80%; height: 1px; color: black;"/>
  @if($isActivoProcess)
  <div class="col-md-12 text-center">
    <button class="btn label-red text-white waves-effect waves-light" data-ine="{{base64_encode($client->dni)}}" id="btn-cancel-verify" name="btn-cancel-verify" title="Permite cancelar un proceso de verificación que se halla iniciado" type="button">
      Cancelar verificación
    </button>
  </div>
  <hr style="width: 80%; height: 1px; color: black;"/>
  @endif
  <h3 class="box-title">
    Datos de identificación del cliente
  </h3>
  <form id="formIdenty">
    <div class="form-group">
      <label class="col-md-12">
        CURP (Clave Única de Registro de Población)
      </label>
      <div class="col-md-12">
        <input class="form-control" id="curp" name="curp" oncut="return false" onpaste="return false" pattern="^[a-zA-Z]{4}[0-9]{2}[0-1][0-9][0-3][0-9][a-zA-Z]{6}[a-zA-Z0-9]{2}$" placeholder="Ingrese codigo curp Ej: ABCS980939ABCDEFZ9" required="" type="text" value="{{ (isset($client->code_curp) && !empty($client->code_curp))? $client->code_curp : ''}}"/>
      </div>
    </div>
    <div class="form-group">
      <label class="col-md-12">
        Correo electrónico
      </label>
      <div class="col-md-12">
        <input class="form-control" disabled="" id="email" name="email" oncopy="return false" oncut="return false" onpaste="return false" placeholder="Correo electronico" required="" type="text" value="{{(isset($client->email) && !empty($client->email))? $client->email : ''}}"/>
      </div>
    </div>
    <div class="form-group" hidden="" id="confirmate-mail">
      <label class="col-md-12">
        Confirmar correo electrónico
      </label>
      <div class="col-md-12">
        <input class="form-control" id="email2" name="email2" oncopy="return false" oncut="return false" onpaste="return false" placeholder="Confirmar correo electronico" required="" type="text" value="{{(isset($client->email) && !empty($client->email))? $client->email : ''}}"/>
      </div>
    </div>
    <div class="form-group">
      <label class="col-md-12">
        Telefono de contacto
      </label>
      <div class="col-md-12">
        <input class="form-control" disabled="" id="phone" maxlength="10" minlength="10" name="phone" oncopy="return false" oncut="return false" onpaste="return false" placeholder="Número de telefono de contacto" required="" type="number" value="{{ (isset($client->phone_home) && !empty($client->phone_home))? $client->phone_home : ''}}"/>
      </div>
    </div>
    <div class="form-group" hidden="" id="confirmate-phone">
      <label class="col-md-12">
        Confirmar telefono de contacto
      </label>
      <div class="col-md-12">
        <input class="form-control" id="phone2" maxlength="10" minlength="10" name="phone2" oncopy="return false" oncut="return false" onpaste="return false" placeholder="Confirmar telefono de contacto" required="" type="number" value="{{(isset($client->phone_home) && !empty($client->phone_home))? $client->phone_home : ''}}"/>
      </div>
    </div>
  </form>
</div>
<div class="col-md-12">
  <button class="btn btn-success waves-effect waves-light" data-ban="E" id="btn-form-edit" name="btn-form-edit" title="Permite editar el correo electronico y el telefono de contacto" type="button">
    Editar datos
  </button>
  <button class="btn btn-primary waves-effect waves-light" hidden="" id="btn-form-save" name="btn-form-save" title="Permite editar el correo electronico y el telefono de contacto" type="button">
    Guardar datos
  </button>
  <button class="btn btn-danger waves-effect waves-light" id="btn-form-verify" name="btn-form-verify" type="button">
    Verificar datos
  </button>
</div>
<div class="col-md-12 py-5 text-center" hidden="" id="getBtnQr">
  <div class="alert alert-info alert-dismissable">
    <button aria-hidden="true" class="close" data-dismiss="alert" type="button">
      ×
    </button>
    <strong>
      Nota:
    </strong>
    <br/>
    <li>
      Permitir abrir ventanas emergentes en el explorador
    </li>
    <li>
      Al concluir el escaneo del QR puede regresar al Seller
    </li>
  </div>
  <button class="btn btn-primary waves-effect waves-light" id="btn-regQr" name="btn-regQr" type="button">
    Verificar Identidad
  </button>
</div>
<div class="col-md-12 my-5 text-center">
  <div class="alert alert-danger alert-dismissable" hidden id="mjgVerify">
  </div>
</div>
<div class="col-md-12 py-5 text-center" hidden="" id="block_endVerify">
  <button class="btn btn-danger waves-effect waves-light" id="btnQr" type="button">
    Verificación finalizada
  </button>
</div>
<div id="viewQr">
</div>
<div class="col-md-12" id="viewInfoClient">
</div>
<div class="row p-b-20" hidden="" id="brand-content">
  <hr style="width: 80%; height: 1px; color: black;"/>
  <h3 class="box-title">
    Selección del smartPhone a adquirir
  </h3>
  <div class="col-md-12">
    <label class="col-md-12">
      Marca del celular
    </label>
    <button class="btn btn-success waves-effect waves-light m-t-10 m-r-10 btn-brand" data-brand="samsung" id="brad-s" name="brad-s" type="button">
      Samsung
    </button>
    <button class="btn btn-success waves-effect waves-light m-t-10 btn-brand" data-brand="other" id="brad" name="brad" type="button">
      Otra marca
    </button>
    <input id="brand-mov" name="brand-mov" type="hidden" value=""/>
  </div>
</div>
<div class="row p-b-20" hidden="" id="model-content">
  <div class="col-md-12" id="select_model">
  </div>
</div>
<div class="row p-b-20" hidden="" id="port-content">
  <div class="col-md-12">
    <label class="col-md-12">
      Tipo de portabilidad
    </label>
    <button class="btn btn-success waves-effect waves-light m-t-10 m-r-10 btn-port" data-type="SP" id="sin-port" name="sin-port" type="button">
      Sin portabilidad
    </button>
    <button class="btn btn-success waves-effect waves-light m-t-10 btn-port" data-type="CP" id="con-port" name="con-port" type="button">
      Con portabilidad
    </button>
  </div>
  <input hidden="" id="port" name="port" type="text" value=""/>
</div>
<div class="row p-b-20" id="plan-content">
</div>
{{--<div class="row p-b-20" id="planView-content">
</div>
<div class="row p-b-20" id="viewInfoCredit">
</div>--}}
<div class="row p-b-20" id="resultCredit">
</div>
<div class="row p-b-20 d-none" id="blok_Contract">
  <label class="col-md-12">
    Escanee el siguiente codigo QR para firmar el contrato
  </label>
  <div class="col-md-12 d-flex justify-content-center py-4" id="QrContract">
  </div>
  <div class="col-md-12 py-4">
    <label class="box-title col-12">
      Recibiste el dinero del enganche?
    </label>
    <div class="form-group px-3">
      <label class="custom-control custom-radio">
        {{--checked=""--}}
        <input class="custom-control-input" name="recibmoney" type="radio" value="N"/>
        <span class="custom-control-indicator">
        </span>
        <span class="custom-control-description">
          No
        </span>
      </label>
      <label class="custom-control custom-radio">
        <input class="custom-control-input" name="recibmoney" type="radio" value="Y"/>
        <span class="custom-control-indicator">
        </span>
        <span class="custom-control-description">
          Si
        </span>
      </label>
    </div>
  </div>
  <button class="btn btn-success waves-effect waves-light m-t-10" id="firm_contract" name="firm_contract" title="Dale click cuando estes 100% que se firmo en el celular el contrato" type="button">
    Notificar firma del contrato
  </button>
</div>
<div class="row p-b-20 py-4 d-none" id="blok_Enrole">
  <div class="alert alert-info alert-dismissable py-4">
    <button aria-hidden="true" class="close" data-dismiss="alert" type="button">
      ×
    </button>
    <li>
      Puedes encender el equipo que va a comprar el cliente  📱
    </li>
    <li>
      <strong>
        Recordatorio:
      </strong>
      Si iniciaste el equipo antes de este ver este mensaje debes restaurarlo de fabrica.
    </li>
    <li>
      Inicie el celular y conectalo a una red Wifi
    </li>
    <li>
      Espera que sean instaladas las actualizaciones y se instale la aplicación de TelmovPay
    </li>
    <li>
      Al finalizar la instalación de la aplicación de TelmovPay notifica que ya se enrolo el equipo
    </li>
  </div>
  <label class="col-md-12 d-none" id="labelNotSamsumg">
    Escanee el siguiente codigo QR para iniciar el enrolamiento del equipo no Samsung
  </label>
  <div class="col-md-12 d-flex justify-content-center py-4" id="QrEnrole">
  </div>
  <button class="btn btn-success waves-effect waves-light m-t-10" id="noti_enrole" name="noti_enrole" type="button">
    Notificar enrolamiento
  </button>
</div>
<div class="row p-b-20 d-none" id="blok_sincroni">
  <label class="col-md-12">
    Escanee el siguiente codigo para sincronizar la APP de telmovPay con la financiación del cliente
  </label>
  <div class="col-md-12 d-flex justify-content-center py-4" id="QrSincroni">
  </div>
  <button class="btn btn-success waves-effect waves-light m-t-10" id="btn_sincroni" name="btn_sincroni" title="Dale click cuando se halla sincronizado la app de telmovPay en el celular" type="button">
    Notificar que fue sincronizado
  </button>
</div>
<div class="col-md-12 text-center d-none" id="blok_end">
  <button class="btn btn-success waves-effect waves-light m-t-10" id="btn_end" name="btn_end" title="El proceso de TelmovPay se a completado. Debes dirigirte a: 'Venta + Activacion' para continuar el proceso" type="button">
    Finalizar
  </button>
</div>
{{--@section('scriptJS')--}}
<script src="{{ asset('plugins/bower_components/jquery-validation/dist/jquery.validate.min.js') }}">
</script>
<script type="text/javascript">

  ScrollBotton = function (){
    $("html, body").animate({scrollTop:$(document).height()}, 'slow');
  }
  resetView = function (level){
    if(level==1){
      $('#mjgVerify').attr('hidden', true);
      $('#mjgVerify').html('');
      $('#viewInfoClient').html('');
      $('#brand-content').attr('hidden', true);
      $('#select_model').html('');
      $('#port-content').attr('hidden', true);
    }
    if(level==1 || level==2){
      $('#plan-content').html('');
    //  $('#viewInfoCredit').html('');
      $('#resultCredit').html('');
    }
    if(level==1 || level==2 || level==3){
      $('#blok_Contract').addClass('d-none');
      $('#blok_Contract').removeClass('d-block');
      $('#blok_Contract input:radio[name=recibmoney]:checked').each(function(){
        $(this).removeAttr("checked");
        $(this).val("");
      });
      $('#QrContract').html('');
      $('#blok_Enrole').addClass('d-none');
      $('#blok_Enrole').removeClass('d-block');
      $('#labelNotSamsumg').addClass('d-none');
      $('#labelNotSamsumg').removeClass('d-block');
      $('#QrEnrole').html('');
      $('#blok_sincroni').removeClass('d-block');
      $('#blok_sincroni').addClass('d-none');
      $('#blok_end').removeClass('d-block');
      $('#blok_end').addClass('d-none');
    }
  }
  ViewBtnEdit = function (res){
      $('#btn-form-edit').html('Editar datos');
      $('#btn-form-save').attr('hidden', true);
      $('#btn-form-verify').attr('hidden', null);
     // $('#getBtnQr').attr('hidden', null);
      var bandera = document.getElementById("btn-form-edit");
      bandera.dataset.ban = 'E';
     // $(this).data('ban','E');
      $('#phone').attr('disabled', true);
      $('#confirmate-phone').attr('hidden', true);
      $('#email').attr('disabled', true);
      $('#confirmate-mail').attr('hidden', true);
    }

  $('#btn-form-edit').on('click', function(e){
      var bandera = document.getElementById("btn-form-edit");

      if(bandera.dataset.ban == 'E'){
        $('#btn-form-edit').html('Cancelar edición');
        $('#getBtnQr').attr('hidden', true);
        $('#block_endVerify').attr('hidden', true);
        $('#btn-form-save').attr('hidden', null);
        $('#btn-form-verify').attr('hidden', true);
        bandera.dataset.ban = 'C';
        $('#phone2').val('');
        $('#email2').val('');
        $('#phone').attr('disabled', false);
        $('#confirmate-phone').attr('hidden', null);
        $('#email').attr('disabled', false);
        $('#confirmate-mail').attr('hidden', null);
      }else{
          ViewBtnEdit();
      }
      resetView(1);
  });

  jQuery.extend(jQuery.validator.messages, {
       required: "Este campo es obligatorio.",
       number: "Por favor, escribe un número entero válido.",
       digits: "Por favor, escribe sólo dígitos.",
       equalTo: "Por favor, escribe el mismo valor de nuevo.",
       maxlength: jQuery.validator.format("Por favor, no escribas más de {0} caracteres."),
       minlength: jQuery.validator.format("Por favor, no escribas menos de {0} caracteres."),
     });
  jQuery.validator.addMethod("mailValidate", function(value, element) {
    return this.optional(element) || /^[a-zA-Z0-9_\-\.~]{2,}@[a-zA-Z0-9_\-\.~]{2,}\.[a-zA-Z]{2,4}$/.test(value);
  }, 'Email inválido.');

  jQuery.validator.addMethod("curlValidate", function(value, element) {
    return this.optional(element) || /^[a-zA-Z]{4}[0-9]{2}[0-1][0-9][0-3][0-9][a-zA-Z]{6}[a-zA-Z0-9]{2}$/.test(value);
  }, 'curp inválido.');


  $("#formIdenty").validate({
          rules: {
            curp: {
              required: true,
              curlValidate: true
            },
            email: {
              required: true,
              mailValidate: true
            },
            email2: {
              equalTo: "#email"
            },
            phone: {
              required: true,
              digits: true,
              minlength: 10,
              maxlength: 10
            },
            phone2: {
              equalTo: "#phone"
            }
          }
  });

  isSaveContact = function (res){
      resetView(1);
      $('.loading-ajax').fadeOut();

      if((res.success && !res.error) || (!res.success && res.error)){
        showMessageAjax(res.icon, res.message);
      }
      if(res.success){
        ViewBtnEdit();
      }
  }

  SaveInfoContact = function (){
      $('.loading-ajax').show();
      $('#getBtnQr').attr('hidden', true);
      $('#block_endVerify').attr('hidden', true);
      doPostAjax(
        "{{ route('telmovpay.updateConctactClient') }}",
          isSaveContact,
          {
            ine: '{{$client->dni}}',
            email: $("#email").val(),
            phone: $('#phone').val(),
            curp: $('#curp').val()
        },
          '{{ csrf_token() }}'
      );
  }

  $('#btn-form-save').on('click', function(e){
    valid = $("#formIdenty").valid();
    if(!valid){
       showMessageAjax('alert-danger', 'Por favor revisa los datos de identificación del cliente.');
    }else{
      SaveInfoContact();
    }
  });

  @if($isActivoProcess)
    isCancelTelmov = function (res){
      $('.loading-ajax').fadeOut();
      showMessageAjax(res.icon, res.message);
      setTimeout(() => {  location.reload(); }, 1000);
    }
    $('#btn-cancel-verify').on('click', function(e){
      var bandera = document.getElementById("btn-cancel-verify");
      //var encodedStringBtoA = btoa(decodedStringBtoA);
      //var decoStringAtoB = atob(bandera.dataset.ine);
      doPostAjax(
        "{{ route('telmovpay.cancelTelmov') }}",
          isCancelTelmov,
          {
            ine: bandera.dataset.ine
        },
          '{{ csrf_token() }}'
      );
    });
  @endif

  isOKmail = function (res){
    $('.loading-ajax').fadeOut();
    showMessageAjax(res.icon, res.message);
    if(res.success){
        $('#getBtnQr').attr('hidden', null);
    }else{
      $('#getBtnQr').attr('hidden', true);
      $('#block_endVerify').attr('hidden', true);
    }
  }

  $('#btn-form-verify').on('click', function(e){
    valid = $("#formIdenty").valid();
    if(!valid){
       showMessageAjax('alert-danger', 'Por favor revisa los datos de identificación del cliente.');
    }else{

      SaveInfoContact();
      doPostAjax(
        "{{ route('telmovpay.chekingMail') }}",
          isOKmail,
          {
            ine: '{{$client->dni}}',
            email: $("#email").val(),
            phone: $('#phone').val(),
            curp: $('#curp').val()
        },
          '{{ csrf_token() }}'
      );
    }
  });

  viewPlan = function(res){
    $('.loading-ajax').fadeOut();
    if(res.success){
      $('#plan-content').html(res.html);
      ScrollBotton();
    }
  }

  BuildSelectPlan = function (){
    resetView(2);
    $('.loading-ajax').show();
    doPostAjax(
        "{{ route('telmovpay.buildPlan') }}",
          viewPlan,
          {
            brand: $('#brand-mov').val(),
            model: $('#modelPhone').val(),
            port:  $('#port').val()
        },
          '{{ csrf_token() }}'
      );
  }

  view_Qr = function (res){
    $('.loading-ajax').fadeOut();
    showMessageAjax(res.icon, res.message);
    if(res.success){
      if(!res.infoClient){
        $('#getBtnQr').attr('hidden', true);
        $('#block_endVerify').attr('hidden', null);
         // Abrir nuevo tab
        var win = window.open(res.urlQR, '_blank');
        // Cambiar el foco al nuevo tab (punto opcional)
        win.focus();
        //$('#viewQr').html(res.html);
        //$('#identity-qr').modal('show');
      }else{
        if(res.html.length > 0 && res.infoClient){
          $('#viewInfoClient').html(res.html);
          $('#brand-content').attr('hidden', null);
          ScrollBotton();
        }
      }
    }else{
      if(res.resetView){
        $('#getBtnQr').attr('hidden', true);
        $('#mjgVerify').html(res.message);
        $('#mjgVerify').attr('hidden', null);
        ScrollBotton();
        //setTimeout(() => {  location.reload(); }, 2500);
      }else{
        if(res.html.length > 0 && res.infoClient){
          $('#viewInfoClient').html(res.html);
          $('#brand-content').attr('hidden', null);
          ScrollBotton();
        }
      }
    }
  }

  $('#btn-regQr').on('click', function(e){
    $('.loading-ajax').show();
    resetView(1);
    doPostAjax(
        "{{ route('telmovpay.requestQr') }}",
          view_Qr,
          {
            ine: '{{$client->dni}}'
        },
          '{{ csrf_token() }}'
      );
  });

  lastQr = function (res){
    $('.loading-ajax').fadeOut();
    $('#getBtnQr').attr('hidden', null);
    $('#block_endVerify').attr('hidden', true);
    showMessageAjax(res.icon, res.message);
    if(res.success){
      if(res.html.length > 0 && res.infoClient){
        $('#viewInfoClient').html(res.html);
        $('#brand-content').attr('hidden', null);
        ScrollBotton();
      }
    }else{
      //Deberia volver a verificar el correo para estar seguros
      $('#getBtnQr').attr('hidden', true);
    }
  }

  $('#btnQr').on('click', function(e){
    $('.loading-ajax').show();
    doPostAjax(
        "{{ route('telmovpay.requestQrVerifyLast') }}",
          lastQr,
          {
            ine: '{{$client->dni}}'
        },
          '{{ csrf_token() }}'
      );
  });

  let activeBtn = function(listBtn, btnPort=false){
       listBtn.forEach(btnNode => {
        btnNode.onclick = () => {
            listBtn.forEach( btnNode =>{
                btnNode.classList.remove('btn-danger');
            });
            btnNode.classList.add('btn-danger');
            if(btnPort){
                btnPort.forEach(btnNode3 => {
                    btnNode3.classList.remove('btn-danger');
                });
           }
        }
        });
  }

  let getBtnSelect = function(selector){
        return document.querySelectorAll(selector);
  }

  const btnBrand = getBtnSelect('#brand-content button');
  const btnPort = getBtnSelect('#port-content button');
  activeBtn(btnBrand, btnPort);
  activeBtn(btnPort);

  let listModelSmartPhone = function(res){
     $('.loading-ajax').fadeOut();
     if(!res.error){
        $('#select_model').html(res.htmlModel);
        $('#model-content').attr('hidden', null);
        ScrollBotton();
     }else{
       if(res.message == 'TOKEN_EXPIRED'){
             showMessageAjax('alert-danger','Su session a expirado, por favor actualice la página.');
         }else{
           $('#alert-comp').removeClass('alert-success');
           $('#alert-comp').removeClass('alert-warning');
           $('#alert-comp').addClass('alert-danger');
           $('#alert-comp').text('No hay modelos disponibles');
           $('#alert-comp').show();
         }
     }
  }

  $('.btn-brand').on('click', function(e){
     $('.loading-ajax').show();
     let type = $(this).data('brand');
     $('#brand-mov').val(type);
     $('#port-content').attr('hidden', true);
     resetView(2);
     doPostAjax(
         "{{ route('seller.listModelSmartPhone') }}",
         listModelSmartPhone,
         {
         brand: type,
         _token: '{{ csrf_token() }}'
      });
  });

  $('.btn-port').on('click', function(e){
    let type = $(this).data('type');
    $('#port').val(type);

    BuildSelectPlan();
  });

  ResultEndContract = function(res){
     $('.loading-ajax').fadeOut();
     if(!res.success){
        showMessageAjax(res.icon, res.message);
     }else{
      if($('#brand-mov').val().toUpperCase() != 'SAMSUNG'){
        $('#QrEnrole').html(res.QrInitEnrole);
        $('#blok_Enrole').removeClass('d-none');
        $('#blok_Enrole').addClass('d-block');
        $('#labelNotSamsumg').removeClass('d-none');
        $('#labelNotSamsumg').addClass('d-block');
        $('#QrEnrole svg').attr('width', '300px');
        $('#QrEnrole svg').attr('height', '300px');
        ScrollBotton();
      }else{
        //Exclusivo samsung
        $('#blok_Enrole').removeClass('d-none');
        $('#blok_Enrole').addClass('d-block');
        $('#labelNotSamsumg').addClass('d-none');
        $('#labelNotSamsumg').removeClass('d-block');
      }
     }
  }

  End_contract = function(){
    $('.loading-ajax').show();
    doPostAjax(
         "{{ route('telmovpay.endContract') }}",
         ResultEndContract,
         {
         dni: '{{$client->dni}}',
         brand: $('#brand-mov').val(),
         _token: '{{ csrf_token() }}'
      });
  }

  $('#firm_contract').on('click', function(e){

    var recib = $("input:radio[name=recibmoney]:checked").val();

    if(recib != '' && recib !== undefined){
      if(recib == 'N'){
        swal({
          title: "Seguro no recibiste $ "+$('#enganche').val()+" del enganche?",
          text: "Esta acción es requerida de lo contrario no puedes usar una compra a travez de TelmovPay",
          icon: "warning",
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
              value: 'ok',
              visible: true,
              className: "",
              closeModal: true
            },
          },
          dangerMode: true,
        });
      }else{
         End_contract();
      }
    }else{
      showMessageAjax('alert-danger', 'Debes confirmar si recibiste el dinero del enganche');
    }
  });

  ResultEndEnrole = function(res){
    $('.loading-ajax').fadeOut();
     if(!res.success){
        showMessageAjax(res.icon, res.message);
     }else{
        $('#QrSincroni').html(res.QrSincronice);
        $('#blok_sincroni').removeClass('d-none');
        $('#blok_sincroni').addClass('d-block');
        $('#QrSincroni svg').attr('width', '300px');
        $('#QrSincroni svg').attr('height', '300px');
        ScrollBotton();
     }
  }

  $('#noti_enrole').on('click', function(e){
    $('.loading-ajax').show();
    doPostAjax(
         "{{ route('telmovpay.endEnrole') }}",
         ResultEndEnrole,
         {
         dni: '{{$client->dni}}',
         _token: '{{ csrf_token() }}'
      });
  });

  EnroleSincronice = function(res){
    $('.loading-ajax').fadeOut();
     if(!res.success){
        showMessageAjax(res.icon, res.message);
     }else{
        $('#blok_end').removeClass('d-none');
        $('#blok_end').addClass('d-block');
     }
  }

  $('#btn_sincroni').on('click', function(e){
    $('.loading-ajax').show();
    doPostAjax(
         "{{ route('telmovpay.sincronizeApp') }}",
         EnroleSincronice,
         {
         dni: '{{$client->dni}}',
         _token: '{{ csrf_token() }}'
      });
  });

  $('#btn_end').on('click', function(e){

        // Cambiar el foco al nuevo tab (punto opcional)
    swal({
          title: "Exito!",
          text: "Felicidades completaste la configuración del equipo, ahora debes realizar la 'Venta + Activación'",
          icon: "success",
          button: {text: "OK"},
      }).then(() => {
        @if(hasPermit('ACV-DSE'))
          var win = window.open("{{route('seller.index')}}", '_self');
          win.focus();
        @else
          swal("Debes solicitar a netwey la asignación de la politica correspondiente para vender y activar");
          setTimeout(() => {  location.reload(); }, 5000);
        @endif
      });
  });
</script>
{{--@stop--}}

<div class="col-sm-12">
  <h3 class="box-title">Verificación de telefono del cliente</h3>
  <div class="alert label-primary text-white alert-dismissable" id="alert-verify-phone">
      <button aria-hidden="true" class="close" data-dismiss="alert" type="button">
        ×
      </button>
      <span>
        <h4>
          <strong>
            &#128241; Atención, se hara uso de mensajeria SMS:
          </strong>
        </h4>
      </span>
      <br>
      <li>
        <strong>1 -</strong> El cliente debe contar con un telefono celular ya que sera(n) enviado(s) SMS de importancia para el proceso de compra de fibra que se lleva a cabo.
      </li>
      <li>
        <strong>2 -</strong> El vendedor realizara solicitud de información que sera enviada via SMS al telefono del cliente.
      </li>
      <li>
        <strong>3 -</strong> Solicitar al cliente que por favor debe leer con atención la información que reciba via SMS .
      </li>
  </div>
  <div class="row justify-content-center align-items-center pb-4" id="block_confirm_contact">
    @php
    /*
    if(!isset($isVerifyPhone)){
      $isVerifyPhone = true;
    }
    if($isVerifyPhone){
      $pedirtoken='';//insertVerifyPhone
      $esverificado='d-none';//esverificado
    }else{
      $pedirtoken='d-none';//insertVerifyPhone
      $esverificado='';//esverificado
    }
    */
    @endphp
    <div class="row justify-content-center" id="insertVerifyPhone">
      <div class="col-md-8 col-12">
        <label>Telefono de contacto del cliente:</label>
        <input class="form-control" id="msisdn-contact" minlength="10" maxlength="10" placeholder="Ejemplo: 1234567890" type="number"/>
      </div>
      <div class="col-md-4 col-12 text-center pt-md-0 pt-4">
        <button class="btn btn-success waves-effect waves-light" onclick="VerifyPhone()" type="button">
          Verificar telefono
        </button>
      </div>
    </div>
    <div class="row justify-content-md-between justify-content-center d-none" id="phoneVerifyOK">
      <div class="col-md-6 col-12 text-center">
        <label>Telefono de contacto del cliente verificado:</label>
        <strong>&nbsp;&nbsp;<label id="phoneOK"> </label></strong>
        <span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
        <i class="ti-check-box" title="Celular verificado" style="cursor:pointer;"></i>
      </div>
      <div class="col-md-6 col-12 text-center pt-md-0 pt-4">
        <button class="btn btn-success waves-effect waves-light" onclick="newVerify()" type="button" title="Puedes configurar un nuevo telefono y verificarlo">
          Verificar otro telefono
        </button>
        <button class="btn btn-primary waves-effect waves-light" id="nextV" onclick="nexVerify()" type="button" title="Continuar con el telefono verificado">
          Continuar
        </button>
      </div>
    </div>
    <input type="hidden" id="client_dni" name="client_dni">
  </div>
</div>

<div class="col-sm-12">
<div aria-labelledby="myModalLabel" class="modal fade py-5" id="modalNewToken" role="dialog" tabindex="-1" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog py-5" role="document" style="width: 94%; height: 100%; margin-top: @handheld 50% @elsehandheld 14% @endhandheld">
    <div class="modal-content">
      <div class="modal-header" style="text-align: center;">
        <button aria-label="Close" class="close close_modal_cancel_app" data-dismiss="modal" type="button">
          <span aria-hidden="true">
            ×
          </span>
        </button>
          <span>
            <i class="glyphicon glyphicon-exclamation-sign" style="font-size: 50px; color: #f68e6b;">
            </i>
          </span>
          <strong>
            <h4 class="modal-title" id="modalTitle_newToken">

            </h4>
          </strong>
          <h5 id="msgModal">

          </h5>
      </div>
      <div class="modal-body text-center" id="blockNewToken">
        <input type="text" name="tokensms" id="tokensms" placeholder="ABC123" hidden>
        <div id="resultToken"></div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-default close_modal_cancel_app" id="closeVerify" data-dismiss="modal" type="button">
          Cancelar
        </button>
        <div id="counterCode">
          <button class="btn btn-danger" id="newToken" style="background-color: #e64942;" type="button" onclick="getToken()">
          Solicitar codigo
          </button>
          <button class="btn btn-danger" id="hms" hidden title="Tiempo de espera para volver a intentar pedir otro codigo" style="background-color: #e64942;"></button>
        </div>
        <button class="btn btn-primary" hidden id="verifytoken" type="button" onclick="sendToken()">
          Verificar codigo
        </button>
        <button class="btn btn-danger" hidden id="requestAutorizer" type="button" onclick="requestAutorized()">
          Solicitar autorización
        </button>
        <button class="btn btn-primary" hidden id="nextverifytoken" type="button" onclick="nexVerify()">
          Continuar agendamiento
        </button>
      </div>
    </div>
  </div>
</div>
</div>
<script type="text/javascript">
  var h = 0;
  var m = 0;
  var s = 0;
  var idIntervalo = 0;
  var idIntervaloChecking = 0;
  sessionStorage.setItem("intentos", "0");

  $("#modalNewToken").on("hidden.bs.modal", function () {
    clearInterval(idIntervalo);
    //Verifico en que status esta la verificacion del celular
  });

  function changerModeVerify(mode = "INPUT"){
   if(mode == "INPUT"){
    //Debo verificar phone
    newVerify();
   }else{
    $('#nextV').attr('hidden', null);
    $('#insertVerifyPhone').addClass('d-none');
    $('#phoneVerifyOK').removeClass('d-none');
   }
  }

  function newVerify(){
    //Voy a verificar un nuevo telefono y agrego el focus en el input
    $('#closeVerify').attr('hidden', null);//boton de cerrar
    $('.close_modal_cancel_app').attr('hidden', null);//boton de cerrar
    $('#phoneVerifyOK').addClass('d-none');
    $('#insertVerifyPhone').removeClass('d-none');
    $('#msisdn-contact').focus();
  }

  function nexVerify(){
    //Se verifico el telefono OK y pintara como verificado el telefono, se quita el boton de continuar y listamos los estados.
    changerModeVerify("VALID");
    $('#nextV').attr('hidden', true);
    $('#modalNewToken').modal('hide');
    $('#blockState').attr('hidden', null);
  }

  function decremented_timer(etiqueta = "hms", type = "NEW_TOKEN"){
    h = 0;
    m = 2;
    s = 60;
    document.getElementById(etiqueta).innerHTML="00:03:00";
    escribirTiempo(etiqueta, type);
    idIntervalo = setInterval(() => {
                      escribirTiempo(etiqueta, type);
                  }, 1000);
    document.querySelector("#"+etiqueta).removeEventListener("click",decremented_timer);
  }

  function escribirTiempo(etiqueta, type){
    //console.log(h+':'+m+':'+s);
    if(h<=0 && m<=0 && s<=1){
      //cerramos el contador xq llego a tope 00:00:00
      $('#'+etiqueta).attr('hidden', true);

      if(type == "NEW_TOKEN"){
        //Proceso de verificacion celular
        if(sessionStorage.getItem("intentos")<=3){
          //Puedo pedir otro token
          $('#counterCode #newToken').attr('hidden', null);
        }else{
          //Pido autorizacion
          $('#requestAutorizer').attr('hidden', null);
        }
      }else{
        if(type == 'SEND_URL'){
          //Proceso de reenvio de contrato
          //habilito volver a reenviar
          $('#requestQr_tyc_resend').attr('hidden', null);
          $('#txt_resend').attr('hidden', true);
        }
      }
      clearInterval(idIntervalo);
      //console.log("Reset contador");
    }else{
      var hAux, mAux, sAux;

      s--;
      if (s<1){
        m--;
        s=59;
      }
      if (m<0){
        h--;
        m=59;
      }
      if (h<0){h=24;}

      if (s<10){sAux="0"+s;}else{sAux=s;}
      if (m<10){mAux="0"+m;}else{mAux=m;}
      if (h<10){hAux="0"+h;}else{hAux=h;}
      document.getElementById(etiqueta).innerHTML = hAux + ":" + mAux + ":" + sAux;
    }
  }

  function getToken(){
    $('#resultToken').html('');
    $('#tokensms').val('');
    //Se solicito el token y se necesita luego recibir para verificarlo
    if(sessionStorage.getItem("intentos")<=3){

      $('.loading-ajax').fadeIn();
      doPostAjax(
        "{{ route('sellerFiber.newToken') }}",
        function(res){
          $('.loading-ajax').fadeOut();
          if(res.success){
            //Ingresamos el codigo enviado
            $('#tokensms').attr('hidden', null);
            $('#counterCode #hms').attr('hidden', null);
            $('#counterCode #newToken').attr('hidden', true);
            $('#verifytoken').attr('hidden', null);
            sessionStorage.setItem("intentos", res.intento);
            decremented_timer("hms", "NEW_TOKEN");
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
          phoneVal: $("#msisdn-contact").val(),
          clientId: $(".client-dni").text().trim()
        },
        $('meta[name="csrf-token"]').attr('content')
      );
    }else{
      swal({
        title: "No recomendamos que pidas otro token",
        text: "Sugerimos usar la opcion 'Solicitar autorización' y a la par informar a mesa de control que autorice continuar el proceso sin verificar el telefono",
        icon: "warning"
      });
      $('#requestAutorizer').attr('hidden', null);
    }
  }

/**
 * [sendToken Se ingresa el token que se recibio via sms para verificar el celular, si es valido el telefono del prospecto se actualiza]
 * @param  {[type]} phoneClient [description]
 * @return {[type]}             [description]
 */
  function sendToken(phoneClient){
    $('#resultToken').html('');

   // var key = $('.swal-content #tokensms').val();
    var key = $('#tokensms').val();
    var regex = /^[a-zA-Z]{3}[0-9]{3}$/;
    var result = regex.test(key);
    if(result){

      $('.loading-ajax').fadeIn();
      doPostAjax(
        "{{ route('sellerFiber.verifyPhone') }}",
        function(res){
          $('.loading-ajax').fadeOut();
          $('#resultToken').html(res.icon);

          if(res.success){
            //Ingresamos el codigo valido
            $('#tokensms').attr('hidden', true);//input de token
            $('#closeVerify').attr('hidden', true);//boton de cerrar
            $('.close_modal_cancel_app').attr('hidden', true);//boton de cerrar
            $('#verifytoken').attr('hidden', true);//boton de verificacion
            $('#counterCode').attr('hidden', true);//boton de conteo
            $('#requestAutorizer').attr('hidden', true);//boton de autorizacion
            $('#nextverifytoken').attr('hidden', null);//boton de seguir
          }else{
            if(res.message == 'TOKEN_EXPIRED'){
                showMessageAjax('alert-danger','Su session a expirado, por favor actualice la página.');
            }else{
              /*@handheld
                swal({
                  title: res.title,
                  text: res.msg,
                  icon: res.icon
                });
              @elsehandheld
                var tipy="alert-danger";
                showMessageAjax(tipy, res.msg);
              @endhandheld*/
            }
          }
        },
        {
          phoneVal: $("#msisdn-contact").val(),
          clientId: $(".client-dni").text().trim(),
          tokenPhone: $('#tokensms').val()
        },
        $('meta[name="csrf-token"]').attr('content')
      );
    } else {
      /*@handheld
        swal({
          title: res.title,
          text: res.msg,
          icon: res.icon
        });
      @elsehandheld
          var tipy="alert-danger";
          showMessageAjax(tipy, res.msg);
      @endhandheld*/

      swal("El codigo ingresado no tiene un formato valido para continuar", {
        icon: "warning",
      });
    }
  }

  function checkingAutorized(){
    doPostAjax(
        "{{ route('sellerFiber.checkingAutorized') }}",
        function(res){
          $('.loading-ajax').fadeOut();
          var today = new Date();
          var now = today.toLocaleString();
          //console.log('RESPUESTA '+res.code +' - '+now);
          if(res.success){
            $('#resultToken').html(res.icon+res.msg);
            if(res.code=="AUTHORIZED"){
              $('#nextverifytoken').attr('hidden', null);//boton de seguir
              $('#blockState').attr('hidden', null);
            }else{
              if(res.code=="REJECTED"){
                //No se puede seguir, fue rechado seguir sin verificar el telefono
                $('#blockState').attr('hidden', true);
                ResetViewFiberCite('states');
                $('#closeVerify').attr('hidden', null);
              }
            }
            if(res.code!="CREATE"){
              //Si ya se recibio respuesta de mesa se apaga la verificacion y se muestra que respondio mesa
              clearInterval(idIntervaloChecking);
              $('#closeVerify').attr('hidden', null);
            }
          }else{
            if(res.message == 'TOKEN_EXPIRED'){
                showMessageAjax('alert-danger','Su session a expirado, por favor actualice la página.');
            }else{
              /*@handheld
                swal({
                  title: res.title,
                  text: res.msg,
                  icon: res.icon
                });
              @elsehandheld
                var tipy="alert-danger";
                showMessageAjax(tipy, res.msg);
              @endhandheld*/
            }
          }
        },
        {
          phoneVal: $("#msisdn-contact").val(),
          clientId: $(".client-dni").text().trim()
        },
        $('meta[name="csrf-token"]').attr('content')
      );

  }

  function requestAutorized(){

    $('#resultToken').html('');
    let num = $("#msisdn-contact").val();
    var regex = /^[0-9]{10}$/;
    var result = regex.test(num);

    if(result){
      $('#requestAutorizer').attr('hidden', true);//boton de autorizacion
      $('#tokensms').attr('hidden', true);//entrada de codigo
      $('#closeVerify').attr('hidden', true);//boton de cerrar
      $('.close_modal_cancel_app').attr('hidden', true);//boton de cerrar
      $('.loading-ajax').fadeIn();
      doPostAjax(
        "{{ route('sellerFiber.requestAutorized') }}",
        function(res){
          $('.loading-ajax').fadeOut();

          if(res.success){
            //Ingresamos la solicitud de autorizacion
            $('#resultToken').html(res.icon);
            //Iniciamos el revisado automatico cada minuto cuando mesa de control apruebe o rechace la solicitud de autorizacion
            idIntervaloChecking  = setInterval(checkingAutorized, 60000);
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
          phoneVal: $("#msisdn-contact").val(),
          clientId: $(".client-dni").text().trim()
        },
        $('meta[name="csrf-token"]').attr('content')
      );
    }else{
      swal({
        title: "Error en datos a enviar",
        text: "Se debe suministrar un numero de telefono valido (10 digitos)",
        icon: 'warning'
      });
    }
  }

/**
 * [VerifyPhone Realizare una nueva verificacion de telefono]
 */
 function VerifyPhone(){
  let num = $("#msisdn-contact").val();
  var regex = /^[0-9]{10}$/;
  var result = regex.test(num);
  if(result){
    $('#phoneOK').html(num);
    $('#resultToken').html('');
    $('#tokensms').val('');
    //Verificamos cuantas veces se ha hecho solicitud de codigo para el dni con ese telefono en el dia, solo se permite 3
    $('.loading-ajax').fadeIn();
      doPostAjax(
        "{{ route('sellerFiber.cantToken') }}",
        function(res){
          $('.loading-ajax').fadeOut();
          if(res.success){
            sessionStorage.setItem("intentos", res.msg);
            if(idIntervalo!=0){
              //Detengo el cronometro
              clearInterval(idIntervalo);
            }
            $('#modalTitle_newToken').text('Verificación del celular '+num);
            $('#tokensms').attr('hidden', true);
            $('#verifytoken').attr('hidden', true);
            $('#nextverifytoken').attr('hidden', true);

            if(res.msg < 3){
              //puedo pedir token
              $('#counterCode').attr('hidden', null);
              $('#counterCode #hms').attr('hidden', true);
              $('#counterCode #newToken').attr('hidden', null);
              $('#requestAutorizer').attr('hidden', true);
              $('#msgModal').html("A continuación el telefono ingresado recibira un SMS con un codigo el cual se debe suministrar al vendedor para continuar! <br/><br/> <strong>Nota: </strong> Por favor no cierre esta ventana para ingresar el codigo recibido.");
            }else{
              //Miro si tiene verificacion el celular
              $('#counterCode').attr('hidden', true);
              if(res.code == "VERIFIED"){
                //Agote los 3 intentos pero tengo registro de verificacion
                $('#msgModal').html("El telefono ingresado agoto los intentos de solicitud de tokens de verificación, pero existe registro que el celular esta verificado en sistema, tienes opción de continuar con el telefono registrado o realizar cambio de numero de celular de contacto del cliente y proceder a verificarlo.");
                $('#nextverifytoken').attr('hidden', null);
                $('#closeVerify').attr('hidden', true);
              }else{
                //debo pedir autorizacion a mesa de control desde el primero momento
                $('#msgModal').html("El telefono ingresado agoto los intentos de solicitud de tokens de verificación, recomendamos solicitar autorización a mesa de control para continuar con el proceso de venta de fibra.<br/><br/> <strong>Nota: </strong> Por favor no cierre esta ventana hasta recibir la aprobación de mesa de control.");
                $('#requestAutorizer').attr('hidden', null);
              }
            }
            $('#modalNewToken').modal('show');

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
          phoneVal: $("#msisdn-contact").val(),
          clientId: $(".client-dni").text().trim()
        },
        $('meta[name="csrf-token"]').attr('content')
      );
  }else{
    swal({
      title: "Error en datos a enviar",
      text: "Se debe suministrar un numero de telefono valido (10 digitos)",
      icon: 'warning'
    });
  }
}
</script>

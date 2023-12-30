{{-- Modal de edición --}}

<div aria-hidden="true" aria-labelledby="edit-modal" data-backdrop="static" data-keyboard="false" class="modal fade" id="edit-modal" role="dialog" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content" style="top: 32vh; margin-bottom: 45px;">
      <div class="modal-header">
        <button aria-label="Close" class="close close_modal_edit_app" data-failphone="{{$client->phoneFail}}" data-failmail="{{$client->mailFail}}" type="button">
          ×
        </button>
        <h4 class="modal-title">
          Editar datos del cliente.
        </h4>
      </div>
      <form action="" class="form-horizontal" data-toggle="validator" id="editclientformodal" method="POST" name="editclientformodal">
        {{ csrf_field() }}
        <div class="modal-body">
          <input id="dni" name="dni" type="hidden" value="{{ $client->dni }}">
            <div class="form-group">
              <label class="col-md-12">
                Nombres
              </label>
              <div class="col-md-12">
                <input class="form-control" id="name" name="name" required="" type="text" value="{{ $client->name }}" placeholder="Nombres del prospecto">
                  <div class="help-block with-errors">
                  </div>
                </input>
              </div>
            </div>
            <div class="form-group">
              <label class="col-md-12">
                Apellidos
              </label>
              <div class="col-md-12">
                <input class="form-control" id="last_name" name="last_name" placeholder="Apellidos del prospecto" required="" type="text" value="{{ $client->last_name }}">
                  <div class="help-block with-errors">
                  </div>
                </input>
              </div>
            </div>
            <div class="form-group">
              <div class="col-sm-12">
                <label>
                  Teléfono principal (*)
                </label>
                <span>
                  (10 dígitos)
                </span>
              </div>
              <div class="col-md-12">
                {{--pattern="^[0-9]{10}$"--}}
                <input class="form-control" id="phone" maxlength="10" minlength="10" name="phone" required="" type="text" value="{{ $client->phone_home }}" placeholder="1234567890">
                  <div class="help-block with-errors">
                  </div>
                </input>
              </div>
            </div>
            <div class="form-group">
              <div class="col-sm-12">
                <label>
                  Teléfono secundario
                </label>
                <span>
                  (10 dígitos)
                </span>
              </div>
              <div class="col-md-12">
                {{--pattern="^[0-9]{10}$"--}}
                <input class="form-control" id="phone2" maxlength="10" minlength="10" name="phone2" type="text" value="{{ !empty($client->phone) ? $client->phone : '' }}" placeholder="1234567890">
                  <div class="help-block with-errors">
                  </div>
                </input>
              </div>
            </div>
            <div class="form-group">
              <label class="col-md-12">
                Email (*)
              </label>
              <div class="col-md-12">
                {{--pattern="(([a-z]|[0-9]|[._-]))+@([a-z]|[0-9])+\.[a-z]+"--}}
                {{--data-error="Dirección de email no válida"--}}
                <input class="form-control"  id="email" name="email"  placeholder="correo@servidor.com" type="email" value="{{ !empty($client->email) ? $client->email : '' }}">
                  <div class="help-block with-errors">
                  </div>
                </input>
              </div>
            </div>
            <div class="form-group">
              <label class="col-md-12">
                Dirección
              </label>
              <div class="col-md-12">
                <input class="form-control" id="address" name="address" type="text" value="{{ !empty($client->address) ? $client->address : '' }}">
                  <div class="help-block with-errors">
                  </div>
                </input>
              </div>
            </div>
          </input>
        </div>
        @if($client->phoneFail != 'OK' || $client->mailFail == 'FAIL' )
        <div class="alert label-red text-white">
          <p>
            <strong>Aviso importante!</strong> es necesario verificar los datos registrado en los campos teléfono principal (*) y correo electrónico (*) del cliente, ya que es usado para notificar información de interés para los servicios que contrate el cliente.
          </p>
        </div>
        @endif
        <div class="modal-footer">
          <button class="btn btn-default close_modal_edit_app"  data-failphone="{{$client->phoneFail}}" data-failmail="{{$client->mailFail}}" type="button">
            Cerrar
          </button>
          <button class="btn btn-success waves-effect waves-light m-r-10" type="button" id="saveUpdateClient">
            Guardar
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
<script src="{{ asset('plugins/bower_components/jquery-validation/dist/jquery.validate.min.js') }}">
</script>
<script async type="text/javascript">
  function viewAlert(msg){
    swal({
      title: "Verifica por favor los datos del cliente",
      text: msg,
      icon: "warning",
      button: {
        text: "OK"
      }
    });
  }
   jQuery.extend(jQuery.validator.messages, {
    required: "Este campo es obligatorio.",
    date: "Por favor, escribe una fecha válida.",
    number: "Por favor, escribe un número entero válido.",
    email: "Por favor, escribe una dirección de correo válida",
    digits: "Por favor, escribe sólo dígitos.",
    maxlength: jQuery.validator.format("Por favor, no escribas más de {0} caracteres."),
    minlength: jQuery.validator.format("Por favor, no escribas menos de {0} caracteres."),
    rangelength: jQuery.validator.format("Por favor, escribe un valor entre {0} y {1} caracteres."),
    range: jQuery.validator.format("Por favor, escribe un valor entre {0} y {1}."),
    max: jQuery.validator.format("Por favor, escribe un valor menor o igual a {0}."),
    min: jQuery.validator.format("Por favor, escribe un valor mayor o igual a {0}.")
  });
  jQuery.validator.addMethod("mailValidate", function(value, element) {
    return this.optional(element) || /^[A-Za-z0-9][A-Za-z0-9._-]*[A-Za-z0-9]@[A-Za-z0-9]+(\.[A-Za-z0-9]+)*\.[a-zA-Z]{2,4}$/.test(value);
  }, "Email inválido.");
  jQuery.validator.addMethod("notEqual", function(value, element, param) {
    /*https://stackoverflow.com/questions/3571347/how-to-add-a-not-equal-to-rule-in-jquery-validation*/
    return this.optional(element) || value != $(param).val();
  }, "El valor se encuentra repetido");

  $("#editclientformodal").validate({
    rules: {
      name: {
        required: true,
        minlength: 4
      },
      last_name: {
        required: true,
        minlength: 4
      },
      phone: {
        required: true,
        digits: true,
        minlength: 10,
        maxlength: 10
      },
      phone2: {
        digits: true,
        minlength: 10,
        maxlength: 10,
        notEqual: "#phone"
      },
      email: {
        required: false,
        mailValidate: true
      },
      address:{
        minlength: 15
      }
    }
  });

  $(function () {

    $('.close_modal_edit_app').on('click', function(e){
      e.preventDefault();
      let phone = $(this).data('failphone');
      let mail = $(this).data('failmail');

      if(phone != 'OK' || $('#phone').val().length != 10 || $('#phone').val() == $('#phone2').val() ){
        /*data-dismiss="modal"*/
        viewAlert("El número de teléfono principal que se encuentra registrado del cliente es invalido y requiere atención!");
      }else{
        if(mail == 'FAIL' /*&& $('#email').val().length == ''*/){
          viewAlert("El correo electrónico que se encuentra registrado del cliente es invalido y requiere atención");
        }else{
          $('#edit-modal').modal('hide');
        }
      }
    });
    $('#saveUpdateClient').on('click', function(e){
      valid = $("#editclientformodal").valid();
      if(valid){
        $('.loading-ajax').fadeIn();

        sessionStorage.setItem('lastDniEditFiber', $('#dni').val());

        doPostAjax(
          '{{ route('seller.updateClientM') }}',
          function(res){

              if(!res.error){
                  getClient($('#dni').val());
              }else{
                $('.loading-ajax').fadeOut();
              }
              $('#edit-modal').modal('hide');
              showMessageAjax(res.icon, res.msg);
          },
          $("#editclientformodal").serialize()
        );
      }
    });

    @if($client->phoneFail =! 'OK' || $client->mailFail == 'FAIL' )
    //Si tiene fallo lo debe correguir
    //if(sessionStorage.getItem('lastDniEditFiber') == "undefined"){
      sessionStorage.setItem('lastDniEditFiber', "0000");
    //}

    if(sessionStorage.getItem('lastDniEditFiber') != $('#dni').val()){

      $('#edit-modal').modal('show');
    }
     // $("#editClient").trigger('click');
    @endif

  });
</script>

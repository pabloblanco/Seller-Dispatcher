$(document).ready(function() {
  //console.log('PORTABILIDAD');
  jQuery.extend(jQuery.validator.messages, {
    required: "Este campo es obligatorio.",
    number: "Por favor, escribe un número entero válido.",
    digits: "Por favor, escribe sólo dígitos.",
    equalTo: "Por favor, los valores deben coincidir.",
    maxlength: jQuery.validator.format("Por favor, no escribas más de {0} caracteres."),
    minlength: jQuery.validator.format("Por favor, no escribas menos de {0} caracteres."),
  });
  $("#formPort").validate({
    rules: {
      nip: {
        required: true,
        digits: true,
        minlength: 4,
        maxlength: 4
      },
      dn_port: {
        required: true,
        digits: true,
        minlength: 10,
        maxlength: 10
      },
      dn_port2: {
        equalTo: "#dn_port"
      },
      nip2: {
        equalTo: "#nip"
      }
    }
  });
});

function loadJSFile(filename) {
  var fileref = document.createElement('script')
  fileref.setAttribute("type", "text/javascript")
  fileref.setAttribute("src", filename)
  document.getElementsByTagName("head")[0].appendChild(fileref)
}

function fromValidPort() {
  $('#error-verifyPort').text('');
  $('#error-verifyPort').removeClass('alert').removeClass('alert-danger');
  return $("#formPort").valid();
}

function resetFormPort() {
  $('#nip').val('');
  $('#nip').parent().parent().removeClass('has-error');
  $('#nip').siblings('div').text('');
  $('#nip2').val('');
  $('#nip2').parent().parent().removeClass('has-error');
  $('#nip2').siblings('div').text('');
  $('#dn_port').val('');
  $('#dn_port').parent().parent().removeClass('has-error');
  $('#dn_port').siblings('div').text('Número de 10 dígitos que se quiere portar');
  $('#dn_port2').val('');
  $('#dn_port2').parent().parent().removeClass('has-error');
  $('#dn_port2').siblings('div').text('Ingresa nuevamente el número a portar');
  $('#port-prov').val($('#port-prov option:first').val())
  $('#port-prov').parent().parent().removeClass('has-error');
  $('#port-prov').siblings('div').text('');
}
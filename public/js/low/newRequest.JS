var BASE_URL = $('meta[name="base-url"]').attr('content');
var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
//$(function() {
$(document).ready(function() {
  /*Busca los usuarios asociados*/
  $('#list-users').selectize({
    valueField: 'email',
    searchField: 'name',
    labelField: 'name',
    render: {
      option: function(item, escape) {
        return '<p>' + escape(item.name_profile) + ': ' + escape(item.name) + ' ' + escape(item.last_name) + '</p>';
      }
    },
    load: function(query, callback) {
      if (!query.length) return callback();
      $.ajax({
        headers: {
          'X-CSRF-TOKEN': CSRF_TOKEN
        },
        // url: '{{ route('findRelationUsers ') }}',
        url: BASE_URL + 'find-relation-users',
        type: 'POST',
        dataType: 'json',
        data: {
          q: query
        },
        error: function() {
          callback();
        },
        success: function(res) {
          if (!res.error) {
            callback(res.users);
          } else {
            callback();
          }
        }
      });
    }
  });
  /*END Busca los usuarios asociados*/
  /*reset from*/
  let resetForm = function(all = false) {
    //ocultando secciones
    $('#reason').val('');
    $('#block-sales').addClass('hidden');
    $('#low-evidence').addClass('hidden');
    $('#low-btnSend').addClass('hidden');
    $('#block-deuda').addClass('hidden');
    $('#block-abonos').addClass('hidden');
    $('#block-equipos').addClass('hidden');
    $('#detail-sales').addClass('hidden');
    $('#device-sales').addClass('hidden');
    $('#btnPlusDetailSales').html('Ver detalles');
    $('#block-evidence').html('');
    $('#device-sales').html('');
    $('#device-content').html('');
    $('#cant_hbb').html('0');
    $('#cant_telf').html('0');
    $('#cant_mifi').html('0');
    $('#cant_fibra').html('0');
    if (all) {
      $('#list-users').data('selectize').setValue("");
      $('#list-users').val('');
      $('#data-seller').addClass('hidden');
      $('#deuda-seller').addClass('hidden');
      $('#block-deuda').addClass('hidden');
      $('#without-deuda').addClass('hidden');
      $('#info-seller').addClass('hidden');
      $('#without-device').addClass('hidden');
      $('#without-article').addClass('hidden');
      $('#device-content').addClass('hidden');
      $('#low-motive').addClass('hidden');
      $('#without-sales').addClass('hidden');
      $('#low-btnSend').addClass('hidden');
    }
  }
  /*end reset from*/
  /*Busca la info del inventario del usuario seleccionado*/
  $('#list-users').on('change', function(e) {
    //$('.loading-ajax').show();
    let email = $(this).val();
    //inicializo
    resetForm();
    //end inicializo
    if (email && email != '') {
      $(".preloader").fadeIn();
      $('#device-content').html('');
      $('#info-seller').addClass('hidden');
      $('#without-article').addClass('hidden');
      $('#without-device').addClass('hidden');
      $('#device-content').addClass('hidden');
      $('#data-seller').addClass('hidden');
      $('#deuda-seller').addClass('hidden');
      $('#low-motive').addClass('hidden');
      $.ajax({
        headers: {
          'X-CSRF-TOKEN': CSRF_TOKEN
        },
        type: 'POST',
        dataType: 'json',
        //url: "{{route('coordination.findInveSeller')}}",
        url: BASE_URL + 'coordination/stock-seller',
        data: {
          seller: email,
          low: true
        },
        success: function(data) {
          if (!data.error) {
            //Validando que el usuario no tenga solicitud en espera de ser procesada
            if (data.isInProcess) {
              $('#alert-content-inw').attr('hidden', null);
              $('#user_selected').text(data.seller.name + ' ' + data.seller.last_name);
              $(".preloader").fadeOut();
            } else {
              $('#alert-content-inw').attr('hidden', true);
              $('#info-seller').removeClass('hidden');
              //Mostrando datos del vendedor
              if (data.seller) {
                $('.name_seller').text(data.seller.name + ' ' + data.seller.last_name);
                $('.phone_seller').text(data.seller.phone);
                $('.email_seller').text(data.seller.email);
                $('#data-seller').removeClass('hidden');
                $('#low-motive').removeClass('hidden');
              }
              if (data.stock.length > 0) {
                $('#device-content').html(data.htmlStock);
                $('#device-content').removeClass('hidden');
                $('#without-device').removeClass('hidden');
                //$("#btnsend").prop('disabled', true);
                // $("#btnsend").prop('title', "Debes retirar primero el inventario asignado para continuar");
              } else {
                //Significa que no tiene inventario asignado
                // $("#btnsend").prop('disabled', false);
                $("#btnsend").prop('title', "Solicitar baja");
                $('#without-article').removeClass('hidden');
              }
              //Reviso la informacion de deuda del vendedor
              getDeudaVendedor();
              //End reviso la informacion de deuda del vendedor
              //$(".preloader").fadeOut();
            }
          } else {
            if (data.message == 'TOKEN_EXPIRED') {
              showMessageAjax('alert-danger', 'Su session a expirado, por favor actualice la página.');
            } else {
              showMessageAjax('alert-danger', data.message);
            }
            $(".preloader").fadeOut();
          }
        },
        error: function() {
          $('.loading-ajax').hide();
          showMessageAjax('alert-danger', 'Ocurrio un error.');
          $(".preloader").fadeOut();
        }
      });
    }
  });
  /*END Busca la info del inventario del usuario seleccionado*/
  /*Si se selecciono el motivo de productividad se muestra la tabla de ventas*/
  $("#reason").change(function() {
    if ($(this).val() == 6) {
      //opcion de Productividad
      $('.loading-ajax').show();
      $.ajax({
        headers: {
          'X-CSRF-TOKEN': CSRF_TOKEN
        },
        url: BASE_URL + 'low/find-sales-user',
        type: 'POST',
        dataType: 'json',
        data: {
          seller: $('#list-users').val()
        },
        error: function() {
          showMessageAjax('alert-danger', 'Ocurrio un error al consultar ventas.');
          $(".preloader").fadeOut();
        },
        success: function(data) {
          if (!data.error) {
            if (data.success) {
              $('#block-sales').removeClass('hidden');
              $('#dateSales').html('<strong>Desde:</strong> ' + data.date_star + '  <strong> A: </strong> ' + data.date_end);
              if (data.cantSales > 0) {
                $('#detail-sales').removeClass('hidden');
                $('#without-sales').addClass('hidden');
                $('#cantSale').text(data.cantSales);
                $('#mountSale').text('$ ' + data.mountSales);
                $('#device-sales').html(data.salesView);
                // $('#device-sales').removeClass('hidden');
              } else {
                $('#without-sales').removeClass('hidden');
              }
            }
            initialiteFile();
            $('#low-evidence').removeClass('hidden');
            $('#low-btnSend').removeClass('hidden');
          } else {
            if (data.message == 'TOKEN_EXPIRED') {
              showMessageAjax('alert-danger', 'Su session a expirado, por favor actualice la página.');
            } else {
              showMessageAjax('alert-danger', data.message);
            }
          }
          $('.loading-ajax').hide();
        }
      });
    } else {
      //cualquier otra opcion oculto los detalles de las ventas
      $('#block-sales').addClass('hidden');
      $('#device-sales').addClass('hidden');
      if ($(this).val() == '') {
        //significa que no ha seleccionado motivo de baja
        $('#low-evidence').addClass('hidden');
        $('#low-btnSend').addClass('hidden');
      } else {
        initialiteFile();
        $('#low-evidence').removeClass('hidden');
        $('#low-btnSend').removeClass('hidden');
      }
    }
  });
  /*END Si se selecciono el motivo de productividad se muestra la tabla de ventas*/
  /*Metodo para la carga de evidencia*/
  let click = true; //Habilitado para un click el envio
  let CantEvi = 0; //cantidad de campos de evidencia habilitado
  let CantMaxEvi = 3; //cantidad maxima de evidencia a subir
  let $objEvi = [];
  //Inicializo en false el array de evidencias
  function initialiteFile() {
    clickBan = true;
    CantEvi = 0;
    CantMaxEvi = 3;
    for (var i = 1; i <= CantMaxEvi; i++) {
      $objEvi['photo' + i] = false;
    }
  }

  function getEmphy() {
    for (var i = 1; i <= CantMaxEvi; i++) {
      if (!$objEvi['photo' + i]) {
        $objEvi['photo' + i] = true;
        //console.log($objEvi);
        return "photo" + i;
      }
    }
  }
  $("#btnEvidence").click(function() {
    CantEvi++;
    //console.log(CantEvi);
    //Foto de referencia
    let messagesDF = {
      default: 'Click o arrastre una imágen para subirla',
      replace: 'Reemplazar imágen',
      remove: 'Borrar',
      error: 'Error al cargar imágen'
    };
    if (CantEvi <= CantMaxEvi) {
      var disponible = getEmphy();
      // $("#btnEvidence").prop('disabled', false);
      $("#btnEvidence").prop('title', "Cargar nueva evidencia. Dispone  " + (CantMaxEvi - CantEvi));
      html = '<div id="content-' + disponible + '"><input type="file" data-max-file-size="3M" max-file-size="3000000" accept="image/png, image/jpeg, image/jpg, application/pdf" data-allowed-file-extensions="png jpg jpeg pdf" class="dropify" id="' + disponible + '" multiple="true" name="' + disponible + '" data-show-loader="true"/> <div class = "help-block with-errors" > </div></div>';
      $("#block-evidence").append(html);
      var df = $('#' + disponible).dropify({
        messages: {
          default: 'Click o arrastre una imágen para subirla',
          replace: 'Reemplazar imágen',
          remove: 'Borrar',
          error: 'Error al cargar imágen',
          fileSize: 'El tamaño del archivo es demasiado grande ({{ value }} max).',
          imageFormat: 'El formato de la imagen no está permitido ({{ value }} solo).',
        }
        /*,
        error: {        
        }*/
      });
      df.on('dropify.beforeClear', function(event, element) {
        return swal({
          title: "Eliminar Evidencia ",
          text: "Estas seguro de quitar la evidencia?",
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
        }).then((option) => {
          if (option == 'ok') {
            var removePho = $('#content-' + $(this).attr("id")).remove();
            $objEvi[$(this).attr("id")] = false;
            //console.log($objEvi);
            CantEvi--;
            $('#btnEvidence').removeClass('hidden');
          }
        });
        //return confirm("Deseas eliminar la imagen \"" + element.filename + "\" ?");
      });
      df.on('dropify.errors', function(event, element) {
        var removePho = $('#content-' + $(this).attr("id")).remove();
        $objEvi[$(this).attr("id")] = false;
        //console.log($objEvi);
        CantEvi--;
        $('#btnEvidence').removeClass('hidden');
        return swal({
          title: "Error en Evidencia",
          text: "Hubo un problema al cargar la evidencia. Verifica que el tamano no exceda los 3mb y sea un archivo de extension (png, jpg, jpeg o pdf)",
          icon: "warning"
        });
      });
    }
    if (CantEvi >= CantMaxEvi) {
      //oculta el boton de agregar evidencia
      $('#btnEvidence').addClass('hidden');
      //  $("#btnEvidence").prop('disabled', true);
      //  $("#btnEvidence").prop('title', "Solo se permite " + CantMaxEvi + " archivos de evidencia");
    }
  });
  /*Metodo para la carga de evidencia*/
  /*Reviso las deudas del vendedor*/
  function getDeudaVendedor() {
    $.ajax({
      headers: {
        'X-CSRF-TOKEN': CSRF_TOKEN
      },
      url: BASE_URL + 'low/find-deuda-user',
      type: 'POST',
      dataType: 'json',
      data: {
        seller: $('#list-users').val()
      },
      error: function() {
        showMessageAjax('alert-danger', 'Ocurrio un error al consultar deudas.');
        $(".preloader").fadeOut();
      },
      success: function(data) {
        if (!data.error) {
          if (data.success) {
            $('#deuda-seller').removeClass('hidden');
            isDeuda = false;
            deudaTotal = 0;
            if (data.mountDeuda > 0) {
              isDeuda = true;
              //Deuda en efectivo de ventas hechas
              $('#block-deuda').removeClass('hidden');
              $('#cantDeuda').text(data.cantDeuda);
              $('#mountDeuda').text('$ ' + data.mountDeuda);
              deudaTotal += data.mountDeuda;
              $('#dayDeuda').text(data.dayDeuda);
            }
            if (data.inv_deuda_hbb > 0 || data.inv_deuda_telf > 0 || data.inv_deuda_mifi > 0 || data.inv_deuda_fibra > 0) {
              isDeuda = true;
              $('#block-equipos').removeClass('hidden');
              if (data.inv_deuda_hbb > 0) {
                $('#cash_hbb').html('$ ' + data.inv_deuda_hbb);
                $('#cant_hbb').html(data.inv_cant_hbb);
                deudaTotal += data.inv_deuda_hbb;
              }
              if (data.inv_deuda_telf > 0) {
                $('#cash_telf').html('$ ' + data.inv_deuda_telf);
                $('#cant_telf').html(data.inv_cant_telf);
                deudaTotal += data.inv_deuda_telf;
              }
              if (data.inv_deuda_mifi > 0) {
                $('#cash_mifi').html('$ ' + data.inv_deuda_mifi);
                $('#cant_mifi').html(data.inv_cant_mifi);
                deudaTotal += data.inv_deuda_mifi;
              }
              if (data.inv_deuda_fibra > 0) {
                $('#cash_fibra').html('$ ' + data.inv_deuda_fibra);
                $('#cant_fibra').html(data.inv_cant_fibra);
                deudaTotal += data.inv_deuda_fibra;
              }
            }
            /*if (data.deuda_abono > 0) {
              isDeuda = true;
              $('#block-abonos').removeClass('hidden');
              $('#cash_abonos').html('$ ' + data.deuda_abono);
              $('#count_abonos').html(data.count_abono);
              deudaTotal += data.deuda_abono;
            }*/
            if (!isDeuda) {
              //No hay deuda
              $('#block-deuda').addClass('hidden');
              $('#without-deuda').removeClass('hidden');
              $('#deuda_total').addClass('hidden');
            } else {
              //Hay una deuda
              $('#without-deuda').addClass('hidden');
              $('#deuda_total').removeClass('hidden');
              $('#mountTotal').html('$ ' + deudaTotal);
            }
          }
        } else {
          if (data.message == 'TOKEN_EXPIRED') {
            showMessageAjax('alert-danger', 'Su session a expirado, por favor actualice la página.');
          } else {
            showMessageAjax('alert-danger', data.message);
          }
        }
        $('.loading-ajax').hide();
        $(".preloader").fadeOut();
      }
    });
  }
  /*END Reviso las deudas del vendedor*/
  /*Identifica si es un celular*/
  var isMobile = {
    Android: function() {
      return navigator.userAgent.match(/Android/i);
    },
    BlackBerry: function() {
      return navigator.userAgent.match(/BlackBerry/i);
    },
    iOS: function() {
      return navigator.userAgent.match(/iPhone|iPad|iPod/i);
    },
    Opera: function() {
      return navigator.userAgent.match(/Opera Mini/i);
    },
    Windows: function() {
      return navigator.userAgent.match(/IEMobile/i);
    },
    any: function() {
      return (isMobile.Android() || isMobile.BlackBerry() || isMobile.iOS() || isMobile.Opera() || isMobile.Windows());
    }
  };
  /*END Identifica si es un celular*/
  /*Evento de envio de solicitud de baja*/
  $("#btnsend").click(function() {
    $("#btnsend").prop('disabled', true);
    //$(".preloader").fadeIn();
    $('.loading-ajax').show();
    if (clickBan) {
      clickBan = false;
      let data = new FormData();
      //Se agrega la evidencia
      for (var i = 1; i <= CantMaxEvi; i++) {
        var itemPhoto = "photo" + i;
        if ($objEvi[itemPhoto]) {
          fileUpload = document.getElementById(itemPhoto).files[0];
          if (fileUpload) {
            if (fileUpload.size > 0) {
              data.append(itemPhoto, document.getElementById(itemPhoto).files[0]);
            }
          }
          /*else {
            console.log("No se cargo " + itemPhoto);
          }*/
        }
      }
      data.append('sellerMail', $('#list-users').val());
      /* if (isMobile.any()) {
         data.append('machine', 'mobile');
       } else {
         data.append('machine', 'pc');
       }*/
      data.append('reason', $('#reason').val());
      doPostAjaxForm(BASE_URL + 'low/send-low-user', function(res) {
        $('.loading-ajax').hide();
        //$(".preloader").fadeOut();
        res = JSON.parse(res);
        if (res.success) {
          resetForm(true);
          swal({
            title: "Éxito",
            text: res.msg,
            icon: res.icon,
            button: {
              text: "OK"
            }
          });
        } else {
          swal({
            title: "No se pudo registrar la solicitud",
            text: res.msg,
            icon: "error",
            button: {
              text: "OK"
            }
          });
          resetForm(true);
        }
      }, data, CSRF_TOKEN);
    } else {
      // console.log('Block');
    }
    $("#btnsend").prop('disabled', false);
  });
  /*END Evento de envio de solicitud de baja*/
  /*ver detalles de venta*/
  $("#btnPlusDetailSales").click(function() {
    var icons = '<i class="zmdi zmdi-eye"></i>';
    if ($("#btnPlusDetailSales").html().includes("Ver")) {
      $('#device-sales').removeClass('hidden');
      $("#btnPlusDetailSales").html(icons + ' Ocultar detalles');
    } else {
      $('#device-sales').addClass('hidden');
      $("#btnPlusDetailSales").html(icons + ' Ver detalles');
    }
  });
  /*END ver detalles de venta*/
});
@extends('layouts.admin')

@section('customCSS')
<link href="{{ asset('css/styleScanBar.css') }}" rel="stylesheet"/>
{{--
<link href="{{ asset('plugins/bower_components/dropify/dist/css/dropify.min.css') }}" rel="stylesheet"/>
--}}
@if(session('user_type') != 'vendor')
<link href="{{ asset('css/selectize.css') }}" rel="stylesheet"/>
<link href="{{ asset('css/selectize.bootstrap.css') }}" rel="stylesheet"/>
@endif
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
      Lista de MSISDNs con notificación
    </h4>
  </div>
  <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
    <ol class="breadcrumb">
      <li>
        <a href="#">
          Dashboard
        </a>
      </li>
      <li class="active">
        Lista de MSISDNs con notificación.
      </li>
    </ol>
  </div>
</div>
<div class="row">
  @if(session('user_type') != 'vendor')
  <div class="col-md-12">
    <div class="white-box">
      <h3 class="box-title">
        Filtro
      </h3>
      <form data-toggle="validator" method="GET">
        <div class="row">
          <div class="col-md-6">
            <div class="form-group">
              <label class="control-label">
                Usuario
              </label>
              <div id="scrollable-dropdown-menu">
                <select class="form-control" id="seller" name="seller">
                </select>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <label class="control-label">
            </label>
            <div class="form-group">
              <button class="btn btn-success waves-effect" id="do-search" type="button">
                Filtrar
              </button>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
  @endif
  <div class="col-md-12">
    <div class="white-box">
      <h3 class="box-title">
        Lista MSISDNs con notificación.
      </h3>
      @if($dns->count())
      <div class="row justify-content-center" id="sales-list">
        <div class="table-responsive" id="list-article">
          <div class="row justify-content-md-end justify-content-center align-items-center">
            @if(session('user_type') != 'vendor')
            <div class="col-md-auto col-sm-6 col-12 text-sm-center text-right">
              <button class="btn btn-success m-b-20" id="exportCsv" type="button">
                Exportar CSV
              </button>
              <a href="#" id="downloadfile" style="display: none;">
              </a>
            </div>
            @endif
            <div class="col-md-auto col-sm-6 col-12 text-sm-center text-right">
              <button class="btn btn-success m-b-20" data-target="#modalChangeStatus" data-toggle="modal" id="changeStatus" type="button">
                Cambiar status
              </button>
            </div>
          </div>
          <div class="col-12">
            <div class="form-group">
              <label class="control-label">
                Estatus
              </label>
              <select class="form-control" id="typeOR" name="typeOR">
                <option value="">
                  Ver todos
                </option>
                <option value="O">
                  Naranja
                </option>
                <option value="R">
                  Rojos
                </option>
              </select>
            </div>
            <div class="text-center">
              <button class="btn btn-success m-b-20" id="verList" name="verList" type="button">
                Mostrar
              </button>
            </div>
          </div>
          <div class="blockList">
          </div>
          <div class="row d-none" id="notNotify">
            <div class="alert alert-danger">
              <p>
                No se consiguieron MSISDNs con notificación.
              </p>
            </div>
          </div>
        </div>
      </div>
      @endif
      <div class="row" id="notNotifyGlobal">
        <div class="alert alert-danger">
          <p>
            No se consiguieron MSISDNs con notificación.
          </p>
        </div>
      </div>
    </div>
  </div>
</div>
{{--
<a data-target="#modalChangeStatus" data-toggle="modal" hidden="true" href="#" id="btn-show-m">
</a>
--}}
<div aria-hidden="true" aria-labelledby="myModalLabel" class="modal fade" id="modalChangeStatus" role="dialog" tabindex="-1">
  <div class="modal-dialog">
    {{--w-75 style="top: 140px;"--}}
    <div class="modal-content">
      <div class="modal-header">
        <button aria-hidden="true" class="close closeCam" data-dismiss="modal" type="button">
          ×
        </button>
        <h4 class="modal-title">
          Cambiar status de inventario
        </h4>
      </div>
      <div class="modal-body">
        <div class="row justify-content-center">
          <div class="col-12">
            <div class="alert alert-primary">
              <p class="font-weight-bold">
                Este proceso pasa el inventario que tienes asignado en color ROJO a NARANJA siempre y cuando el codigo de barras se pueda scanear, caso contrario se debe solicitar a administración el cambio de status.
              </p>
            </div>
          </div>
          <div class="col-12" id="blockCam">
            <label>
              Scaneo de msisdn
            </label>
            <div id="scan-content" style="width: 100%; height:250px">
              <div class="row justify-content-center align-items-center" id="labelCam">
                <div class="col-12 text-center">
                  <h3 class="font-weight-bold">
                    Por favor, debes dar permisos a la camara para hacer uso del scaneo, luego de dar permiso se recomienda refrescar el sitio.
                  </h3>
                </div>
              </div>
            </div>
            <div class="alert alert-heading">
              <p class="font-weight-bold">
                Para realizar un proceso de scaneo satisfactorio verifica por favor que se encuentre en buen estado la etiqueta y cuentes con una adecuada iliminación.
              </p>
            </div>
          </div>
          <div class="col-12 text-center pb-4" id="blockbtnReScan">
            <button class="btn btn-danger waves-effect waves-light" id="btnReScan" type="button">
              Volver a escanear
            </button>
          </div>
          <div class="col-12" id="blockCheck">
            <span class="d-flex align-items-center">
              <label class="mr-3 switch">
                <input id="CheckScaneo" type="checkbox">
                  <span class="slider round">
                  </span>
                </input>
              </label>
              <span id="labelSwitch">
                No se pudo scanear código de barras
              </span>
            </span>
          </div>
          <div class="col-12 py-4" id="blockDN">
            <form id="inputDN" method="post" role="form">
              <div class="row align-items-center justify-content-center">
                <div class="col-auto">
                  MSISDN:
                </div>
                <input autocomplete="off" autofocus="true" class="col-auto ml-3" id="resultScan" name="resultScan" placeholder="Ingresa el MSISDN" type="text"/>
              </div>
            </form>
            <input id="resultScan2" name="resultScan2" type="hidden"/>
          </div>
          <div class="col-12" id="blockEvidence">
            <div class="form-group">
              <label>
                Foto de evidencia
              </label>
              <div class="text-center" id="evidence">
                <video autoplay="" id="video-content" style="object-fit: initial; width: 100%;">
                </video>
                <canvas hidden="true" id="canvas-content">
                </canvas>
                <img alt="" height="100%" hidden="true" id="img-content" style="width: 100%" width="100%"/>
                <button class="btn btn-success waves-effect waves-light m-t-10" id="btnTakePic" type="button">
                  Tomar foto
                </button>
                <button class="btn btn-danger waves-effect waves-light m-t-10" hidden="true" id="btnDesPic" type="button">
                  Tomar otra fotografia
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer" id="blockFooter">
        <button class="btn btn-default waves-effect" data-dismiss="modal" type="button">
          Cancelar
        </button>
        <button class="btn btn-success waves-effect waves-light" id="toOrange" name="toOrange" type="button">
          Solicitar
        </button>
      </div>
    </div>
  </div>
</div>
<div aria-hidden="true" aria-labelledby="myModalLabel" class="modal" id="modalViewStatus" role="dialog" tabindex="-1">
  <div class="modal-dialog">
    {{--w-75 style="top: 140px;"--}}
    <div class="modal-content">
      <div class="modal-header">
        <button aria-hidden="true" class="close" data-dismiss="modal" type="button">
          ×
        </button>
        <h4 class="modal-title">
          Detalles del cambio de status en inventario
        </h4>
      </div>
      <div class="modal-body">
        <div id="detailStatusInv">
        </div>
      </div>
      <div class="modal-footer" id="">
        <button class="btn btn-default waves-effect" data-dismiss="modal" type="button">
          OK
        </button>
      </div>
    </div>
  </div>
</div>
@stop

@section('scriptJS')
{{--
<script src="https://unpkg.com/quagga@0.12.1/dist/quagga.min.js">
</script>
--}}
<script src="{{ asset('plugins/bower_components/quagga/quagga.min.js') }}">
</script>
<script src="{{ asset('plugins/bower_components/jquery-validation/dist/jquery.validate.min.js') }}">
</script>
{{--
<script src="{{ asset('plugins/bower_components/dropify/dist/js/dropify.min.js') }}">
</script>
--}}
<script src="{{ asset('js/sweetalert.min.js') }}">
</script>
<script src="{{ asset('js/scanBar.js')}}">
</script>
@if(session('user_type') != 'vendor')
<script src="{{ asset('js/selectize.js')}}">
</script>
@endif
<script type="text/javascript">
  $('#labelCam').hide();
  
  function alerta(mensaje, time){
    swal({
      text: mensaje,
      icon: "warning",
      timer: time,
      timerProgressBar: true,
      didOpen: () => {
        Swal.showLoading()
        const b = Swal.getHtmlContainer().querySelector('b')
        timerInterval = setInterval(() => {
          b.textContent = Swal.getTimerLeft()
        }, 100)
      },
      willClose: () => {
        clearInterval(timerInterval)
      },
      button: {
        text: "OK"
      }
    });
  }
  //Tomar foto  
  const videoTag = $('#video-content');
  const canvasTag = $('#canvas-content');
  const widthRes = 800;
  const heightRes = 600;

  let initCam = () => {
    if ('mediaDevices' in navigator && 'getUserMedia' in navigator.mediaDevices) {
      navigator.mediaDevices.enumerateDevices()
      .then(devices => {
        let deviceBack = false;
        let deviceFront = false;
        devices.forEach(device => {
          if (device.kind == "videoinput"){
            if(device.label && device.label.length > 0){
              if(device.label.toLowerCase().indexOf('back') >= 0){
                deviceBack = device;
              }else{
                deviceFront = device;
              }
            }
          }
        });

        if(deviceBack || deviceFront){
          navigator.mediaDevices.getUserMedia({
            audio: false,
            video: {
              width: widthRes,
              height: heightRes,
              deviceId: deviceBack ? deviceBack.deviceId : deviceFront.deviceId
            }
          }).then(stream => {
            videoTag.height(videoTag.width() * 0.75);
            videoTag[0].srcObject = stream;
          }).catch(e => {
            console.log(e.toString());
            alerta('Algo falló por favor intenta de nuevo.', 6000);
          });
        }else{
          alerta('No se pudo configurar la camara a utilizar', 6000);
        }
      }).catch(e => {
        console.log(e.toString());
        alerta('Algo falló por favor intenta de nuevo.', 6000);
      });
    }else{
      $('#modalChangeStatus').modal('hide');
      alerta('Tú navegador no soporta el uso de la cámara, te recomendamos usar Chrome', 6000);
    }
  }

  $('#btnTakePic').on('click', function(){
    canvasTag.attr('width', widthRes);
    canvasTag.attr('height', heightRes);
    let canvasContext = canvasTag[0].getContext('2d');
    canvasContext.drawImage(videoTag[0], 0, 0);
    videoTag.attr('hidden', true);
    $('#img-content').attr('src', canvasTag[0].toDataURL('image/png'));
    $('#img-content').attr('hidden',null);
    $('#btnDesPic').attr('hidden',null);
    $('#btnTakePic').attr('hidden',true);
    videoTag[0].srcObject.getTracks()[0].stop();
    $('#blockFooter').show();
  });

  $('#btnDesPic').on('click', function(){
    resetPicFlow();
    initCam();
  });

  $('#modalChangeStatus').on('hide.bs.modal', function(event) {
    if(videoTag && videoTag.length && videoTag[0].srcObject){
      videoTag[0].srcObject.getTracks()[0].stop();
    }
    //detengo scam
    StopScan();
  });

  let resetPicFlow = () => {
    $('#img-content').attr('hidden',true);
    $('#btnDesPic').attr('hidden',true);
    $('#btnTakePic').attr('hidden',null);
    videoTag.attr('hidden', null);
    $('#blockFooter').hide();
  }

  let dataURLtoBlob = (dataURL) => {
      let arr = dataURL.split(','),
          mime = arr[0].match(/:(.*?);/)[1],
          bstr = atob(arr[1]),
          n = bstr.length,
          u8arr = new Uint8Array(n);
      while (n--) {
          u8arr[n] = bstr.charCodeAt(n);
      }
      return new Blob([u8arr], {
          type: mime
      });
  }
  //Fin de tomar foto
  //
  //Escaneo de codigo de barras
  function opencam(){
    $('#blockCam').show();
    $('#blockbtnReScan').hide();
    $("#blockCheck").show();
    $("#CheckScaneo").prop( "checked", false );
    $('#labelSwitch').html('No se pudo scanear codigo de barras');
    $("#blockDN").hide(); 
    $('#resultScan').val('');
    $('#resultScan2').val('');  
    $("#blockEvidence").hide();
    $("#photo").val(null);
    //$('#photo').parent().find(".dropify-clear").trigger('click');
    $("#blockFooter").hide();
    resetPicFlow();
    StartScan();
  }

  function processDN(){
    msisdn = $('#resultScan').val().trim();
    //Descomentar esto cuando se suba a desarrollo
    valid = $("#inputDN").valid();
    if(valid && msisdn.length>0){
       $.ajax({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
          url: '{{ route('inventory.verifyDnStatus') }}',
          type: 'POST',
          dataType: 'json',
          data: {
              dn: msisdn,
              type: $("#CheckScaneo").is(':checked')
          },
          error: function() {
          },
          success: function(res){
            if(!res.success){
              $('#modalChangeStatus').modal('hide');
              alerta(res.msg, 10000);
            }else{
              //Se muestra el bloque de evidencia
              $("#blockEvidence").show();
              //inicio la camara para la foto
              initCam();
            }
          }
      });  
    }else{
      $("#blockEvidence").hide();     
    }
  }

  function viewStatus(id){
    $('.loading-ajax').fadeIn();
      $.ajax({
        url: "{{ route('inventory.chekingRequestStatus') }}",
        type: 'POST',
        data:{
            id: id,
            _token: "{{ csrf_token() }}"
        },
        dataType: 'json',
        success: function(result){
            $('#detailStatusInv').html(result.htmlCode);
            $('#modalViewStatus').modal('show');
        }
      });
    $('.loading-ajax').fadeOut();
  }

  function viewStatusOR(){
    $('.loading-ajax').show();
      $.ajax({
            url: "{{ route('inventory.searchListDN_OR') }}",
            type: 'POST',
            data:{
                seller: $("#seller").val(),
                typeColor: $("#typeOR").val(),
                _token: "{{ csrf_token() }}"
            },
            dataType: 'json',
            success: function(result){
              if(result.dns.length>0){
                $('#notNotify').addClass('d-none');
                $('#notNotifyGlobal').addClass('d-none');
                $('#sales-list').show(); 
                $('.blockList').removeClass('d-none');
                $('.blockList').html(result.htmlCode);
              }else{
                if($("#seller").val()!='' && $("#typeOR").val()!=''){
                  $('#notNotify').removeClass('d-none');
                }else{
                  $('#sales-list').hide(); 
                  $('#notNotifyGlobal').removeClass('d-none');
                }
                $('.blockList').addClass('d-none');
              }
            }
        });
        $('.loading-ajax').hide();
  }

  $(function() {
    @if($dns->count()!=0)
      $('#notNotifyGlobal').addClass('d-none');
    @endif

    var myInput = document.getElementById('resultScan');
    myInput.onpaste = function(e) {
      e.preventDefault();
      alerta("Esta acción no está permitida",5000);
    }
    myInput.oncopy = function(e) {
    e.preventDefault();
      alerta("Esta acción no está permitida",5000);
    }

    @if(session('user_type') != 'vendor')
    $('#seller').selectize({
        valueField: 'email',
        searchField: 'name',
        labelField: 'info',
        render: {
            option: function(item, escape) {
                return '<p>'+escape(item.name_profile)+': '+escape(item.name)+' '+escape(item.last_name)+'</p>';
            }
        },
        load: function(query, callback) {
            if (!query.length) return callback();
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route('findRelationUsers') }}',
                type: 'POST',
                dataType: 'json',
                data: {
                    q: query,
                    me: true
                },
                error: function() {
                    callback();
                },
                success: function(res){
                    if(!res.error){
                        callback(res.users);
                    }else{
                        callback();
                    }
                }
            });
        }
    });

    $('#exportCsv').on('click', function(e){
        $('.loading-ajax').show();
        $.ajax({
            url: "{{ route('inventory.downloadInvNoty') }}",
            type: 'POST',
            data:{
                seller: $("#seller").val(),
                typeColor: $("#typeOR").val(),
                _token: "{{ csrf_token() }}"
            },
            dataType: 'text',
            success: function(result){
                var uri = 'data:application/csv;charset=UTF-8,' + encodeURIComponent(result);
                var download = document.getElementById('downloadfile');
                download.setAttribute('href', uri);
                download.setAttribute('download','inv_asig_'+'{{ date('Ymd') }}'+'.csv');
                download.click();
                $('.loading-ajax').hide();
            }
        });
    });
    @endif

    $('#changeStatus').on('click', function(e){
      $('.loading-ajax').show();
      opencam();
      $('.loading-ajax').hide();
     // $('#btn-show-m').trigger('click');
     // si retiro la clase fade uso lo siguiente:
      //$('#modalChangeStatus').modal('show');
    });

    $('#btnReScan').on('click', function(e){
      opencam();
    });

    $('#do-search').on('click', function(e){
      $("#typeOR").val('');
      viewStatusOR();
    });

    $("#CheckScaneo").on('change', function() {
      if( $(this).is(':checked') ) {
          // Hacer algo si el checkbox ha sido seleccionado
          $('#blockCam').hide();
          $('#labelSwitch').html('Scanear codigo de barras');
          //alert("El checkbox con valor " + $(this).val() + " ha sido seleccionado");
          $('#resultScan').prop("disabled", false );
          $('#resultScan').prop("autofocus", true );

         // $('input:text:visible:first').focus();

          $("#blockDN").show();  
          Quagga.stop();
         // console.log("Detenido OK");
        //  sessionStorage.setItem('banPast', 'M');
      } else {
          // Hacer algo si el checkbox ha sido deseleccionado
          opencam();
          //alert("El checkbox con valor " + $(this).val() + " ha sido deseleccionado");
          $('#resultScan').prop("disabled", true );
          $('#resultScan').prop("autofocus", false );
      }
    });

  jQuery.extend(jQuery.validator.messages, {
    required: "Este campo es obligatorio.",
    number: "Por favor, escribe un número entero válido.",
    digits: "Por favor, escribe sólo dígitos.",
    maxlength: jQuery.validator.format("Haz ingresado un elemento no valido. Maximo  {0} caracteres."),
    minlength: jQuery.validator.format("Haz ingresado un elemento no valido. Minimo {0} caracteres."),
    rangelength: jQuery.validator.format("Por favor, escribe un valor entre {0} y {1} caracteres."),
    range: jQuery.validator.format("Por favor, escribe un valor entre {0} y {1}."),
    max: jQuery.validator.format("Por favor, escribe un valor menor o igual a {0}."),
    min: jQuery.validator.format("Por favor, escribe un valor mayor o igual a {0}.")
  });

  $("#inputDN").validate({
    rules: {
      resultScan: {
        required: true,
        digits: true,
        minlength: 10,
        maxlength: 10
      }
    }
  });

    $('#resultScan').on('keyup', function(e) {
      //Si se ingresa por teclado
      processDN();
    }); 

    $('#verList').on('click', function(e){
      viewStatusOR();
    });

    $('#toOrange').on('click', function(e){
      $('.loading-ajax').fadeIn();
      const data = new FormData();
      const blob = dataURLtoBlob(canvasTag[0].toDataURL('image/png'));
      data.append('photo', blob, 'evidencia.png');

      doPostAjaxForm(
        '{{ route('inventory.changeStatus') }}',
        function(res){
          $('.loading-ajax').fadeOut();
          res = JSON.parse(res);

          if(res.success){                        
            swal({
              title: "Éxito",
              text: "Solicitud registrada.", 
              icon: "success",
              button: {
                text: "OK"
              }
            });
            viewStatusOR();
          }else{
            swal({
              title: "No se pudo registrar la solicitud",
              text: res.msg, 
              icon: "error",
              button: {
                text: "OK"
              }
            });
          }
          $('#modalChangeStatus').modal('hide');
        },
        data,
        $('meta[name="csrf-token"]').attr('content')
      );
    });

    //Deshabilitando envío de formulario por post
    $('#inputDN').on('submit', function(e){
      e.preventDefault();
      processDN();
    });
  });
</script>
@stop

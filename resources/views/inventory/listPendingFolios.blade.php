@extends('layouts.admin')

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
      Lista de guías pendientes
    </h4>
  </div>
  <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
    <ol class="breadcrumb">
      <li>
        <a href="#">
          Inventario
        </a>
      </li>
      <li class="active">
        Lista de guías pendientes.
      </li>
    </ol>
  </div>
</div>
<div class="row">
  <div class="col-md-12">
    <div class="white-box">
      <div class="row">
        <div class="col-md-12 p-b-20" id="list-pending">
          @if(count($pending))
          <div class="col-md-12 p-b-20">
            <button class="btn btn-success waves-effect" id="scan-box" type="button">
              Escanear
            </button>
          </div>
          @foreach($pending as $folio => $boxes)
          <div class="card card-outline-danger text-dark m-b-10">
            <div class="card-block">
              <div class="col-md-12">
                <h3 class="box-title">
                  Guía
                  <b>
                    {{$folio}}
                  </b>
                </h3>
                <ul class="list-icons">
                  @foreach($boxes as $box)
                  <li>
                    <i class="ti-angle-right">
                    </i>
                    <strong>
                      Caja:
                    </strong>
                    <span>
                      {{$box->box}}
                    </span>
                    <button class="btn btn-sm btn-success waves-effect check-box" data-box="{{$box->box}}" type="button">
                      Revisar
                    </button>
                  </li>
                  @endforeach
                </ul>
              </div>
            </div>
          </div>
          @endforeach
          @else
          <h2>
            No tienes guias pendientes
          </h2>
          @endif
        </div>
      </div>
    </div>
  </div>
</div>
<div aria-hidden="true" aria-labelledby="myModalLabel" class="modal fade" id="show-scan" role="dialog" tabindex="-1">
  <div class="modal-dialog" style="top: 140px;">
    <div class="modal-content">
      <div class="modal-header">
        <button aria-hidden="true" class="close" data-dismiss="modal" type="button">
          ×
        </button>
        <h4 class="modal-title">
          Escanear caja
        </h4>
      </div>
      <div class="modal-body" style="max-height: 50vh; overflow: auto;">
        <div id="scan-content" style="width: 100%">
        </div>
        <div class="alert alert-info alert-dismissable" hidden="true" id="adv-inv">
          Los artículos aceptados y marcados con error serán notificados a un administrador y no se agregarán al inventario asociado a ti.
        </div>
        <div id="list-inv">
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-danger waves-effect waves-light" hidden="true" id="accept-inv" type="button">
          Aceptar inventario
        </button>
      </div>
    </div>
  </div>
</div>
@stop

@section('scriptJS')
<script src="https://unpkg.com/html5-qrcode@2.0.9/dist/html5-qrcode.min.js">
</script>
<script src="{{ asset('js/sweetalert.min.js') }}">
</script>
<script type="text/javascript">
  $(function() {
    const getBoxDetail = (box) => {
      $('.loading-ajax').show();

      $.ajax({
          headers: {
              'X-CSRF-TOKEN': "{{ csrf_token() }}"
          },
          async: true,
          url: "{{route('inventory.boxDetail')}}",
          method: 'POST',
          data: {box: box},
          dataType: 'json',
          success: function (res) {
              $('.loading-ajax').hide();

              if(res.success){
                $('#adv-inv').attr('hidden', null);
                $('#list-inv').html(res.html);

                $('.opt-error').bind('change', function(e){
                  let val = $(this).val(),
                      detail = $(this).data('detail');

                  $('#error-txt-'+detail).attr('hidden', true);
                  if(val == 'ot'){
                    $('#error-txt-'+detail).attr('hidden', null);
                  }
                });

                $('#accept-inv').attr('hidden', null);
              }else{
                  showMessageAjax('alert-danger', res.msg);
              }
          },
          error: function (res) {
              $('.loading-ajax').hide();
              showMessageAjax('alert-danger', 'No se pudo cargar el detalle de la caja.');
          }
      });
    };

    const html5QrCode = new Html5Qrcode("scan-content");

    const successScan = (decodedText, decodedResult) => {
        //console.log(`Code scanned = ${decodedText}`, decodedResult);
        getBoxDetail(decodedText);

        html5QrCode.stop();
        html5QrCode.clear();
    };

    const getWidth = () => {
      let size = Math.ceil(window.innerWidth * .55);
      if(size > 450){
        return 450;
      }
      return size;
    };

    const config = { fps: 40, qrbox: getWidth(), formatsToSupport: [Html5QrcodeSupportedFormats.CODE_128] };
    let banScan = true;

    $('#scan-box').on('click', function(e){
      banScan = true;
      $('#show-scan').modal('show');
      $('.loading-ajax').show();
    });

    $('#show-scan').on('shown.bs.modal', function(event) {
      if(banScan){
        html5QrCode.start({ facingMode: "environment" }, config, successScan);

        let inter = setInterval(() => {
          if(html5QrCode.isScanning){
            $('.loading-ajax').hide();
            clearInterval(inter);
          }
        }, 200);
      }
    });

    $('#show-scan').on('hidden.bs.modal', function(event) {
      html5QrCode.stop();
      html5QrCode.clear();

      $('#accept-inv').attr('hidden', true);

      $('#adv-inv').attr('hidden', true);
      $('#list-inv').html('');
      $('.opt-error').unbind('change');
    });

    $('.check-box').click(function(e){
      let box = $(this).data('box');

      if(box){
        banScan = false;
        $('#show-scan').modal('show');
        getBoxDetail(box);
      }
    })

    $('#accept-inv').on('click', function(){
      let data = [];

      $('.all-inv-box').each(function(){
        let detail = $(this).val(),
            error = $('#opt-error-'+detail).val(),
            errorD = $('#txt-opt-error-'+detail).val();

        data.push({
          detail: detail,
          error: error,
          errorD: errorD
        });
      });

      if(data.length){
        $('.loading-ajax').show();

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            },
            async: true,
            url: "{{route('inventory.acceptBoxDetail')}}",
            method: 'POST',
            data: {data: data},
            dataType: 'json',
            success: function (res) {
                $('.loading-ajax').hide();

                if(res.success){
                  $('#show-scan').modal('hide');

                  let msg = 'MSISDN asignados.';

                  if(res.dns_not_asigned.length){
                    msg = 'Los siguientes MSISDN no fueron asignados: '+res.dns_not_asigned.join();
                  }

                  if(res.dns_reciclers.length){
                    msg = 'Los siguientes msisdns se encuentran en proceso de reciclaje: '+res.dns_reciclers.join()+' y serán asignados al inventario en un periodo no mayor a 24 horas';
                  }

                  swal({
                    title: 'Solicitud procesada', 
                    text: msg, 
                    icon: "success",
                    button: {
                      text: "OK"
                    }
                  })
                  .then((value) => {
                    window.location.reload()
                  });
                }else{
                    showMessageAjax('alert-danger', res.msg);
                }
            },
            error: function (res) {
                $('.loading-ajax').hide();
                showMessageAjax('alert-danger', 'No se pudo asignar el detalle de la caja.');
            }
        });
      }
    });
  });
</script>
@stop

@if(!empty($statusPayment))
<div class="col-md-12 pt-5">
<span class="box-title text-left">
  Status del link de pago:
</span>
<span>
  <strong> {!!$statusPayment!!} </strong>
</span>
</div>
@endif
@if(!empty($QrPayment))
  @if($is_pending)
  <h3 class="box-title col-md-12 p-t-10 text-left">
    Qr de pago de servicio recurrente
  </h3>
  @endif
  <div class="col-md-12 pt-4 pb-5 row justify-content-center align-items-center" id="qr_subscrip">
      {!!$QrPayment!!}
  </div>
  @if($is_pending)
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
        &#128179; La QR se genero para el correo: <strong> {{$email_client}} </strong>
      </span>
    </div>
  </div>
  <div class="container pb-5" id="block_btn_payment">
    <div class="row justify-content-center">
      <button class="btn btn-primary waves-effect waves-light my-2 mx-1 col-auto" id="paymentCash" type="button" onclick="actionChangerCash()" title="Se desea pagar en efectivo">
          &#128181; Pagar en efectivo
      </button>
      <button class="btn btn-secondary waves-effect waves-light my-2 mx-1 col-auto" id="paymentCard_reload" type="button" onclick="actionChangerUrl()" title="Solicitar una nueva url de pago">
          &#128179; Renovar enlace de pago
      </button>
      <button class="btn btn-success waves-effect waves-light my-2 mx-1 col-auto" id="paymentChangeMail" type="button" onclick="actionChangerMail()" title="Correo en netwey: {{$email_client}}">
          &#128231; Cambiar correo
      </button>
    </div>
  </div>
  <div id="block_share_payment">
    @if($sharePayment['success'] && !empty($QrPayment))
      {!!$sharePayment['html']!!}
    @else
    <div class="alert alert-danger">
      {!!$sharePayment['msg']!!}
    </div>
    @endif
  </div>
  @endif

  <script type="text/javascript">
    @if($is_pending)
    function notifyChanger(res){
      swal({
          title: res.title,
          text: res.msg,
          icon: res.icon,
          dangerMode: true,
        }).then((value) => {
          $('.loading-ajax').fadeIn();
          if(res.htmlPlanes.length){
            $('#listPacks').html(res.htmlPlanes);
            $('#listPacks').attr('hidden', null);
            $('#label_new_pack').attr('hidden', null);
            $('#block_qr_subscrip').html('');
            $('#blockBtnChangerPack').attr('hidden', null);
            $('.loading-ajax').fadeOut();
          }else{
            window.location.reload();
          }
        });
    }

    function actionChangerCash(){
      swal({
        title: "¿Seguro que desea pagar en efectivo?",
        text: "Esta acción perdera el beneficio de pago recurrente y el cliente se dara de alta con un servicio con recargas manuales.",
        icon: "warning",
        dangerMode: true,
        buttons: {
          cancel: {
              text: 'Cancelar',
              visible: true,
              value: 'cancelar'
          },
          confirm: {
              text: 'Aceptar',
              visible: true,
              value: 'ok'
          }
        }
      }).then((value) => {
        if(value == 'ok'){
          $('.loading-ajax').fadeIn();
          doPostAjax(
            '{{ route('sellerFiber.changer_incash') }}',
            function(res){
              $('.loading-ajax').fadeOut();
              if(res.success){
                notifyChanger(res);
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
              id: $("#cita").val(),
            },
            $('meta[name="csrf-token"]').attr('content')
          );
        }
      });
    }

    function actionChangerUrl(){
      $('.loading-ajax').fadeIn();
      doPostAjax(
        '{{ route('sellerFiber.reloadQrPayment') }}',
        function(res){
          if(res.success){
            window.location.reload();
          }else{
            $('.loading-ajax').fadeOut();
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
          id: $("#cita").val(),
          packprices: "{{$packpricesORbundleID}}",
          isBundle: "{{$is_bundle}}"
        },
        $('meta[name="csrf-token"]').attr('content')
      );
    }
    /*
    function actionChangerMail(){
      //La funcion se paso a install.blade cuando el cliente no tenga correo se carge el modal de ingreso de correo
    }
    */
    @endif
    window.onload = function() {
      @if(!empty($QrPayment))
        $('#qr_subscrip svg').attr('width', '300px');
        $('#qr_subscrip svg').attr('height', '300px');
        $('#qr_subscrip svg').addClass('d-flex d-md-block img-fluid');
      @endif

      @if(!$is_pending)
        $('#btn_cancel_packToCS').attr('hidden', true);
      @endif
    };
  </script>
@endif

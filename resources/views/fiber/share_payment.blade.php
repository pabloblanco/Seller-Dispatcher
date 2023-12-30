<link as="style" async="async" href="{{ asset('css/styleshare.css').'?v=1' }}" rel="stylesheet">

<div class="talign-center d-block wa-btn-cont pb-5" id="block_share">
  <div class="share-options d-none">
    <div class="container">
      <div class="row justify-content-center align-items-center text-center">
        @if(!empty($phone_client))
        <div class="col-md-12" id="icon_whatsapp">
          <a href="https://wa.me/+52{{$phone_client}}?text=Buen%20día%20Sr(a)%20{{$name_client}}.%0A%0A%20{{$msgWhatsapp}}%0A%0A%20%20Te%20invitamos%20a%20revisar%20el%20siguiente%20enlace%20de%20pago%20del%20servicio%20a%20contratar.%0A%0A%0A{{$urlQr}}" target="_blank" title="Compartir a travez de whatsapp">
            <i class="fa fa-whatsapp" aria-hidden="true"></i>
          </a>
        </div>
        @endif
        <div class="col-md-12" id="icon_mail">
          <a onclick="sendMailPayment()" rel="noreferrer" role="button" title="Compartir por medio de correo electronico">

          <i class="fa fa-envelope-o" aria-hidden="true"></i>
          </a>
        </div>
        @if(!empty($phone_client))
        @handheld
        <div class="col-md-12" id="icon_sms">
        {{--
        <a href="sms:+573196638594;?&body=Mensaje%20a%20enviar">Send me SMS</a>
        --}}
          <a href="sms:+52{{$phone_client}};?&body=Buen dia Sr(a) {{$name_client}}. {{$msgText}} {{$urlQr}}" target="_blank" title="Compartir por mensajeria de texto">

            <i class="fa fa-mobile" aria-hidden="true"></i>
          </a>
        </div>
        @endhandheld
        @endif
        <div class="col-md-12" id="icon_copy">
          <a id="copyUrl" type="button" title="Copiar enlace" href="javascript:copyUrl();">
            <i class="fa fa-files-o" aria-hidden="true"></i>
          </a>
        </div>
      </div>
    </div>
  </div>

  <div class="d-flex justify-content-end">
    <a class="btn-link" onclick="wa_btn_click()" rel="noreferrer" role="button" title="Compartir enlace de pago">
      <i class="fa fa-share-alt" aria-hidden="true"></i>
      Compartir enlace "Pago recurrente"
    </a>
  </div>
</div>

<script as="script" rel="preload">
  function closeShare(){
    if(!$('.share-options').hasClass('d-none')){
      $(".share-options").fadeOut(1000);
    }
  }
  function wa_btn_click(){
    //console.log($('.wa-btn').data('id'));
    if($('.share-options').hasClass('d-none')){
      $('.share-options').removeClass('d-none');
      @if(empty($phone_client))
        showMessageAjax('alert-danger', "{{$phone_error}}");
      @endif
      $('.share-options').fadeIn(1000);
      //setInterval(closeShare, 10000);
      setTimeout(() => {
        closeShare();
      }, 10000);
    }
    else{
      $('.share-options').addClass('d-none');
      $(".share-options").fadeOut(1000);
    }
  }
  function sendMailPayment(){
    $('.loading-ajax').show();
    doPostAjax(
      '{{ route('sellerFiber.sendMailQrPayment') }}',
      function(res){
        $('.loading-ajax').fadeOut();
        swal({
          title: res.title,
          text: res.msg,
          icon: res.icon
        });
      },
      {
        id: '{{$idInstall}}'
      },
      $('meta[name="csrf-token"]').attr('content')
    );
  }
  function copyUrl(){
    ////&#128077; window.location.href
      var aux = document.createElement("input");
      aux.setAttribute("value","{{$urlQr}}");
      document.body.appendChild(aux);
      aux.select();
      document.execCommand("copy");
      document.body.removeChild(aux);
      swal({
        title: "Enlace de pago copiado! 👍",
        //text: "Puedes pegar y enviar el enlace de pago por el medio que se adapte el cliente",
        type: "success",
        timer: 5000
      });
  }

</script>

<link as="style" async="async" href="{{ asset('css/styleshare.css').'?v=1' }}" rel="stylesheet">

<div class="talign-center d-block wa-btn-cont pb-5" id="block_share">
  <div class="share-options d-none">
    <div class="container">
      <div class="row justify-content-center align-items-center text-center">
        @if(!empty($phone_client))
        <div class="col-md-12" id="icon_whatsapp">
          <a href="https://wa.me/+52{{$phone_client}}?text=Buen%20día%20Sr(a)%20{{$name_client}}.%0A%0A%20{{$msgWhatsapp}}%0A%0A%20%20Te%20invitamos%20a%20ver%20más%20detalles%20en%20el%20siguiente%20enlace%0A%0A{{$urlQr}}" target="_blank" title="Compartir a travez de whatsapp">
            <i class="fa fa-whatsapp" aria-hidden="true"></i>
          </a>
        </div>
        @endif
        <div class="col-md-12" id="icon_mail">
          <a onclick="sendMail()" rel="noreferrer" role="button" title="Compartir por medio de correo electronico">

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
        {{--Input deprecado--}}
        {{--<input type="hidden" name="tyc" id="tyc" value="{{compoundId(base64_encode($tyc))}}">--}}
      </div>
    </div>
  </div>

  <div class="share_tyc">
    <a class="btn-link" onclick="wa_btn_click()" rel="noreferrer" role="button" title="Compartir informacion de terminos y condiciones">
      <i class="fa fa-share-alt" aria-hidden="true"></i>
      Compartir enlace "Contrato de Adhesión"
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
  function sendMail(){
    $('.loading-ajax').show();
    doPostAjax(
      '{{ route('sellerFiber.sendMailQr') }}',
      function(res){
        $('.loading-ajax').fadeOut();
        swal({
          title: res.title,
          text: res.msg,
          icon: res.icon
        });
      },
      {
        dni: '{{$dni}}',
        type: '{{compoundId(base64_encode($type))}}'
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
        title: "Enlace de contrato copiado! 👍",
        //text: "Puedes pegar y enviar el enlace de pago por el medio que se adapte el cliente",
        type: "success",
        timer: 5000
      });
  }
</script>

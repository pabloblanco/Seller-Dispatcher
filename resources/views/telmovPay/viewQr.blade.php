<div aria-hidden="true" aria-labelledby="myModalLabel" class="modal fade" id="identity-qr" role="dialog" style="display: none;" tabindex="-1">
  <div class="modal-dialog" style="max-height:95%;  margin-top: 180px;">
    <div class="modal-content">
      <div class="modal-header">
        <button aria-hidden="true" class="close" data-dismiss="modal" type="button">
          ×
        </button>
        <h4 class="modal-title">
          Proceso de verificación de identidad
          <span id="type-sell-txt">
          </span>
        </h4>
      </div>
      <div class="modal-body" style="height:550px;width:100%;border:none;">
        {{--
        <embed onload="frameLoaded();" height="550px" src="{{$urlQR}}" type="text/html" width="100%">
        </embed>
        --}}
        {{--
        <object onload="frameLoaded();" data="{{$urlQR}}"
        width="100%"
        height="550px"
        type="text/html">
        </object>
        --}}
        <iframe onload="frameLoaded();" src="{{$urlQR}}" style="height:550px;width:100%;border:none;">
        </iframe>
      </div>
      <div class="modal-footer">
        <span class="text-center" id="msgLoading">
          Cargando... Por favor espere
        </span>
        <button class="btn btn-default waves-effect" data-dismiss="modal" hidden="" id="btnQrcancel" type="button">
          Cancelar
        </button>
        <button class="btn btn-danger waves-effect waves-light" hidden="" id="btnQr" type="button">
          Verificación finalizada
        </button>
      </div>
    </div>
  </div>
</div>
<script type="text/javascript">
  frameLoaded = function(){
    $('#msgLoading').attr('hidden', true);
    $('#btnQrcancel').attr('hidden', null);
    $('#btnQr').attr('hidden', null);
  }

  lastQr = function (res){
    $('.loading-ajax').fadeOut();
    $('#identity-qr').modal('hide');
    showMessageAjax(res.icon, res.message);
    if(!res.success){
      if(res.html.length > 0 && res.infoClient){
        $('#viewInfoClient').html(res.html);
        $('#brand-content').attr('hidden', null);
      }
    }
  }

  $('#btnQr').on('click', function(e){
    $('.loading-ajax').show();
    doPostAjax(
        "{{ route('telmovpay.requestQrVerifyLast') }}",
          lastQr,
          {
            ine: '{{$Dni}}'
        },
          '{{ csrf_token() }}'
      );
  });
</script>

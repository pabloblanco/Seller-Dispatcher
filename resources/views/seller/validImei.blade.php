<div class="row p-b-20" hidden="" id="val-content-phone">
  <label class="col-md-12">
    Comprobar compatibilidad
  </label>
  <div class="col-md-12">
    <div class="alert alert-info alert-dismissable">
      <button aria-hidden="true" class="close" data-dismiss="alert" type="button">
        ×
      </button>
      Para obtener el número de IMEI del teléfono celular marca *#06#.
    </div>
    <div class="input-group">
      <input class="form-control" id="imei" maxlength="15" name="imei" placeholder="IMEI" type="number"/>
      <input type="hidden" id="imei_copy" value="">
      <span class="input-group-btn">
        <button class="btn btn-success" id="valid-imei" type="button">
          Consultar
        </button>
      </span>
    </div>
    <div class="help-block "{{--with-errors--}} id="imei-error">
      Imei 15 dígitos
    </div>
    <input id="is-band-te" name="is-band-te" type="hidden" value=""/>
    <input id="imei_brand" name="imei_brand" type="hidden" value=""/>
    <input id="imei_model" name="imei_model" type="hidden" value=""/>
    <div class="help-block alert" id="alert-comp">
    </div>
  </div>
</div>
<script type="text/javascript">

$('#imei').on('change',function(){
  if($('#imei_copy').val() != ''){
    showMessageAjax('alert-danger','El imei fue cambiado, se debe verificar compatibilidad');
    $('#imei_copy').val('');
    $('#alert-comp').text("Se debe oprimir 'consultar' ya que ingresaste un nuevo IMEI...!");
    $('#alert-comp').removeClass('alert-danger').removeClass('alert-success').removeClass('text-white');
    $('#alert-comp').addClass('alert-warning');
    $('#alert-comp').addClass('text-dark');

    @if(isset($saleFiber))
      @if($saleFiber)
        $('#installer-content').attr('hidden', true);
      @endif
    @endif

  }
});
</script>

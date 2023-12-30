<div class="row justify-content-center align-items-center">
  <div class="col-md-4 col-12">
      <label>
        Tipo de documento
      </label>
      <select class="form-control" id="typeDocument" name="typeDocument" required>
        <option value="">
          Seleccione un tipo
        </option>
        @foreach ($typeDocument as $status)
          <option value="{{$status}}">
            {{$status}}
          </option>
        @endforeach
      </select>
      <div class="help-block" id="error-typeDocument"></div>
  </div>
  <div class="col-md-8 col-12">
    <label>Numero del documento de identidad fotografiado: (9 a 25 caracteres)</label>
    <input class="form-control" id="identification" autocomplete="off" title="Ingresa el numero del documento de identidad que se ha fotografiado, esta informacion sera verificada." name="identification" placeholder="Ingrese el documento de identidad" required="" type="text" minlength="9" maxlength="25" />
    <div class="help-block" id="error-identification"></div>
  </div>
</div>
<script type="text/javascript">
$(function () {

  $('#identification').bind("keyup change", function (e) {
      if($('#identification').val().trim().length >= 9 && $('#identification').val().trim().length <= 25){
        $('#error-identification').text('');
      }
  });
  $('#typeDocument').bind("change", function (e) {
      if($('#typeDocument').val()!=''){
        $('#identification').val('');
        $('#error-typeDocument').text('');
        $('#error-identification').text('');
      }
  });
});
</script>

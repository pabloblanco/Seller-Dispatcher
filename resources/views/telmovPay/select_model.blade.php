<div class="form-group">
  <label class="control-label">
    Modelo del celular
  </label>
  @if(!empty($modelosSmartPhone))
  <select class="form-control" id="modelPhone" name="modelPhone">
    <option value="">
      Seleccione un modelo
    </option>
    @foreach ($modelosSmartPhone as $item)
    <option value="{{base64_encode($item['id'])}}">
      {{$item['model']}}
    </option>
    @endforeach
  </select>
  @else
  <div class="alert alert-danger">
    <p>
      No hay equipos de {{$brand}}.
    </p>
  </div>
  @endif
</div>
<script type="text/javascript">
  $('#modelPhone').on('change', function(e){
      var model = $(this).val().trim();

      //console.log("model ",model);
      if(model != ''){
         $('#port-content').attr('hidden', null);
      }else{
        resetView(2);
      }
   });
</script>

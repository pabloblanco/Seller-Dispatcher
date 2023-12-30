@include('fiber.resumenInstallFiber',['dataInstall' => $dataInstall])

<label class="col-md-12">
  Tipificacion:
</label>
@if(count($listTypification))
<div>
  <select class="form-control" id="typification" data-cita="{{$citaId}}" data-type="{{$typeBtn}}" name="typification" placeholder="Seleccione una tipificacion" required="">
    <option value="">
      Seleccione una tipificacion
    </option>
    @foreach($listTypification as $item)
    <option value="{{ $item->id }}">
      {{ $item->descripcion }}
    </option>
    @endforeach
  </select>
  <label class="help-block with-errors" id="error-typification">
  </label>
</div>
<hr width="80%"/>
<div id="blockTypi">
  <label for="limitTypi">
    Detalles de cancelación:
  </label>
  (
  <span id="limitTypi">
    250
  </span>
  )
  caracteres disponibles
  <textarea class="form-control" id="msgTypi" name="msgTypi" onkeydown="textMaximo(this,'#limitTypi');" onkeyup="textMaximo(this,'#limitTypi');" placeholder="Indique la causa de cancelacion de la cita" required="true" rows="3" style="line-height: 2.4rem;">
  </textarea>
  <label class="help-block with-errors" id="error-msgTypi">
  </label>
</div>

<button hidden id="returnStep" data-id="{{$citaId}}" data-type="{{$typeBtn}}" data-target="#detail-install-modal" data-toggle="modal" type="button">
</button>
@else
<div class="alert alert-danger">
  <p>
    No hay tipificación para mostrar en este momento.
  </p>
</div>
@endif

<script type="text/javascript">

@if(count($listTypification))

$(function () {
  var typificationField = document.getElementById('typification');
  initCausa();
  if(typeof typificationField !== 'undefined'){

    $('#typification').selectize();
    $('#typification').change(function (e) {
      e.preventDefault();
      if($('#typification').val()!==''){
        $('#error-typification').text('');
          if($('#typification').val()!='13'){
            $('#error-msgTypi').text('')
          }
        /*if($('#typification').val()=='13'){
          //Ingreso la otra causa
          $('#blockTypi').attr('hidden', null);
        }else{*/
          initCausa();
        //}
      }
    });
    $('#msgTypi').change(function (e) {
      e.preventDefault();
      if($('#msgTypi').val()!==''){
        $('#error-msgTypi').text('');
      }
    });
  }
});

function initCausa(){
  //$('#blockTypi').attr('hidden', true);
  $('#msgTypi').val('');
  $('#limitTypi').html('250');
}

function textMaximo(campo, itemHtml, limite = 250) {
  if (campo.value.length > limite) {
    campo.value = campo.value.substring(0, limite);
  } else {
    $(itemHtml).html(limite - campo.value.length);
  }
}
@endif
</script>

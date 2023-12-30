<label>
  Horario de la instalación:
</label>
@if($availableTime)
<select class="form-control" id="hour" name="hour" required="">
  <option value="">
    Seleccione un turno para instalar
  </option>
  @php
  $unoccupied = " (Horario no disponible)";
  @endphp
  @foreach($listTurno as $turno)
  <option value="{{ compoundId(base64_encode($turno['hour'])) }}" @if($turno['habilite']=='N') class="d-none" disabled @endif data-installer="{{ $turno['habilite'] }}">
    {{ $turno['hour'] }} @if($turno['habilite']=='N') {{ $unoccupied }} @endif
  </option>
  @endforeach
</select>
<div class="help-block with-errors" id="error-hours"></div>

@else
<div class="container alert alert-danger">
  No hay disponibilidad horaria en la fecha seleccionada para agendar su cita de instalación
</div>
@endif
<script type="text/javascript">
  $(function () {
    $('#hour').change(function (e) {
      e.preventDefault();
      if($('#hour').val()!==''){
        $('#error-hours').text('');
        $('#photo-content').attr('hidden', null);
        if(!$('#typePlan').is(':checked')){
          /*Planes normales*/
          $('#forzoso-content').attr('hidden', true);
          $('#reg-install').attr('disabled', null);
          $('#reg-install').attr('hidden', null);
        }else{
          /*planes forzados*/
          $('#forzoso-content').attr('hidden', null);
          $('#reg-install').attr('hidden', true);
          //recibo=>foto del recibo publico
          //identiF=>foto frontal de la identidad
          createCam('recibo','identiF');
        }
      }
    });
  });
</script>

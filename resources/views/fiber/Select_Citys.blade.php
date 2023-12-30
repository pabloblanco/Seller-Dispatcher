<label class="col-md-12">
  Ciudad
</label>
@if(count($cities))
<select class="form-control" id="city" name="city" placeholder="Seleccione una ciudad" required="">
  <option value="">
    Seleccione una ciudad
  </option>
  @foreach($cities as $city)
  <option value="{{ $city->fiber_city_id }}">
    {{ $city->fiber_city }}
  </option>
  @endforeach
</select>
<div class="help-block with-errors">
</div>
@else
<div class="alert alert-danger">
  <p>
    Falló consulta de ciudades por favor actualice la página.
  </p>
</div>
@endif
<script type="text/javascript">
  @if(count($cities))
    $(function () {
      @desktop
        $('#city').selectize();
      @enddesktop
    });
  @endif
$('#city').change(function (e) {
  e.preventDefault();
  ResetViewFiberCite('city');
  if($('#city').val()!==''){

    $('.loading-ajax').show();
    $.ajax({
        headers: {
          'X-CSRF-TOKEN': CSRF_TOKEN
        },
        url: '{{ route('sellerFiber.getOlts') }}',
        type: 'POST',
        dataType: 'json',
        data: {
          cityid: $(this).val()
        },
        error: function() {
          $(".preloader").fadeOut();
          showMessageAjax('alert-danger', 'Ocurrio un error al consultar puntos de conexion OLT.');
        },
        success: function(data) {
          if (!data.error) {
            $('.loading-ajax').fadeOut();
            if (data.success) {
              $('#blockOlt').removeClass('d-none');
              $('#blockOlt').html(data.msg);
            }
          } else {
            $('.loading-ajax').fadeOut();
            if (data.message == 'TOKEN_EXPIRED') {
              showMessageAjax('alert-danger', 'Su session a expirado, por favor actualice la página.');
            } else {
              showMessageAjax('alert-danger', data.message);
            }
          }
        }
      });
  }else{
      ResetViewFiberCite('city');
  }
});
</script>

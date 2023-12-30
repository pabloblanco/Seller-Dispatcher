@if(!empty($fin))
<div class="col-md-12">
  <div class="col-md-10 p-l-0 su-payjoy">
    <div class="alert alert-success">
      <p>
        Financiamiento aprobado.
      </p>
    </div>
  </div>
  <div class="col-sm-12 col-md-6">
    <label>
      Cliente
    </label>
    <p>
      {{ $client->name }} {{ $client->last_name }}
    </p>
  </div>
  <div class="col-sm-12 col-md-6">
    <label>
      Teléfono del cliente
    </label>
    <p>
      {{ $fin->msisdn }}
    </p>
  </div>
  <div class="col-sm-12 col-md-6">
    <label>
      Monto financiado
    </label>
    <p>
      $ {{ $fin->initial_amount }}
    </p>
  </div>
  <div class="col-sm-12 col-md-6">
    <label>
      Monto inicial
    </label>
    <p>
      $ {{ $fin->total_amount - $fin->initial_amount }}
    </p>
  </div>
  <div class="col-sm-12 col-md-6">
    <label>
      Fecha del financiamiento
    </label>
    <p>
      {{ $fin->date_enganche }}
    </p>
  </div>
  <div class="col-md-12">
    <button class="btn btn-success waves-effect waves-light" id="associate" type="button">
      Asociar
    </button>
  </div>
  @else
  <div class="col-md-10 p-l-0 su-payjoy">
    <div class="alert alert-warning">
      <p>
        No hay financiamiento aprobado para el msisdn seleccionado.
      </p>
    </div>
  </div>
</div>
@endif
<script type="text/javascript">
  $(function () {
    let resultSave = function(res){
      $('.loading-ajax').fadeOut();
                
            $('#associate').attr('disabled',false);

            if(!res.error){
                swal({
                    title: "Exito!",
                    text: 'Se realizo la asociación de forma exitosa.',
                    icon: "success",
                    button: {text: "OK"},
                }).then(() => {
                  $('#result-q').html('');
                  $('#dn').val('');
                });
            }else{
                if(res.message == 'TOKEN_EXPIRED'){
                    showMessageAjax('alert-danger','Su session a expirado, por favor actualice la página.');
                }else if(res.message){
                    swal({
                        title: "Advertencia",
                        text: res.message,
                        icon: "warning",
                        button: {text: "OK"},
                    });
                }
            }
    };

    $('#associate').unbind('click');

    $('#associate').on('click', function(e){
      let msisdn = $('#dn').val().trim();

      if(msisdn != '' && msisdn.length == 10){
                $(e.currentTarget).attr('disabled',true);
                $('.loading-ajax').show();

                doPostAjax(
                    "{{ route('paguitos.savePaguitos') }}", 
                    resultSave, 
                    {msisdn: msisdn},
                    '{{ csrf_token() }}'
                );
            }else{
                showMessageAjax('alert-danger', 'Debe escribir un MSISDN válido.');
            }
    });
  });
</script>
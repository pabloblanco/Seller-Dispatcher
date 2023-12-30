<form class="col-md-12" id="formPaymentTelmov">
  <label class="box-title col-md-12">
    Datos del enganche
  </label>
  <div class="form-group mb-2 col-md-12">
    <label class="col-md-12">
      Semanalidad del financiamiento:
    </label>
    <div class="input-group">
      <select class="form-control" id="periodicity" name="periodicity">
        <option value="">
          Seleccione una semanalidad
        </option>
        @php
      $periodos = json_decode($infoCredit->WeekAmounts);
      @endphp
      @foreach ($periodos as $item => $value)
        <option value="{{base64_encode($item)}}">
          {{$item}} Semanas - {{$value}} $
        </option>
        @endforeach
      </select>
    </div>
  </div>
  <div class="form-group mb-2 col-md-12">
    <label class="col-md-12">
      Monto del enganche:
    </label>
    <div class="col-md-12 input-group">
      <strong>{{ '$ '. $infoCredit->minimumPayment }}</strong>
      <input class="d-none" id="enganche" max="{{$infoCredit->payment}}" min="{{$infoCredit->minimumPayment}}" name="enganche" type="hidden" value="{{$infoCredit->minimumPayment}}"/>
    </div>
  </div>
  <div class="my-5 col-md-12">
    <button class="btn btn-success waves-effect waves-light" id="create-finance" type="button">
      Iniciar contrato de telmovPay
    </button>
  </div>
</form>
<script type="text/javascript">
  jQuery.extend(jQuery.validator.messages, {
       required: "Este campo es obligatorio.",
       number: "Por favor, escribe un número entero válido.",
       digits: "Por favor, escribe sólo dígitos.",
       equalTo: "Por favor, escribe el mismo valor de nuevo.",
       max: jQuery.validator.format("Por favor, escribe un valor menor o igual a {0}."),
       min: jQuery.validator.format("Por favor, escribe un valor mayor o igual a {0}.")
    });

    $("#formPaymentTelmov").validate({
      rules: {
        periodicity: {
          required: true
        },
        enganche: {
          required: true,
          number: true,
          max: {{$infoCredit->payment}},
          min: {{$infoCredit->minimumPayment}}
        }
      }
    });

    resultInitContract = function(res){
      $('.loading-ajax').hide();
      if(!res.success){
        showMessageAjax(res.icon, res.message);
      }else{
        //var QR = document.getElementById("QrContract");
        //QR.dataset.src = res.QrContract;
        $('#QrContract').html(res.QrContract);
        $('#blok_Contract').removeClass('d-none');
        $('#blok_Contract').addClass('d-block');
        $('#QrContract svg').attr('width', '300px');
        $('#QrContract svg').attr('height', '300px');
      }
    }

    $('#create-finance').on('click', function(e){
      resetView(3);
      valid = $("#formPaymentTelmov").valid();
      if(!valid){
         showMessageAjax('alert-danger', 'Por favor revisa los datos del financiamiento.');
      }else{
        $('.loading-ajax').show();
        doPostAjax(
            "{{ route('telmovpay.initContract') }}",
            resultInitContract,
            {
              dni: $('#client').val(),
              enganche: $('#enganche').val(),
              periodicity: $('#periodicity').val()
            },
            '{{ csrf_token() }}'
        );
      }
    });
</script>

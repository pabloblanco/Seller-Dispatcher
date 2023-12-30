@if(count($packs))

<select class="form-control listPlan" id="plan" required >
  <option value="">
    Seleccione un plan
  </option>
  @foreach($packs as $pack)
  <option value="{{compoundId(base64_encode($pack->id))}}" data-force="{{compoundId(base64_encode($pack->is_payment_forcer))}}" data-suscrip="{{compoundId(base64_encode($pack->for_subscription))}}"  data-bundle={{compoundId(base64_encode($pack->is_bundle))}}>
    {{$pack->title}}
  </option>
  @endforeach
</select>
@else
<div class="alert alert-danger">
  <input type="hidden" name="plan" id="plan" value="">
  <p>
    No hay planes disponibles.
  </p>
</div>
@endif

@if(count($packs))
  @if($view != "seller")
  <script src="{{ asset('plugins/bower_components/jquery/dist/jquery.min.js') }}">
  </script>
  @endif
<script as="script" rel="preload">

  function getNewPaymentSubs(plan, bundle){
    //console.log('PLAN '+plan+' bundle '+bundle);
    $('.loading-ajax').show();
    doPostAjax(
      "{{ route('sellerFiber.getPaymentSubscrip') }}",
      function(res){
        $('.loading-ajax').hide();
        if(!res.success){
          let code = res.code;
          swal({
            title: res.title,
            text: res.msg,
            icon: res.icon,
            dangerMode: true,
            closeOnClickOutside: false,
          }).then(() => {
            if(code == "EMP_MAI"){
              actionChangerMail(plan);
            }
          });
        }else{
          $('#block_payment_subscrip').html(res.html);
        }
      },
      {
        id: $('#cita').val(),
        plan: plan,
        isBundle: bundle
      },
      $('meta[name="csrf-token"]').attr('content')
    );
  }

  function setearAreaImei(resetValue=true){
    $('#blockValidImei').addClass('d-none');
    if(resetValue){
      $('#imei').val('');
      $('#imei_copy').val('');
      $('#alert-comp').removeClass('alert-danger').removeClass('alert-success');
      $('#alert-comp').removeClass('text-white').removeClass('text-dark');
      $('#alert-comp').text("");
      $('#alert-comp').hide();
    }
  }

  $('#btn_changer_packToSS').attr('disabled', true);

  $('#plan').on('change', function(e){

    let id = '#'+ $(this).attr('id');

    if($(id).val()!==''){
      let suscrip = $(id+' option:selected').attr('data-suscrip');
      let force   = $(id+' option:selected').attr('data-force');
      let bundle  = $(id+' option:selected').attr('data-bundle');
      //console.log(id+' '+suscrip+' '+force+' '+bundle);

      $("#plan").val($(id).val());
      $("#plan").data('description',$(id+' option:selected').text().trim());
      let plan = $(id).val();
      $('#plan-content').html('');

      if(plan && plan != ''){
        $('.loading-ajax').show();

        doPostAjax(
          '{{ route('sellerFiber.getPlan') }}',
          function(res){
            $('.loading-ajax').hide();

            if(res.sucess){
              $('#plan-content').html(res.html);
              if("{{$view}}" == "seller"){
                //Ya no selecciono instalador al vender (Se depreco)
                //$('#insta-content').attr('hidden', null);
                $('#cal-content').attr('hidden', null);
                /*Se inicializa segun la zona la disponibilidad del dia*/
                initCalendar();
              }else{
                //Modulo de instalador cambio a plan con suscripcion
                $('#plan-content').attr('hidden', null);
                //Realizo la peticion para pedir la url de pago de plan por suscripcion
                $('#btn_changer_packToSS').data('newpack',plan);
                getNewPaymentSubs(plan, bundle);
                $('#btn_changer_packToSS').attr('disabled', false);
              }
              //Venta de algun bundle
              var categoryT = document.getElementById('category_T');
              // Make sure field object exists
              if (typeof categoryT !== 'undefined') {
                if($("#category_T").val()=='2'){
                  //Verifico imei por existir SimCard
                  $('#blockValidImei').removeClass('d-none');
                  $('#val-content-phone').attr('hidden', null);
                  $('#installer-content').attr('hidden', true);
                }else{
                  //no se verifica imei de nuevo xq ya acabaron de colocar uno
                  setearAreaImei(false);
                  $("#is-band-te").val('Y');
                  //Los telefonos vendidos son para la banda28
                  $('#installer-content').attr('hidden', null);
                }
              }else{
                //No se verifica imei ya que es otra categoria de producto
                setearAreaImei();
                $('#installer-content').attr('hidden', null);
              }
            }else{
              showMessageAjax('alert-danger',res.msg);
            }
          },
          {
            plan: plan,
            isbundle: bundle
          },
          $('meta[name="csrf-token"]').attr('content')
        );
      }
    }else{
      if("{{$view}}" == "seller"){
        //Modulo de vendedor
        setearAreaImei();
        ResetViewFiberCite('plan');
      }else{
        //Modulo de instalador
        $('#plan-content').attr('hidden', true);
        $('#block_payment_subscrip').html('');
        $('#btn_changer_packToSS').attr('disabled', true);
      }
    }
  });
</script>
@endif

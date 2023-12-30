@if(empty($packs->status) || $packs->status == false)
<div class="alert alert-danger">
  <ul>
    <li>
      {{ empty($packs->message) ? 'Ocurrio un error.' : $packs->message }}
    </li>
  </ul>
</div>
@else
<div class="col-md-12">
  <label>
    Plan a asignar
  </label>
  <div class="form-group">
    <select class="form-control" id="plan">
      <option value="">
        Seleccione un plan.
      </option>
      @foreach($packs->packs as $pack)
      <option data-sale="{{ $pack->sale_type }}" data-type="{{$pack->servicio->type}}" value="{{compoundId(base64_encode($pack->id))}}">
        {{$pack->title}}
      </option>
      @endforeach
    </select>
  </div>
</div>

@foreach($packs->packs as $pack)
  <div class="block-plan p-l-0 col-md-12 hidden {{compoundId(base64_encode($pack->id))}}">
    @include('seller.InfoPlan', ['pack' => $pack, 'isOptionTelmov' => false])
    <div class="col-sm-12 col-md-6 router-{{compoundId(base64_encode($pack->id))}}">
      <label>
        MSISDN:
      </label>
      <input class="typeahead form-control msisdn @if(count($pack->servicio->articles) == 1) one-msisdn @endif" placeholder="MSISDN" type="text" value="@if(count($pack->servicio->articles) == 1){{$pack->servicio->articles[0]->msisdn}}@endif"/>
    </div>

    <div class="p-l-0 col-sm-12 col-md-12 detail-{{compoundId(base64_encode($pack->id))}}">
      @if(count($pack->servicio->articles) == 1)
      <div class="col-sm-12 p-l-0 col-md-12 m-t-20 arti-detail {{$pack->servicio->articles[0]->msisdn}}">
        @php
          $equip  = new \stdClass;
          $equip->brand = $pack->servicio->articles[0]->brand;;
          $equip->model = $pack->servicio->articles[0]->model;;
          $equip->imei = $pack->servicio->articles[0]->imei;
          $equip->serial = $pack->servicio->articles[0]->serial;;
          $equip->iccid = $pack->servicio->articles[0]->iccid;
          $equip->msisdn = $pack->servicio->articles[0]->msisdn;
        @endphp
        @include('seller.InfoEquip', ['equip' => $equip])

      </div>
      @else
      @foreach($pack->servicio->articles as $arti)
      <div class="col-sm-12 p-l-0 col-md-12 m-t-20 hidden arti-detail {{$arti->msisdn}}">
        @php
          $equip  = new \stdClass;
          $equip->brand = $arti->brand;;
          $equip->model = $arti->model;;
          $equip->imei = $arti->imei;
          $equip->serial = $arti->serial;;
          $equip->iccid = $arti->iccid;
          $equip->msisdn = $arti->msisdn;
        @endphp
        @include('seller.InfoEquip', ['equip' => $equip])

      </div>
      @endforeach
      @endif
    </div>
    <div class="col-md-12 py-5">
      <button type="button" id="btn-reg-{{compoundId(base64_encode($pack->id))}}" class="btn btn-success waves-effect waves-light cheking-finance" data-art="{{compoundId(base64_encode($pack->id))}}" @if($pack->valid_identity == 'Y') hidden @endif>
          Consultar Financiación
      </button>
    </div>
  </div>
@endforeach

<script type="text/javascript">
$(function() {

    var typeH = [],
    numSelected = false;

    @foreach($packs->packs as $pack)
    var arr = [];
    @foreach($pack->servicio->articles as $arti)
    arr.push({{ $arti->msisdn }});
    @endforeach
    typeT = $('.router-{{compoundId(base64_encode($pack->id))}} .typeahead').typeahead({
      hint: true
      , highlight: true
      , minLength: 1
      , dynamic: true
    }, {
      name: 'articles'
      , source: substringMatcher(arr)
    }).on('typeahead:selected', function(evt, data) {
      dnSelected(data);
    });
    typeH['{{compoundId(base64_encode($pack->id))}}'] = typeT;
    @endforeach

  let dnSelected = function(dn) {
      if (!$('.' + dn).length) {
        showMessageAjax('alert-danger', 'El msisdn no existe.');
      }

      if ($('.' + dn).hasClass('hidden')) {
        $('.arti-detail').addClass('hidden');
        $('.' + dn).removeClass('hidden');
      }
    }

  $('#plan').on('change', function(e) {
    var block = $('#plan').val();
    $('.block-plan').addClass('hidden');

    if (block && block != '') {
      $('.' + block).removeClass('hidden');
    }

    if (!$('.router-' + block + ' .msisdn').hasClass('one-msisdn')) {
        $('.router-' + block + ' .msisdn').val('');
    }
  });

    isNumeric = function(val) {
      return /^\d+$/.test(val);
    }

    validMSISDN = function(e){
      let num = typeH[$(e.currentTarget).data('art')].val();
      let art = $(e.currentTarget).data('art');

      let dataR = {
        'success': true,
        'num': num,
        'art': art};

      if (!num || num == '') {
        showMessageAjax('alert-danger', 'Debe seleccionar el MSISDN');
        dataR['success'] = false;
        return dataR;
      }else{
        if(num.length != 10 || !isNumeric(num) ){
          showMessageAjax('alert-danger', 'Debe ingresar un MSISDN valido');
          dataR['success'] = false;
          return dataR;
        }
      }
      return dataR;
    }

    SearhSmartPhoneTelmov = function(res){
      $('.loading-ajax').hide();
      if(!res.success){
        showMessageAjax(res.icon, res.message);
        $('#resultCredit').html(res.message);
      }else{
        $('#resultCredit').html(res.html);
      }
    }

    $('.cheking-finance').on('click', function(e) {
      dataR = validMSISDN(e);
      if(dataR['success']){
        resetView(3);
        $('.loading-ajax').show();
        $.ajax({
          type: 'POST',
          url: "{{route('seller.validNumberSale')}}",
          data: {
            _token: "{{ csrf_token() }}",
            msisdn: dataR['num'],
            pack: dataR['art']
          },
          success: function(data) {

            if (data.message == 'TOKEN_EXPIRED') {
              showMessageAjax('alert-danger', 'Su session a expirado, por favor actualice la página.');
            } else {
              if (data.error == false) {
                doPostAjax(
                  "{{ route('telmovpay.getModels') }}",
                  SearhSmartPhoneTelmov,
                  {
                    msisdn: dataR['num'],
                    pack: dataR['art'],
                    brand: $('#brand-mov').val(),
                    model: $('#modelPhone').val(),
                    port:  $('#port').val(),
                    ine: $('#client').val(),
                    _token: '{{ csrf_token() }}'
                });
              }else {
                if (data.msg) {
                  showMessageAjax('alert-danger', data.msg);
                } else {
                  showMessageAjax('alert-danger', 'El Artículo no esta asociado con el plan seleccionado.');
                }
              }
            }
          },
          error: function() {
            $('.loading-ajax').hide();
            showMessageAjax('alert-danger', 'Ups Ocurrio un error, por favor intenta de nuevo.');
          }
        });
      }
    });

});
</script>
@endif

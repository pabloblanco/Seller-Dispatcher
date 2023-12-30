@php
  $citas_hoy=0;
  //$citas_vencidas_all=0;
  foreach($card_data[$card_id] as $date){
    /*if(strtotime($date->date_instalation) < strtotime(date('d-m-Y'))){
      $citas_vencidas_all++;
    }*/
    if(strtotime($date->date_instalation) == strtotime(date('d-m-Y'))){
      $citas_hoy++;
    }
  }
@endphp

  <div class="col-lg-3 col-md-4 col-sm-6" id="{{$card_id}}">
    <div class="white-box text-center px-4">
      <h3 class="box-title" style="line-height: normal;">
        {!!$card_title!!}
      </h3>
      <div class="row justify-content-center text-center">
        <div class="col-12">
          <div class="row justify-content-center">
            <div class="icon-dashboard">
              <i class="{{$card_icon}} icon">
              </i>
            </div>
          </div>
          <div class="py-3 text-left">
            Total de </br> {{$card_label}}: <span class="counter font-weight-bold">{{count($card_data[$card_id])}}</span>
          </div>
          <div class="py-2 text-left">
            Planificado para hoy: <span class="counter font-weight-bold" id="{{$hoy_id}}"> {{$citas_hoy}} </span>
          </div>
          @if($card_data[$caduce_array])
            <div class="py-2 text-left">
              {{ucfirst($card_label)}} vencidas ayer: <span class="counter font-weight-bold" id="{{$div_caduce}}">{{ $card_data[$caduce_array] }}</span>
            </div>
          @endif
        </div>
        <div class="col-sm-12 pt-3">
          @if(count($card_data[$card_id]))
            <button class="btn btn-success waves-effect my-3" id="{{$card_btn}}" type="button" onclick='viewDetailFiber("{{$card_type}}","{{base64_encode(json_encode($card_data))}}", "{{$operation}}")' data-info="{{base64_encode(json_encode($card_data))}}" data-type="{{$card_type}}">
              Ver detalles
            </button>
          @else
            <div class="alert alert-danger py-2 mb-0">
              <p style="line-height: normal;">
                No hay {{$card_label}} por listar!
              </p>
            </div>
          @endif
        </div>
      </div>
    </div>
  </div>

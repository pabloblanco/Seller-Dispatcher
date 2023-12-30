<label class="box-title col-md-12">
  Datos del plan
</label>
<input class="service-{{compoundId(base64_encode($pack->id))}}" name="service" type="hidden" value="{{base64_encode($pack->servicio->id)}}"/>
<div class=" mb-2">
  <label class="col-sm-12">
    Descripción del plan:
  </label>
  <div class="col-sm-12 pb-md-3 pack-descrip">
    {{ (isset($pack->description) && !empty($pack->description))? $pack->description : "S/N" }}
  </div>
</div>
<div class=" mb-2">
  <label class="col-sm-12">
    Servicio:
  </label>
  <div class="col-sm-12 pb-md-3 service-title">
    {{ (isset($pack->servicio->title) && !empty($pack->servicio->title))? $pack->servicio->title : "S/N" }}
  </div>
</div>
<div class=" mb-2">
  <label class="col-sm-12">
    Descripción del servicio:
  </label>
  <div class="col-sm-12 pb-md-3 service-descrip">
    {{ (isset($pack->servicio->description) && !empty($pack->servicio->description))? $pack->servicio->description : "S/N" }}
  </div>
</div>
@if(($pack->sale_type == 'N' && empty($pack->config)) || $isOptionTelmov )
<div class="mb-2">
  <label class="col-sm-12">
    Monto a pagar:
  </label>
  <div class="col-sm-12 pb-md-3 service-descrip">
    <strong class="price-client-{{compoundId(base64_encode($pack->id))}}">
    @if($isOptionTelmov)
      $ {{ $pack->enganche}}
    @else
      $ {{ ($pack->servicio->price_pack + $pack->servicio->price_serv) }}
      @endif
    </strong>
  </div>
</div>
@endif

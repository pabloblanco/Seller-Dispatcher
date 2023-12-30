@if(isset($plan))
  <div class="col-sm-12 col-md-6">
    <label>Descripción del plan {{$titleView}}:</label>
    <p id="txt-plan-description_{{$typeArt}}">{{$plan->description}}</p>
  </div>

  <div class="col-sm-12 col-md-6">
    <label>Servicio {{$titleView}}:</label>
    <p id="txt-service_{{$typeArt}}">{{$plan->service_title}}</p>
  </div>

  <div class="col-sm-12 col-md-6">
    <label>Descripción del servicio {{$titleView}}:</label>
    <p id="txt-service-description_{{$typeArt}}">{{$plan->service_description}}</p>
  </div>

  <div class="col-sm-12 col-md-6">
    <label>Art&iacute;culo {{$titleView}}:</label>

    @if(isset($arti_install_zone))
      @if($arti_install_zone['success'])
        @if($data->owner=='V')
          <input id="equipo" name="equipo" type="hidden" value="{{$arti_install_zone['article_id']}}"/>
        @endif
        <p id="txt-product_{{$typeArt}}">
          {{$plan->product_title}}
        </p>
      @else
        <div class="alert alert-danger">
          <p>
            No se pudo obtener información del articulo que se debe instalar
          </p>
        </div>
      @endif
    @else
      <p id="txt-product_{{$typeArt}}">{{$plan->product_title}}</p>
    @endif
  </div>

  <div class="col-sm-12 col-md-6">
    <label>Precio {{$titleView}}:</label>
    <p id="txt-price_{{$typeArt}}">${{number_format($plan->total_price, 2, '.', ',')}}</p>
  </div>
@else
  <div class="alert alert-danger">
    Detalles del plan configurado no disponible.
  </div>
@endif

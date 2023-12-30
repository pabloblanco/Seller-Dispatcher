<div class="row">
  <div class="col-12 px-3 pb-3">
    <hr class="mb-4 mt-4"/>
    <label>
      Detalles de conexión
    </label>
  </div>
  <div class="col-12 col-md-6 px-3">
    <label>
      OLT de fibra:
    </label>
    <p>
      {{$data->name_zone ?? 'S/I'}}
    </p>
  </div>
  <div class="col-12 col-md-6 px-3">
    @if($view)
      <label>
        Nodo de red:
      </label>
      @php
        $nameNode = json_decode(json_encode($data->config_conex));
        if(isset($nameNode->nodo_de_red_name) && !empty($nameNode->nodo_de_red_name)){
          $nameNode = $nameNode->nodo_de_red_name.' ('.$nameNode->nodo_de_red.')';
        }else{
          $nameNode = "Por establecer...";
        }
      @endphp
      <p>
        {{$nameNode}}
      </p>
    @else
      @include('fiber.Select_nodosRed', ['data'=> $data, 'NodoRed' => $NodoRed])
    @endif
  </div>
</div>

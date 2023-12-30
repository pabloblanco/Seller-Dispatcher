@php
  $status = [
    'P' => 'success',
    'AS' => 'default',
    'E' => 'danger'
  ];
@endphp

@foreach($detail as $art)
  @if(!empty($status[$art->status]))
    <div class="card card-outline-{{$status[$art->status]}} text-dark m-b-10">
      <div class="card-block">
        <div class="col-md-12">
          <p><strong>msisdn:</strong> {{$art->msisdn ?? 'No se consiguio el msisdn'}} </p>
          <p><strong>art&iacute;culo:</strong> {{$art->title ?? 'No se consiguio el artículo'}} </p>
          <p><strong>SKU:</strong> {{$art->sku ?? 'No se consiguio el sku'}} </p>

          @if($art->status == 'P')
            <input class="all-inv-box" type="hidden" value="{{$art->id}}">
            <div class="error-inv-content">
              <p><strong>En caso de error seleccione una opci&oacute;n</strong></p>
              <select class="form-control opt-error" id="opt-error-{{$art->id}}" data-detail="{{$art->id}}">
                <option selected="true" value="">Sin error</option>
                <option value="np">Art&iacute;culo incorrecto</option>
                <option value="nf">No viene el art&iacute;culo</option>
                <option value="ot">Otro</option>
              </select>

              <div id="error-txt-{{$art->id}}" hidden="true">
                <p class="p-t-10"><strong>Por favor describa el error.</strong></p>
                <textarea class="form-control" id="txt-opt-error-{{$art->id}}" rows="3" maxlength="100"></textarea>
              </div>
            </div>
          @else
            @if($art->status == 'E')
              <p><strong>No se puede asignar por: </strong> {{$art->comment ?? '' }} </p>
            @else
              <p><strong>El art&iacute;culo ya fue asignado</strong> </p>
            @endif
          @endif
        </div>
      </div>
    </div>
  @endif
@endforeach
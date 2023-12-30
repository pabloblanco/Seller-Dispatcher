<div class="row justify-content-start">
  <div class="col-12 px-3 pb-3">
    <label>
      Datos del Cliente
    </label>
  </div>
  <div class="col-md-6 col-12 px-3">
    <label>
      ID cliente:
    </label>
    <p>
      {{$data->clients_dni}}
    </p>
  </div>
  <div class="col-md-6 col-12 px-3">
    <label>
      Nombre / Apellido:
    </label>
    <p>
      {{$data->name}} {{$data->last_name ?? ''}}
    </p>
  </div>
  <div class="col-md-6 col-12 px-3">
    <label>
      Email:
    </label>
    <p>
      {{$data->email ?? 'S/I'}}
    </p>
  </div>
  <div class="col-md-6 col-12 px-3">
    <label>
      Teléfono:
    </label>
    <p>
      {{$data->phone_home ?? 'S/I'}}
    </p>
  </div>
  <div class="col-md-6 col-12 px-3">
    <label>
      Teléfono 2:
    </label>
    <p>
      {{$data->phone ?? 'S/I'}}
    </p>
  </div>
  @if($data->is_payment_forcer == 'Y')
  <div class="col-12 col-md-12 px-3 aling-items-center">
    <label class="mr-3">
      Identificación {{ $data->doc_type ? '('.$data->doc_type.')' : '' }}: {{ $data->doc_id ?? 'S/I' }}
    </label>
    <button type="button" class="btn btn-info" data-toggle="collapse" data-target="#id_collapse" aria-expanded="false" aria-controls="id_collapse" title="Ver Documento"><i class="fa fa-eye"></i></button>
  </div>
  <div id="id_collapse" class="collapse">
      @if($data->photo_front)
        <div class="card">
          <center>
            <img alt="image" class="img-responsive img-rounded" src="{{ $data->photo_front }}" width="100%">
          </center>
        </div>
      @else
      <div class="alert alert-light">
        <center>
          <h2 class="text-light"><i class="fa fa-exclamation-triangle"></i> La imagen no se encuentra Disponible</h2>
        </center>
      </div>
      @endif
    </div>
  @endif
</div>

<form class="form-horizontal" id="form-paid-install" data-toggle="validator" data-id="{{$data->id}}">
  <div class="col-sm-12 col-md-6">
    <label>Cliente:</label>
    <p>{{$data->name}} {{$data->last_name ?? ''}}</p>
  </div>

  <div class="col-sm-12 col-md-6">
    <label>Tel&eacute;fono:</label>
    <p>{{$data->phone_home}}</p>
  </div>

  <div class="col-sm-12 col-md-6">
    <label>Tel&eacute;fono 2:</label>
    <p>{{$data->phone ?? 'S/I'}}</p>
  </div>

  <div class="col-sm-12 col-md-6">
    <label>Email:</label>
    <p>{{$data->email ?? 'S/I'}}</p>
  </div>

  <div class="col-sm-12 col-md-12">
    <label>Direcci&oacute;n:</label>
    <p>{{$data->address_instalation}}</p>
  </div>

  <div class="col-sm-12 col-md-12">
    <label>Foto referencia:</label>
    @if(!empty($data->photo))
    <img src="{{$data->photo}}" alt="image" class="img-responsive img-rounded" width="100%">
    @else
    <p>S/I</p>
    @endif
  </div>

  <div class="col-sm-12 col-md-12 p-t-10">
    <label>Fecha de instalaci&oacute;n:</label>
    <p> {{date('d-m-Y H:i', strtotime($data->date_install))}} </p>
  </div>

  <div class="col-sm-12 col-md-12 p-t-10">
    <label>Instalador:</label>
    <p> {{$data->name_inst}} {{$data->last_name_inst ?? ''}} </p>
  </div>

  <div class="col-sm-12 col-md-6">
    <label>Plan:</label>
    <p>{{$data->pack}}</p>
  </div>

  <div class="col-sm-12 col-md-6">
    <label>Servicio:</label>
    <p>{{$data->service}}</p>
  </div>

  <div class="col-sm-12 col-md-6">
    <label>MSISDN:</label>
    <p>{{$data->msisdn}}</p>
  </div>

  <div class="col-sm-12 col-md-6">
    <label>Precio:</label>
    <p>${{number_format($data->price,2,'.',',')}}</p>
  </div>
</form>

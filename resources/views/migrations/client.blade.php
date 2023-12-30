<h3 class="box-title pt-4">Datos del cliente</h3>
<div class="alert alert-warning">
    <p style="font-size: 15px;">
        Por favor, asegurarse que los datos que se muestran a continuación, corresponden con los del cliente que quiere hacer la migración. <b>El proceso de migraci&oacute;n no tiene reverso</b>.
    </p>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>Nombre</label>
            <p id="name-c">
                {{ $client->name }} {{ $client->last_name }}
            </p>
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label>INE</label>
            <p id="dni-c">
                {{ $client->dni }}
            </p>
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label>Tel&eacute;fono</label>
            <p>
                {{ $client->phone_home }}
            </p>
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label>Tel&eacute;fono 2</label>
            <p>
                {{ !empty($client->phone) ? $client->phone : 'S/I' }}
            </p>
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label>Email</label>
            <p>
                {{ !empty($client->email) ? $client->email : 'S/I' }}
            </p>
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label>Direcci&oacute;n</label>
            <p>
                {{ !empty($client->address) ? $client->address : 'S/I' }}
            </p>
        </div>
    </div>

    <div class="col-md-12">
        <button type="button" class="btn btn-success waves-effect waves-light m-r-10" data-toggle="modal" data-target="#edit-modal">Editar datos</button>
    </div>
</div>

{{-- Modal de edición --}}
<div id="edit-modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="edit-modal" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title">Editar datos del cliente.</h4>
            </div>
            <form class="form-horizontal" id="editclientformodal" name="editclientformodal" method="POST" action="" data-toggle="validator">
                {{ csrf_field() }}
                <div class="modal-body">
                    <input type="hidden" name="dni" id="dni" value="{{ $client->dni }}">
                    <div class="form-group">
                        <label class="col-md-12">Nombre</label>
                        <div class="col-md-12">
                            <input type="text" class="form-control" id="name" name="name" value="{{ $client->name }}" required>
                            <div class="help-block with-errors"></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-12">Apellido</label>
                        <div class="col-md-12">
                            <input type="text" class="form-control" id="last_name" name="last_name" placeholder="Apellido del prospecto" value="{{ $client->last_name }}" required>
                            <div class="help-block with-errors"></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-12">Telefono (1)</label>
                        <div class="col-md-12 p-t-0 help-block">10 d&iacute;gitos num&eacute;ricos</div>
                        <div class="col-md-12">
                            <input class="form-control" id="phone" name="phone" type="text" minlength="10" maxlength="10" pattern="^[0-9]{10}$" value="{{ $client->phone_home }}" required>
                            <div class="help-block with-errors"></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-12">Telefono (2)</label>
                        <div class="col-md-12 p-t-0 help-block">10 d&iacute;gitos num&eacute;ricos</div>
                        <div class="col-md-12">
                            <input class="form-control" id="phone2" name="phone2" type="text" minlength="10" maxlength="10" pattern="^[0-9]{10}$" value="{{ !empty($client->phone) ? $client->phone : '' }}">
                            <div class="help-block with-errors"></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-12">Email</label>
                        <div class="col-md-12">
                            <input class="form-control" id="email" name="email" type="email" placeholder="correo@servidor.com" pattern="(([a-z]|[0-9]|[._-]))+@([a-z]|[0-9])+\.[a-z]+" data-error="Dirección de email no válida" value="{{ !empty($client->email) ? $client->email : '' }}">
                            <div class="help-block with-errors"></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-12">Direcci&oacute;n</label>
                        <div class="col-md-12">
                            <input type="text" class="form-control" id="address" name="address" value="{{ !empty($client->address) ? $client->address : '' }}">
                            <div class="help-block with-errors"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default waves-effect" id="closeEditM" data-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-success waves-effect waves-light m-r-10">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>
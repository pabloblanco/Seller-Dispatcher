@if(!empty($bankUser))
<h5 class="p-b-10"> Usuario: <b> {{$bankUser->name}} {{$bankUser->last_name}} </b> </h5>
<h5 class="p-b-10"> Código: <b> {{$bankUser->id_deposit}} </b> </h5>
@endif
<div class="col-md-12">
    @foreach($deposits as $deposit)
        <div class="card card-outline-primary text-center text-dark m-b-10">
            <div class="card-block">
                <div class="row">
                    <div class="col-md-12 pull-left">
                        <label class="pull-left">
                            Banco: <span> {{!empty($deposit->name) ? $deposit->name : 'Otro' }} </span>
                        </label>
                    </div>
                    <div class="col-md-12 pull-left">
                        <label class="pull-left">
                            Cod. Dep&oacute;sito: <span> {{$deposit->id}} </span>
                        </label>
                    </div>
                    <div class="col-md-12 pull-left">
                        <label class="pull-left">
                            Monto: <span> ${{number_format($deposit->amount,2,'.',',')}} </span>
                        </label>
                    </div>
                    <div class="col-md-12">
                        <label class="pull-left">
                            Fecha dep&oacute;sito: <span> {{$deposit->date_dep}} </span>
                        </label>
                    </div>

                    <div class="col-md-12">
                        <label class="pull-left">
                            Fecha recepci&oacute;n: <span> {{$deposit->date_reg}} </span>
                        </label>
                    </div>

                    @if(empty($isDelete) || !$isDelete)
                    <div class="col-md-12">
                        <label class="pull-left">
                            Estatus: <span> @if($deposit->status == 'P') Sin conciliar @else Conciliado @endif </span>
                        </label>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    @endforeach

    {{-- @if(!empty($isDelete) && $isDelete)
        <div class="col-md-12 text-center">
            <button type="button" class="btn btn-danger btn-md" id="deleteDep">Eliminar</button>
        </div>
    @endif --}}
</div>
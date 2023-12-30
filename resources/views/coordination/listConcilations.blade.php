@if($data->count())
    @foreach($data as $conc)
    <div class="card card-outline-danger text-dark m-b-10">
        <div class="card-block">
            <div class="col-md-12">
                <h3 class="box-title">Conciliaci&oacute;n - {{ $conc->id }}</h3>
                <ul class="list-icons">
                    <li>
                        <i class="ti-angle-right"></i> 
                        <strong>Monto:</strong> 
                        <span>{{ '$'.number_format($conc->amount,2,'.',',') }}</span>
                    </li>
                    <li>
                        <i class="ti-angle-right"></i> 
                        <strong>Banco:</strong> 
                        <span>
                            {{ !empty($conc->bank) ? $conc->bank : 'Otro' }}
                        </span>
                    </li>
                    <li>
                        <i class="ti-angle-right"></i> 
                        <strong>Operario:</strong> 
                        <span>
                            {{ $conc->ope_name }} {{ $conc->ope_last_name }}
                        </span>
                    </li>
                    <li>
                        <i class="ti-angle-right"></i> 
                        <strong>Cod. Dep&oacute;sito:</strong> 
                        <span>{{ $conc->id_deposit }}</span>
                    </li>
                    <li>
                        <i class="ti-angle-right"></i> 
                        <strong>Fecha:</strong> 
                        <span>{{ $conc->date_process }}</span>
                    </li>
                    <li>
                        <i class="ti-angle-right"></i> 
                        <strong>Motivo:</strong> 
                        <span>{{ !empty($conc->reason_deposit) ? $conc->reason_deposit : 'N/A' }}</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    @endforeach
@else
    <div class="row">
        <div class="alert alert-danger">
            <p>No hay conciliaciones registradas.</p>
        </div>
    </div>
@endif
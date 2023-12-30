@php
    $types = [
        'H' => 'Internet Hogar',
        'T' => 'Telefonía Celular',
        'M' => 'MIFI',
        'F' => 'Fibra'
    ]
@endphp

@if(!empty($stock) && count($stock))
    @foreach($stock as $item)
        @if(!empty($item->msisdn))
        <blockquote class="{{ $item->msisdn }}  {{ $item->artic_type }}">
            <label>Equipo:</label>
            <p>{{ $item->title }}</p>

            <label>Tipo:</label>
            <p>{{ $types[$item->artic_type] ?? 'Otro' }}</p>

            <label>MSISDN:</label>
            <p>{{ $item->msisdn }}</p>

            @if($item->artic_type == 'H')
                <label>IMEI:</label>
                <p>{{ $item->imei }}</p>
            @endif

            @if(!empty($item->preassigned))
                @if($item->preassigned == 'Y')
                    <label>Preasignado:</label>
                    <p>Si</p>
                @endif
            @endif

            <div class="text-center">
                <button type="button" class="btn btn-danger waves-effect waves-light return-inv" data-seller="{{ $item->users_email }}" data-num="{{ $item->msisdn }}" data-pasg="{{ $item->preassigned  }}">
                    Retornar
                </button>
            </div>
        </blockquote>
        @endif
    @endforeach
@endif
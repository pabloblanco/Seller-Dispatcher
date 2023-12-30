@php
  $types = [
    'H' => 'Internet Hogar',
    'T' => 'Telefonía Celular',
    'M' => 'MIFI',
    'MH' => 'MIFI Huella',
    'F' => 'Fibra'
  ]
@endphp
<table class="table table-striped display nowrap">
  <thead>
    <tr>
      <th class="text-center align-middle">
        #
      </th>
      <th class="text-center align-middle">
        Equipo
      </th>
      <th class="text-center align-middle">
        Tipo
      </th>
      <th class="text-center align-middle">
        MSISDN
      </th>
      @if(isset($viewMount))
      <th class="text-center align-middle">
        Monto
      </th>
      @endif
      <th class="text-center align-middle">
        Fecha que se registro
      </th>
    </tr>
  </thead>
  <tbody>
    @php $i=0; @endphp
    @foreach($stock as $item)
        @php $i++; @endphp
    <tr class="item">
      <td>
        {{$i}}
      </td>
      <td>
        {{ $item->title }}
      </td>
      <td>
        {{ $types[$item->artic_type] ?? 'Otro' }}
      </td>
      <td>
        {{ $item->msisdn }}
      </td>
      @if(isset($item->amount))
      <td>
        {{ $item->amount }}
      </td>
      @endif
      <td>
        {{ $item->date_reg }}
      </td>
    </tr>
    @endforeach
  </tbody>
</table>

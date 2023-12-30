@php
$types = [
  'H' => 'Internet Hogar',
  'T' => 'Telefonía',
  'M' => 'Mifi',
  'F' => 'Fibra'
]
@endphp

@if(count($dns)>0)
<table class="table table-bordered table-dn-noty" id="OrangeRed" name="OrangeRed">
  <thead>
    <tr>
      <th>
        MSISDN
      </th>
      @if(session('user_type') != 'vendor')
      <th>
        Usuario
      </th>
      <th>
        Origen
      </th>
      @endif
      <th>
        notificación
      </th>
      <th>
        Tipo
      </th>
      <th class="hidden-xs">
        Artículo
      </th>
      <th>
        Fecha de notificación
      </th>
      <th>
        Cambio de status
      </th>
    </tr>
  </thead>
  <tbody>
    @foreach($dns as $dn)
    <tr class="item">
      <td>
        {{ $dn->msisdn }}
      </td>
      @if(session('user_type') != 'vendor')
      <td>
        {{ $dn->name }} {{ $dn->last_name ?? '' }}
      </td>
      <td>
        @if(!empty($dn->origin)) {{$dn->origin}} @else   @endif
      </td>
      @endif
      <td class="noty @if(!empty($dn->date_red)) red-noty @else org-noty @endif">
        {{ !empty($dn->date_red) ?  'Roja' : 'Naranja' }}
      </td>
      <td>
        {{ !empty($types[$dn->artic_type]) ? $types[$dn->artic_type] : 'Otro' }}
      </td>
      <td class="hidden-xs">
        {{ $dn->title }}
      </td>
      <td>
        {{!empty($dn->date_red) ? date('d-m-Y', strtotime($dn->date_red)) : date('d-m-Y', strtotime($dn->date_orange))}}
      </td>
      <td>
        @php
        $isHability=false;
        if(!empty($dn->ChangeStatus)){
          if($dn->ChangeStatus=='C' || $dn->ChangeStatus=='R'){
            $isHability=true;
          }
        }
        @endphp

        @if($isHability)
        <button class="btn btn-success waves-effect" id="doChangeStatus" name="doChangeStatus" onclick="viewStatus('{{ $dn->inv_arti_details_id }}')" title="Ver detalles de solicitud de cambio de status" type="button">
          Ver detalles
        </button>
        @else
        <button class="btn btn-light" disabled="" id="doChangeStatus" name="doChangeStatus" title="No existen detalles disponibles de cambio de status" type="button">
          Ver detalles
        </button>
        @endif
      </td>
    </tr>
    @endforeach
  </tbody>
</table>
@else
<div class="row justify-content-center">
  <div class="alert alert-danger">
    <p>
      Lo sentimos, no hay registros.
    </p>
  </div>
</div>
@endif

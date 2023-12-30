@php
$status = [
  'R' => 'Solicitada',
  'P' => 'En Proceso',
  'D' => 'Rechazada'
];
@endphp


@if($userslow->count())
      <div id="sales-list" class="row">
        <div class="table-responsive" id="list-article">
          <table class="mt-5 table table-bordered table-dn-noty">
            <thead>
              <tr>
                <th>Vendedor</th>
                <th>Motivo de Baja</th>
                <th>Fecha de Solicitud</th>
                <th>Estatus</th>
                <th>Motivo de Rechazo</th>
                <th>Fecha de Aceptación o Rechazo</th>
              </tr>
            </thead>
            <tbody>
              @foreach($userslow as $usrl)
              <tr class="item">
                <td>
                  {{$usrl->name}} {{$usrl->last_name}} ({{ $usrl->user_dismissal }})
                </td>

                <td>
                  {{ $usrl->reason }}
                </td>

                <td>
                  {{ date('d-m-Y', strtotime($usrl->date_reg)) }}
                </td>

                <td>
                  {{ !empty($status[$usrl->status]) ? $status[$usrl->status] : 'Otro' }}
                </td>

                <td>
                  {{ !empty($usrl->reason_deny) ? $usrl->reason_deny : 'N/A' }}
                </td>

                <td>
                  {{ !empty($usrl->date_step1) ? date('d-m-Y', strtotime($usrl->date_step1)) : 'N/A' }}

                </td>

              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
      @else
      <div class="row">
        <div class="alert alert-danger" id="list-empty">
          <p>No se encontraron solicitudes de baja</p>
        </div>
      </div>
      @endif

<div class="table-responsive">
  <table class="table table-bordered">
    <thead>
      <tr>
        <th class="text-center">
          Cliente
        </th>
        <th class="text-center">
          Dirección
        </th>
        <th class="text-center">
          Fecha de soporte
        </th>
        <th class="text-center">
          Horario de horario
        </th>
        <th class="text-center">
          Instalador
        </th>
        <th class="text-center">
          Ticket
        </th>
        <th class="text-center">
          DN
        </th>        
        <th class="text-center">
          Falla
        </th>         
        <th class="text-center">
          Ver
        </th>
      </tr>
    </thead>
    <tbody id="row-detail-content">
      @php
        $citas_hoy=0;
        $citas_vencidas=0;
      @endphp
      @foreach($dates as $date)

        @if(strtotime($date->date_support) < strtotime(date('d-m-Y')))
          @php $citas_vencidas++; @endphp
          <tr id="row-date-{{$date->id}}" style="border: 2px solid #ad7d7d; background-color: #fdbdbd;">

        @else
          @if(strtotime($date->date_support) == strtotime(date('d-m-Y')))
          @php $citas_hoy++; @endphp
            <tr id="row-date-{{$date->id}}" style="border: 2px solid red; background-color: antiquewhite;">
          @else
            <tr id="row-date-{{$date->id}}">
          @endif
        @endif
        <td>
          {{$date->name}} {{$date->last_name ?? ''}}
        </td>
        <td>
          {{$date->address}}
        </td>
        <td>
          {{date('d-m-Y', strtotime($date->date_support))}}
        </td>
        <td>
          {{$date->schedule}}
        </td>
        <td>
          @if(!empty($date->user_installer))
            {{$date->user_installer}}
          @else
            Por establecer...
          @endif
        </td>
        <td>
          {{$date->ticket}}
        </td>
        <td>
          {{$date->msisdn}}
        </td>
        <td>
          {{$date->description_fail_support}}
        </td>
        <td>
          <button class="btn btn-success waves-effect waves-light m-r-10" data-id="{{$date->id}}" data-type="{{$type}}" data-target="#detail-support-modal" data-toggle="modal" type="button">
            Ver detalle
          </button>
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>
</div>
<script src="{{ asset('plugins/bower_components/jquery/dist/jquery.min.js') }}"></script>
<script type="text/javascript">
    switch('{{$type}}'){
      case "installer":
        /*Instalaciones por realizar.*/
        $('#cant_instalation_hoy').text('{{$citas_hoy}}');
        //$('#cant_instalation_vencida').text('{{$citas_vencidas}}');
        break;
      case "installerAgenda":
        /*citas confirmadas por asignar instalador.*/
        $('#cant_citas_hoy').text('{{$citas_hoy}}');
        //$('#cant_citas_vencida').text('{{$citas_vencidas}}');
        break;
      case "installerAsigne":
        /*Citas asignadas a instaladores.*/
        $('#cant_asignaciones_hoy').text('{{$citas_hoy}}');
        //$('#cant_asignaciones_vencida').text('{{$citas_vencidas}}');
        break;
      /*default:
        console.log('Opcion '+option+' no definida');*/
      }

</script>

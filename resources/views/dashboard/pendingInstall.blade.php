@foreach($dates as $date)
    <tr id="row-date-{{$date->id}}">
        <td>
            {{$date->name}} {{$date->last_name ?? ''}}
        </td>
        <td>
            @if( $date->daysElapsed )
                {{ $date->daysElapsed }} días 
            @else
                0 dias
            @endif
        </td>
        <td>
            {{$date->address_instalation}}
        </td>
        <td>
            {{ $date->municipality }}
        </td>
        <td>
            {{ $date->zoneName }}
        </td>
        <td>
        {{ $date->num_rescheduling }}
        </td>
        <td>
            {{$date->date_instalation}}
            <br>
            {{$date->schedule}}
        </td>
    </tr>
@endforeach
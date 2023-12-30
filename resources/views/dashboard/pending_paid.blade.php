@foreach($instalations as $install)
    <tr id="row-date-paid-{{$install->id}}">
        <td>
            {{$install->name}} {{$install->last_name ?? ''}}
        </td>
        <td>
            {{$install->phone_home}}
        </td>
        <td>
            {{$install->address_instalation}}
        </td>
        <td>
            {{date('d-m-Y H:i', strtotime($install->date_install))}}
        </td>
        <td>
            <button type="button" data-toggle="modal" data-id="{{$install->id}}" data-target="#detail-pending-pay-modal" class="btn btn-success waves-effect waves-light m-r-10">Ver detalle</button>
        </td>
    </tr>
@endforeach
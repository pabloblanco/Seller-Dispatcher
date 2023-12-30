@if(!$salesDetail || !count($salesDetail))
    <p>No hay ventas registradas para la fecha consultada.</p>
@else
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>DN</th>
                    <th>Cliente</th>
                </tr>
            </thead>
            <tbody id="detail-sales">
                @foreach($salesDetail as $detail)
                    <tr>
                        <td>
                            {{$detail->msisdn}}
                        </td>
                        <td>
                            {{$detail->name}} {{$detail->last_name}}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
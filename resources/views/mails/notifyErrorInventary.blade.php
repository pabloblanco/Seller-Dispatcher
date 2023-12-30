@extends('layouts.mail')

@section('content')
<tr>
  <td>
    <table bgcolor="#fff" border="0" cellpadding="0" cellspacing="0" width="100%">
      <tr>
        <td align="center" valign="top">
          <h1 style="color: #000;">
            Notificación de error en inventario
          </h1>
        </td>
      </tr>
      <tr>
        <td valign="top">
          <h2>
            Los siguientes msisdns fueron reportados con error o no se pudieron asignar.
          </h2>
          <p>
            Usuario al que se iban a asignar:
            <b>
              {{$dataDelivery['name']}}
            </b>
            email:
            <b>
              {{$dataDelivery['usuer']}}
            </b>
          </p>
          <p>
            Folio:
            <b>
              {{$dataDelivery['folio']}}
            </b>
          </p>
          <p>
            Caja:
            <b>
              {{$dataDelivery['box']}}
            </b>
          </p>
          <ul>
            @foreach($data as $err)
            <li>
              <p>
                MSISDN:
                <b>
                  {{$err['msisdn']}}
                </b>
              </p>
              <p>
                SKU:
                <b>
                  {{$err['sku']}}
                </b>
              </p>
              <p>
                Error:
                <b>
                  {{$err['error']}}
                </b>
              </p>
            </li>
            @endforeach
          </ul>
        </td>
      </tr>
    </table>
  </td>
</tr>
@stop

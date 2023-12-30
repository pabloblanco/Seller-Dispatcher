@php
    $types = [
        'H' => 'Internet Hogar',
        'T' => 'Telefonía',
        'M' => 'Mifi',
        'F' => 'Fibra'
    ]
@endphp
@foreach($articles as $article)
    <tr style="@if(!empty($article->color)) background: {{$article->color}}; color:#FFF; @endif">
        <td class="text-nowrap">
            {{$i + $loop->index + 1}}
        </td>
        <td>
            {{$article->title}} </br> 
            ({{ $types[$type]}})
        </td>
        <td>
            {{$article->msisdn}}
        </td>
        <td>
            {{$type == 'T' ? $article->iccid : 'N/A'}}
        </td>
        <td>
            {{$article->imei}}
        </td>
        <td>
            {{date('d-m-Y', strtotime($article->date_reg))}}
        </td>
    </tr>
@endforeach
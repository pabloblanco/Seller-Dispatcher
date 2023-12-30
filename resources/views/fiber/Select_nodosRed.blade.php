<label class="col-md-12">
  Nodo de red:
</label>
@if(count($NodoRed))
<select class="form-control" id="nodo_red" name="nodo_red" placeholder="Seleccione un nodo" required="">
  {{--<option value="">
    Seleccione un nodo de red
  </option>
  @foreach($NodoRed as $nodo)
  <option value="{{ $nodo->node }}">
    {{ $nodo->name_origin.'('.$nodo->node.') - '.$nodo->name }}
  </option>
  @endforeach--}}
</select>
<div class="help-block with-errors" id="error-nodo">
</div>
@else
<div class="alert alert-danger">
  <p>
    Falló el listado de nodos de red de la OLT seleccionada, puede deberse que no tenga nodos configurados.
  </p>
</div>
@endif

@section('scriptJS2')
<script defer type="text/javascript">
  @if(count($NodoRed))

    $(function () {

     let myArray = new Array();
      @foreach($NodoRed as $nodo)
        var obj = {id: {{$nodo->node}}, name_origin: '{{$nodo->name_origin}}', name_net: '{{$nodo->name}}' };
        myArray.push(obj);
        // console.log('obj> '+obj.id+' '+obj.name_origin+' '+obj.name_net);
      @endforeach

    $('#nodo_red').selectize({
      maxItems: 1,
      valueField: 'id',
      labelField: 'name_net',
      searchField: ['name_origin','name_net'],
      options: myArray,
      create: false,
      render: {
        item: function (item, escape) {

          opt = "<div>";
          opt += '<span>' + escape(item.name_net.toLocaleUpperCase()) + " ("+escape(item.id)+")</span></br>";
          opt += "</div>";
          return opt;
        },
        option: function(item, escape) {

          opt = "<div>";
          opt += '<span style="color:#666; opacity:0.75; font-weight:600;">Nombre en netwey:</span><span> ' + escape(item.name_net.toLocaleUpperCase()) + "</span></br>";
          opt += '<span class="aai_description mb-0" style="color:#666; opacity:0.75; font-weight:600;"> Nombre en 815: </span><span>' + escape(item.name_origin) +"</span></br>";
          opt += '<span class="aai_description mb-0" style="color:#666; opacity:0.85; font-weight:700;"> Nodo: </span><span>' + escape(item.id) +"</span></br>";
          opt += "</div>";
          return opt;
        }
      },
    });

      @if(count($NodoRed)==1)
        $('#nodo_red').val({{$NodoRed[0]->node}});
        $('#nodo_red').data('selectize').setValue({{$NodoRed[0]->node}});
      @endif

      $('#nodo_red').change(function (e) {
       // e.preventDefault();
        if($('#nodo_red').val()!==''){
          $('#error-nodo').text('');
          $('#error-nodo').removeClass('alert').removeClass('alert-danger');
        }
      });

      @php
        $nameNode = json_decode(json_encode($data->config_conex));
        if(!empty($nameNode)){
          if(isset($nameNode->nodo_de_red) && !empty($nameNode->nodo_de_red)){
          @endphp
            $('#nodo_red').val({{$nameNode->nodo_de_red}});
            $('#nodo_red').data('selectize').setValue({{$nameNode->nodo_de_red}});
          @php
          }
        }
      @endphp
    });
  @endif
</script>
@stop

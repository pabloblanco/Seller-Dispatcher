<div class="col-md-8 col-sm-12">
  <label class="col-md-12 ">
    Linea telefonica de reemplazo:
  </label>
  <input autocomplete="off" class="form-control" id="dn_new" maxlength="10" minlength="10" name="dn_new" pattern="^([0-9]{10})$" placeholder="Escribe el MSISDN de reemplazo" required=""/>
  <div class="help-block with-errors" id="error-dn_new"></div>
</div>

<script type="text/javascript">
  $(function () {
    $('#error-dn_new').text('');
    $('#error-dn_new').removeClass('alert').removeClass('alert-danger');
    $('#dn_new').selectize({
        maxItems: 1,
        valueField: 'id',
        searchField: ['msisdn'],
        labelField: 'msisdn',
        render: {
          item: function (item, escape) {
            $("#txt-dn_transt").text(item.msisdn);
            $("#block_dnTrans").attr('hidden', true);
            if($('#typePort').is(':checked')){
              $("#block_dnTrans").attr('hidden', null);
            }
            opt = "<div>";
            opt += "<span>" + escape(item.msisdn) + "</span></br>";
            opt += "</div>";
            return opt;
          },
          option: function(item, escape) {

            let tipoArt="";
            switch (item.artic_type) {
              case 'T':
                tipoArt="Telefonia"
                break;
              case 'H':
                tipoArt="Hogar"
                break;
              case 'M':
                tipoArt="Mifi"
                break;
              case 'MH':
                tipoArt="Mifi Huella"
                break;
              case 'F':
                tipoArt="Fibra"
                break;
              default:
                tipoArt="No definido";
                break;
            }
            opt = "<div class='row'>";
            opt += '<div class="col-12"><span style="color:#666; opacity:0.75; font-weight:600;"> Msisdn: </span><span> ' + escape(item.msisdn) + '</span></div>';
            opt += '<div class="col-12"><span class="aai_description mb-0" style="color:#666; opacity:0.75; font-weight:600;"> Tipo de producto: </span><span>' + escape(tipoArt) + '</span></div>';
            opt += '<div class="col-12"><span class="aai_description mb-0" style="color:#666; opacity:0.85; font-weight:700;"> Articulo: </span><span>' + escape(item.product_name) + '</span></div>';
            opt += "</div>";
            return opt;
          }
        },
        load: function(query, callback) {
          //if (!query.length) return callback();
          //Debe escribir 7 digitos para que termine de autocompletar el Msisdn que entrega el instalador
          if (query.length<7) return callback();

          doPostAjax(
            '{{ route('sellerFiber.findInventoryAsigned') }}',
            function(res){
              if(!res.error){
                callback(res);
              }else{
                callback();
              }
            },
            {
              search: query,
              type: '{{$dn_type}}'
            },
            $('meta[name="csrf-token"]').attr('content')
          );
        }
      });
  });
</script>

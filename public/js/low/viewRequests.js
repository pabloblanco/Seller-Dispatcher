var BASE_URL = $('meta[name="base-url"]').attr('content');
var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
//$(function() {
$(document).ready(function() {
  /*Busca los usuarios asociados*/
  $('#list-users').selectize({
    valueField: 'email',
    searchField: 'name',
    labelField: 'name',
    render: {
      option: function(item, escape) {
        return '<p>' + escape(item.name_profile) + ': ' + escape(item.name) + ' ' + escape(item.last_name) + '</p>';
      }
    },
    load: function(query, callback) {
      if (!query.length) return callback();
      $.ajax({
        headers: {
          'X-CSRF-TOKEN': CSRF_TOKEN
        },
        // url: '{{ route('findRelationUsers ') }}',
        url: BASE_URL + 'find-relation-users',
        type: 'POST',
        dataType: 'json',
        data: {
          q: query
        },
        error: function() {
          callback();
        },
        success: function(res) {
          if (!res.error) {
            callback(res.users);
          } else {
            callback();
          }
        }
      });
    }
  });
  /*END Busca los usuarios asociados*/

  $('#status').selectize();


  $('#do-search').on('click', function(e){

      let data = new FormData();
        data.append('status', $('#status').val().trim());
        data.append('vendor', $('#list-users').val().trim());
        $('.loading-ajax').fadeIn();
        doPostAjaxForm(
          BASE_URL + 'low/get-requests-list',
          function(res){
            $('.loading-ajax').fadeOut();
            res = JSON.parse(res);

            if(res.success){
              $('#list-request').html(res.html);
            }else{
              swal({
                title: "No se pudo procesar la solicitud",
                text: res.msg,
                icon: "error",
                button: {
                  text: "OK"
                }
              });
            }
          },
          data,
          CSRF_TOKEN
        );

  });

  if($('#list-empty').length > 0){
    $('#filters-container').hide();
  }
  else{
    $('#filters-container').show();
  }
});
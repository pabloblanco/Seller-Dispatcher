$(function() {
  //configurando idioma para el calendario
  if ($.fn.datepicker && $.fn.datepicker.dates) {
    $.fn.datepicker.dates['es'] = {
      days: ["Domingo", "Lunes", "Martes", "Miercoles", "Jueves", "Viernes", "Sabado"],
      daysShort: ["Dom", "Lun", "Mar", "Mie", "Jue", "Vie", "Sab"],
      daysMin: ["Do", "Lu", "Ma", "Mi", "Ju", "Vi", "Sa"],
      months: ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"],
      monthsShort: ["Ene", "Feb", "Mar", "Abr", "May", "Jun", "Jul", "Ago", "Sep", "Oct", "Nov", "Dec"],
      today: "Hoy",
      clear: "Borrar",
      format: "dd/mm/yyyy",
      titleFormat: "MM yyyy",
      /* Leverages same syntax as 'format' */
      weekStart: 0
    };
  }
  $('.activate-loader').on('click', function(event) {
    $(".preloader").fadeIn();
  });
  $('.closed').on('click', function(e) {
    $('.myadmin-alert').hide();
  });
  //Muestra mensaje 
  showMessageAjax = function(type, messaje) {
    $('#msgAjax').removeClass('alert-info').removeClass('alert-primary').removeClass('alert-danger').removeClass('alert-warning').removeClass('alert-dark').removeClass('alert-success').removeClass('alert-inverse').removeClass('alert-custom');
    $('#txtMsg').text(messaje);
    $('#msgAjax').addClass(type).show();
    setTimeout(function() {
      $('#msgAjax').removeClass(type).hide(300);
      $('#txtMsg').text('');
    }, 6000);
  }
  //Ejecuta un llamado ajax
  doPostAjax = function(url, callback, data = false, token = false) {
    let ajax = {
      async: true,
      url: url,
      method: 'POST',
      dataType: 'json',
      success: callback,
      error: function(e) {
        if (swal) {
          swal.close();
        }
        if ($(".preloader").is(':visible')) {
          $(".preloader").fadeOut();
        }
        if ($(".loading-ajax").is(':visible')) {
          $('.loading-ajax').fadeOut();
        }
        showMessageAjax('alert-danger', 'Fallo la consulta.')
      }
    }
    if (data) {
      ajax.data = data
    }
    if (token) {
      ajax.headers = {
        'X-CSRF-TOKEN': token
      };
    }
    $.ajax(ajax);
  }
  doPostAjaxForm = function(url, callback, data = false, token = false) {
    let ajax = {
      async: true,
      url: url,
      method: 'POST',
      contentType: false,
      processData: false,
      cache: false,
      mimeType: "multipart/form-data",
      success: callback,
      error: function(e) {
        if ($(".preloader").is(':visible')) {
          $(".preloader").fadeOut();
        }
        if ($(".loading-ajax").is(':visible')) {
          $('.loading-ajax').fadeOut();
        }
        showMessageAjax('alert-danger', 'Fallo la consulta!')
      }
    }
    if (data) {
      ajax.data = data
    }
    if (token) {
      ajax.headers = {
        'X-CSRF-TOKEN': token
      };
    }
    $.ajax(ajax);
  }
  substringMatcher = function(strs) {
    return function findMatches(q, cb) {
      var matches, substringRegex;
      // an array that will be populated with substring matches
      matches = [];
      // regex used to determine if a string contains the substring `q`
      substrRegex = new RegExp(q, 'i');
      // iterate through the pool of strings and for any string that
      // contains the substring `q`, add it to the `matches` array
      $.each(strs, function(i, str) {
        if (substrRegex.test(str)) {
          matches.push(str);
        }
      });
      cb(matches);
    };
  };
});
formatNumber = {
  separador: ".",
  sepDecimal: ',',
  formatear: function(num) {
    num += '';
    let splitStr = num.split('.');
    let splitLeft = splitStr[0];
    let splitRight = splitStr.length > 1 ? this.sepDecimal + splitStr[1] : '';
    let regx = /(\d+)(\d{3})/;
    while (regx.test(splitLeft)) {
      splitLeft = splitLeft.replace(regx, '$1' + this.separador + '$2');
    }
    return this.simbol + splitLeft + splitRight;
  },
  new: function(num, simbol) {
    this.simbol = simbol || '';
    return this.formatear(num);
  }
};
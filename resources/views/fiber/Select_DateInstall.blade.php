<h3 class="box-title">
  Seleccionar fecha y hora de la instalación
</h3>
<div class="row justify-content-start">
  <div class="col-md-6 has-error">
    <label>
      Fecha de la instalación:
    </label>
    <div class="d-flex justify-content-center" id="calendar">
    </div>
    <input id="dateCalendar" name="dateCalendar" type="hidden" value=""/>
    <div class="help-block with-errors" id="error-calendar">
    </div>
  </div>
  <div class="col-md-6" id="blockClock">

  </div>
</div>
<script type="text/javascript">
  $(function () {

    function initClock(){
      $('.loading-ajax').show();
      doPostAjax(
          '{{ route('sellerFiber.getClock') }}',
          function(res){
            $('.loading-ajax').fadeOut();
            if(res.sucess){
              //$('#pay-content').attr('hidden', null);
              $('#blockClock').html(res.html);
            }else{
              showMessageAjax('alert-danger',res.msg);
            }
          },
          {
            date: $('#dateCalendar').val(),
            zone_id: $('#olt').val(),
            city: $('#city').val()
          },
          $('meta[name="csrf-token"]').attr('content')
      );
    }
      //Agenda
      //var disabledDates = ["10-12-2022","5-1-2023"];
      //[dia - mes - ano]
      var disabledDates = @json($BlockDay);

      $('#calendar').datepicker({
          firstDay: 1,
          language: 'es',
          todayHighlight: true,
          format: 'dd-mm-yyyy',
          startDate: '{{$starDate}}',
          endDate: '{{$endDate}}',
          beforeShowDay: function (in_date) {
              in_date = in_date.getDate() + '-' +
             (in_date.getMonth() + 1) + '-' +
              in_date.getFullYear();
                //var my_array = new Array('9-12-2019', '13-12-2019');
                if (disabledDates.indexOf(in_date) >= 0) {
                  return " disabled";
                } else {
                  return true;
                }
          }
          //daysOfWeekDisabled:[0,6]
      }).on('changeDate', function (selected) {
          var date = selected.date,
              month = ((date.getMonth() + 1) < 10) ? ('0' + (date.getMonth() + 1 )) : (date.getMonth() + 1 ),
              day = (date.getDate() < 10) ? ('0' + date.getDate()) : date.getDate(),
              fecha = date ? date.getFullYear() + '-' + month + '-' + day : '';

          $('#dateCalendar').val(fecha);
          $('#error-calendar').text('');
          initClock();
      });
    });
</script>

$(document).ready(function() {
  $('#filter_report').on("click", function(e) {
    e.preventDefault();
    let dateStart = new Date($('#date_start').val()),
      dateEnd = new Date($('#date_end').val()),
      diffTime = dateEnd.getTime() - dateStart.getTime(),
      difDays = diffTime / (1000 * 3600 * 24);
    if (diffTime < 0) {
      alert('Erro: Data de inico inferior a data de fim')
      return false
    }
    if (difDays > 40) {
      alert('Erro: Diferença de dias superior a 40 dias.')
      return false
    }
    $(this).closest('form').submit()
  })
  $('#datetime_start, #datetime_end').datetimepicker({
    format: 'YYYY-MM-DD',
    sideBySide: true,
    allowInputToggle: true,
    icons: {
      date: "fa fa-calendar-alt",
      time: "fa fa-clock",
    }
  });

});
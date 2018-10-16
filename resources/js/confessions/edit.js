const moment = require('moment');

require('daterangepicker');

const $scheduled = $('.scheduled-form-group');

const format = 'D MMM YYYY h:mm A';

$('select[name="status"]').on('change', evt => {
  if (['Featured', 'Approved'].indexOf($(evt.currentTarget).val()) !== -1) {
    $scheduled.removeClass('d-none');
  } else {
    $scheduled.addClass('d-none');
  }
});

$('.date-picker')
  .each((idx, picker) => {
    $picker = $(picker);

    const $label = $picker.children('.label');
    const value = $picker.children('input').val();

    if (value) {
      $label.html(moment.unix(value).format(format));
      $scheduled.removeClass('d-none');
    }
  })
  .daterangepicker({
    singleDatePicker: true,
    locale: { format },
    minDate: moment(),
    timePicker: true,
    timePickerIncrement: 30,
  })
  .on('apply.daterangepicker', (evt, picker) => {
    $picker = $(evt.currentTarget);

    $picker.children('.label').html(picker.startDate.format(format));
    $picker.children('input').val(picker.startDate.unix());
  });

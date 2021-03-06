$('.date-picker')
  .each((idx, picker) => {
    const $picker = $(picker);
    const $label = $picker.children('.label');

    const $start = $picker.children('input[name="start"]');
    const $end = $picker.children('input[name="end"]');

    const $clear = $picker.next('.clear');

    const start = $start.val();
    const end = $end.val();

    if (start && end) {
      $label.html(
        `${moment.unix(start).format('D MMM YYYY')} - ${moment
          .unix(end)
          .format('D MMM YYYY')}`
      );
    } else {
      $label.html('Anytime');
      $clear.addClass('d-none');
    }

    $clear.on('click', evt => {
      evt.preventDefault();

      $label.html('Anytime');

      $start.val('');
      $end.val('');

      $clear.addClass('d-none');
    });
  })
  .daterangepicker({
    autoUpdateInput: false,
    locale: { format: 'D MMM YYYY' },
  })
  .on('apply.daterangepicker', (evt, picker) => {
    const $picker = $(evt.currentTarget);

    $picker.next('.clear').removeClass('d-none');

    $picker
      .children('.label')
      .html(
        `${picker.startDate.format('D MMM YYYY')} - ${picker.endDate.format(
          'D MMM YYYY'
        )}`
      );

    $picker.children('input[name="start"]').val(picker.startDate.unix());
    $picker.children('input[name="end"]').val(picker.endDate.unix());
  })
  .on('show.daterangepicker', evt => {
    $(evt.currentTarget).addClass('date-picker-focused');
  })
  .on('hide.daterangepicker', evt => {
    $(evt.currentTarget).removeClass('date-picker-focused');
  });

(function($, moment) {
    'use strict';

    var $startInput = $('input[name="start"]'),
      $endInput = $('input[name="end"]'),
      $label = $('.date-range span'),
      $clearFilter = $('.clear-dates');

    var startDate = ($startInput.val() !== '') ? moment($startInput.val(), 'DDMMYYYY') : moment(),
      endDate = ($endInput.val() !== '') ? moment($endInput.val(), 'DDMMYYYY') : moment();

    if ($startInput.val() !== '' && $endInput.val() !== '') {
      $label.html(startDate.format('DD/MM/YYYY') + ' &ndash; ' + endDate.format('DD/MM/YYYY'));
      $clearFilter.show();
    }

    $('document').ready(function() {
      $('.date-range').daterangepicker({
        'format': 'DD/MM/YYYY',
        'startDate': startDate,
        'endDate': endDate,
        'timeZone': $('input[name="tz"]').val(),
        ranges: {
          'Today': [moment(), moment()],
          'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
          'Last 7 Days': [moment().subtract(6, 'days'), moment()],
          'Last 30 Days': [moment().subtract(29, 'days'), moment()],
          'This Month': [moment().startOf('month'), moment().endOf('month')],
          'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        },
      }, function(start, end, label) {
        applyDate(start, end);
      }).on('apply.daterangepicker', function(evt, picker) {
        applyDate(picker.startDate, picker.endDate);
      });
      $clearFilter.on('click', function(evt) {
        $label.html('Anytime');
        $startInput.val('');
        $endInput.val('');
        $('.date-range').data('daterangepicker').setStartDate(moment());
        $('.date-range').data('daterangepicker').setEndDate(moment());
        $(this).hide();
      });
    });

    function applyDate(start, end) {
      $label.html(start.format('DD/MM/YYYY') + ' &ndash; ' + end.format('DD/MM/YYYY'));
      $startInput.val(start.format('DDMMYYYY'));
      $endInput.val(end.format('DDMMYYYY'));
      $clearFilter.show();
    }
  })(jQuery, moment);

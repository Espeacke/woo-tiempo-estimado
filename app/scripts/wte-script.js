jQuery(function() {
    var dateComponent = jQuery('.wte-date').css({
        'margin': '24px 0'
    });

    jQuery('.shop_table.order_details').before(dateComponent);

    var calendar = jQuery('.wte-calendar');

    calendar.css('margin-bottom', 28);
    
    var minDate = calendar.attr('data-min');
    var maxDate = calendar.attr('data-max');
    var maxIsNextMonth = calendar.attr('data-is-next-month') == '1';

    var setRange = function() {
            jQuery('[data-date="'+minDate+'"]').addClass('pignose-calendar-unit-active pignose-calendar-unit-wte');
            jQuery('[data-date="'+maxDate+'"]').addClass('pignose-calendar-unit-active pignose-calendar-unit-wte');

            jQuery('.pignose-calendar-unit').not('.pignose-calendar-unit-disabled')
                                            .not('.pignose-calendar-unit-active')
                                               .addClass('pignose-calendar-unit-range');
    };

    calendar.pignoseCalendar({
        page: setRange,
        multiple: true,
        minDate: minDate,
        maxDate: maxDate,
        theme: 'blue',
        weeks: [
          'Dom',
          'Lun',
          'Mar',
          'Mie',
          'Jue',
          'Vie',
          'Sab'
        ],
    });

    setRange();
});

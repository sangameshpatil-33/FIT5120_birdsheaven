jQuery(document).ready(function (jQuery) {

  var datetime_container = jQuery(".cp-datetime-picker");
  datetime_container.each(function (i, e) {

    var container = jQuery(datetime_container[i]),
      input_datetimepicker_id = container.find(".cp-datetimepicker").attr("id"),
      datecontainer_replace = input_datetimepicker_id.replace('cp_', ''),
      datecontainer = jQuery("#" + datecontainer_replace),
      input = jQuery("#" + input_datetimepicker_id),
      newinput = "#" + input_datetimepicker_id,
      val = '',
      timestring = jQuery(".cp_timezone").val(),
      currenttime = '';
    if (timestring == 'system') {
      currenttime = new Date();
      currenttime = cp_create_date(currenttime)
    } else {
      currenttime = jQuery(".cp_currenttime").val();
    }

    jQuery("#start_date, #end_date").datetimepicker({
      sideBySide: false,
      widgetPositioning: {
        horizontal: 'left',
        vertical: 'bottom'
      },
      icons: {
        time: 'dashicons dashicons-clock',
        date: 'dashicons dashicons-calendar-alt',
        up: 'dashicons dashicons-arrow-up-alt2',
        down: 'dashicons dashicons-arrow-down-alt2',
        previous: 'dashicons dashicons-arrow-left-alt2',
        next: 'dashicons dashicons-arrow-right-alt2',
        today: 'dashicons dashicons-screenoptions',
        clear: 'dashicons dashicons-trash',
      }
    });

    datecontainer.on("dp.change", function (e) {
      var date = e.date;
      var date_Obj = new Date(date);

      if (newinput == '#cp_start_date') {

        var strTime = cp_create_date(date_Obj);
        input.attr("value", strTime);
        input.attr('data-default-date', strTime);
        jQuery(document).trigger('cp-datepicker-change', [input, strTime]);
        jQuery("#end_date").data("DateTimePicker").minDate(e.date); // set min date

      } else if (newinput == '#cp_end_date') {

        var endTime = cp_create_date(date_Obj);
        input.attr("value", endTime);
        input.attr('data-default-date', endTime);
        jQuery(document).trigger('cp-datepicker-change', [input, endTime]);
        // jQuery("#start_date").data("DateTimePicker").maxDate(e.date); // enable if we want to set max date too
      }
      input.trigger('change');
      input.trigger('keyup');
    });

    val = input.data('default-date');
    if (val.length == 0) {
      val = currenttime;
    }
    input.attr("value", val);
  });

  //function to get date in 11/25/2016 8:10pm format
  function cp_create_date(date_Obj) {
    var month = date_Obj.getMonth() + 1;
    var date = date_Obj.getDate()
    month = month < 10 ? '0' + month : month;
    date = date < 10 ? '0' + date : date;
    var newDate = month + "/" + date + "/" + date_Obj.getFullYear() + " ";
    //get time
    var hours = date_Obj.getHours();
    var minutes = date_Obj.getMinutes();
    var ampm = hours >= 12 ? 'PM' : 'AM';
    hours = hours % 12;
    hours = hours ? hours : 12; // the hour '0' should be '12'
    minutes = minutes < 10 ? '0' + minutes : minutes;
    var strTime = newDate + hours + ':' + minutes + ' ' + ampm;
    return strTime;
  }
});

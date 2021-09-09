// Litepicker
//
// The date pickers in Material Admin Pro
// are powered by the Litepicker plugin.
// Litepicker is a lightweight, no dependencies
// date picker that allows for date ranges
// and other options. For more usage details
// visit the Litepicker docs.
//
// Litepicker Documentation
// https://wakirin.github.io/Litepicker

window.addEventListener('DOMContentLoaded', event => {

    const litepickerSingleDate = document.getElementById('litepickerSingleDate');
    if (litepickerSingleDate) {
        new Litepicker({
            element: litepickerSingleDate,
            format: 'MMM DD, YYYY'
        });
    }

    const litepickerDateRange = document.getElementById('litepickerDateRange');
    if (litepickerDateRange) {
        new Litepicker({
            element: litepickerDateRange,
            singleMode: false,
            format: 'YYYY-MM-DD',
            lang: 'ko',
            onSelect: function(start, end){
                var parse_start = Date.parse(start) / 1000
                var parse_end = (Date.parse(end) / 1000) + 86399
                location.href = `${window.location.pathname}?start=${parse_start}&end=${parse_end}`
            }
        });
    }

    const litepickerDateRange2Months = document.getElementById('litepickerDateRange2Months');
    if (litepickerDateRange2Months) {
        new Litepicker({
            element: litepickerDateRange2Months,
            singleMode: false,
            numberOfMonths: 2,
            numberOfColumns: 2,
            format: 'MMM DD, YYYY'
        });
    }
});

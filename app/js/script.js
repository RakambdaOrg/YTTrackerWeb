$(document).ready(function () {
    $.tablesorter.addParser({
        id: 'duration',
        is: function(s) {
            return s.match(/(\d{2}h)?(\d{2}m)?\d{2}s/ig);
        },
        format: function(str) {
            var h = 0;
            var m = 0;
            var s = 0;
            if(str.indexOf('h') > -1)
            {
                h = parseInt(str.substring(0, str.indexOf('h')));
                str = str.substring(str.indexOf('h') + 1);
            }
            if(str.indexOf('m') > -1)
            {
                m = parseInt(str.substring(0, str.indexOf('m')));
                str = str.substring(str.indexOf('m') + 1);
            }
            if(str.indexOf('s') > -1)
            {
                s = parseInt(str.substring(0, str.indexOf('s')));
            }
            return s + m * 60 + h * 3600;
        },
        type: 'numeric'
    });

    $(function() {
        $("#dataTable").tablesorter({
            headers: {
                1: {
                    sorter:'duration'
                },
                2: {
                    sorter:'duration'
                },
                4: {
                    sorter:'duration'
                },
                5: {
                    sorter:'duration'
                }
            }
        });
    });
});

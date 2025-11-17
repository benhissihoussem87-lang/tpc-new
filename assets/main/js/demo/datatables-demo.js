// Shared DataTable bootstrap with optional year filtering + default order hooks
$(document).ready(function() {
  $('table#dataTable').each(function() {
    var $table = $(this);
    var options = {};

    var orderColAttr = $table.attr('data-order-column');
    if (typeof orderColAttr !== 'undefined') {
      var orderDir = $table.attr('data-order-direction') || 'desc';
      options.order = [[parseInt(orderColAttr, 10) || 0, orderDir]];
    }

    var tableInstance;
    if ($.fn.DataTable.isDataTable($table)) {
      var existing = $table.DataTable();
      existing.destroy();
    }
    tableInstance = $table.DataTable(options);

    var yearColumnAttr = $table.attr('data-year-column');
    var yearColumnIndex = (typeof yearColumnAttr !== 'undefined') ? parseInt(yearColumnAttr, 10) : NaN;
    var yearFilterSelector = $table.attr('data-year-filter');
    if (typeof yearFilterSelector === 'string') {
      var $filter = $(yearFilterSelector);
      if ($filter.length) {
        var filterFn = function(settings, data, dataIndex) {
          if (settings.nTable !== $table[0]) { return true; }
          var yearVal = $filter.val();
          if (!yearVal) { return true; }
          var compare = '';
          if (settings.aoData && settings.aoData[dataIndex] && settings.aoData[dataIndex].nTr) {
            compare = settings.aoData[dataIndex].nTr.getAttribute('data-year-values') || '';
          }
          if (!compare && !Number.isNaN(yearColumnIndex)) {
            compare = (data[yearColumnIndex] || '').replace(/<[^>]+>/g, ' ');
          }
          if (!compare) { return false; }
          return compare.indexOf(yearVal) !== -1;
        };
        $.fn.dataTable.ext.search.push(filterFn);
        $table.on('destroy.dt', function() {
          var idx = $.fn.dataTable.ext.search.indexOf(filterFn);
          if (idx !== -1) {
            $.fn.dataTable.ext.search.splice(idx, 1);
          }
        });
        $filter.on('change keyup', function() {
          tableInstance.draw();
        });
      }
    }
  });
});

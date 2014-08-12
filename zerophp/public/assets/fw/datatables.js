$(document).ready(function($) {
    if (FW.settings.datatables != undefined) {
        $('#datatables').dataTable(FW.settings.datatables);
    }   
} );
var oTable;

$(document).ready(function(){
	
	$.getJSON(
			"index.php?page=view&get=all",
			function (data) {
				oTable = $(".receiptsTable").dataTable( {
					"bJQueryUI": true,
					"sPaginationType": "full_numbers",
					"aaData": data,
					"aaSorting": [[ 0, "asc" ]],
					"aLengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
					"bAutoWidth": false,
                    "iDisplayLength": 10,
                    "sDom": '<"H"ipr>t<"F"lf>',
					"aoColumns": [
						{ "sTitle": "ID", "sWidth": "40px", "sClass": "idcell" },
						{ "sTitle": "Name", "bSearchable": true },
						{ "sTitle": "Email", "bSearchable": true },
						{ "sTitle": "Student ID", "bSearchable": true },
						{ "sTitle": "Products", "bSearchable": true, "sWidth": "150px" },
						{ "sTitle": "Comments", "bSearchable": true },
						{ "sTitle": "Total" },
						{ "sTitle": "Issuer", "bSearchable": true },
						{ "sTitle": "Society", "bSearchable": true }
					] } );
				
			});
	
});
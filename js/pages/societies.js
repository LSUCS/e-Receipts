var oTable;

$(document).ready(function(){
	
	updateSocietyTable();
	
	$(".addSocietyButton").click(function() {
		$.post(
        	"index.php?page=societies&get=set",
        	{ society_id: "", name: $(".societyName").val(), email: $(".societyEmail").val() },
        	function(data) {
        		if (data.error) {
        			alert(data.error);
        			return;
        		}
        		alert("Society Added!");
        		updateSocietyTable();
        		$(".societyName").val("");
        		$(".societyEmail").val("");
        	},
        	"json"
        );
	});
	
});

function updateSocietyTable() {
	$(".societies").html('<table class="societyTable"></table>');
	$.getJSON(
			"index.php?page=societies&get=all",
			function (data) {
				oTable = $(".societyTable").dataTable( {
					"bJQueryUI": true,
					"sPaginationType": "full_numbers",
					"aaData": data,
					"aaSorting": [[ 0, "asc" ]],
					"aLengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
					"bAutoWidth": false,
                    "iDisplayLength": 10,
                    "sDom": '<"H"ipr>t<"F"lf>',
					"aoColumns": [
						{ "sTitle": "ID", "sWidth": "60px", "sClass": "idcell" },
						{ "sTitle": "Society", "bSearchable": true, "sClass": "editable" },
						{ "sTitle": "Email", "sWidth": "400px", "sClass": "editable", "bSearchable": true }
					] } );
				
				$('.editable', oTable.fnGetNodes()).editable(
					function(value, settings) { return makeEditable(this, value); },
					{ 
					    type    : 'text',
					    onblur  : 'submit',
					    height  : '35px',
					    width   : '100%',
					    tooltip : 'Click to edit...'
					}
				);
				$('.selectable', oTable.fnGetNodes()).editable(
						function(value, settings) { return makeEditable(this, value); },
						{ 
						    type    : 'select',
						    data    : { 1: "Yes", 0: "No" },
						    onblur  : 'submit',
						    height  : '35px',
						    width   : '100%',
						    tooltip : 'Click to edit...'
						}
					);
				
			});
}

function makeEditable(obj, value) {
	var rowPos = oTable.fnGetPosition(obj);
    var rowData = oTable.fnGetData(rowPos[0]);
    var old = rowData[rowPos[1]];
    rowData[rowPos[1]] = value;
    $.post(
    	"index.php?page=societies&get=set",
    	{ society_id: rowData[0], name: rowData[1], email: rowData[2] },
    	function(data) {
    		if (data.error) {
    			alert(data.error);
    			return;
    		}
    		$(".societyTable tbody .idcell").each(
        		function() {
        			if ($(this).html() == data.data[0]) {
        				var oTable = $(".societyTable").dataTable();
        				var rowPos = oTable.fnGetPosition(this);
        				oTable.fnUpdate(data.data, rowPos[0], 0);
        			}
        		}
    		);
    	},
    	"json"
    );
    return(old);
}
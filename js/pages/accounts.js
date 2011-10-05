var oTable;
var societies;

$(document).ready(function(){
	
	$.getJSON(
    	"index.php?page=accounts&get=socs",
    	function(data) {
    		societies = data;
    	}
    );
	
	updateAccountTable();
	
	$(".addAccountButton").click(function() {
		$.post(
        	"index.php?page=accounts&get=set",
        	{ user_id: "", email: $(".accountEmail").val(), student_id: $(".accountStudentId").val(), password: $(".accountPassword").val(), active: $(".accountActive").val(), society_id: $(".accountSocietyId").val() },
        	function(data) {
        		if (data.error) {
        			alert(data.error);
        			return;
        		}
        		alert("Account Added!");
        		updateAccountTable();
        		$(".accountName").val("");
        		$(".accountEmail").val("");
        	},
        	"json"
        );
	});
	
});

function updateAccountTable() {
	$(".accounts").html('<table class="accountTable"></table>');
	$.getJSON(
			"index.php?page=accounts&get=all",
			function (data) {
				oTable = $(".accountTable").dataTable( {
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
						{ "sTitle": "Email", "bSearchable": true, "sClass": "editable", "sWidth": "170px" },
						{ "sTitle": "Student ID", "bSearchable": true, "sClass": "editable", "sWidth": "110px"},
						{ "sTitle": "Password", "sClass": "editable"},
						{ "sTitle": "Active", "bSearchable": true, "sClass": "active", "sWidth": "80px" },
						{ "sTitle": "Society", "sWidth": "120px", "sClass": "selectable", "bSearchable": true}
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
					    data    : societies,
					    onblur  : 'submit',
					    height  : '35px',
					    width   : '100%',
					    tooltip : 'Click to edit...'
					}
				);
				$('.active', oTable.fnGetNodes()).editable(
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
    for (n in societies) {
    	if (societies[n] == rowData[5]) rowData[5] = n;
    }
    $.post(
    	"index.php?page=accounts&get=set",
    	{ user_id: rowData[0], email: rowData[1], student_id: rowData[2], password: rowData[3], active: rowData[4], society_id: rowData[5] },
    	function(data) {
    		if (data.error) {
    			alert(data.error);
    			return;
    		}
    		$(".accountTable tbody .idcell").each(
        		function() {
        			if ($(this).html() == data.data[0]) {
        				var oTable = $(".accountTable").dataTable();
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
var oTable;

$(document).ready(function(){
	
	updateProductTable();
	
	$(".addProductButton").click(function() {
		$.post(
        	"index.php?page=product&get=set",
        	{ product_id: "", name: $(".productName").val(), price: $(".productPrice").val().replace(/[^0-9.]/g, ""), available: $(".productAvailable").val() },
        	function(data) {
        		if (data.error) {
        			alert(data.error);
        			return;
        		}
        		alert("Product Added!");
        		updateProductTable();
        		$(".productName").val("Product Name");
        		$(".productPrice").val("Price");
        	},
        	"json"
        );
	});
	
});

function updateProductTable() {
	$(".products").html('<table class="productTable"></table>');
	$.getJSON(
			"index.php?page=product&get=all",
			function (data) {
				oTable = $(".productTable").dataTable( {
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
						{ "sTitle": "Product", "sWidth": "160px", "bSearchable": true, "sClass": "editable" },
						{ "sTitle": "Price", "sWidth": "100px", "sClass": "editable" },
						{ "sTitle": "Available", "sWidth": "100px", "sClass": "selectable" },
						{ "sTitle": "Society", "sWidth": "130px", "bSearchable": true }
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
    	"index.php?page=product&get=set",
    	{ product_id: rowData[0], name: rowData[1], price: rowData[2].replace(/[^0-9.]/g, ""), available: rowData[3] },
    	function(data) {
    		if (data.error) {
    			alert(data.error);
    			return;
    		}
    		$(".productTable tbody .idcell").each(
        		function() {
        			if ($(this).html() == data.data[0]) {
        				var oTable = $(".productTable").dataTable();
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
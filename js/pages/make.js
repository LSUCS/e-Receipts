$(document).ready(function(){
	
	$("input:button").button();
	
	$(".addButton").click(function() {
		sel = $(".selectProduct option:selected");
		$(".selectedProducts").append("<option value='" + sel.val() + "'>" + sel.text() + '</option>');
		updateTotal();
	});
	
	$(".removeButton").click(function() {
		$(".selectedProducts option:selected").remove();
		updateTotal();
	});
	
	$(".receiptComments").click(function() {
		if ($(this).val() == "Comments") {
			$(this).val("");
			$(this).css("color", "#000");
		}
	});
	$(".receiptComments").blur(function() {
		if ($(this).val() == "") {
			$(this).val("Comments");
			$(this).css("color", "#999");
		}
	});
	
	$(".submitButton").click(function() {
		$(".overlay").css("display", "inline");
		products = new Array();
		$(".selectedProducts option").each(function() {
			products.push($(this).val());
		});
		$.post(
        	"index.php?page=make&get=set",
        	{ email: $(".emailBox").val(), name: $(".nameBox").val(), student_id: $(".studentidBox").val(), comments: $(".receiptComments").val(), products: products.join(",") },
        	function(data) {
        		if (data.error) {
        			alert(data.error);
            		$(".overlay").css("display", "none");
        			return;
        		}
        		$(".overlay").css("display", "none");
        		alert("Receipt Added!");
        		$(".emailBox").val("");
        		$(".nameBox").val("");
        		$(".studentidBox").val("");
        		$(".receiptComments").val("Comments");
        		$(".receiptComments").css("color", "#999");
        		$(".selectedProducts").empty();
        		updateTotal();
        	},
        	"json"
        );
	});
	
});

function updateTotal() {
	 var total = 0;
         $(".selectedProducts option").each(function() {
         	total += parseFloat($(this).html().substr($(this).html().lastIndexOf('£') + 1));
         });
         $("#totalNum").html("&pound;" + currencyFormatted(total));
}

function currencyFormatted(amount) {
	var i = parseFloat(amount);
	if(isNaN(i)) { i = 0.00; }
	var minus = '';
	if(i < 0) { minus = '-'; }
	i = Math.abs(i);
	i = parseInt((i + .005) * 100);
	i = i / 100;
	s = new String(i);
	if(s.indexOf('.') < 0) { s += '.00'; }
	if(s.indexOf('.') == (s.length - 2)) { s += '0'; }
	s = minus + s;
	return s;
}
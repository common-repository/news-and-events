jQuery(document).ready(function($) {
	$("table tr td h2").click(function() {
		//We have no way of knowing how many table rows are associated with each header. As such, we just hide each row underneath a header until we run into the next header.

		var span = $(this).children('span');
		if(span.text() == '+') {
			span.text('-');
		}
		else {
			span.text('+');
		}

		var tr = $(this).parent().parent();
		var n = tr.next('tr');
		var stop = false;
		while(stop == false) { //Iterates through the next table rows until it finds the next header or the end of the table
			if(n.css('display') == 'none') {
				n.css('display', 'table-row');
			}
			else {
				n.css('display', 'none');
			}

			n = n.next('tr');

			if(n.length == 0) stop = true; //If there are no more rows (i.e. end of table), stop
			if(n.children('td').children('h2').length > 0) stop = true; //If we run into the next header, stop
		}
	});
});

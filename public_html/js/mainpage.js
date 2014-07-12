$( document ).ready( function() {
	$( "#ok" ).click( function() {
		var wiki = $("#wiki option:selected").text();
		var user = $("#user").val();
		if($('#namespace').val() != "0") {
			window.location.href = "http://tools.wmflabs.org/lrtools/pages/" + wiki + "/" + user + "/" + $('#namespace').val();
		} else {
			window.location.href = "http://tools.wmflabs.org/lrtools/pages/" + wiki + "/" + user;
		}
	});

	$( "#wiki" ).change( loadns );

	function loadns() {
		var el = $( "#namespace" )
		el.html('<option value="0">Main</option>');
		$.ajax({
			url: "namespaces.php",
			data: {
				wiki: $("#wiki option:selected").text()
			}
		}).done(function(data) {
			$.each(data, function (key, value) {
				el.append('<option value="' + key + '">' + value + '</option>');
			});
		});
		
	}

	loadns();
});
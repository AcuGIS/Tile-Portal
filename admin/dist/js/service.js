var tbl_action = 'service';

$(document).ready(function() {
	$('[data-toggle="tooltip"]').tooltip();	
	
	$(document).on("click", ".add-modal", function() {

		$('#addnew_modal').modal('show');
		$('#btn_create').html('Create');
	});
	
	// action click
	$(document).on("click", ".start, .stop, .restart, .enable, .disable", function() {
			var obj = $(this);
			var svc = obj.parents("tr").attr('data-svc');
			var data = {'action': obj.attr('class'), 'svc': svc}
			
			let tr = obj.parents("tr");
			
			obj.prop('disabled', true);
			
			$.ajax({
				type: "POST",
				url: 'action/' + tbl_action+ '.php',
				data: data,
				dataType:"json",
				success: function(response){
					if(response.success) { // means, new record is added
						window.location.href = 'services.php';
					}else{
						alert(response.message);
					}
				},
				complete: function(data){
					obj.prop('disabled', false);
				}
			});
	});
	
	// Delete row on delete button click
	$(document).on("click", ".delete", function() {
			var obj = $(this);
			var id = obj.parents("tr").attr('data-id');
			var data = {'action': 'delete', 'id': id}
			
			if(confirm('Service file will be deleted ?')){
				$.ajax({
					type: "POST",
					url: 'action/' + tbl_action + '.php',
					data: data,
					dataType:"json",
					success: function(response){
						if(response.success) { // means, new record is added
							sortTable.row(obj.parents("tr")).remove().draw();
						}

						alert(response.message);
					}
				});
			}
	});

	$(document).on("click", "#btn_create", function() {
			var obj = $(this);
			var input = $('#service_form').find('input[type="text"], input[type="checkbox"], select');
			var empty = false;
			
			obj.toggle();
			
			input.each(function() {
				if (!$(this).prop('disabled') && $(this).prop('required') && !$(this).val()) {
					$(this).addClass("error");
					empty = true;
				} else {
					$(this).removeClass("error");
				}
			});

			if(empty){
				$('#service_form').find(".error").first().focus();
					obj.toggle();
			}else{
				let data = new FormData($('#service_form')[0]);
				
				$.ajax({
					type: "POST",
					url: 'action/' + tbl_action + '.php',
					data: data,
					processData: false,
					contentType: false,
					dataType: "json",
					success: function(response){
						if(response.success){
							window.location.href = 'services.php';
						}else{
							alert("Create failed:" + response.message);
						}
					}
				});
			}
	});
});
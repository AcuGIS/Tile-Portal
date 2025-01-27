var tbl_action = 'layer';

function load_select(id, name, arr){
	var obj = $('#' + id);
	if(arr.length === 0){
		obj.replaceWith(`<input type="text" class="form-control" name="` + name +`" id="` + id + `" value="" required/>`);
		return;
	}
	
	var opts = '';
	var first = 'selected';
	$.each(arr, function(x){
		opts += '<option value="' + arr[x] + '" ' + first + '>' + arr[x] + '</option>' + "\n";
		first = '';
	});
	
	// check if
	idx = 0;
	if(obj.prop('tagName').toLowerCase() == 'input'){
		var idx = $.inArray(obj.val(), arr);
		if(idx == -1){
			idx = 0;
		}
	}
	
	//change input to select
	obj.replaceWith(`<select class="form-select" id="`+ id + `" name="`+ name +`" required>` + opts + `</select>`);
	// selecting first element
	$('#' + id).val(arr[idx]);
	$('#' + id).trigger('change');
}

$(document).ready(function() {

	$('[data-toggle="tooltip"]').tooltip();	
	$('#layer_form').submit(false);
	
	$(document).on("click", ".add-modal", function() {

		$('#addnew_modal').modal('show');
		$('#btn_create').html('Create');
		
		$('#id').val(0);
		$.ajax({
			type: "POST",
			url: 'action/' + tbl_action + '.php',
			data: {'action': 'get_layers', 'svc_id': $('#svc_id').val()},
			dataType:"json",
			success: function(response){
				if(response.success) { // means, new record is added
					load_select('name', 'name', response.layers);
				}else{
					alert(response.message);
				}
			}
		});
		
		if($('#group_id').length > 0){	// if user tab
			$('#group_id').trigger('change');	// trigger change to reload groups
		}
	});

	// Edit row on edit button click
	$(document).on("click", ".edit", function() {
		let tr = $(this).parents("tr");
		let tds = tr.find('td');
		
		$('#btn_create').html('Update');
		$('#addnew_modal').modal('show');

		$('#id').val(tr.attr('data-id'));
		$('#svc_id').val(tds[1].getAttribute('data-value'));
		$.ajax({
			type: "POST",
			url: 'action/' + tbl_action + '.php',
			data: {'action': 'get_layers', 'svc_id': tds[1].getAttribute('data-value')},
			dataType:"json",
			success: function(response){
				if(response.success) { // means, new record is added
					load_select('name', 'name', response.layers);
					$('#name').val(tds[2].getAttribute('data-order'));
				}else{
					alert(response.message);
				}
			}
		});
		
		$('#public').prop('checked', (tds[3].textContent == 'yes'));
		$('#group_id').val(tds[4].getAttribute('data-value').split(','));
	});

	// Delete row on delete button click
	$(document).on("click", ".delete", function() {
			var obj = $(this);
			var id = obj.parents("tr").attr('data-id');
			var data = {'action': 'delete', 'id': id}
			
			if(confirm('Layer file will be deleted ?')){
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

	$(document).on("change", "#svc_id", function() {
		let obj = $(this);
		let svc_id = obj.val();
		
		$.ajax({
			type: "POST",
			url: 'action/' + tbl_action + '.php',
			data: {'action': 'get_layers', 'svc_id': svc_id},
			dataType:"json",
			success: function(response){
				if(response.success) { // means, new record is added
					load_select('name', 'name', response.layers);
					$('#name').val(response.layers[0]);
				}else{
					alert(response.message);
				}
			}
		});
	});

	$(document).on("click", "#btn_create", function() {
			var obj = $(this);
			var input = $('#layer_form').find('input[type="text"], input[type="checkbox"], select');
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
				$('#layer_form').find(".error").first().focus();
					obj.toggle();
			}else{
				let data = new FormData($('#layer_form')[0]);
				
				$.ajax({
					type: "POST",
					url: 'action/' + tbl_action + '.php',
					data: data,
					processData: false,
					contentType: false,
					dataType: "json",
					success: function(response){
						if(response.success){
							$('#btn_create').toggle();
							$('#addnew_modal').modal('hide');
							
							if(data.get('id') > 0){	// if edit
								location.reload();
							}else if(sortTable.rows().count() == 0){ // if no rows in table, there are no data-order tags!
								location.reload();
							}else{
								const name_a = '<a href="../' + response.id + '.html">' + data.get('name') + '</a>';
								const is_public = data.get('public') == 't' ? 'yes' : 'no';
								
								const tds = [
									response.id,
									{ "display": data.get('svc_id'), "@data-value": data.get('svc_id') },
									{ "display": name_a, "@data-order": data.get('name') },
									is_public,
									data.getAll('group_id[]').join(','),
									`<a class="info" title="Show Connection" data-toggle="tooltip"><i class="text-info bi bi-info-circle"></i></a>
									<a class="edit" title="Edit" data-toggle="tooltip"><i class="text-warning bi bi-pencil-square"></i></a>
									<a class="delete" title="Delete" data-toggle="tooltip"><i class="text-danger bi bi-x-square"></i></a>`
								];

								sortTable.row.add(tds).draw();
								$("table tbody tr:last-child").attr('data-id', response.id);
							}
						}else{
							alert("Create failed:" + response.message);
						}
					}
				});
			}
	});
});	
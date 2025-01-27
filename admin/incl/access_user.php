	<div class="table-responsive">
			<table class="table table-bordered" id="sortTable">
				<thead>
					<tr>
						<th data-name="id" data-editable='false'>ID</th>
						<th data-name="name">Name</th>
						<th data-name="email">Email</th>
						<th data-name="password">Password</th>
						<th data-name="group_id" data-type="select">Access Group</th>
						<th data-name="accesslevel" data-type="select">Access Level</th>
						<th data-editable='false' data-action='true'>Actions</th>
					</tr>
				</thead>

				<tbody> <?php while($user = pg_fetch_assoc($rows)) {
					$row_grps = $grp_obj->getByKV('user', $user['id']);
					?>
					<tr data-id="<?=$user['id']?>" align="left">
						<td><?=$user['id']?> </td>
						<td><?=$user['name']?></td>
						<td><?=$user['email']?></td>
						<td><?=$user['password']?></td>
						<td data-value="<?=implode(',', array_keys($row_grps))?>">
							<?=implode(',', array_values($row_grps))?>
						</td>
						<td data-value="<?=$user['accesslevel']?>"><?=$user['accesslevel']?></td>

						<td>
							<a class="edit" title="Edit" data-toggle="tooltip"><i class="text-warning bi bi-pencil-square"></i></a>
							<a class="delete" title="Delete" data-toggle="tooltip"><i class="text-danger bi bi-x-square"></i></a>
						</td>
					</tr> <?php } ?>
				</tbody>
			</table>           
		</div>

		<div class="row">
		    <div class="col-8"><p>&nbsp;</p>

					<div class="alert alert-success">
					   <strong>Note:</strong> Manage your users from here. <a href="https://tile-portal.docs.acugis.com/en/latest/users.html" target="_blank">Documentation</a>
					</div>
				</div>
		</div>
		
		<div id="addnew_modal" class="modal fade" role="dialog">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<h4 class="modal-title">Create User</h4>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
					</div>
					
					<div class="modal-body" id="addnew_modal_body">
						<form id="user_form" class="border shadow p-3 rounded"
									action=""
									method="post"
									enctype="multipart/form-data"
									style="width: 450px;">

							<input type="hidden" name="action" value="save"/>
							<input type="hidden" name="id" id="id" value="0"/>
							
							<div class="form-group">
								<label for="name">Name:</label>
								<input type="text" class="form-control" id="name" placeholder="Enter name" name="name" required>
							</div>
							
							<div class="form-group">
								<label for="email">Email:</label>
								<input type="email" class="form-control" id="email" placeholder="Enter email" name="email" required>
							</div>
							
							<div class="form-group">
								<label for="pwd">Password:</label>
								<input type="password" class="form-control" id="password" placeholder="Enter password" name="password" required>
							</div>

							<div class="form-group">
								<select name="accesslevel" id="accesslevel" required>
									<option value="User">User</option>
										<option value="Admin">Admin</option>
								</select>
								<label for="accesslevel">Access Level:</label>
							</div>
							
							<div class="form-group">
								<select name="group_id[]" id="group_id" multiple required>
									<?php $sel = 'selected';
									foreach($groups as $k => $v){ ?>
										<option value="<?=$k?>" <?=$sel?>><?=$v?></option>
									<?php $sel = ''; } ?>
								</select>
								<label for="group_id">Access Groups:</label>
							</div>
							
						</form>
					</div>
					<div class="modal-footer">
						<button type="button" class="activate btn btn-secondary" id="btn_create" data-dismiss="modal">Create</button>
					</div>
				</div>
			</div>
		</div>
	</div>
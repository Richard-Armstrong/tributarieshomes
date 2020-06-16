
<?php if (!$static) : ?>
	<script src="<?= base_url("js/create_standard_report.js") ?>"></script>
	<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.css">
	<script src="//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.js"></script>
<?php else : ?>
	<script src="<?= base_url("js/create_static_report.js") ?>"></script>
<?php endif ?>

<div class="content">
	<div class="container-fluid">
		<h1><?= $title ?></h1>

		<?php if ($message) : ?>
			<div><?= $message ?></div>
		<?php endif ?>

		<div class="card">
			<div class="card-body">
				<input type="hidden" id="static" value="<?= $static ?>"/>

				<table class="table">
					<tr>
						<td style="width:16.6%"><label class="control-label">Report Name</label></td>
						<td colspan="2"><input type="text" class="form-control" id="report_name" placeholder="Report Name"/></td>
					</tr>

					<?php if (!$static) : // Static Reports ignore runtimes ?>
						<tr>
							<td><label class="control-label">Report Type</label></td>
							<td><?= form_dropdown('type', $types, 0, 'id="type" onchange="show_weekday()"') ?></td>
							<td id="weekday_td" style="display:none;">
								<?= form_dropdown('weekday', $weekdays, -1, 'id="weekday"') ?>
								<span id="month_message">Runs on the 1st of each month</span>
							</td>
						</tr>

						<tr>
							<td><label class="control-label">Run Time</label></td>
							<td colspan="2"><input type="text" class="form-control timepicker" id="time"/></td>
						</tr>
					<?php endif ?>

					<tr>
						<td><label class="control-label">Private?</label></td>
						<td colspan="2"><input type="checkbox" id="private"/></td>
					</tr>
				</table>

				<table class="table" id="field_table">
					<thead>
						<tr>
							<th>Name</th>
							<th>Field</th>
							<th>Operation</th>
							<th>Cond. Field</th>
							<th>Cond. Value</th>
							<th></th>
						</tr>
					</thead>

					<tbody id="fields_body"></tbody>
				</table>

				<div align="CENTER">
					<button type="button" class="btn btn-primary" onclick="open_modal()"><?= ($static) ? "Choose a Form" : "Add a Field" ?></button>
				</div>

				<button type="button" class="btn btn-primary" onclick="try_submit()">Save</button>
			</div>
		</div>
	</div>
</div>

<?php if ($static) : ?>
	<div class="modal fade" id="static_modal" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title">Choose a Form</h4>
					<button type="button" class="close" data-dismiss="modal">&times;</button>
				</div>

				<div class="modal-body">
					<table class="table">
						<tr>
							<td>Department</td>
							<td><?= form_dropdown('department', $departments, -1, 'class="form-control" id="department" onchange="get_forms()"') ?></td>
						</tr>

						<tr id="form_tr">
							<td>Form</td>
							<td id="form_td"></td>
						</tr>

						<tr id="cond_field_tr">
							<td>Condition Field</td>
							<td id="cond_field_td"></td>
						</tr>

						<tr id="cond_option_tr">
							<td>Condition Value</td>
							<td id="cond_option_td"></td>
						</tr>
					</table>
				</div>

				<div class="modal-footer">
					<button type="button" class="btn btn-primary" id="finish_condition" onclick="finish_condition()">Finish</button>
				</div>
			</div>
		</div>
	</div>
<?php else : ?>
	<div class="modal fade" id="field_modal" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title">Field Options</h4>
					<button type="button" class="close" data-dismiss="modal">&times;</button>
				</div>

				<div class="modal-body">
					<table class="table">
						<tr>
							<td style="width:16.6%;">Report Field Name</td>
							<td><input type="text" class="form-control" id="field_name" placeholder="Name"/></td>
						</tr>

						<tr>
							<td>Department</td>
							<td><?= form_dropdown('department', $departments, -1, 'class="form-control" id="department" onchange="get_forms()"') ?></td>
						</tr>

						<tr id="form_tr">
							<td>Form</td>
							<td id="form_td"></td>
						</tr>

						<tr id="field_tr">
							<td>Field</td>
							<td id="field_td"></td>
						</tr>

						<tr id="operation_tr">
							<td>Operation</td>
							<td id="operation_td"></td>
						</tr>

						<tr id="cond_field_tr">
							<td>Condition Field</td>
							<td id="cond_field_td"></td>
						</tr>

						<tr id="cond_option_tr">
							<td>Condition Value</td>
							<td id="cond_option_td"></td>
						</tr>
					</table>
				</div>

				<div class="modal-footer">
					<button type="button" class="btn btn-primary" id="finish_field" onclick="finish_field()">Finish</button>
				</div>
			</div>
		</div>
	</div>
<?php endif ?>

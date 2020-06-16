var field_count = 0; // Dynamic count of number of fields

/**************************************************** Non-Modal Functions */
function show_weekday() {
	var select = document.getElementById('type');
	var type = select.selectedOptions[0].text;
	if (type == 'Weekly') {
		document.getElementById('weekday_td').style.display = '';
		document.getElementById('weekday').style.display = '';
		document.getElementById('month_message').style.display = 'none';
	} else if (type == 'Monthly') {
		document.getElementById('weekday_td').style.display = '';
		document.getElementById('weekday').style.display = 'none';
		document.getElementById('month_message').style.display = '';
	} else {
		document.getElementById('weekday_td').style.display = 'none';
	}
}

$(document).ready(function() { // Initialize the timepicker
	$('.timepicker').timepicker({
		timeFormat: 'h:mm p',
		interval: 30,
		minTime: '0',
		maxTime: '11:30pm',
		defaultTime: '8:00AM',
		startTime: '00:00',
		dynamic: false,
		dropdown: true,
		scrollbar: true
	});
});

function remove_field(id) {
	var tbody, trs, x, tds;
	tbody = document.getElementById('fields_body');
	// Remove the table row
	tbody.deleteRow(id);
	field_count--;
	// Reorder all rows below the deleted row
	trs = tbody.getElementsByTagName('tr');
	for (x = id; x < trs.length; x++) {
		tds = trs[x].getElementsByTagName('td');
		// Reorder names field
		tds[0].firstElementChild.id = 'names[' + String(x) + ']';
		// Reorder fields field
		tds[1].firstElementChild.id = 'fields[' + String(x) + ']';
		// Reorder operations field
		tds[2].firstElementChild.id = 'operations[' + String(x) + ']';
		// Reorder cond field field
		tds[3].firstElementChild.id = 'cond_fields[' + String(x) + ']';
		// Reorder cond option field
		tds[4].firstElementChild.id = 'cond_options[' + String(x) + ']';
		// Reorder delete button
		tds[5].firstElementChild.setAttribute('onclick', 'remove_field(' + String(x) + ')');
	}
}

async function try_submit() {
	// Catch trying to create a report with no fields
	if (!document.getElementById('names[0]')) {
		alert('The report must have at least one field.');
		return;
	}

	var data = {};
	data['report_name'] = document.getElementById('report_name').value;
	data['static'] = document.getElementById('static').value;
	data['private'] = document.getElementById('private').checked;
	data['type'] = document.getElementById('type').selectedOptions[0].value;
	data['weekday'] = document.getElementById('weekday').selectedOptions[0].value;
	data['time'] = document.getElementById('time').value;

	if (!validate_time(data['time'])) {
		alert('Reports can only run on :00 or :30 times.');
		return false;
	}

	var fields = [];
	for (var i = 0; i < field_count; i++) {
		fields[i] = {};
		fields[i]['name'] = document.getElementById('names[' + i + ']').value;
		fields[i]['field'] = document.getElementById('fields[' + i + ']').value;
		fields[i]['operation'] = document.getElementById('operations[' + i + ']').value;
		fields[i]['cond_field'] = document.getElementById('cond_fields[' + i + ']').value;
		fields[i]['cond_option'] = document.getElementById('cond_options[' + i + ']').value;
	}

	data['fields'] = fields;

	const response = await fetch(window.location.origin + '/index.php/reports_api/create', {
		method: 'POST',
		body: JSON.stringify(data),
		headers:{
			'Content-Type': 'application/json'
		}
	});

	const myJson = await response.json(); // extract JSON from the http response

	alert(myJson);

	if (myJson == "Report created.")
		window.location.href = window.location.origin + '/index.php/reports/list';
}

/******************************************************** Modal Functions */
async function open_modal() { // Stage 0
	document.getElementById('department').selectedIndex = 0;
	hide_steps(0); // Hide all steps beyond Department
	// Clear out any old dropdowns
	document.getElementById('form_td').innerHTML = '';
	document.getElementById('field_td').innerHTML = '';
	document.getElementById('operation_td').innerHTML = '';
	document.getElementById('cond_field_td').innerHTML = '';
	document.getElementById('cond_option_td').innerHTML = '';
	// Clear out the name field
	document.getElementById('field_name').value = '';
	// Show the field modal
	$('#field_modal').modal('show');
}

async function get_forms() { // Stage 1
	// Grab the department id
	var department_id = document.getElementById('department').selectedOptions[0].value;

	// If changing back to "Please Select"
	if (department_id == -1) {
		hide_steps(0); // Hide all steps beyond Department
		return false;
	}

	var data = {};
	data['department_id'] = department_id;

	const response = await fetch(window.location.origin + '/index.php/reports_api/get_forms', {
		method: 'POST',
		body: JSON.stringify(data),
		headers:{
			'Content-Type': 'application/json'
		}
	});

	const myJson = await response.json(); // extract JSON from the http response

	if (myJson[0] != "[" && myJson[0] != "{") {
		alert(myJson);
	} else {
		hide_steps(1); // Show the form row and hide the others
		// Update the form dropdown
		var innerHTML = '<select class="form-control" id="form" onchange="get_fields()">';
		for (const [id, name] of Object.entries(JSON.parse(myJson)))
			innerHTML += "<option value='" + id + "'>" + name + "</option>";
		innerHTML += '</select>';
		document.getElementById('form_td').innerHTML = innerHTML;
	}
}

async function get_fields() { // Stage 2
	// Grab the form id
	var form_id = document.getElementById('form').selectedOptions[0].value;

	// If changing back to "Please Select"
	if (form_id == 0) {
		hide_steps(1); // Show the form row and hide the others
		return false;
	}

	var data = {};
	data['form_id'] = form_id;

	const response = await fetch(window.location.origin + '/index.php/reports_api/get_fields', {
		method: 'POST',
		body: JSON.stringify(data),
		headers:{
			'Content-Type': 'application/json'
		}
	});

	const myJson = await response.json(); // extract JSON from the http response

	if (myJson[0] != "[" && myJson[0] != "{") {
		alert(myJson);
	} else {
		hide_steps(2); // Show the form and field rows and hide the others
		// Update the field dropdown
		var innerHTML = '<select class="form-control" id="field" onchange="get_operations()">';
		for (const [id, name] of Object.entries(JSON.parse(myJson)))
			innerHTML += "<option value='" + id + "'>" + name + "</option>";
		innerHTML += '</select>';
		document.getElementById('field_td').innerHTML = innerHTML;
	}

	await get_cond_fields();
}

async function get_cond_fields() { // Stage 2.1
	// Grab the form id
	var form_id = document.getElementById('form').selectedOptions[0].value;

	var data = {};
	data['form_id'] = form_id;

	const response = await fetch(window.location.origin + '/index.php/reports_api/get_cond_fields', {
		method: 'POST',
		body: JSON.stringify(data),
		headers:{
			'Content-Type': 'application/json'
		}
	});

	const myJson = await response.json(); // extract JSON from the http response

	if (myJson[0] != "[" && myJson[0] != "{") {
		alert(myJson);
	} else {
		// Update the field dropdown
		var innerHTML = '<select class="form-control" id="cond_field" onchange="get_cond_options()">';
		innerHTML += "<option value='-1'>Please Select</option>"; // Change made in JS due to issues in PHP
		for (const [id, name] of Object.entries(JSON.parse(myJson)))
			innerHTML += "<option value='" + id + "'>" + name + "</option>";
		innerHTML += '</select>';
		document.getElementById('cond_field_td').innerHTML = innerHTML;
	}
}

async function get_operations() { // Stage 3
	// Grab the field id
	var field_id = document.getElementById('field').selectedOptions[0].value;

	// If changing back to "Please Select"
	if (field_id == 0) {
		hide_steps(2); // Show the form and field rows and hide the others
		return false;
	}

	var data = {};
	data['field_id'] = field_id;

	const response = await fetch(window.location.origin + '/index.php/reports_api/get_operations', {
		method: 'POST',
		body: JSON.stringify(data),
		headers:{
			'Content-Type': 'application/json'
		}
	});

	const myJson = await response.json(); // extract JSON from the http response

	if (myJson[0] != "[" && myJson[0] != "{") {
		alert(myJson);
	} else {
		hide_steps(3); // Show the form, field, and operation rows and hide the condition rows and finish button
		// Update the operation dropdown
		var innerHTML = '<select class="form-control" id="operation" onchange="show_cond_fields()">';
		innerHTML += "<option value='0'>Please Select</option>"; // Change made in JS due to issues in PHP
		for (const [id, name] of Object.entries(JSON.parse(myJson)))
			innerHTML += "<option value='" + id + "'>" + name + "</option>";
		innerHTML += '</select>';
		document.getElementById('operation_td').innerHTML = innerHTML;
	}
}

function show_cond_fields() { // Stage 4
	// If changing back to "Please Select"
	if (document.getElementById('operation').selectedOptions[0].value == 0)
		hide_steps(3); // Show the form, field, and operation rows and hide the condition rows and finish button
	else
		hide_steps(4); // Show the condition field row and all before it
}

async function get_cond_options() { // Stage 5 (Stage 7 if skipping)
	// Grab the field id
	var cond_field_id = document.getElementById('cond_field').selectedOptions[0].value;

	// If changing back to "Please Select"
	if (cond_field_id == -1) {
		hide_steps(4); // Show the condition field row and all before it
		return false;
	} else if (cond_field_id == 0) {
		hide_steps(7);
		return false;
	}

	var data = {};
	data['cond_field_id'] = cond_field_id;

	const response = await fetch(window.location.origin + '/index.php/reports_api/get_cond_options', {
		method: 'POST',
		body: JSON.stringify(data),
		headers:{
			'Content-Type': 'application/json'
		}
	});

	const myJson = await response.json(); // extract JSON from the http response

	if (myJson[0] != "[" && myJson[0] != "{") {
		alert(myJson);
	} else {
		hide_steps(5); // Show all but the finish button
		// Update the operation dropdown
		var innerHTML = '<select class="form-control" id="cond_option" onchange="show_finish()">';
		innerHTML += "<option value='0'>Please Select</option>"; // Change made in JS due to issues in PHP
		for (const [id, name] of Object.entries(JSON.parse(myJson)))
			innerHTML += "<option value='" + id + "'>" + name + "</option>";
		innerHTML += '</select>';
		document.getElementById('cond_option_td').innerHTML = innerHTML;
	}
}

function show_finish() { // Stage 6
	// If changing back to "Please Select"
	if (document.getElementById('cond_option').selectedOptions[0].value == 0)
		hide_steps(5);
	else
		hide_steps(6);
}

function finish_field() { // Finishing up
	var innerHTML;
	// Grab the report field's name
	var report_field_name = document.getElementById('field_name').value.trim();
	if (!report_field_name) {
		alert("The new field must have a name.");
		return false;
	}
	// Grab the dropdown values and names
	var field = document.getElementById('field');
	var operation = document.getElementById('operation');
	var cond_field = document.getElementById('cond_field');
	// Prevent trying to pull an empty condition option field
	if (cond_field.selectedOptions[0].value == 0)
		var cond_opt = '';
	else
		var cond_opt = document.getElementById('cond_option').selectedOptions[0].text
	// Find the table body
	var tbody = document.getElementById('fields_body');
	// Add a row at the end of the table
	var row = tbody.insertRow(-1);
	// Initialize each cell of the row
	var field_name_cell = row.insertCell(0);
	var field_cell = row.insertCell(1);
	var operation_cell = row.insertCell(2);
	var cond_field_cell = row.insertCell(3);
	var cond_opt_cell = row.insertCell(4);
	var remove_cell = row.insertCell(5);
	// Store the report field name
	field_name_cell.innerHTML = '<input type="text" class="form-control" id="names[' + field_count + ']" value="' + report_field_name + '"/>';
	// Store the field name and id
	innerHTML  = '<input type="hidden" id="fields[' + field_count + ']" value="' + field.selectedOptions[0].value + '"/>';
	innerHTML += '<input type="text" class="form-control" value="' + field.selectedOptions[0].text + '" DISABLED/>';
	field_cell.innerHTML = innerHTML;
	// Store the operation name and id
	innerHTML  = '<input type="hidden" id="operations[' + field_count + ']" value="' + operation.selectedOptions[0].value + '"/>';
	innerHTML += '<input type="text" class="form-control" value="' + operation.selectedOptions[0].text + '" DISABLED/>';
	operation_cell.innerHTML = innerHTML;
	// Store the condition field name and id
	innerHTML  = '<input type="hidden" id="cond_fields[' + field_count + ']" value="' + cond_field.selectedOptions[0].value + '"/>';
	innerHTML += '<input type="text" class="form-control" value="' + cond_field.selectedOptions[0].text + '" DISABLED/>';
	cond_field_cell.innerHTML = innerHTML;
	// Store the condition option name
	cond_opt_cell.innerHTML = '<input type="text" class="form-control" id="cond_options[' + field_count + ']" value="' + cond_opt + '" DISABLED/>';
	// Add remove button
	remove_cell.innerHTML = '<button type="button" class="btn btn-danger btn-sm" onclick="remove_field(' + field_count + ')">Delete</button>';
	// Increment the number of fields
	field_count++;
	// Hide the field modal
	$('#field_modal').modal('hide');
}

/******************************************************* Helper Functions */
function validate_time(time) {
	if (!time.match(/^([1-9]|1[012]):[03]0 [AP]M$/))
		return false;
	return true;
}

function hide_steps(stage) {
	var display;
	var tr_array = ['form_tr', 'field_tr', 'operation_tr', 'cond_field_tr', 'cond_option_tr', 'finish_field'];
	for (var i = 0; i < tr_array.length; i++) {
		if (i < stage)
			display = '';
		else
			display = 'none';
		document.getElementById(tr_array[i]).style.display = display;
	}
	// Catch for when "None" is selected for the condition field
	if (stage == 7) {
		document.getElementById('cond_option_tr').style.display = 'none';
		document.getElementById('finish_field').style.display = '';
	}
}

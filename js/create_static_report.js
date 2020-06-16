var field_count = 0; // Dynamic count of number of fields

/**************************************************** Non-Modal Functions */
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
		if (tds[5].firstElementChild) // Don't try to change the button if it's not there (Insert Date has no delete button)
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
	document.getElementById('cond_field_td').innerHTML = '';
	document.getElementById('cond_option_td').innerHTML = '';
	// Show the field modal
	$('#static_modal').modal('show');
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
		var innerHTML = '<select class="form-control" id="form" onchange="get_cond_fields()">';
		for (const [id, name] of Object.entries(JSON.parse(myJson)))
			innerHTML += "<option value='" + id + "'>" + name + "</option>";
		innerHTML += '</select>';
		document.getElementById('form_td').innerHTML = innerHTML;
	}
}

async function get_cond_fields() { // Stage 2
	// Grab the form id
	var form_id = document.getElementById('form').selectedOptions[0].value;

	// If changing back to "Please Select"
	if (form_id == 0) {
		hide_steps(1); // Hide all steps beyond Form
		return false;
	}

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
		hide_steps(2); // Show the form and field rows and hide the others
		// Update the field dropdown
		var innerHTML = '<select class="form-control" id="cond_field" onchange="get_cond_options()">';
		innerHTML += "<option value='-1'>Please Select</option>"; // Change made in JS due to issues in PHP
		for (const [id, name] of Object.entries(JSON.parse(myJson)))
			innerHTML += "<option value='" + id + "'>" + name + "</option>";
		innerHTML += '</select>';
		document.getElementById('cond_field_td').innerHTML = innerHTML;
	}
}

async function get_cond_options() { // Stage 3 (Stage 5 if skipping)
	// Grab the field id
	var cond_field_id = document.getElementById('cond_field').selectedOptions[0].value;

	// If changing back to "Please Select"
	if (cond_field_id == -1) {
		hide_steps(2); // Show the form and field rows and hide the others
		return false;
	} else if (cond_field_id == 0) {
		hide_steps(5);
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
		hide_steps(3); // Show all but the finish button
		// Update the operation dropdown
		var innerHTML = '<select class="form-control" id="cond_option" onchange="show_finish()">';
		innerHTML += "<option value='0'>Please Select</option>"; // Change made in JS due to issues in PHP
		for (const [id, name] of Object.entries(JSON.parse(myJson)))
			innerHTML += "<option value='" + id + "'>" + name + "</option>";
		innerHTML += '</select>';
		document.getElementById('cond_option_td').innerHTML = innerHTML;
	}
}

function show_finish() { // Stage 4
	// If changing back to "Please Select"
	if (document.getElementById('cond_option').selectedOptions[0].value == 0)
		hide_steps(3);
	else
		hide_steps(4);
}

async function finish_condition() { // Finishing up
	var data = {};
	data['form_id'] = document.getElementById('form').selectedOptions[0].value;

	const response = await fetch(window.location.origin + '/index.php/reports_api/use_fields', {
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
		// Declare looped variables
		var row, field_name_cell, field_cell, operation_cell, cond_field_cell, cond_opt_cell, remove_cell, innerHTML;
		// Find the table body
		var tbody = document.getElementById('fields_body');
		// Grab the condition field and option to use in generating fields in the table
		var cond_field = document.getElementById('cond_field');
		// Prevent trying to pull an empty condition option field
		if (cond_field.selectedOptions[0].value == 0)
			var cond_opt = '';
		else
			var cond_opt = document.getElementById('cond_option').selectedOptions[0].text
		// Loop through the conditional form's fields
		for (const [id, name] of Object.entries(JSON.parse(myJson))) {
			// Add a row at the end of the table
			row = tbody.insertRow(-1);
			// Initialize each cell of the row
			field_name_cell = row.insertCell(0);
			field_cell = row.insertCell(1);
			operation_cell = row.insertCell(2);
			cond_field_cell = row.insertCell(3);
			cond_opt_cell = row.insertCell(4);
			remove_cell = row.insertCell(5);
			// Store the report field name - use the actual field name as default
			if (name == 'insert_date') // Force insert_date to be Insert Date
				field_name_cell.innerHTML = '<input type="text" class="form-control" id="names[' + field_count + ']" value="Insert Date" DISABLED/>';
			else
				field_name_cell.innerHTML = '<input type="text" class="form-control" id="names[' + field_count + ']" value="' + name + '"/>';
			// Store the field name and id
			innerHTML  = '<input type="hidden" id="fields[' + field_count + ']" value="' + id + '"/>';
			innerHTML += '<input type="text" class="form-control" value="' + name + '" DISABLED/>';
			field_cell.innerHTML = innerHTML;
			// Store the operation name and id (none, since static)
			innerHTML  = '<input type="hidden" id="operations[' + field_count + ']" value="0"/>';
			innerHTML += '<input type="text" class="form-control" value="" DISABLED/>';
			operation_cell.innerHTML = innerHTML;
			// Store the condition field name and id
			innerHTML  = '<input type="hidden" id="cond_fields[' + field_count + ']" value="' + cond_field.selectedOptions[0].value + '"/>';
			innerHTML += '<input type="text" class="form-control" value="' + cond_field.selectedOptions[0].text + '" DISABLED/>';
			cond_field_cell.innerHTML = innerHTML;
			// Store the condition option name
			cond_opt_cell.innerHTML = '<input type="text" class="form-control" id="cond_options[' + field_count + ']" value="' + cond_opt + '" DISABLED/>';
			// Add remove button, except for insert_date
			if (name != 'insert_date')
				remove_cell.innerHTML = '<button type="button" class="btn btn-danger btn-sm" onclick="remove_field(' + field_count + ')">Delete</button>';
			// Increment the number of fields
			field_count++;
		}
	}
	// Hide the modal
	$('#static_modal').modal('hide');
}

/******************************************************* Helper Functions */
function hide_steps(stage) {
	var display;
	var tr_array = ['form_tr', 'cond_field_tr', 'cond_option_tr', 'finish_condition'];
	for (var i = 0; i < tr_array.length; i++) {
		if (i < stage)
			display = '';
		else
			display = 'none';
		document.getElementById(tr_array[i]).style.display = display;
	}
	// Catch for when "None" is selected for the condition field
	if (stage == 5) {
		document.getElementById('cond_option_tr').style.display = 'none';
		document.getElementById('finish_condition').style.display = '';
	}
}

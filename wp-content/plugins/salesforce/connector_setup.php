<style>

#object_name {
	padding: 10px;
	margin-bottom: 30px;
}

.salesforce_information label {
	padding: 10px;
}

</style>
<div class="connector_items"></div>
<div class="salesforce_information">
	<label for="salesforce_object">Object:</label>
	<select id="salesforce_object"><option value="-1">Select object...</option></select>
	<div id="object_name"></div>
	<table class="widefat fixed" cellspacing="0" id="salesforce_object_details">
		<thead>
			<tr><th class="manage-column column-columnname" scope="col">Field Label</th><th class="manage-column column-columnname" scope="col">Field Name</th></tr>
		</thead>
		<tbody>
			<tr><td colspan="2">Select an object from the dropdown to get started.</td></tr>
		</tbody>
	</table>
</div>


<script type="text/javascript">

var object_types = <?php echo json_encode( ggsf_get_objects() ) ?>;
var connector_types = Array(
	{
		name: 'Lookup',
		settings: []
	},
	{
		name: 'Update',
		settings: []
	},
	{
		name: 'Create',
		settings: []
	}

);

function add_rule() {
	var new_rule = jQuery('<div>');

	// Connector Type
	var connector_type = jQuery('<select>').appendTo(new_rule);
	for (var I = 0; I < connector_types.length; I++) {
		jQuery('<option>').html( connector_types[I].name ).appendTo(connector_type);
	}

	// Object
	var object = jQuery('<select>').appendTo(new_rule);
	for (var I = 0; I < object_types.length; I++) {
		jQuery('<option value="' + object_types[I].name + '">').html( object_types[I].label ).appendTo(object);
	}

	jQuery('.connector_items').append(new_rule);
	
	return false;
}

function fill_salesforce_info() {
	for (var I = 0; I < object_types.length; I++) {
		jQuery('<option value="' + object_types[I].name + '">').html( object_types[I].label ).appendTo( jQuery('#salesforce_object') );
	}
}

function display_object_info() {
	var data = {
		'action': 'salesforce_object_info',
		'object': jQuery('#salesforce_object').val()
	};

	for (var I = 0; I < object_types.length; I++) {
		if ( object_types[I].name == jQuery('#salesforce_object').val() ) {
			jQuery('#object_name').html('Object Name: ' +  object_types[I].name );
		}
	}

	jQuery.post(ajaxurl, data, function(response) {
		response = jQuery.parseJSON(response);
		var table = jQuery('#salesforce_object_details tbody');
		table.empty();
		var the_class = '';
		for (var I = 0; I < response.fields.length; I++ ) {
			if (the_class == 'alternate') the_class = ''
			else the_class = 'alternate';
			var oTR = jQuery('<tr>').addClass(the_class).appendTo(table);
			var oTD = jQuery('<td>').html(response.fields[I].label).appendTo(oTR);
			var oTD = jQuery('<td>').html(response.fields[I].name).appendTo(oTR);
		}
	});
}

jQuery( document ).ready(function($) {
	$('#salesforce_object').on('change', display_object_info);
	fill_salesforce_info();
	//$('.connector_add').on('click.ggsf', add_rule);
});

</script>

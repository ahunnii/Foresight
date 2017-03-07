function changeSelection(table_id, row, value, color) {
	if (document.table_selections == null) {
		document.table_selections = new Array();
	}
	selections = document.table_selections;
	
	var table_properties = selections[table_id];
	if (table_properties == null) {
		table_properties = new Object();
		table_properties.table_id = table_id;
		selections[table_id] = table_properties;
	}
	
	// Reset row color
	table_properties.selected_row.style.backgroundColor = 
		document.getElementById(table_id).style.backgroundColor;
	
	table_properties.selected_row = row;
	table_properties.value = value;
	row.backgroundColor = color;
}

function selectedValue(table_id) {
	if (document.table_selections) {
		table_properties = document.table_selections[table_id];
		if (table_properties) 
			return table_properties.value;
	}
	return null;
}
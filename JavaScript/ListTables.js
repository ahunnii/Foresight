var listTables = null;

/**
 * Initializes all ListTables on the current page to 
 * support row selection & value retrieval.
 */
function initListTables() {
	listTables = new Array();
	$('table.ListTable').each(function (index, element) {
		var tp = new Object();
		tp.selectedRow = null;
		tp.value = null;
		tp.onRowClick = null;
		listTables[element.id] = tp;
			
		// Set click handler for table rows
		$(this).find('tr').click(function () {
			var id = $(this).closest('table').attr('id');
			var parent = listTables[id];
			
			// Deslect current row
			var row = parent.selectedRow; 
			if (row) {
				row.style.backgroundColor = '#DDDDDD';
				row.style.color = '#000000';
			}
			
			// Select new row
			parent.selectedRow = this;
			parent.value = this.getAttribute('rowValue');
			this.style.backgroundColor = '#666666';
			this.style.color = '#FFFFFF';
			
			// Call custom click listener if set
			if (parent.onRowClick !== null) {
				parent.onRowClick(this);
			}
		});
		
		// Set hover handlers for table rows
		$(this).find('tr').hover(
			function () { // MouseEnter
				this.style.backgroundColor = '#666666';
				this.style.color = 'white';
			},
			function () { // MouseLeave
				var id = $(this).closest('table').attr('id');
				var parent = listTables[id];
				if (parent === null || parent.selectedRow !== this) {
					this.style.backgroundColor = '#DDDDDD';
					this.style.color = '#000000';
				}
			}
		);
	});

}

/**
 * Returns the selected value of the specified ListTable.
 * @param tableId Id of the table element to reference
 */
function getListTableValue(tableId) {
	var table = listTables[tableId];
	if (table) {
		return table.value;
	} else {
		return null;
	}
}

function setRowClickListener(tableId, onClick) {
	listTables[tableId].onRowClick = onClick;
}

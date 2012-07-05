jQuery(document).ready(function($) {
	/**
	 * Globals
	 */
	$smallWidth = 300;
	$bigWidth = 800;

	/**
	 * Update Button
	 */
	function updateButtonText ($newText){
		$('#pods-parts-submit').val($newText);
	}

	/**
	 * Dialog Box Population
	 */
	function populateEditPages() {
		$newHTML = '<div id="pods-parts-search"><label for="pods-parts-search-box">Search:</label><input id="pods-parts-search-box" name="pods-parts-search-box" type="text" /><input id="pods-parts-search-box-submit" name="pods-parts-search-box-submit" class="button-secondary" value="Search Pages" /></div>';
		$newHTML+= '<div class="clear"></div>';
		$newHTML+= '<div class="tablenav top lightgraybackground">';
		$newHTML+= '<div id="pods-parts-display-per-page" class="left">';
		$newHTML+= '<label for="pods-parts-display-per-page-select">Display Per Page:</label>';
		$newHTML+= '<select id="pods-parts-display-per-page-select" name="pods-parts-display-per-page-select">';
		$newHTML+= '<option value="40">40</option>';
		$newHTML+= '<option value="20">20</option>';
		$newHTML+= '<option value="10">10</option>';
		$newHTML+= '</select>';
		$newHTML+= '</div>';
		$newHTML+= '<div class="pods-parts-pagination">';
		$newHTML+= '<div class="tablenav-pages">';
		$newHTML+= '<span class="pagination-links">';
		$newHTML+= '<a class="first-page disabled" href="#" title="Go to the first page">&laquo;</a>';
		$newHTML+= '<a class="prev-page disabled" href="#" title="Go to the previous page">‹</a>';
		$newHTML+= '<span class="paging-input">';
		$newHTML+= '<input class="current-page" type="text" size="2" value="1" name="paged" title="Current page" />';
		$newHTML+= ' of ';
		$newHTML+= '<span class="total-pages">10 </span>';
		$newHTML+= '</span>';
		$newHTML+= '<a class="next-page" href="#" title="Go to the next page">›</a>';
		$newHTML+= '<a class="last-page" href="#" title="Go to the last page">»</a>';
		$newHTML+= '</span>';
		$newHTML+= '</div>';
		$newHTML+= '</div>';
		$newHTML+= '</div>';
		$newHTML+= '<table style="width: 100%">';
		$newHTML+= '<tr>';
		$newHTML+= '<td>';
		$newHTML+= '<div class="bluehighlight">';
		$newHTML+= '<span class="blue"><a href="#">file/photo/single</a></span>';
		$newHTML+= '<span class="gray">Display</span><span class="actions"><a href="#" class="editpodspart">Edit</a> | <a href="#" class="deletepodspart">Delete</a></span>';
		$newHTML+= '</div>';
		$newHTML+= '</td>';
		$newHTML+= '<td>';
		$newHTML+= '<div class="bluehighlight">';
		$newHTML+= '<span class="blue"><a href="#">file/photo/single</a></span>';
		$newHTML+= '<span class="gray">Display</span><span class="actions"><a href="#" class="editpodspart">Edit</a> | <a href="#" class="deletepodspart">Delete</a></span>';
		$newHTML+= '</div>';
		$newHTML+= '</td>';
		$newHTML+= '<td>';
		$newHTML+= '<div class="bluehighlight">';
		$newHTML+= '<span class="blue"><a href="#">file/photo/single</a></span>';
		$newHTML+= '<span class="gray">Display</span><span class="actions"><a href="#" class="editpodspart">Edit</a> | <a href="#" class="deletepodspart">Delete</a></span>';
		$newHTML+= '</div>';
		$newHTML+= '</td>';
		$newHTML+= '<td>';
		$newHTML+= '<div class="bluehighlight">';
		$newHTML+= '<span class="blue"><a href="#">file/photo/single</a></span>';
		$newHTML+= '<span class="gray">Display</span><span class="actions"><a href="#" class="editpodspart">Edit</a> | <a href="#" class="deletepodspart">Delete</a></span>';
		$newHTML+= '</div>';
		$newHTML+= '</td>';
		$newHTML+= '</tr>';
		$newHTML+= '<tr>';
		$newHTML+= '<td>';
		$newHTML+= '<div class="bluehighlight">';
		$newHTML+= '<span class="blue"><a href="#">file/photo/single</a></span>';
		$newHTML+= '<span class="gray">Display</span><span class="actions"><a href="#" class="editpodspart">Edit</a> | <a href="#" class="deletepodspart">Delete</a></span>';
		$newHTML+= '</div>';
		$newHTML+= '</td>';
		$newHTML+= '<td>';
		$newHTML+= '<div class="bluehighlight">';
		$newHTML+= '<span class="blue"><a href="#">file/photo/single</a></span>';
		$newHTML+= '<span class="gray">Display</span><span class="actions"><a href="#" class="editpodspart">Edit</a> | <a href="#" class="deletepodspart">Delete</a></span>';
		$newHTML+= '</div>';
		$newHTML+= '</td>';
		$newHTML+= '<td>';
		$newHTML+= '<div class="bluehighlight">';
		$newHTML+= '<span class="blue"><a href="#">file/photo/single</a></span>';
		$newHTML+= '<span class="gray">Display</span><span class="actions"><a href="#" class="editpodspart">Edit</a> | <a href="#" class="deletepodspart">Delete</a></span>';
		$newHTML+= '</div>';
		$newHTML+= '</td>';
		$newHTML+= '<td>';
		$newHTML+= '<div class="bluehighlight">';
		$newHTML+= '<span class="blue"><a href="#">file/photo/single</a></span>';
		$newHTML+= '<span class="gray">Display</span><span class="actions"><a href="#" class="editpodspart">Edit</a> | <a href="#" class="deletepodspart">Delete</a></span>';
		$newHTML+= '</div>';
		$newHTML+= '</td>';
		$newHTML+= '</tr>';
		$newHTML+= '</table>';
		$newHTML+= '<div class="tablenav top lightgraybackground">';
		$newHTML+= '<div id="pods-parts-display-per-page" class="left">';
		$newHTML+= '<label for="pods-parts-display-per-page-select">Display Per Page</label>';
		$newHTML+= '<select id="pods-parts-display-per-page-select" name="pods-parts-display-per-page-select">';
		$newHTML+= '<option value="40">40</option>';
		$newHTML+= '<option value="20">20</option>';
		$newHTML+= '<option value="10">10</option>';
		$newHTML+= '</select>';
		$newHTML+= '</div>';
		$newHTML+= '<div class="pods-parts-pagination">';
		$newHTML+= '<div class="tablenav-pages">';
		$newHTML+= '<span class="pagination-links">';
		$newHTML+= '<a class="first-page disabled" href="#" title="Go to the first page">&laquo;</a>';
		$newHTML+= '<a class="prev-page disabled" href="#" title="Go to the previous page">‹</a>';
		$newHTML+= '<span class="paging-input">';
		$newHTML+= '<input class="current-page" type="text" size="2" value="1" name="paged" title="Current page" />';
		$newHTML+= ' of ';
		$newHTML+= '<span class="total-pages">10 </span>';
		$newHTML+= '</span>';
		$newHTML+= '<a class="next-page" href="#" title="Go to the next page">›</a>';
		$newHTML+= '<a class="last-page" href="#" title="Go to the last page">»</a>';
		$newHTML+= '</span>';
		$newHTML+= '</div>';
		$newHTML+= '</div>';
		$newHTML+= '</div>';
		$('#pods-parts-popup').html($newHTML);
		updateButtonText('Update Pods Page');
	}

	function populateAddPage() {
		$newHTML = '<p>Create a new Pods Page by entering the name in the text field.</p>';
		$newHTML+= '<label for="pods-parts-dialog-page-new">Name:</label><input id="pods-parts-dialog-page-new" name="pods-parts-dialog-page-new" type="text" />';
		$newHTML+= '<input class="submitnew button-primary" type="" value="Add New Pods Page" />';
		$newHTML+= '<input type="hidden" value="newpodspage" />';

		$('#pods-parts-popup').html($newHTML);
		updateButtonText('Save New Pods Page');
	}

	function populateEditTemplates() {
		$newHTML = '<div id="pods-parts-search"><label for="pods-parts-search-box">Search:</label><input id="pods-parts-search-box" name="pods-parts-search-box" type="text" /><input id="pods-parts-search-box-submit" name="pods-parts-search-box-submit" class="button-secondary" value="Search Templates" /></div>';
		$newHTML+= '<div class="clear"></div>';
		$newHTML+= '<div class="tablenav top lightgraybackground">';
		$newHTML+= '<div id="pods-parts-display-per-page" class="left">';
		$newHTML+= '<label for="pods-parts-display-per-page-select">Display Per Page</label>';
		$newHTML+= '<select id="pods-parts-display-per-page-select" name="pods-parts-display-per-page-select">';
		$newHTML+= '<option value="40">40</option>';
		$newHTML+= '<option value="20">20</option>';
		$newHTML+= '<option value="10">10</option>';
		$newHTML+= '</select>';
		$newHTML+= '</div>';
		$newHTML+= '<div class="pods-parts-pagination">';
		$newHTML+= '<div class="tablenav-pages">';
		$newHTML+= '<span class="pagination-links">';
		$newHTML+= '<a class="first-page disabled" href="#" title="Go to the first page">&laquo;</a>';
		$newHTML+= '<a class="prev-page disabled" href="#" title="Go to the previous page">‹</a>';
		$newHTML+= '<span class="paging-input">';
		$newHTML+= '<input class="current-page" type="text" size="2" value="1" name="paged" title="Current page" />';
		$newHTML+= ' of ';
		$newHTML+= '<span class="total-pages">10 </span>';
		$newHTML+= '</span>';
		$newHTML+= '<a class="next-page" href="#" title="Go to the next page">›</a>';
		$newHTML+= '<a class="last-page" href="#" title="Go to the last page">»</a>';
		$newHTML+= '</span>';
		$newHTML+= '</div>';
		$newHTML+= '</div>';
		$newHTML+= '</div>';
		$newHTML+= '<table style="width: 100%">';
		$newHTML+= '<tr>';
		$newHTML+= '<td>';
		$newHTML+= '<div class="bluehighlight">';
		$newHTML+= '<span class="blue"><a href="#">file/photo/single</a></span>';
		$newHTML+= '<span class="gray">Display</span><span class="actions"><a href="#" class="editpodspart">Edit</a> | <a href="#" class="deletepodspart">Delete</a></span>';
		$newHTML+= '</div>';
		$newHTML+= '</td>';
		$newHTML+= '<td>';
		$newHTML+= '<div class="bluehighlight">';
		$newHTML+= '<span class="blue"><a href="#">file/photo/single</a></span>';
		$newHTML+= '<span class="gray">Display</span><span class="actions"><a href="#" class="editpodspart">Edit</a> | <a href="#" class="deletepodspart">Delete</a></span>';
		$newHTML+= '</div>';
		$newHTML+= '</td>';
		$newHTML+= '<td>';
		$newHTML+= '<div class="bluehighlight">';
		$newHTML+= '<span class="blue"><a href="#">file/photo/single</a></span>';
		$newHTML+= '<span class="gray">Display</span><span class="actions"><a href="#" class="editpodspart">Edit</a> | <a href="#" class="deletepodspart">Delete</a></span>';
		$newHTML+= '</div>';
		$newHTML+= '</td>';
		$newHTML+= '<td>';
		$newHTML+= '<div class="bluehighlight">';
		$newHTML+= '<span class="blue"><a href="#">file/photo/single</a></span>';
		$newHTML+= '<span class="gray">Display</span><span class="actions"><a href="#" class="editpodspart">Edit</a> | <a href="#" class="deletepodspart">Delete</a></span>';
		$newHTML+= '</div>';
		$newHTML+= '</td>';
		$newHTML+= '</tr>';
		$newHTML+= '<tr>';
		$newHTML+= '<td>';
		$newHTML+= '<div class="bluehighlight">';
		$newHTML+= '<span class="blue"><a href="#">file/photo/single</a></span>';
		$newHTML+= '<span class="gray">Display</span><span class="actions"><a href="#" class="editpodspart">Edit</a> | <a href="#" class="deletepodspart">Delete</a></span>';
		$newHTML+= '</div>';
		$newHTML+= '</td>';
		$newHTML+= '<td>';
		$newHTML+= '<div class="bluehighlight">';
		$newHTML+= '<span class="blue"><a href="#">file/photo/single</a></span>';
		$newHTML+= '<span class="gray">Display</span><span class="actions"><a href="#" class="editpodspart">Edit</a> | <a href="#" class="deletepodspart">Delete</a></span>';
		$newHTML+= '</div>';
		$newHTML+= '</td>';
		$newHTML+= '<td>';
		$newHTML+= '<div class="bluehighlight">';
		$newHTML+= '<span class="blue"><a href="#">file/photo/single</a></span>';
		$newHTML+= '<span class="gray">Display</span><span class="actions"><a href="#" class="editpodspart">Edit</a> | <a href="#" class="deletepodspart">Delete</a></span>';
		$newHTML+= '</div>';
		$newHTML+= '</td>';
		$newHTML+= '<td>';
		$newHTML+= '<div class="bluehighlight">';
		$newHTML+= '<span class="blue"><a href="#">file/photo/single</a></span>';
		$newHTML+= '<span class="gray">Display</span><span class="actions"><a href="#" class="editpodspart">Edit</a> | <a href="#" class="deletepodspart">Delete</a></span>';
		$newHTML+= '</div>';
		$newHTML+= '</td>';
		$newHTML+= '</tr>';
		$newHTML+= '</table>';
		$newHTML+= '<div class="tablenav top lightgraybackground">';
		$newHTML+= '<div id="pods-parts-display-per-page" class="left">';
		$newHTML+= '<label for="pods-parts-display-per-page-select">Display Per Page:</label>';
		$newHTML+= '<select id="pods-parts-display-per-page-select" name="pods-parts-display-per-page-select">';
		$newHTML+= '<option value="40">40</option>';
		$newHTML+= '<option value="20">20</option>';
		$newHTML+= '<option value="10">10</option>';
		$newHTML+= '</select>';
		$newHTML+= '</div>';
		$newHTML+= '<div class="pods-parts-pagination">';
		$newHTML+= '<div class="tablenav-pages">';
		$newHTML+= '<span class="pagination-links">';
		$newHTML+= '<a class="first-page disabled" href="#" title="Go to the first page">&laquo;</a>';
		$newHTML+= '<a class="prev-page disabled" href="#" title="Go to the previous page">‹</a>';
		$newHTML+= '<span class="paging-input">';
		$newHTML+= '<input class="current-page" type="text" size="2" value="1" name="paged" title="Current page" />';
		$newHTML+= ' of ';
		$newHTML+= '<span class="total-pages">10 </span>';
		$newHTML+= '</span>';
		$newHTML+= '<a class="next-page" href="#" title="Go to the next page">›</a>';
		$newHTML+= '<a class="last-page" href="#" title="Go to the last page">»</a>';
		$newHTML+= '</span>';
		$newHTML+= '</div>';
		$newHTML+= '</div>';
		$newHTML+= '</div>';
		$('#pods-parts-popup').html($newHTML);
		updateButtonText('Update Pods Template');
	}

	function populateAddTemplate() {
		$newHTML = '<p>Create a new Pods Template by entering the name in the text field.</p>';
		$newHTML+= '<label for="pods-parts-dialog-template-new">Name:</label><input id="pods-parts-dialog-template-new" name="pods-parts-dialog-template-new" type="text" />';
		$newHTML+= '<input class="submitnew button-primary" type="" value="Add New Pods Template" />';
		$newHTML+= '<input type="hidden" value="newpodstemplate" />';

		$('#pods-parts-popup').html($newHTML);
		updateButtonText('Save New Pods Template');
	}

	function populateEditHelpers() {
		$newHTML = '<div id="pods-parts-search"><label for="pods-parts-search-box">Search:</label><input id="pods-parts-search-box" name="pods-parts-search-box" type="text" /><input id="pods-parts-search-box-submit" name="pods-parts-search-box-submit" class="button-secondary" value="Search Helpers" /></div>';
		$newHTML+= '<div id="pods-parts-filter"><label for="pods-parts-filter-box">Pods Helper Type:</label>';
		$newHTML+= '<select id="pods-parts-filter-box" name="pods-parts-filter-box">';
		$newHTML+= '<option value="all">-- All Types --</option>';
		$newHTML+= '<option value="display">Display</option>';
		$newHTML+= '<option value="input">Input</option>';
		$newHTML+= '<option value="presave">Pre-Save</option>';
		$newHTML+= '<option value="postsave">Post-Save</option>';
		$newHTML+= '</select>';
		$newHTML+= '<input id="pods-parts-filter-submit" name="pods-parts-filter-submit" class="button-secondary" value="Filter" />';
		$newHTML+= '</div>';
		$newHTML+= '<div class="clear"></div>';
		$newHTML+= '<div class="tablenav top lightgraybackground">';
		$newHTML+= '<div id="pods-parts-display-per-page" class="left">';
		$newHTML+= '<label for="pods-parts-display-per-page-select">Display Per Page:</label>';
		$newHTML+= '<select id="pods-parts-display-per-page-select" name="pods-parts-display-per-page-select">';
		$newHTML+= '<option value="40">40</option>';
		$newHTML+= '<option value="20">20</option>';
		$newHTML+= '<option value="10">10</option>';
		$newHTML+= '</select>';
		$newHTML+= '</div>';
		$newHTML+= '<div class="pods-parts-pagination">';
		$newHTML+= '<div class="tablenav-pages">';
		$newHTML+= '<span class="pagination-links">';
		$newHTML+= '<a class="first-page disabled" href="#" title="Go to the first page">&laquo;</a>';
		$newHTML+= '<a class="prev-page disabled" href="#" title="Go to the previous page">‹</a>';
		$newHTML+= '<span class="paging-input">';
		$newHTML+= '<input class="current-page" type="text" size="2" value="1" name="paged" title="Current page" />';
		$newHTML+= ' of ';
		$newHTML+= '<span class="total-pages">10 </span>';
		$newHTML+= '</span>';
		$newHTML+= '<a class="next-page" href="#" title="Go to the next page">›</a>';
		$newHTML+= '<a class="last-page" href="#" title="Go to the last page">»</a>';
		$newHTML+= '</span>';
		$newHTML+= '</div>';
		$newHTML+= '</div>';
		$newHTML+= '</div>';
		$newHTML+= '<table style="width: 100%">';
		$newHTML+= '<tr>';
		$newHTML+= '<td>';
		$newHTML+= '<div class="bluehighlight">';
		$newHTML+= '<span class="blue"><a href="#">file/photo/single</a></span>';
		$newHTML+= '<span class="gray">Display</span><span class="actions"><a href="#" class="editpodspart">Edit</a> | <a href="#" class="deletepodspart">Delete</a></span>';
		$newHTML+= '</div>';
		$newHTML+= '</td>';
		$newHTML+= '<td>';
		$newHTML+= '<div class="bluehighlight">';
		$newHTML+= '<span class="blue"><a href="#">file/photo/single</a></span>';
		$newHTML+= '<span class="gray">Display</span><span class="actions"><a href="#" class="editpodspart">Edit</a> | <a href="#" class="deletepodspart">Delete</a></span>';
		$newHTML+= '</div>';
		$newHTML+= '</td>';
		$newHTML+= '<td>';
		$newHTML+= '<div class="bluehighlight">';
		$newHTML+= '<span class="blue"><a href="#">file/photo/single</a></span>';
		$newHTML+= '<span class="gray">Display</span><span class="actions"><a href="#" class="editpodspart">Edit</a> | <a href="#" class="deletepodspart">Delete</a></span>';
		$newHTML+= '</div>';
		$newHTML+= '</td>';
		$newHTML+= '<td>';
		$newHTML+= '<div class="bluehighlight">';
		$newHTML+= '<span class="blue"><a href="#">file/photo/single</a></span>';
		$newHTML+= '<span class="gray">Display</span><span class="actions"><a href="#" class="editpodspart">Edit</a> | <a href="#" class="deletepodspart">Delete</a></span>';
		$newHTML+= '</div>';
		$newHTML+= '</td>';
		$newHTML+= '</tr>';
		$newHTML+= '<tr>';
		$newHTML+= '<td>';
		$newHTML+= '<div class="bluehighlight">';
		$newHTML+= '<span class="blue"><a href="#">file/photo/single</a></span>';
		$newHTML+= '<span class="gray">Display</span><span class="actions"><a href="#" class="editpodspart">Edit</a> | <a href="#" class="deletepodspart">Delete</a></span>';
		$newHTML+= '</div>';
		$newHTML+= '</td>';
		$newHTML+= '<td>';
		$newHTML+= '<div class="bluehighlight">';
		$newHTML+= '<span class="blue"><a href="#">file/photo/single</a></span>';
		$newHTML+= '<span class="gray">Display</span><span class="actions"><a href="#" class="editpodspart">Edit</a> | <a href="#" class="deletepodspart">Delete</a></span>';
		$newHTML+= '</div>';
		$newHTML+= '</td>';
		$newHTML+= '<td>';
		$newHTML+= '<div class="bluehighlight">';
		$newHTML+= '<span class="blue"><a href="#">file/photo/single</a></span>';
		$newHTML+= '<span class="gray">Display</span><span class="actions"><a href="#" class="editpodspart">Edit</a> | <a href="#" class="deletepodspart">Delete</a></span>';
		$newHTML+= '</div>';
		$newHTML+= '</td>';
		$newHTML+= '<td>';
		$newHTML+= '<div class="bluehighlight">';
		$newHTML+= '<span class="blue"><a href="#">file/photo/single</a></span>';
		$newHTML+= '<span class="gray">Display</span><span class="actions"><a href="#" class="editpodspart">Edit</a> | <a href="#" class="deletepodspart">Delete</a></span>';
		$newHTML+= '</div>';
		$newHTML+= '</td>';
		$newHTML+= '</tr>';
		$newHTML+= '<tr>';
		$newHTML+= '<td>';
		$newHTML+= '<div class="bluehighlight">';
		$newHTML+= '<span class="blue"><a href="#">file/photo/single</a></span>';
		$newHTML+= '<span class="gray">Display</span><span class="actions"><a href="#" class="editpodspart">Edit</a> | <a href="#" class="deletepodspart">Delete</a></span>';
		$newHTML+= '</div>';
		$newHTML+= '</td>';
		$newHTML+= '<td>';
		$newHTML+= '<div class="bluehighlight">';
		$newHTML+= '<span class="blue"><a href="#">file/photo/single</a></span>';
		$newHTML+= '<span class="gray">Display</span><span class="actions"><a href="#" class="editpodspart">Edit</a> | <a href="#" class="deletepodspart">Delete</a></span>';
		$newHTML+= '</div>';
		$newHTML+= '</td>';
		$newHTML+= '<td>';
		$newHTML+= '<div class="bluehighlight">';
		$newHTML+= '<span class="blue"><a href="#">file/photo/single</a></span>';
		$newHTML+= '<span class="gray">Display</span><span class="actions"><a href="#" class="editpodspart">Edit</a> | <a href="#" class="deletepodspart">Delete</a></span>';
		$newHTML+= '</div>';
		$newHTML+= '</td>';
		$newHTML+= '<td>';
		$newHTML+= '<div class="bluehighlight">';
		$newHTML+= '<span class="blue"><a href="#">file/photo/single</a></span>';
		$newHTML+= '<span class="gray">Display</span><span class="actions"><a href="#" class="editpodspart">Edit</a> | <a href="#" class="deletepodspart">Delete</a></span>';
		$newHTML+= '</div>';
		$newHTML+= '</td>';
		$newHTML+= '</tr>';
		$newHTML+= '<tr>';
		$newHTML+= '<td>';
		$newHTML+= '<div class="bluehighlight">';
		$newHTML+= '<span class="blue"><a href="#">file/photo/single</a></span>';
		$newHTML+= '<span class="gray">Display</span><span class="actions"><a href="#" class="editpodspart">Edit</a> | <a href="#" class="deletepodspart">Delete</a></span>';
		$newHTML+= '</div>';
		$newHTML+= '</td>';
		$newHTML+= '<td>';
		$newHTML+= '<div class="bluehighlight">';
		$newHTML+= '<span class="blue"><a href="#">file/photo/single</a></span>';
		$newHTML+= '<span class="gray">Display</span><span class="actions"><a href="#" class="editpodspart">Edit</a> | <a href="#" class="deletepodspart">Delete</a></span>';
		$newHTML+= '</div>';
		$newHTML+= '</td>';
		$newHTML+= '<td>';
		$newHTML+= '<div class="bluehighlight">';
		$newHTML+= '<span class="blue"><a href="#">file/photo/single</a></span>';
		$newHTML+= '<span class="gray">Display</span><span class="actions"><a href="#" class="editpodspart">Edit</a> | <a href="#" class="deletepodspart">Delete</a></span>';
		$newHTML+= '</div>';
		$newHTML+= '</td>';
		$newHTML+= '<td>';
		$newHTML+= '<div class="bluehighlight">';
		$newHTML+= '<span class="blue"><a href="#">file/photo/single</a></span>';
		$newHTML+= '<span class="gray">Display</span><span class="actions"><a href="#" class="editpodspart">Edit</a> | <a href="#" class="deletepodspart">Delete</a></span>';
		$newHTML+= '</div>';
		$newHTML+= '</td>';
		$newHTML+= '</tr>';
		$newHTML+= '</table>';
		$newHTML+= '<div class="tablenav top lightgraybackground">';
		$newHTML+= '<div id="pods-parts-display-per-page" class="left">';
		$newHTML+= '<label for="pods-parts-display-per-page-select">Display Per Page</label>';
		$newHTML+= '<select id="pods-parts-display-per-page-select" name="pods-parts-display-per-page-select">';
		$newHTML+= '<option value="40">40</option>';
		$newHTML+= '<option value="20">20</option>';
		$newHTML+= '<option value="10">10</option>';
		$newHTML+= '</select>';
		$newHTML+= '</div>';
		$newHTML+= '<div class="pods-parts-pagination">';
		$newHTML+= '<div class="tablenav-pages">';
		$newHTML+= '<span class="pagination-links">';
		$newHTML+= '<a class="first-page disabled" href="#" title="Go to the first page">&laquo;</a>';
		$newHTML+= '<a class="prev-page disabled" href="#" title="Go to the previous page">‹</a>';
		$newHTML+= '<span class="paging-input">';
		$newHTML+= '<input class="current-page" type="text" size="2" value="1" name="paged" title="Current page" />';
		$newHTML+= ' of ';
		$newHTML+= '<span class="total-pages">10 </span>';
		$newHTML+= '</span>';
		$newHTML+= '<a class="next-page" href="#" title="Go to the next page">›</a>';
		$newHTML+= '<a class="last-page" href="#" title="Go to the last page">»</a>';
		$newHTML+= '</span>';
		$newHTML+= '</div>';
		$newHTML+= '</div>';
		$newHTML+= '</div>';
		$('#pods-parts-popup').html($newHTML);
		updateButtonText('Update Pods Helper');
	}

	function populateAddHelper() {
		$newHTML = '<p>Create a new Pods Helper by entering the name in the text field and selecting what kind of helper it is.</p>';
		$newHTML+= '<label for="pods-parts-dialog-helper-new">Name:</label><input id="pods-parts-dialog-helper-new" name="pods-parts-dialog-helper-new" type="text" />';
		$newHTML+= '<label for="pods-parts-dialog-helper-new-type">Type:</label>';
		$newHTML+= '<select id="pods-parts-dialog-helper-new-type" name="pods-parts-dialog-helper-new-type">';
		$newHTML+= '<option value="display">Display</option>';
		$newHTML+= '<option value="input">Input</option>';
		$newHTML+= '<option value="presave">Pre-Save</option>';
		$newHTML+= '<option value="postsave">Post-Save</option>';
		$newHTML+= '</select>';
		$newHTML+= '<input class="submitnew button-primary" type="" value="Add New Pods Helper" />';
		$newHTML+= '<input type="hidden" value="newpodshelper" />';
		$('#pods-parts-popup').html($newHTML);
		updateButtonText('Save New Pods Helper');
	}

	/**
	 * Event Handlers
	 */
	$('#pods-parts-pages-edit').click(function(e){
		e.preventDefault();
		populateEditPages();
		$('#pods-parts-popup').dialog({title: 'Select Pods Page to Edit', width: $bigWidth}).dialog('open');
	});

	$('#pods-parts-pages-add').click(function(e){
		e.preventDefault();
		populateAddPage();
		$('#pods-parts-popup').dialog({title: 'Create New Pods Page', width: $smallWidth}).dialog('open');
	});

	$('#pods-parts-templates-edit').click(function(e){
		e.preventDefault();
		populateEditTemplates();
		$('#pods-parts-popup').dialog({title: 'Select Pods Template to Edit', width: $bigWidth}).dialog('open');
	});

	$('#pods-parts-templates-add').click(function(e){
		e.preventDefault();
		populateAddTemplate();
		$('#pods-parts-popup').dialog({title: 'Create New Pods Template', width: $smallWidth}).dialog('open');
	});

	$('#pods-parts-helpers-edit').click(function(e){
		e.preventDefault();
		populateEditHelpers();
		$('#pods-parts-popup').dialog({title: 'Select Pods Helper to Edit', width: $bigWidth}).dialog('open');
	});

	$('#pods-parts-helpers-add').click(function(e){
		e.preventDefault();
		populateAddHelper();
		$('#pods-parts-popup').dialog({title: 'Create New Pods Helper', width: $smallWidth}).dialog('open');
	});

	/**
	 * Run Startup Commands
	 */
	$('#pods-parts-popup').dialog({
		autoOpen: false,
		closeOnEscape: true,
		modal: true,
		dialogClass: 'dialogWithDropShadow',
		resizable: false,
		hide: 'fade',
		show: 'fade'
	})
});
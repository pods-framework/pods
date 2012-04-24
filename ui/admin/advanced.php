<div class="wrap pods-admin">
    <div id="icon-themes" class="icon32"></div><h2>Edit Pods Parts</h2>
    <form action="" method="post" class="pods pods-parts">
		<div id="poststuff" class="has-right-sidebar meta-box-sortables">
			<h4>Currently Editing: <span id="pods-currently-editing"><!-- This will be replaced via AJAX --></span></h4>
			<div id="pods-parts-right" class="pods right">
				<div id="side-sortables">
					<div id="pods-parts-pages" class="postbox">
						<h3><span>Pods Pages</span></h3>
						<div class="inside">
							<a href="#" id="pods-parts-pages-edit" class="pods-parts-icon-edit pods-parts-icon">Edit Pods Pages</a>
							<br />
							<a href="#" id="pods-parts-pages-add" class="pods-parts-icon-add pods-parts-icon">Add New Pods Page</a>
						</div>
					</div><!-- #pods-parts-pages -->
					<div id="pods-parts-templates" class="postbox">
						<h3><span>Pods Templates</span></h3>
						<div class="inside">
							<a href="#" id="pods-parts-templates-edit" class="pods-parts-icon-edit pods-parts-icon">Edit Pods Templates</a>
							<br />
							<a href="#" id="pods-parts-templates-add" class="pods-parts-icon-add pods-parts-icon">Add New Pods Template</a>
						</div>
					</div><!-- #pods-parts-templates -->
					<div id="pods-parts-helpers" class="postbox">
						<h3><span>Pods Helpers</span></h3>
						<div class="inside">
							<a href="#" id="pods-parts-helpers-edit" class="pods-parts-icon-edit pods-parts-icon">Edit Pods Helpers</a>
							<br />
							<a href="#" id="pods-parts-helpers-add" class="pods-parts-icon-add pods-parts-icon">Add New Pods Helper</a>
						</div>
					</div><!-- #pods-parts-helpers -->
				</div><!-- #side-sortables -->
			</div><!-- #pods-parts-right .pods .right -->
			<div id="pods-parts-left" class="pods left">
				<div id="pods-parts-content-editors">
					<textarea id="pods-currently-editing-content" class="editor-wide"></textarea>
					<br /><br />
					<input id="pods-parts-submit" name="pods-parts-submit" class="button-primary" type="submit" value="Update Helper" />
				</div><!-- #pods-parts-content-editors -->
			</div><!-- #pods-parts-left .pods .left -->
			<div class="clear"></div>
		</div>
    </form>
	<div id="pods-parts-popup" style="display: none;"></div>
</div>
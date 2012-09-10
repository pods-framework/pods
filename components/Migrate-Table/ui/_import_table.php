<?php
$allTables = PodsData::get_tables( false, false );
global $wpdb;

$prefixedTables = array();
$unprefixedTables = array();

foreach ( $allTables as $eachTable ) {
    if ( strpos( $eachTable, $wpdb->prefix ) === 0 ) {
        $prefixedTables[] = $eachTable;
    }
    else {
        $unprefixedTables[] = $eachTable;
    }
}
?>

<?php if ( isset( $_GET[ 'message' ] ) ): ?>
<div id="message" class="fade error"><?php echo $_GET[ 'message' ]; ?></div>
<?php endif; ?>

<div class="wrap pods-admin">
    <div id="icon-options-general" class="icon32"><br /></div>
    <form id="pods-import-table-selection" class="pods pods-fields" action="/wp-admin/admin.php?page=pods-import-convert-fields" method="post">
        <h2>Import a Table To Pods</h2>

        <div id="poststuff" class="has-right-sidebar meta-box-sortables">
            <div id="side-info-field" class="inner-sidebar pods_floatmenu">
                <div id="side-sortables">
                    <div id="submitdiv" class="postbox">
                        <h3><span>Manage</span></h3>

                        <div class="inside">
                            <div class="submitbox" id="submitpost">
                                <div id="import-table-progress"><span>Select a Table.</span></div>
                                <div id="major-publishing-actions">
                                    <div id="delete-action">
                                        <a class="submitdelete deletion" href="/wp-admin/admin.php?page=pods-import-table">Start Over</a>
                                    </div>
                                    <div id="publishing-action">
                                        <button id="continue-to-field-selection"
                                            class="button-primary pods-import-continue"
                                            disabled="disabled"
                                            type="submit">Continue
                                        </button>
                                    </div>
                                    <div class="clear"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /#submitdiv -->
                </div>
            </div>
            <!-- /inner-sidebar -->
            <div id="post-body">
                <div id="post-body-content">
                    <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis sit amet pellentesque tortor. Vivamus non sem sit amet metus dapibus hendrerit ac at lectus. Nunc libero neque, varius vitae luctus ac, semper molestie massa. In hendrerit, odio in lacinia bibendum, purus dolor condimentum dui, ac imperdiet quam ipsum non felis. Phasellus ornare sem ut mi varius vulputate. Aenean tempus sollicitudin felis. Aliquam sed dui ipsum, ut mattis turpis.

                        Quisque tempus pretium rutrum. Aliquam vestibulum sem in nunc scelerisque feugiat. Donec vel nulla sit amet felis bibendum commodo. Suspendisse non leo erat, sit amet lacinia nunc. Sed risus erat, malesuada non faucibus vitae, tempor et est. Etiam malesuada sodales elementum. Integer dolor dui, congue quis iaculis sodales, posuere vitae nulla.</p>

                    <h2 class="has-subheading">Select a Table</h2>

                    <h3 class="italic">If a table you're looking for isn't listed here, it's most likely either a table created by Pods already, or a core Wordpress table.</h3>

                    <div id="pods-pod-advanced-settings" class="postbox closed" style="margin-top:15px;">
                        <div class="handlediv" title="Click to toggle">
                            <br>
                        </div>
                        <h3><span>Choose a Table to Import to Pods</span></h3>

                        <div class="inside pods-form" style="display: block; ">
                            <?php wp_nonce_field( 'pods-import-table-selection', 'pods-import-table-selection-nonce' ); ?>
                        <div id="pods-manage-settings-wrapper" class="pods-field pods-pick">
                        <div id="tables-left-col">
                            <?php if ( count( $unprefixedTables ) > 5 ): ?>
                            <input type="text" id="filter-left-tables" class="filter-tables-input" name="filter-left-tables" value="Filter Tables" />
                            <?php endif; ?>


                        <div class="pods-pick-values pods-pick-checkbox">
                            <?php if ( !empty( $allTables ) ): ?>
                            <ul class="list-tables-left">
                                <?php foreach ( $unprefixedTables as $individualTable ): ?>
                                <li class="left-table">
                                    <div>
                                        <input type="checkbox"
                                            id="<?php echo $individualTable; ?>"
                                            name="import_table[<?php echo $individualTable; ?>]"
                                            value="<?php echo $individualTable; ?>"
                                            class="pods-importable-table" />
                                        <label for="<?php echo $individualTable; ?>"><?php echo $individualTable; ?></label>
                                    </div>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                                        </div>
                                    </div>


                                    <div id="tables-right-col">
                                        <?php if ( count( $prefixedTables ) > 10 ): ?>
                                        <input type="text" id="filter-right-tables" class="filter-tables-input" name="filter-right-tables" value="Filter Tables" />
                                        <br />
                                        <?php endif; ?>
                                        <ul class="list-tables-right">
                                            <div class="pods-pick-values pods-pick-checkbox">
                                                <?php foreach ( $prefixedTables as $individualTable ): ?>
                                                <li class="right-table">
                                                    <div>
                                                        <input type="checkbox"
                                                            id="<?php echo $individualTable; ?>"
                                                            name="import_table[<?php echo $individualTable; ?>]"
                                                            value="<?php echo $individualTable; ?>"
                                                            class="pods-importable-table" />
                                                        <label for="<?php echo $individualTable; ?>"><?php echo $individualTable; ?></label>
                                                    </div>
                                                </li>
                                                <?php endforeach; ?>
                                        </ul>
                                    </div>
                                </div>
                            <?php else: ?>
                            You have no tables that can be imported to Pods.
                            <?php endif; ?>
                        </div>
    </form>
</div>
<!-- /pods-manage-settings-wrapper -->
</div>
<!-- /inside -->
</div>
</div>
<!-- /#post-body-content -->
</div>
<!-- /#post-body -->
</div>
<!-- /poststuff -->
</div>

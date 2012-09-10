<?php
if ( empty( $_POST[ 'import_table' ] ) || !wp_verify_nonce( $_POST[ 'pods-import-table-selection-nonce' ], 'pods-import-table-selection' ) ) {
    // Handle error
    exit();
}
?>

<?php if ( isset( $_GET[ 'message' ] ) ): // pods_ui_message not included at this point  ?>
<div id="message" class="fade error"><?php echo $_GET[ 'message' ]; ?></div>
<?php endif; ?>

<?php
$selectedTable = key( $_POST[ 'import_table' ] );
$tableColumns = PodsData::get_table_columns( $selectedTable );
?>

<div class="wrap pods-admin">
    <div id="post-body-content" style="margin-right:400px;">
        <div class="wrap pods-admin">
            <h2>Convert Table Fields</h2>
            <hr />
            <div id="pods-part-left">
                <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis sit amet pellentesque tortor. Vivamus non sem sit amet metus dapibus hendrerit ac at lectus. Nunc libero neque, varius vitae luctus ac, semper molestie massa. In hendrerit, odio in lacinia bibendum, purus dolor condimentum dui, ac imperdiet quam ipsum non felis. Phasellus ornare sem ut mi varius vulputate. Aenean tempus sollicitudin felis. Aliquam sed dui ipsum, ut mattis turpis.

                    Quisque tempus pretium rutrum. Aliquam vestibulum sem in nunc scelerisque feugiat. Donec vel nulla sit amet felis bibendum commodo. Suspendisse non leo erat, sit amet lacinia nunc. Sed risus erat, malesuada non faucibus vitae, tempor et est. Etiam malesuada sodales elementum. Integer dolor dui, congue quis iaculis sodales, posuere vitae nulla.</p>

                <h3>Converting <?php echo $selectedTable; ?> ....</h3>

                <div id="poststuff">

                    <div id="pods-pod-advanced-settings" class="postbox closed">
                        <div class="handlediv" title="Click to toggle">
                            <br>
                        </div>
                        <h3><span>Select Column Fields, and their Pod Field Type</span></h3>

                        <div class="inside pods-form" style="display: block; ">
                            <form id="pods-import-create-pod" action="/wp-admin/admin.php?page=pods-import-create-pod&table=<?php echo $selectedTable; ?>" method="post">
                                <?php wp_nonce_field( 'pods-import-create-pod', 'pods-import-create-pod-nonce' ); ?>
                                <ul>
                                    <?php foreach ( $tableColumns as $colName => $colType ): ?>

                                    <li>
                                        <table>
                                            <tr class="pod-column-row enabled">
                                                <td width="120">
                                                    <?php echo $colName; ?>
                                                </td>
                                                <td width="65">
                                                    <?php echo $colType; ?>
                                                </td>
                                                <td width="80">
                                                    <?php

                                                    $columnData = PodsData::get_column_data( $colName, $selectedTable );

                                                    $colIsKey = ( $columnData[ 'Key' ] == '' ) ? '' : ' - <strong>Key</strong>';
                                                    $colDefault = ( $columnData[ 'Default' ] === null ) ? 'null' : $columnData[ 'Default' ];
                                                    $colExtra = ( $columnData[ 'Extra' ] != '' ) ? 'Extra: ' . $columnData[ 'Extra' ] : '';

                                                    $columnInfo = <<<EOI
`{$colName}` {$columnData[ 'Type' ]}{$colIsKey}<br />
Default Value: {$colDefault}<br />
{$colExtra}
EOI;

                                                    ?>
                                                    <?php pods_help( $columnInfo ); ?>
                                                </td>

                                                <td><img src="<?php echo PODS_URL; ?>ui/images/arrow-right.png" width="12" height="12" style="vertical-align:middle;" />
                                                    <input type="text" name="pod_cols[<?php echo $colName; ?>]" value="<?php echo $colName; ?>" />
                                                </td>
                                                <?php $api = pods_api(); ?>
                                                <td>
                                                    <select name="pod_col_types[<?php echo $colName; ?>]">
                                                        <?php foreach ( $api->get_pods_field_types() as $fieldName => $fieldLabel ): ?>
                                                        <?php $selected = ( PodsApi::detect_pod_field_from_sql_data_type( $colType ) == $fieldName ) ? 'selected' : ''; ?>
                                                        <option value="<?php echo $fieldName; ?>" <?php echo $selected; ?>><?php echo $fieldLabel; ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </td>
                                                <td class="enabled-status enabled status-switcher"></td>
                                            </tr>
                                        </table>

                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                        </div>
                        <!-- /pods-manage-settings-wrapper -->
                    </div>
                    <!-- /inside -->

                    <div id="pods-pod-advanced-settings" class="postbox closed">
                        <div class="handlediv" title="Click to toggle">
                            <br>
                        </div>
                        <h3><span>Pod Details</span></h3>

                        <div class="inside pods-form" style="display: block; ">
                            <ul>
                                <li>
                                    <label class="pod-detail">Pod is a Top level menu item?</label>
                                    <input type="checkbox" class="new-pod-data" name="new_pod_data[top_level_menu]" />
                                </li>
                                <li>
                                    <label class="pod-detail">Pod Name</label>
                                    <input type="text" class="new-pod-data" name="new_pod_data[pod_name]" /><?php pods_help( 'Lowercase letters and underscores only.' ); ?>
                                </li>
                                <li>
                                    <label class="pod-detail">Pod Label</label>
                                    <input type="text" class="new-pod-data" name="new_pod_data[pod_label]" />
                                </li>
                            </ul>
                            </form>
                        </div>
                        <!-- /pods-manage-settings-wrapper -->
                    </div>
                </div>

                <a class="button-secondary" id="pods-import-create-pod" style="float:right;">Create Pod</a>

            </div>
        </div>
    </div>
</div>

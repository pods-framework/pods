<?php
    // handle form submission
?>
<form action="<?php echo pods_var_update( array( '_p_submitted' => 1 ) ); ?>" method="post" class="pods-manage pod-event">
	<div id="post-body">
		<div id="post-body-content">
			<div class="inside">
				<ul class="form-fields">
                    <?php
                        foreach ( $fields as $field ) {
                    ?>
                        <li class="pods-field <?php echo 'pods-form-ui-row-type-' . $type . ' pods-form-ui-row-name-' . Podsform::clean( $name, true ); ?>">
                            <?php PodsForm::row( $field[ 'name' ], $pod->field( $field[ 'name' ] ), $field[ 'type' ], $field[ 'options' ], $pod, $pod->id() ); ?>
                        </li>
                    <?php
                        }
                    ?>
		        </ul>
            </div>
        </div>
	</div>
</form>

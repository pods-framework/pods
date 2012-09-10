<div id="pods-field-<?php echo $params[ 'name' ]; ?>" class="pods-slider-field"></div>
<div id="pods-field-<?php echo $params[ 'name' ]; ?>-amount-display" class="pods-slider-field-display" /></div>
<input name="pods-field-<?php echo $params[ 'name' ]; ?>-amount-hidden" id="pods-field-<?php echo $params[ 'name' ]; ?>-amount-hidden" type="hidden" value="" />
<script>
    jQuery( function ( $ ) {
        $( "#pods-field-<?php echo $params[ 'name' ]; ?>" ).slider( {
                                                                    <?php if ( !empty( $params[ 'options' ][ 'range' ] ) ) : ?>
                                    range :<?php echo $params[ 'options' ][ 'range' ]; ?>,
                                <?php else: ?>
            range : false,
            <?php endif; ?>

                                                                    <?php if ( !empty( $params[ 'options' ][ 'value' ] ) ) : ?>
                                    value :<?php echo $params[ 'options' ][ 'value' ]; ?>,
                                <?php else: ?>
            value : 0,
            <?php endif; ?>

                                                                    <?php if ( !empty( $params[ 'options' ][ 'range' ] ) ) : ?>
            <?php if ( !empty( $params[ 'options' ][ 'values' ] ) ) : ?>
                values : [<?php echo $params[ 'options' ][ 'values' ]; ?>],
                <?php else: ?>
                values : [0,
                    <?php if ( !empty( $params[ 'options' ][ 'maxnumber' ] ) ) : ?>
                        <?php echo $params[ 'options' ][ 'maxnumber' ]; ?>
                        <?php else: ?>
                          100
                        <?php endif; ?>
                ],
                <?php endif; ?>
            <?php endif; ?>

                                                                    <?php if ( !empty( $params[ 'options' ][ 'orientation' ] ) ): ?>
            orientation : "<?php echo $params[ 'options' ][ 'orientation' ]; ?>",
            <?php else : ?>
            orientation : "horizontal",
            <?php endif; ?>

                                                                    <?php if ( !empty( $params[ 'options' ][ 'minnumber' ] ) ) : ?>
                                    min :<?php echo $params[ 'options' ][ 'minnumber' ]; ?>,
                                <?php else: ?>
            min : 0,
            <?php endif; ?>

                                                                    <?php if ( !empty( $params[ 'options' ][ 'maxnumber' ] ) ) : ?>
                                    max :<?php echo $params[ 'options' ][ 'maxnumber' ]; ?>,
                                <?php else: ?>
            max : 100,
            <?php endif; ?>

                                                                    <?php if ( !empty( $params[ 'options' ][ 'step' ] ) ) : ?>
                                    step :<?php echo $params[ 'options' ][ 'step' ]; ?>,
                                <?php else: ?>
            step : 1,
            <?php endif; ?>

                                                                        slide : function ( event, ui ) {
                                                                        <?php if ( !empty( $params[ 'options' ][ 'range' ] ) ) : ?>
                                                                            $( "#pods-field-<?php echo $params[ 'name' ]; ?>-amount-hidden" ).val( ui.values[ 0 ] + " - " + ui.values[ 1 ] );
                                                                            $( "#pods-field-<?php echo $params[ 'name' ]; ?>-amount-display" ).html( ui.values[ 0 ] + " - " + ui.values[ 1 ] );
                                                                            <?php else : ?>
                                                                            $( "#pods-field-<?php echo $params[ 'name' ]; ?>-amount-hidden" ).val( ui.value );
                                                                            $( "#pods-field-<?php echo $params[ 'name' ]; ?>-amount-display" ).html( ui.value );
                                                                            <?php endif; ?>
                                                                        }
                                                                    } );
    <?php if ( !empty( $params[ 'options' ][ 'range' ] ) ) : ?>
        $( "#pods-field-<?php echo $params[ 'name' ]; ?>-amount-hidden" ).val( $( "#pods-field-<?php echo $params[ 'name' ]; ?>" ).slider( "values", 0 ) + " - " + $( "#pods-field-<?php echo $params[ 'name' ]; ?>" ).slider( "values", 1 ) );
        $( "#pods-field-<?php echo $params[ 'name' ]; ?>-amount-display" ).html( $( "#pods-field-<?php echo $params[ 'name' ]; ?>" ).slider( "values", 0 ) + " - " + $( "#pods-field-<?php echo $params[ 'name' ]; ?>" ).slider( "values", 1 ) );
        <?php else : ?>
        $( "#pods-field-<?php echo $params[ 'name' ]; ?>-amount-hidden" ).val( $( "#pods-field-<?php echo $params[ 'name' ]; ?>" ).slider( "value" ) );
        $( "#pods-field-<?php echo $params[ 'name' ]; ?>-amount-display" ).html( $( "#pods-field-<?php echo $params[ 'name' ]; ?>" ).slider( "value" ) );
        <?php endif; ?>
    } );
</script>

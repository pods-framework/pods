<?php
class PodsField_Slug extends PodsField {

    /**
     * Field Type Identifier
     *
     * @var string
     * @since 2.0.0
     */
    protected static $type = 'slug';

    /**
     * Field Type Label
     *
     * @var string
     * @since 2.0.0
     */
    protected static $label = 'Permalink (url-friendly)';

    /**
     * Do things like register/enqueue scripts and stylesheets
     *
     * @since 2.0.0
     */
    public function __construct () {

    }

    /**
     * Customize output of the form field
     *
     * @param string $name
     * @param string $value
     * @param array $options
     * @param string $pod
     * @param int $id
     *
     * @since 2.0.0
     */
    public function input ( $name, $value = null, $options = null, $pod = null, $id = null ) {
        $options = (array) $options;

        pods_view( PODS_DIR . 'ui/fields/slug.php', compact( array_keys( get_defined_vars() ) ) );
    }

    /**
     * Change the value or perform actions after validation but before saving to the DB
     *
     * @param string $value
     * @param string $name
     * @param array $options
     * @param array $data
     * @param object $api
     * @param string $pod
     * @param int $id
     *
     * @since 2.0.0
     */
    public function pre_save ( &$value, $name, $options, $data, &$api, &$pod, $id = false ) {
        $value = pods_unique_slug( $value, $name, $pod );
    }
}
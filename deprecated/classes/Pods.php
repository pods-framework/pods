<?php
/**
 * @package Pods\Deprecated
 */
class Pods_Deprecated
{
    private $obj;

    var $id;

    var $data;

    var $datatype;

    var $datatype_id;

    /**
     * Constructor - Pods Deprecated functionality (pre 2.0)
     *
     * @param object $obj The Pods object
     * @license http://www.gnu.org/licenses/gpl-2.0.html
     * @since 2.0
     */
    public function __construct ( $obj ) {
        // backwards-compatibility with references to $this->var_name
        $vars = get_object_vars( $obj );

        foreach ( (array) $vars as $key => $val ) {
            $this->{$key} = $val;
        }

        // keeping references pointing back to the source
        $this->obj =& $obj;
    }

    /**
     * Set a custom data value (no database changes)
     *
     * @param string $name The field name
     * @param mixed $data The value to set
     *
     * @return mixed The value of $data
     * @since 1.2.0
     */
    public function set_field ( $name, $data = null ) {
        pods_deprecated( 'Pods::set_field', '2.0' );

        $this->obj->row[ $name ] = $data;

        return $this->obj->row[ $name ];
    }

    /**
     * Display HTML for all datatype fields
     *
     * @deprecated deprecated since 2.0
     */
    public function showform ( $id = null, $public_fields = null, $label = 'Save changes' ) {
        pods_deprecated( 'Pods::showform', '2.0' );

        $public_columns =& $public_fields;

        $pod = $this->obj->pod;
        $pod_id = $this->obj->pod_id;
        $this->obj->type_counter = array();

        if ( !empty( $public_fields ) ) {
            $attributes = array();

            foreach ( $public_fields as $key => $value ) {
                if ( is_array( $public_fields[ $key ] ) )
                    $attributes[ $key ] = $value;
                else
                    $attributes[ $value ] = array();
            }
        }

        $fields = $this->obj->fields;

        // Re-order the fields if a public form
        if ( !empty( $attributes ) ) {
            $fields = array();

            foreach ( $attributes as $key => $value ) {
                if ( isset( $this->obj->fields[ $key ] ) )
                    $fields[ $key ] = $this->obj->fields[ $key ];
            }
        }

        do_action( 'pods_showform_pre', $pod_id, $public_fields, $label, $this );

        foreach ( $fields as $key => $field ) {
            if ( !is_array( $field ) || in_array( $key, array( 'created', 'modified' ) ) )
                continue;

            // Pass options so they can be manipulated via form
            $field = array_merge( $field[ 'options' ], $field );

            // Replace field attributes with public form attributes
            if ( !empty( $attributes ) && is_array( $attributes[ $key ] ) )
                $field = array_merge( $field, $attributes[ $key ] );

            // Replace the input helper name with the helper code
            if ( !empty( $field[ 'input_helper' ] ) ) {
                $helper = $this->obj->api->load_helper( array( 'name' => $field[ 'input_helper' ] ) );
                $field[ 'input_helper' ] = '';

                if ( !empty( $helper ) )
                    $field[ 'input_helper' ] = $helper[ 'code' ];
            }

            if ( empty( $field[ 'label' ] ) )
                $field[ 'label' ] = ucwords( $key );

            if ( 1 == $field[ 'required' ] )
                $field[ 'label' ] .= ' <span class="red">*</span>';

            if ( !empty( $field[ 'pick_val' ] ) ) {
                $selected_ids = array();
                $pick_object = $field[ 'pick_object' ];
                $pick_val = $field[ 'pick_val' ];

                if ( 'pod' == $pick_object ) {
                    $pick_pod = $this->obj->api->load_pod( array( 'name' => $pick_val ) );
                    $pick_object = $pick_pod[ 'type' ];
                    $pick_val = $pick_pod[ 'name' ];
                }

                $pick_table = $pick_join = $pick_where = '';

                $pick_field_id = 'id';
                $pick_field_name = 'name';
                switch ( $pick_object ) {
                    case 'pod':
                        $pick_table = "@wp_pods_{$pick_val}";
                        $pick_field_id = 'id';
                        $pick_field_name = 'name';
                        break;
                    case 'post_type':
                        $pick_table = '@wp_posts';
                        $pick_field_id = 'ID';
                        $pick_field_name = 'post_title';
                        $pick_where = "t.`post_type` = '{$pick_val}'";
                        break;
                    case 'taxonomy':
                        $pick_table = '@wp_terms';
                        $pick_field_id = 'term_id';
                        $pick_field_name = 'name';
                        $pick_join = "`@wp_term_taxonomy` AS tx ON tx.`term_id` = t.`term_id";
                        $pick_where = "tx.`taxonomy` = '{$pick_val}' AND tx.`taxonomy` IS NOT NULL";
                        break;
                    case 'user':
                        $pick_table = '@wp_users';
                        $pick_field_id = 'ID';
                        $pick_field_name = 'user_login';
                        break;
                    case 'comment':
                        $pick_table = '@wp_comments';
                        $pick_field_id = 'comment_ID';
                        $pick_field_name = 'comment_date';
                        $pick_where = "t.`comment_type` = '{$pick_val}'";
                        break;
                    case 'table':
                        $pick_table = "{$pick_val}";
                        $pick_field_id = 'id';
                        $pick_field_name = 'name';
                        break;
                }

                $sql = "SELECT `related_item_id` FROM `@wp_podsrel` WHERE `item_id` = %d AND `field_id` = %d";

                $sql = array( $sql, array( $id, $field[ 'id' ] ) );

                $result = pods_query( $sql, $this );

                foreach ( $result as $row ) {
                    $selected_ids[] = $row->related_item_id;
                }

                // Use default values for public forms
                if ( empty( $selected_ids ) && !empty( $field[ 'default' ] ) ) {
                    $default_ids = $field[ 'default' ];

                    if ( !is_array( $field[ 'default' ] ) )
                        $default_ids = explode( ',', $default_ids );

                    foreach ( $default_ids as $default_id ) {
                        $default_id = pods_absint( $default_id );

                        if ( 0 < $default_id )
                            $selected_ids[] = $default_id;
                    }
                }

                // If the PICK field is unique, get values already chosen
                $exclude = false;

                if ( 1 == $field[ 'unique' ] ) {
                    $unique_where = ( empty( $id ) ) ? '' : " AND `item_id` != %d";

                    $sql = "SELECT `related_item_id` FROM `@wp_podsrel` WHERE `field_id` = %d {$unique_where}";

                    $sql = array( $sql, array( $field[ 'id' ] ) );

                    if ( !empty( $id ) )
                        $sql[ 1 ][] = $id;

                    $result = pods_query( $sql, $this );

                    if ( !empty( $result ) ) {

                        $exclude = array();

                        foreach ( $result as $row ) {
                            $exclude[] = (int) $row->related_item_id;
                        }

                        $exclude = implode( ',', $exclude );
                    }
                }

                if ( !empty( $field[ 'options' ][ 'pick_filter' ] ) )
                    $pick_where .= ' AND ' . $field[ 'options' ][ 'pick_filter' ];

                $params = array(
                    'exclude' => $exclude,
                    'selected_ids' => $selected_ids,
                    'table' => $pick_table,
                    'field_id' => $pick_field_id,
                    'field_name' => $pick_field_name,
                    'join' => $pick_join,
                    'orderby' => $field[ 'options' ][ 'pick_orderby' ],
                    'where' => $pick_where
                );

                $this->obj->row[ $key ] = $this->get_dropdown_values( $params );
            }
            else {
                // Set a default value if no value is entered
                if ( !isset( $this->obj->row[ $key ] ) || ( null === $this->obj->row[ $key ] || false === $this->obj->row[ $key ] ) ) {
                    if ( !empty( $field[ 'default' ] ) )
                        $this->obj->row[ $key ] = $field[ 'default' ];
                    else
                        $this->obj->row[ $key ] = null;
                }
            }

            $this->obj->build_field_html( $field );
        }

        $uri_hash = wp_hash( $_SERVER[ 'REQUEST_URI' ] );

        $save_button_atts = array(
            'type' => 'button',
            'class' => 'button btn_save',
            'value' => $label,
            'onclick' => "saveForm(1)"
        );

        $save_button_atts = apply_filters( 'pods_showform_save_button_atts', $save_button_atts, $this );
        $atts = '';

        foreach ( $save_button_atts as $att => $value ) {
            $atts .= ' ' . esc_attr( $att ) . '="' . esc_attr( $value ) . '"';
        }

        $save_button = '<input ' . $atts . '/>';
?>
    <div>
    <input type="hidden" class="form num id" value="<?php echo $id; ?>" />
    <input type="hidden" class="form txt pod" value="<?php echo $pod; ?>" />
    <input type="hidden" class="form txt pod_id" value="<?php echo $pod_id; ?>" />
    <input type="hidden" class="form txt form_count" value="1" />
    <input type="hidden" class="form txt token" value="<?php echo pods_generate_key($pod, $uri_hash, $public_fields, 1); ?>" />
    <input type="hidden" class="form txt uri_hash" value="<?php echo $uri_hash; ?>" />
    <?php echo apply_filters('pods_showform_save_button', $save_button, $save_button_atts, $this); ?>
    </div>
<?php
        do_action( 'pods_showform_post', $pod_id, $public_fields, $label, $this );
    }

    /**
     * Get pod or category drop-down values
     *
     * @param array $params
     *
     * @return array
     */
    public function get_dropdown_values ( $params ) {
        pods_deprecated( 'Pods::get_dropdown_values', '2.0' );

        global $wpdb;

        $params = (object) $params;

        $params->orderby = empty( $params->orderby ) ? '' : ' ORDER BY ' . $params->orderby;
        $params->join = empty( $params->join ) ? '' : ' LEFT JOIN ' . $params->join;

        $where = ( false !== $params->exclude ) ? "WHERE `t`.term_id NOT IN ({$params->exclude})" : '';

        if ( !empty( $params->pick_filter ) ) {
            $where .= ( empty( $where ) ? ' WHERE ' : ' AND ' ) . $params->pick_filter;
        }

        if ( !empty( $params->where ) ) {
            $where .= ( empty( $where ) ? ' WHERE ' : ' AND ' ) . $params->where;
        }

        $sql = "
            SELECT
                `t`.`{$params->field_id}` AS `id`,
                `t`.`{$params->field_name}` AS `name`
            FROM `{$params->table}` AS `t`
            {$params->join}
            {$where}
            {$params->orderby}
        ";

        //override with custom dropdown values
        $sql = apply_filters( 'pods_get_dropdown_values', $sql, $params, $this );

        $val = array();
        $result = pods_query( $sql );

        foreach ( $result as $row ) {
            $row = get_object_vars( $row );
            $row[ 'active' ] = false;

            if ( !empty( $params->selected_ids ) )
                $row[ 'active' ] = in_array( $row[ 'id' ], $params->selected_ids );

            $val[] = $row;
        }

        return $val;
    }

    /**
     * Build public input form
     *
     * @deprecated deprecated since 2.0
     */
    public function publicForm ( $fields = null, $label = 'Save Changes', $thankyou_url = null ) {
        pods_deprecated( 'Pods::publicForm', '2.0', 'Pods::form' );

        if ( !empty( $fields ) ) {
            // Just update field name here, form() will handle the rest
            foreach ( $fields as $k => $field ) {
                $name = $k;

                if ( !is_array( $field ) ) {
                    $name = $field;
                    $field = array();
                }
                elseif ( isset( $field[ 'name' ] ) )
                    $name = $field[ 'name' ];

                if ( in_array( $name, array( 'created', 'modified', 'author' ) ) && isset( $this->obj->fields[ $name . '2' ] ) )
                    $name .= '2';

                $field[ 'name' ] = $name;

                $fields[ $k ] = $field;
            }
        }

        echo $this->obj->form( $fields, $label, $thankyou_url );
    }

    /**
     * Build HTML for a single field
     *
     * @deprecated deprecated since 2.0
     */
    public function build_field_html ( $field ) {
        pods_deprecated( 'Pods::build_field_html', '2.0' );

        include PODS_DIR . 'deprecated/input_fields.php';
    }

    /**
     * Fetch a row of results from the DB
     *
     * @since 1.2.0
     * @deprecated deprecated since 2.0
     */
    public function fetchRecord () {
        pods_deprecated( 'Pods::fetchRecord', '2.0', 'Pods::fetch' );

        return $this->obj->fetch();
    }

    /**
     * Return a field's value(s)
     *
     * @param string $name The field name
     * @param string $orderby (optional) The orderby string, for PICK fields
     *
     * @since 1.2.0
     * @deprecated deprecated since version 2.0
     */
    public function get_field ( $name, $orderby = null ) {
        pods_deprecated( 'Pods::get_field', '2.0', 'Pods::field' );

        $value = $this->obj->field( array( 'name' => $name, 'orderby' => $orderby, 'deprecated' => true ) );

        if ( is_array( $value ) && !empty( $value ) ) {
            if ( false === strpos( $name, '.' ) && !isset( $value[ 0 ] ) )
                $value = array( $value ); // fix for single tableless fields
            elseif ( false !== strpos( $name, '.' ) && 1 == count( $value ) )
                $value = current( $value );
        }

        return $value;
    }

    /**
     * Get the current item's pod ID from its datatype ID and tbl_row_id
     *
     * @return int The ID from the wp_pod table
     * @since 1.2.0
     * @deprecated deprecated since version 2.0
     */
    public function get_pod_id () {
        pods_deprecated( 'Pods::get_pod_id', '2.0' );

        if ( !empty( $this->obj->row ) )
            return $this->obj->row[ $this->obj->data->field_id ];

        return false;
    }

    /**
     * Search and filter records
     *
     * @since 1.x
     * @deprecated deprecated since version 2.0
     */
    public function findRecords ( $orderby = null, $rows_per_page = 15, $where = null, $sql = null ) {
        pods_deprecated( 'Pods::findRecords', '2.0', 'Pods::find' );

        $find = array(
            ' p.created',
            '`p`.`created`',
            '`p`.created',
            ' p.`created`',
            ' p.modified',
            '`p`.`modified`',
            '`p`.modified',
            ' p.`modified`',
            ' p.id',
            '`p`.`id`',
            '`p`.id',
            ' p.`id`',
            ' p.pod_id',
            '`p`.`pod_id`',
            '`p`.pod_id',
            ' p.`pod_id`'
        );

        $replace = array(
            ' t.created',
            '`t`.`created`',
            '`t`.created',
            ' t.`created`',
            ' t.modified',
            '`t`.`modified`',
            '`t`.modified',
            ' t.`modified`',
            ' t.id',
            '`t`.`id`',
            '`t`.id',
            ' t.`id`',
            ' t.id',
            '`t`.`id`',
            '`t`.id',
            ' t.`id`'
        );

        $params = array(
            'where' => $where,
            'orderby' => "`t`.`{$this->obj->data->field_id}` DESC",
            'limit' => (int) $rows_per_page,
            'page' => $this->obj->page,
            'search' => $this->obj->search,
            'search_across' => true,
            'search_across_picks' => false,
            'sql' => $sql
        );

        if ( is_array( $orderby ) )
            $params = array_merge( $params, $orderby );
        elseif ( !empty( $orderby ) )
            $params[ 'orderby' ] = $orderby;

        $params[ 'where' ] = trim( str_replace( $find, $replace, ' ' . $params[ 'where' ] ) );
        $params[ 'orderby' ] = trim( str_replace( $find, $replace, ' ' . $params[ 'orderby' ] ) );

        $params = (object) $params;

        $this->obj->limit = $params->limit;
        $this->obj->page = $params->page;
        $this->obj->search = $params->search;

        return $this->obj->find( $params );
    }

    /**
     * Return a single record
     *
     * @since 1.x
     * @deprecated deprecated since version 2.0
     */
    public function getRecordById ( $id ) {
        pods_deprecated( 'Pods::getRecordById', '2.0', 'Pods::fetch_item' );

        return $this->obj->fetch_item( $id );
    }

    /**
     * Fetch the total row count
     *
     * @deprecated deprecated since version 2.0
     */
    public function getTotalRows () {
        pods_deprecated( 'Pods::getTotalRows', '2.0', 'Pods::total_found' );

        return $this->obj->total_found();
    }

    /**
     * (Re)set the MySQL result pointer
     *
     * @deprecated deprecated since version 2.0
     */
    public function resetPointer ( $row_number = 0 ) {
        pods_deprecated( 'Pods::resetPointer', '2.0', 'Pods::reset' );

        return $this->obj->reset( $row_number );
    }

    /**
     * Display the pagination controls
     *
     * @deprecated deprecated since 2.0
     */
    public function getPagination ( $label = 'Go to page:' ) {
        pods_deprecated( 'Pods::getPagination', '2.0', 'Pods::pagination' );

        echo $this->obj->pagination( array( 'type' => 'advanced', 'label' => $label ) );
    }

    /**
     * Display the list filters
     *
     * @deprecated deprecated since 2.0
     */
    public function getFilters ( $filters = null, $label = 'Filter', $action = '' ) {
        pods_deprecated( 'Pods::getFilters', '2.0', 'Pods::filters' );

        $params = array(
            'fields' => $filters,
            'label' => $label,
            'action' => $action
        );

        if ( is_array( $filters ) )
            $params = array_merge( $params, $filters );

        echo $this->obj->filters( $params );
    }

    /**
     * Run a helper within a Pod Page or WP Template
     *
     * @param string $helper The helper name
     *
     * @return mixed Anything returned by the helper
     * @since 1.2.0
     * @deprecated deprecated since version 2.0
     */
    public function pod_helper ( $helper_name, $value = null, $name = null ) {
        pods_deprecated( 'Pods::pod_helper', '2.0', 'Pods::helper' );

        $params = array(
            'helper' => $helper_name,
            'value' => $value,
            'name' => $name,
            'deprecated' => true
        );

        return $this->obj->helper( $params );
    }

    /**
     * Display the page template
     *
     * @deprecated deprecated since version 2.0
     */
    public function showTemplate ( $template_name, $code = null ) {
        pods_deprecated( 'Pods::showTemplate', '2.0', 'Pods::template' );

        return $this->obj->template( $template_name, $code, true );
    }
}

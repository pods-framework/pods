<?php
class PodsAPI {

    public $display_errors = false;

    public $pod;

    public $pod_id;

    public $pod_data;

    public $fields;

    public $format = 'php';

    /**
     * Store and retrieve data programatically
     *
     * @param string $dtname (optional) The pod name
     * @param string $format (optional) Format for import/export, "php" or "csv"
     *
     * @license http://www.gnu.org/licenses/gpl-2.0.html
     * @since 1.7.1
     */
    public function __construct ( $pod = null, $format = 'php' ) {
        if ( null !== $pod && 0 < strlen( (string) $pod ) ) {
            $this->format = $format;
            $this->pod_data = $this->load_pod( array( 'name' => $pod ) );
            if ( false !== $this->pod_data && is_array( $this->pod_data ) ) {
                $this->pod = $this->pod_data[ 'name' ];
                $this->pod_id = $this->pod_data[ 'id' ];
                $this->fields = $this->pod_data[ 'fields' ];
            }
            else
                return false;
        }
    }

    /**
     * Add a Pod via the Wizard
     *
     * $params['create_extend'] string Create or Extend a Content Type
     * $params['create_pod_type'] string Pod Type (for Creating)
     * $params['create_name'] string Pod Name (for Creating)
     * $params['create_label_plural'] string Plural Label (for Creating)
     * $params['create_label_singular'] string Singular Label (for Creating)
     * $params['create_storage'] string Storage Type (for Creating Post Types)
     * $params['extend_pod_type'] string Pod Type (for Extending)
     * $params['extend_post_type'] string Post Type (for Extending Post Types)
     * $params['extend_taxonomy'] string Taxonomy (for Extending Taxonomies)
     * $params['extend_storage'] string Storage Type (for Extending Post Types / Users / Comments)
     *
     * @param array $params An associative array of parameters
     *
     * @since 2.0.0
     */
    public function add_pod ( $params ) {
        $defaults = array(
            'create_extend' => 'create',
            'create_pod_type' => 'post_type',
            'create_name' => '',
            'create_label_plural' => '',
            'create_label_singular' => '',
            'create_storage' => 'meta',
            'extend_pod_type' => 'post_type',
            'extend_post_type' => 'post',
            'extend_taxonomy' => 'category',
            'extend_storage' => 'meta'
        );
        $params = (object) array_merge( $defaults, (array) $params );

        if ( empty( $params->create_extend ) || !in_array( $params->create_extend, array( 'create', 'extend' ) ) )
            return pods_error( __( 'Please choose whether to Create or Extend a Content Type', $this ) );

        $pod_params = array();
        if ( 'create' == $params->create_extend ) {
            if ( empty( $params->create_name ) )
                return pods_error( 'Please enter a Name for this Pod', $this );

            $pod_params = array(
                'name' => $params->create_name,
                'type' => $params->create_pod_type,
                'storage' => $params->create_storage,
                'options' => array(
                    'cpt_show_ui' => 1,
                    'ct_show_ui' => 1,
                    'cpt_public' => 1,
                    'ct_public' => 1
                )
            );
            if ( 'post_type' == $pod_params[ 'type' ] ) {
                $pod_params[ 'options' ][ 'cpt_label' ] = ( !empty( $params->create_label_plural ) ? $params->create_label_plural : ucwords( str_replace( '_', ' ', $params->create_name ) ) );
                $pod_params[ 'options' ][ 'cpt_singular_label' ] = ( !empty( $params->create_label_singular ) ? $params->create_label_singular : ucwords( str_replace( '_', ' ', $params->create_name ) ) );
            }
            elseif ( 'taxonomy' == $pod_params[ 'type' ] ) {
                $pod_params[ 'storage' ] = 'table';
                $pod_params[ 'options' ][ 'ct_label' ] = ( !empty( $params->create_label_plural ) ? $params->create_label_plural : ucwords( str_replace( '_', ' ', $params->create_name ) ) );
                $pod_params[ 'options' ][ 'ct_singular_label' ] = ( !empty( $params->create_label_singular ) ? $params->create_label_singular : ucwords( str_replace( '_', ' ', $params->create_name ) ) );
            }
            elseif ( 'pod' == $pod_params[ 'type' ] ) {
                $pod_params[ 'storage' ] = 'table';
                $pod_params[ 'options' ][ 'label' ] = ( !empty( $params->create_label_plural ) ? $params->create_label_plural : ucwords( str_replace( '_', ' ', $params->create_name ) ) );
                $pod_params[ 'options' ][ 'singular_label' ] = ( !empty( $params->create_label_singular ) ? $params->create_label_singular : ucwords( str_replace( '_', ' ', $params->create_name ) ) );
            }
        }
        elseif ( 'extend' == $params->create_extend ) {
            $pod_params = array(
                'type' => $params->extend_pod_type,
                'storage' => $params->extend_storage,
                'options' => array()
            );
            if ( 'post_type' == $pod_params[ 'type' ] )
                $pod_params[ 'name' ] = $pod_params[ 'object' ] = $params->extend_post_type;
            elseif ( 'taxonomy' == $pod_params[ 'type' ] ) {
                $pod_params[ 'storage' ] = 'table';
                $pod_params[ 'name' ] = $pod_params[ 'object' ] = $params->extend_taxonomy;
            }
            else
                $pod_params[ 'name' ] = $pod_params[ 'object' ] = $params->extend_pod_type;
        }
        if ( !empty( $pod_params ) )
            return $this->save_pod( $pod_params );
        return false;
    }

    /**
     * Add or edit a Pod
     *
     * $params['id'] int The Pod ID
     * $params['name'] string The Pod name
     * $params['type'] string The Pod type
     * $params['object'] string Object name
     * $params['storage'] string Storage type
     * $params['alias'] string Alias
     * $params['weight'] int Weight (used to sort admin menu)
     * $params['options'] array Options
     *
     * @param array $params An associative array of parameters
     *
     * @since 1.7.9
     *
     * @todo Save new fields with save_field, delete with delete_field (it updates cache etc)
     *
     */
    public function save_pod ( $params ) {
        $pod = $this->load_pod( $params, false );
        $params = (object) pods_sanitize( $params );

        $old_name = $old_id = null;
        if ( !empty( $pod ) ) {
            if ( isset( $params->id ) && 0 < $params->id )
                $old_id = $params->id;
            $params->id = $pod[ 'id' ];
            if ( isset( $params->name ) && 0 < strlen( $params->name ) )
                $old_name = $pod[ 'name' ];
            else
                $params->name = $old_name = $pod[ 'name' ];
            if ( $old_name != $params->name && false !== $this->pod_exists( array( 'name' => $params->name ) ) )
                return pods_error( 'Pod ' . $params->name . ' already exists, you cannot rename ' . $old_name . ' to that', $this );
            elseif ( $old_id != $params->id ) {
                if ( $params->type == $pod[ 'type' ] && isset( $params->object ) && $params->object == $pod[ 'object' ] )
                    return pods_error( 'Pod using ' . $params->object . ' already exists, you can not reuse an object across multiple pods', $this );
                else
                    return pods_error( 'Pod ' . $params->name . ' already exists', $this );
            }
        }

        // Add new pod
        if ( empty( $params->id ) ) {
            $params->name = pods_clean_name( $params->name );
            if ( strlen( $params->name ) < 1 )
                return pods_error( 'Pod name cannot be empty', $this );

            $check = pods_query( "SELECT `id` FROM `@wp_pods` WHERE `name` = '{$params->name}' LIMIT 1", $this );
            if ( !empty( $check ) )
                return pods_error( 'Pod ' . $params->name . ' already exists, you can not add one using the same name', $this );

            $pod = array( 'name' => $params->name, 'options' => '', 'type' => 'pod', 'storage' => 'table' );
            if ( isset( $params->type ) && 0 < strlen( $params->type ) )
                $pod[ 'type' ] = $params->type;
            if ( isset( $params->object ) && 0 < strlen( $params->object ) )
                $pod[ 'object' ] = $params->object;
            if ( isset( $params->storage ) && 0 < strlen( $params->storage ) )
                $pod[ 'storage' ] = $params->storage;
            if ( isset( $params->alias ) && 0 < strlen( $params->alias ) )
                $pod[ 'alias' ] = $params->alias;
            if ( isset( $params->weight ) && 0 < strlen( $params->weight ) )
                $pod[ 'weight' ] = $params->weight;
            if ( !isset( $params->options ) || empty( $params->options ) ) {
                $options = get_object_vars( $params );
                $exclude = array(
                    'id',
                    'name',
                    'type',
                    'object',
                    'storage',
                    'alias',
                    'weight',
                    'options',
                    'field_data'
                );
                foreach ( $exclude as $exclude_field ) {
                    if ( isset( $options[ $exclude_field ] ) )
                        unset( $options[ $exclude_field ] );
                }
                $params->options = '';
                if ( !empty( $options ) )
                    $params->options = $options;
            }
            if ( !empty( $params->options ) )
                $params->options = pods_sanitize( str_replace( '@wp_', '{prefix}', json_encode( $params->options ) ) );
            $params->id = pods_query( "INSERT INTO `@wp_pods` (`" . implode( '`,`', array_keys( $pod ) ) . "`) VALUES ('" . implode( "','", $pod ) . "')", $this );
            if ( false === $params->id )
                return pods_error( 'Cannot add entry for new Pod', $this );

            $field_columns = array(
                'pod_id' => $params->id,
                'name' => '',
                'label' => '',
                'type' => 'text',
                'pick_object' => '',
                'pick_val' => '',
                'sister_field_id' => 0,
                'weight' => 0,
                'options' => ''
            );
            $fields = array();
            $weight = 0;
            if ( 'pod' == $params->type ) {
                $fields[] = array(
                    'name' => 'name',
                    'label' => 'Name',
                    'type' => 'text',
                    'weight' => $weight,
                    'options' => array( 'required' => '1' )
                );
                $weight++;
                $fields[] = array(
                    'name' => 'created',
                    'label' => 'Date Created',
                    'type' => 'date',
                    'weight' => $weight
                );
                $weight++;
                $fields[] = array(
                    'name' => 'modified',
                    'label' => 'Date Modified',
                    'type' => 'date',
                    'weight' => $weight
                );
                $weight++;
                $fields[] = array(
                    'name' => 'author',
                    'label' => 'Author',
                    'type' => 'pick',
                    'pick_object' => 'user',
                    'weight' => $weight
                );
                $weight++;
                $fields[] = array(
                    'name' => 'permalink',
                    'label' => 'Permalink',
                    'type' => 'slug',
                    'weight' => $weight,
                    'options' => array( 'comment' => 'Leave blank to auto-generate from Name' )
                );
                $weight++;
            }
            if ( isset( $params->fields ) && is_array( $params->fields ) && !empty( $params->fields ) )
                $fields = $params->fields;
            $rows = array();
            $definitions = array( "`id` BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY" );
            foreach ( $fields as $field ) {
                $row = array();
                foreach ( $field_columns as $field => $default ) {
                    $row[ $field ] = $default;
                    if ( isset( $field[ $field ] ) )
                        $row[ $field ] = $field[ $field ];
                }
                if ( !empty( $row[ 'options' ] ) ) {
                    if ( is_array( $row[ 'options' ] ) ) {
                        $options = $row[ 'options' ];
                        $exclude = array_keys( $field_columns );
                        foreach ( $exclude as $exclude_field ) {
                            if ( isset( $options[ $exclude_field ] ) )
                                unset( $options[ $exclude_field ] );
                        }
                        $row[ 'options' ] = '';
                        if ( !empty( $options ) )
                            $row[ 'options' ] = pods_sanitize( str_replace( '@wp_', '{prefix}', json_encode( $options ) ) );
                    }
                }
                $rows[] = implode( "','", $row );
                if ( !in_array( $row[ 'type' ], array( 'pick', 'file' ) ) )
                    $definitions[] = "`{$field['name']}` " . $this->get_field_definition( $field[ 'type' ] );
            }
            if ( 'table' == $pod[ 'storage' ] ) {
                $result = pods_query( "CREATE TABLE `@wp_pods_tbl_{$params->name}` (" . implode( ', ', $definitions ) . ") DEFAULT CHARSET utf8", $this );
                if ( empty( $result ) )
                    return pods_error( 'Cannot add Database Table for new Pod' );
            }
            if ( !empty( $rows ) ) {
                $result = pods_query( "INSERT INTO `@wp_pods_fields` (`" . implode( '`,`', array_keys( $field_columns ) ) . "`) VALUES ('" . implode( "'),('", $rows ) . "')", $this );
                if ( empty( $result ) )
                    return pods_error( 'Cannot add fields for new Pod' );
            }
        }
        // Edit existing pod
        else {
            if ( empty( $pod ) )
                return pods_error( 'Pod not found' );
            $set = array();
            if ( !isset( $params->options ) || empty( $params->options ) ) {
                $options = get_object_vars( $params );
                $exclude = array( 'id', 'name', 'type', 'object', 'options', 'field_data' );
                foreach ( $exclude as $exclude_field ) {
                    if ( isset( $options[ $exclude_field ] ) ) {
                        if ( 'field_data' != $exclude_field )
                            $set[] = "`{$exclude_field}` = '{$params->$exclude_field}'";
                        unset( $options[ $exclude_field ] );
                    }
                }
                $params->options = '';
                if ( !empty( $options ) )
                    $params->options = $options;
            }
            if ( !empty( $params->options ) )
                $params->options = pods_sanitize( str_replace( '@wp_', '{prefix}', json_encode( $params->options ) ) );
            $saved = array();
            if ( isset( $params->field_data ) && is_array( $params->field_data ) && !empty( $params->field_data ) ) {
                $weight = 0;
                foreach ( $params->field_data as $field_data ) {
                    if ( !is_array( $field_data ) )
                        continue;
                    $field_data[ 'pod_id' ] = $params->id;
                    $field_data[ 'weight' ] = $weight;
                    $field_id = $this->save_field( $field_data );
                    if ( 0 < $field_id )
                        $saved[ $field_id ] = true;
                    else {
                        return pods_error( 'Cannot edit field', $this );
                    }
                    $weight++;
                }
                foreach ( $pod[ 'fields' ] as $field ) {
                    if ( !isset( $saved[ $field[ 'id' ] ] ) ) {
                        $this->delete_field( array(
                                                 'id' => (int) $field[ 'id' ],
                                                 'name' => (int) $field[ 'name' ],
                                                 'pod_id' => $params->id,
                                                 'pod' => $params->name
                                             ) );
                    }
                }
            }
            $set[] = "`options` = '{$params->options}'";
            $set = implode( ', ', $set );
            pods_query( "UPDATE `@wp_pods` SET {$set} WHERE `id` = {$params->id}", $this );
            if ( 'table' == $pod[ 'storage' ] && null !== $old_name && $old_name != $params->name ) {
                pods_query( "ALTER TABLE `@wp_pods_tbl_{$old_name}` RENAME `@wp_pods_tbl_{$params->name}`", $this );
            }
        }
        return $params->id;
    }

    /**
     * Add or edit a column within a Pod
     *
     * $params['id'] int The field ID
     * $params['pod_id'] int The Pod ID
     * $params['pod'] string The Pod name
     * $params['name'] string The field name
     * $params['label'] string The field label
     * $params['type'] string The column type ("txt", "desc", "pick", etc)
     * $params['pick_object'] string The related PICK object name
     * $params['pick_val'] string The related PICK object value
     * $params['sister_field_id'] int (optional) The related field ID
     * $params['weight'] int The field weight
     * $params['options'] array The field options
     *
     * @param array $params An associative array of parameters
     *
     * @since 1.7.9
     */
    public function save_field ( $params ) {
        $params = (object) $params;

        if ( isset( $params->pod_id ) ) {
            $params->pod_id = pods_absint( $params->pod_id );
        }
        $pod = null;
        if ( !isset( $params->pod_id ) || empty( $params->pod_id ) ) {
            if ( isset( $params->pod ) ) {
                $pod = $this->load_pod( array( 'name' => $params->name ) );
                if ( empty( $pod ) )
                    return pods_error( 'Pod ID or name is required', $this );
                else {
                    $params->pod_id = $pod[ 'id' ];
                    $params->pod = $pod[ 'name' ];
                }
            }
            else
                return pods_error( 'Pod ID or name is required', $this );
        }
        elseif ( !isset( $params->pod ) ) {
            $pod = $this->load_pod( array( 'id' => $params->pod_id ) );
            if ( empty( $pod ) )
                return pods_error( 'Pod not found', $this );
            else {
                $params->pod_id = $pod[ 'id' ];
                $params->pod = $pod[ 'name' ];
            }
        }
        if ( !isset( $pod ) )
            $pod = $this->load_pod( array( 'id' => $params->pod_id ) );

        $params->name = pods_clean_name( $params->name );
        if ( empty( $params->name ) )
            return pods_error( 'Pod Column name is required', $this );

        $defaults = array(
            'id' => 0,
            'pod_id' => 0,
            'name' => '',
            'label' => '',
            'type' => '',
            'pick_object' => '',
            'pick_val' => '',
            'sister_field_id' => '',
            'weight' => 0,
            'options' => array()
        );
        $params = (object) array_merge( $defaults, (array) $params );

        $tableless_field_types = $this->do_hook( 'tableless_field_types', array( 'pick', 'file' ) );

        // Add new column
        if ( !isset( $params->id ) || empty( $params->id ) ) {
            if ( in_array( $params->name, array( 'p' ) ) ) // there are more, let's add them as we find them
                return pods_error( "$params->name is reserved for internal WordPress usage, please try a different name", $this );
            if ( in_array( $params->name, array( 'id', 'created', 'modified', 'author' ) ) )
                return pods_error( "$params->name is reserved for internal Pods usage, please try a different name", $this );

            $sql = "SELECT `id` FROM `@wp_pods_fields` WHERE `pod_id` = {$params->pod_id} AND `name` = '{$params->name}' LIMIT 1";
            $result = pods_query( $sql, $this );
            if ( !empty( $result ) )
                return pods_error( "Pod Column {$params->name} already exists", $this );

            if ( 'slug' == $params->type ) {
                $sql = "SELECT `id` FROM `@wp_pods_fields` WHERE `pod_id` = {$params->pod_id} AND `type` = 'slug' LIMIT 1";
                $result = pods_query( $sql, $this );
                if ( !empty( $result ) )
                    return pods_error( 'This pod already has a permalink column', $this );
            }

            // Sink the new column to the bottom of the list
            if ( !isset( $params->weight ) ) {
                $params->weight = 0;
                $result = pods_query( "SELECT `weight` FROM `@wp_pods_fields` WHERE `pod_id` = {$params->pod_id} ORDER BY `weight` DESC LIMIT 1", $this );
                if ( !empty( $result ) )
                    $params->weight = pods_absint( $result[ 0 ]->weight ) + 1;
            }

            $params->sister_field_id = pods_absint( $params->sister_field_id );
            $params->weight = pods_absint( $params->weight );

            if ( !isset( $params->options ) || empty( $params->options ) ) {
                $options = get_object_vars( $params );
                $exclude = array(
                    'id',
                    'pod_id',
                    'pod',
                    'name',
                    'label',
                    'type',
                    'pick_object',
                    'pick_val',
                    'sister_field_id',
                    'weight',
                    'options'
                );
                foreach ( $exclude as $exclude_field ) {
                    if ( isset( $options[ $exclude_field ] ) )
                        unset( $options[ $exclude_field ] );
                }
                $params->options = '';
                if ( !empty( $options ) )
                    $params->options = $options;
            }
            if ( !empty( $params->options ) ) {
                $params->options = pods_sanitize( str_replace( '@wp_', '{prefix}', json_encode( $params->options ) ) );
            }

            $field_id = pods_query( "INSERT INTO `@wp_pods_fields` (`pod_id`, `name`, `label`, `type`, `pick_object`, `pick_val`, `sister_field_id`, `weight`, `options`) VALUES ('{$params->pod_id}', '{$params->name}', '{$params->label}', '{$params->type}', '{$params->pick_object}', '{$params->pick_val}', {$params->sister_field_id}, {$params->weight}, '{$params->options}')", 'Cannot add new field' );
            if ( empty( $field_id ) )
                return pods_error( "Cannot add new field {$params->name}", $this );

            if ( 'table' == $pod[ 'storage' ] && !in_array( $params->type, $tableless_field_types ) ) {
                $dbtype = $this->get_field_definition( $params->type );
                pods_query( "ALTER TABLE `@wp_pods_tbl_{$params->pod}` ADD COLUMN `{$params->name}` {$dbtype}", 'Cannot create new column' );
            }
            elseif ( 0 < $params->sister_field_id ) {
                pods_query( "UPDATE `@wp_pods_fields` SET `sister_field_id` = '{$field_id}' WHERE `id` = {$params->sister_field_id} LIMIT 1", 'Cannot update sister field' );
            }

            $params->id = $field_id;
        }
        // Edit existing column
        else {
            $params->id = pods_absint( $params->id );
            if ( 'id' == $params->name ) {
                return pods_error( "$params->name is not editable", $this );
            }

            $sql = "SELECT `id` FROM `@wp_pods_fields` WHERE `pod_id` = {$params->pod_id} AND `id` != {$params->id} AND name = '{$params->name}' LIMIT 1";
            $check = pods_query( $sql, $this );
            if ( !empty( $check ) )
                return pods_error( "Column {$params->name} already exists", $this );

            $sql = "SELECT * FROM `@wp_pods_fields` WHERE `id` = {$params->id} LIMIT 1";
            $result = pods_query( $sql, $this );
            if ( empty( $result ) )
                return pods_error( "Column {$params->name} not found, cannot edit", $this );

            $old_type = $result[ 0 ]->type;
            $old_name = $result[ 0 ]->name;

            $dbtype = $this->get_field_definition( $params->type );
            $params->pick_val = ( 'pick' != $params->type || empty( $params->pick_val ) ) ? '' : "$params->pick_val";
            $params->sister_field_id = pods_absint( $params->sister_field_id );
            $params->weight = pods_absint( $params->weight );

            if ( $params->type != $old_type ) {
                if ( in_array( $params->type, $tableless_field_types ) ) {
                    if ( 'table' == $pod[ 'storage' ] && !in_array( $old_type, $tableless_field_types ) ) {
                        pods_query( "ALTER TABLE `@wp_pods_tbl_$params->pod` DROP COLUMN `$old_name`" );
                    }
                }
                elseif ( in_array( $old_type, $tableless_field_types ) ) {
                    if ( 'table' == $pod[ 'storage' ] )
                        pods_query( "ALTER TABLE `@wp_pods_tbl_$params->pod` ADD COLUMN `$params->name` $dbtype", 'Cannot create column' );
                    pods_query( "UPDATE @wp_pods_fields SET sister_field_id = NULL WHERE sister_field_id = $params->id" );
                    pods_query( "DELETE FROM @wp_pods_rel WHERE field_id = $params->id" );
                }
                else {
                    pods_query( "ALTER TABLE `@wp_pods_tbl_$params->pod` CHANGE `$old_name` `$params->name` $dbtype" );
                }
            }
            elseif ( 'table' == $pod[ 'storage' ] && $params->name != $old_name && !in_array( $params->type, $tableless_field_types ) ) {
                pods_query( "ALTER TABLE `@wp_pods_tbl_$params->pod` CHANGE `$old_name` `$params->name` $dbtype" );
            }
            if ( !isset( $params->options ) || empty( $params->options ) ) {
                $options = get_object_vars( $params );
                $exclude = array(
                    'id',
                    'pod_id',
                    'pod',
                    'name',
                    'label',
                    'type',
                    'pick_object',
                    'pick_val',
                    'sister_field_id',
                    'weight',
                    'options'
                );
                foreach ( $exclude as $exclude_field ) {
                    if ( isset( $options[ $exclude_field ] ) )
                        unset( $options[ $exclude_field ] );
                }
                $params->options = '';
                if ( !empty( $options ) )
                    $params->options = $options;
            }
            if ( !empty( $params->options ) ) {
                $params->options = pods_sanitize( str_replace( '@wp_', '{prefix}', json_encode( $params->options ) ) );
            }
            pods_query( "UPDATE `@wp_pods_fields` SET `name` = '{$params->name}', `label` = '{$params->label}', `type` = '{$params->type}', `pick_object` = '{$params->pick_object}', `pick_val` = '{$params->pick_val}', `sister_field_id` = {$params->sister_field_id}, `weight` = {$params->weight}, `options` = '{$params->options}' WHERE `id` = {$params->id} LIMIT 1", 'Cannot edit column' );
        }

        wp_cache_delete( 'pods', 'pods' );
        wp_cache_delete( $params->pod, 'pods_pods' );

        return $params->id;
    }

    /**
     * Add or Edit a Pods Object
     *
     * $params['id'] int The Object ID
     * $params['name'] string The Object name
     * $params['type'] string The Object type
     * $params['options'] Associative array of Object options
     *
     * @param array $params An associative array of parameters
     *
     * @since 2.0.0
     */
    public function save_object ( $params ) {
        $params = (object) $params;

        if ( !isset( $params->name ) || empty( $params->name ) )
            return pods_error( 'Name must be given to save an Object', $this );

        if ( !isset( $params->type ) || empty( $params->type ) )
            return pods_error( 'Type must be given to save an Object', $this );

        if ( !isset( $params->options ) || empty( $params->options ) ) {
            $options = get_object_vars( $params );
            $exclude = array( 'id', 'name', 'type', 'options' );

            foreach ( $exclude as $exclude_field ) {
                if ( isset( $options[ $exclude_field ] ) )
                    unset( $options[ $exclude_field ] );
            }
            $params->options = '';

            if ( !empty( $options ) )
                $params->options = $options;
        }

        if ( !empty( $params->options ) )
            $params->options = str_replace( '@wp_', '{prefix}', json_encode( $params->options ) );

        $params = pods_sanitize( $params );

        if ( isset( $params->id ) && !empty( $params->id ) ) {
            $params->id = pods_absint( $params->id );

            $result = pods_query( "UPDATE `@wp_pods_objects` SET `name` = '{$params->name}', `type` = '{$params->type}', `options` = '{$params->options}' WHERE `id` = " . pods_absint( $params->id ) );

            if ( empty( $result ) )
                return pods_error( ucwords( $params->type ) . ' Object not saved', $this );

            return $params->id;
        }
        else {
            $sql = "SELECT id FROM `@wp_pods_objects` WHERE `name` = '{$params->name}' LIMIT 1";
            $check = pods_query( $sql, $this );

            if ( !empty( $check ) )
                return pods_error( ucwords( $params->type ) . " Object {$params->name} already exists", $this );

            $object_id = pods_query( "INSERT INTO `@wp_pods_objects` (`name`, `type`, `options`) VALUES ('{$params->name}', '{$params->type}', '{$params->options}')" );

            if ( empty( $object_id ) )
                return pods_error( ucwords( $params->type ) . ' Object not saved', $this );

            $params->id = $object_id;
        }

        wp_cache_delete( $params->type, 'pods_objects' );
        wp_cache_delete( $params->name, 'pods_object_' . $params->type );
    }

    /**
     * Add or edit a Pod Template
     *
     * $params['id'] int The template ID
     * $params['name'] string The template name
     * $params['code'] string The template code
     *
     * @param array $params An associative array of parameters
     *
     * @since 1.7.9
     */
    public function save_template ( $params ) {
        $params = (object) $params;
        $params->type = 'template';
        return $this->save_object( $params );
    }

    /**
     * Add or edit a Pod Page
     *
     * $params['id'] int The page ID
     * $params['uri'] string The page URI
     * $params['phpcode'] string The page code
     *
     * @param array $params An associative array of parameters
     *
     * @since 1.7.9
     */
    public function save_page ( $params ) {
        $params = (object) $params;
        if ( !isset( $params->name ) ) {
            $params->name = $params->uri;
            unset( $params->uri );
        }
        $params->name = trim( $params->name, '/' );
        $params->type = 'page';
        return $this->save_object( $params );
    }

    /**
     * Add or edit a Pod Helper
     *
     * $params['id'] int The helper ID
     * $params['name'] string The helper name
     * $params['helper_type'] string The helper type ("pre_save", "display", etc)
     * $params['phpcode'] string The helper code
     *
     * @param array $params An associative array of parameters
     *
     * @since 1.7.9
     */
    public function save_helper ( $params ) {
        $params = (object) $params;
        $params->type = 'helper';
        return $this->save_object( $params );
    }

    /**
     * Save the entire role structure
     *
     * @param array $params An associative array of parameters
     *
     * @since 1.7.9
     */
    public function save_roles ( $params ) {
        $params = pods_sanitize( $params );
        $roles = array();
        foreach ( $params as $key => $val ) {
            if ( 'action' != $key ) {
                $tmp = empty( $val ) ? array() : explode( ',', $val );
                $roles[ $key ] = $tmp;
            }
        }
        delete_option( 'pods_roles' );
        add_option( 'pods_roles', serialize( $roles ) );
    }

    /**
     * Add or edit a single pod item
     *
     * $params['pod'] string The Pod name
     * $params['pod_id'] string The Pod name
     * $params['columns'] array (optional) Associative array of column names + values
     * $params['data'] array (optional) Associative array of a set of associative arrays of column names + values (for bulk operations)
     * $params['id'] int The item's ID from the wp_pod_tbl_* table (or alternatively use the pod_id parameter instead)
     * $params['bypass_helpers'] bool Set to true to bypass running pre-save and post-save helpers
     *
     * @param array $params An associative array of parameters
     *
     * @return int The item ID
     * @since 1.7.9
     */
    public function save_pod_item ( $params ) {
        $params = (object) str_replace( '@wp_', '{prefix}', pods_sanitize( $params ) );

        // @deprecated 2.0.0
        if ( isset( $params->datatype ) ) {
            pods_deprecated( '$params->pod instead of $params->datatype', '2.0.0' );
            $params->pod = $params->datatype;
            unset( $params->datatype );

            if ( isset( $params->pod_id ) ) {
                pods_deprecated( '$params->id instead of $params->pod_id', '2.0.0' );
                $params->id = $params->pod_id;
                unset( $params->pod_id );
            }
        }

        // @deprecated 2.0.0
        if ( isset( $params->tbl_row_id ) ) {
            pods_deprecated( '$params->id instead of $params->tbl_row_id', '2.0.0' );
            $params->id = $params->tbl_row_id;
            unset( $params->tbl_row_id );
        }

        // @deprecated 2.0.0
        if ( isset( $params->columns ) ) {
            pods_deprecated( '$params->data instead of $params->columns', '2.0.0' );
            $params->data = $params->columns;
            unset( $params->columns );
        }

        if ( !isset( $params->pod ) )
            $params->pod = false;
        if ( isset( $params->pod_id ) )
            $params->pod_id = pods_absint( $params->pod_id );
        else
            $params->pod_id = 0;

        if ( isset( $params->id ) )
            $params->id = pods_absint( $params->id );
        else
            $params->id = 0;

        // support for multiple save_pod_item operations at the same time
        if ( isset( $params->data ) && !empty( $params->data ) && is_array( $params->data ) ) {
            $ids = array();
            $new_params = $params;
            unset( $new_params->data );
            foreach ( $params->data as $fields ) {
                $new_params->data = $fields;
                $ids[] = $this->save_pod_item( $new_params );
            }
            return $ids;
        }

        // Support for bulk edit
        if ( isset( $params->id ) && !empty( $params->id ) && is_array( $params->id ) ) {
            $ids = array();
            $new_params = $params;
            foreach ( $params->id as $id ) {
                $new_params->id = $id;
                $ids[] = $this->save_pod_item( $new_params );
            }
            return $ids;
        }

        // Allow Helpers to know what's going on, are we adding or saving?
        $is_new_item = false;
        if ( empty( $params->id ) ) {
            $is_new_item = true;
        }

        // Allow Helpers to bypass subsequent helpers in recursive save_pod_item calls
        $bypass_helpers = false;
        if ( isset( $params->bypass_helpers ) && false !== $params->bypass_helpers ) {
            $bypass_helpers = true;
        }

        // Get array of Pods
        if ( empty( $this->pod_data ) || ( $this->pod != $params->pod && $this->pod_id != $params->pod_id ) )
            $this->pod_data = $this->load_pod( array( 'id' => $params->pod_id, 'name' => $params->pod ) );
        if ( false === $this->pod_data )
            return pods_error( "Pod not found", $this );
        $this->pod = $params->pod = $this->pod_data[ 'name' ];
        $this->pod_id = $params->pod_id = $this->pod_data[ 'id' ];
        $this->fields = $this->pod_data[ 'fields' ];

        $fields =& $this->fields; // easy to use variable for helpers

        $fields_active = array();

        // Find the active columns (loop through $params->data to retain order)
        if ( !empty( $params->data ) && is_array( $params->data ) ) {
            foreach ( $params->data as $field => $value ) {
                if ( isset( $fields[ $field ] ) ) {
                    $fields[ $field ][ 'value' ] = $value;
                    $fields_active[] = $field;
                }
            }
            unset( $params->data );
        }

        $columns =& $fields; // @deprecated 2.0.0
        $active_columns =& $fields_active; // @deprecated 2.0.0

        $pre_save_helpers = $post_save_helpers = array();

        if ( false === $bypass_helpers ) {
            // Plugin hook
            do_action( 'pods_pre_save_pod_item', $params, $fields, $this );
            do_action( "pods_pre_save_pod_item_{$params->pod}", $params, $this );
            if ( false !== $is_new_item ) {
                do_action( 'pods_pre_create_pod_item', $params, $this );
                do_action( "pods_pre_create_pod_item_{$params->pod}", $params, $this );
            }
            else {
                do_action( 'pods_pre_edit_pod_item', $params, $this );
                do_action( "pods_pre_edit_pod_item_{$params->pod}", $params, $this );
            }

            // Call any pre-save helpers (if not bypassed)
            if ( !defined( 'PODS_DISABLE_EVAL' ) || PODS_DISABLE_EVAL ) {
                if ( !empty( $this->pod_data[ 'options' ] ) && is_array( $this->pod_data[ 'options' ] ) ) {
                    $helpers = array( 'pre_save_helpers', 'post_save_helpers' );
                    foreach ( $helpers as $helper ) {
                        if ( isset( $this->pod_data[ 'options' ][ $helper ] ) && !empty( $this->pod_data[ 'options' ][ $helper ] ) )
                            ${$helper} = explode( ',', $this->pod_data[ 'options' ][ $helper ] );
                    }
                }

                if ( !empty( $pre_save_helpers ) ) {
                    pods_deprecated( 'Pre-save helpers are deprecated, use the action pods_pre_save_pod_item_' . $params->pod . ' instead', '2.0.0' );

                    foreach ( $pre_save_helpers as $helper ) {
                        $helper = $this->load_helper( array( 'name' => $helper ) );
                        if ( false !== $helper )
                            echo eval( '?>' . $helper[ 'code' ] );
                    }
                }
            }
        }

        $table_data = $rel_fields = $rel_field_ids = array();

        // Loop through each active column, validating and preparing the table data
        foreach ( $fields_active as $field ) {
            $value = $fields[ $field ][ 'value' ];
            $type = $fields[ $field ][ 'type' ];

            // Validate value
            $value = $this->handle_field_validation( $value, $field, $fields, $params );
            if ( false === $value )
                return false;

            // Prepare all table (non-relational) data
            if ( !in_array( $type, array( 'pick', 'file' ) ) )
                $table_data[] = "`{$field}` = '{$value}'";
            // Store relational column data to be looped through later
            else {
                $rel_fields[ $type ][ $field ] = $value;
                $rel_field_ids[] = $fields[ $field ][ 'id' ];
            }
        }

        // @todo: Use REPLACE INTO instead and set defaults on created / modified / author (and check if they exist)
        if ( false !== $is_new_item ) {
            $current_time = current_time( 'mysql' );
            $author = 0;
            if ( is_user_logged_in() ) {
                global $user_ID;
                get_currentuserinfo();
                $author = pods_absint( $user_ID );
            }
            $params->id = pods_query( "INSERT INTO `@wp_pods_tbl_{$params->pod}` (`created`, `modified`, `author`) VALUES ('{$current_time}', '{$current_time}', {$author})", 'Cannot add new table row' );
        }

        // Save the table row
        if ( !empty( $table_data ) ) {
            $table_data = implode( ', ', $table_data );
            pods_query( "UPDATE `@wp_pods_tbl_{$params->pod}` SET {$table_data} WHERE `id` = {$params->id} LIMIT 1" );
        }

        // Save relational column data
        if ( !empty( $rel_fields ) ) {
            // E.g. $rel_fields['pick']['related_events'] = '3,15';
            foreach ( $rel_fields as $type => $data ) {
                foreach ( $data as $field => $values ) {
                    $field_id = pods_absint( $fields[ $field ][ 'id' ] );

                    // Remove existing relationships
                    pods_query( "DELETE FROM `@wp_pods_rel` WHERE `pod_id` = {$params->pod_id} AND `field_id` = {$field_id} AND `item_id` = {$params->id}", $this );

                    // Convert values from a comma-separated string into an array
                    if ( !is_array( $values ) )
                        $values = explode( ',', $values );

                    // File relationships
                    if ( 'file' == $type ) {
                        if ( empty( $values ) )
                            continue;
                        $weight = 0;
                        foreach ( $values as $id ) {
                            $id = pods_absint( $id );
                            pods_query( "INSERT INTO `@wp_pods_rel` (`pod_id`, `field_id`, `item_id`, `related_item_id`, `weight`) VALUES ({$params->pod_id}, {$field_id}, {$params->id}, {$id}, {$weight})" );
                            $weight++;
                        }
                    }
                    // Pick relationships
                    elseif ( 'pick' == $type ) {
                        $pick_object = $fields[ $field ][ 'pick_object' ]; // pod, post_type, taxonomy, etc..
                        $pick_val = $fields[ $field ][ 'pick_val' ]; // pod name, post type name, taxonomy name, etc..
                        $related_pod_id = $related_field_id = 0;
                        if ( 'pod' == $pick_object ) {
                            $related_pod = $this->load_pod( array( 'name' => $pick_val ) );
                            if ( false !== $related_pod )
                                $related_pod_id = $related_pod[ 'id' ];
                            if ( 0 < $fields[ $field ][ 'sister_field_id' ] ) {
                                foreach ( $related_pod[ 'fields' ] as $field ) {
                                    if ( 'pick' == $field[ 'type' ] && $fields[ $field ][ 'sister_field_id' ] == $field[ 'id' ] ) {
                                        $related_field_id = $field[ 'id' ];
                                        break;
                                    }
                                }
                            }
                        }

                        // Delete existing sister relationships
                        if ( !empty( $related_field_id ) && !empty( $related_pod_id ) && in_array( $related_field_id, $rel_field_ids ) ) {
                            pods_query( "DELETE FROM `@wp_pods_rel` WHERE `pod_id` = {$related_pod_id} AND `field_id` = {$related_field_id} AND `related_pod_id` = {$params->pod_id} AND `related_field_id` = {$field_id} AND `related_item_id` = {$params->id}", $this );
                        }

                        if ( empty( $values ) )
                            continue;

                        // Add relationship values
                        $weight = 0;
                        foreach ( $values as $id ) {
                            if ( !empty( $related_pod_id ) && !empty( $related_field_id ) ) {
                                $related_weight = 0;
                                $result = pods_query( "SELECT `weight` FROM `@wp_pods_rel` WHERE `pod_id` = {$related_pod_id} AND `field_id` = {$related_field_id} ORDER BY `weight` DESC LIMIT 1", $this );
                                if ( !empty( $result ) )
                                    $related_weight = pods_absint( $result[ 0 ]->weight ) + 1;
                                pods_query( "INSERT INTO `@wp_pods_rel` (`pod_id`, `field_id`, `item_id`, `related_pod_id`, `related_field_id`, `related_item_id`, `weight`) VALUES ({$related_pod_id}, {$related_field_id}, {$id}, {$params->pod_id}, {$field_id}, {$params->id}, {$related_weight}", 'Cannot add sister relationship' );
                            }
                            pods_query( "INSERT INTO `@wp_pods_rel` (`pod_id`, `field_id`, `item_id`, `related_pod_id`, `related_field_id`, `related_item_id`, `weight`) VALUES ({$params->pod_id}, {$field_id}, {$params->id}, {$related_pod_id}, {$related_field_id}, {$id}, {$weight})", 'Cannot add relationship' );
                            $weight++;
                        }
                    }
                }
            }
        }

        if ( false === $bypass_helpers ) {
            // Plugin hook
            do_action( 'pods_post_save_pod_item', $params, $fields, $this );
            do_action( "pods_post_save_pod_item_{$params->pod}", $params, $fields, $this );
            if ( false !== $is_new_item ) {
                do_action( 'pods_post_create_pod_item', $params, $fields, $this );
                do_action( "pods_post_create_pod_item_{$params->pod}", $params, $fields, $this );
            }
            else {
                do_action( 'pods_post_edit_pod_item', $params, $fields, $this );
                do_action( "pods_post_edit_pod_item_{$params->pod}", $params, $fields, $this );
            }

            // Call any post-save helpers (if not bypassed)
            if ( !defined( 'PODS_DISABLE_EVAL' ) || PODS_DISABLE_EVAL ) {
                if ( !empty( $post_save_helpers ) ) {
                    pods_deprecated( 'Post-save helpers are deprecated, use the action pods_post_save_pod_item_' . $params->pod . ' instead', '2.0.0' );

                    foreach ( $post_save_helpers as $helper ) {
                        $helper = $this->load_helper( array( 'name' => $helper ) );
                        if ( false !== $helper && ( !defined( 'PODS_DISABLE_EVAL' ) || PODS_DISABLE_EVAL ) )
                            echo eval( '?>' . $helper[ 'code' ] );
                    }
                }
            }
        }

        wp_cache_delete( $params->id, 'pods_items_' . $params->pod );

        // Success! Return the id
        return $params->id;
    }

    /**
     * Duplicate a pod item
     *
     * $params['pod'] string The Pod name
     * $params['id'] int The item's ID from the wp_pods_tbl_* table
     *
     * @param array $params An associative array of parameters
     *
     * @return int The table row ID
     * @since 1.12
     */
    public function duplicate_pod_item ( $params ) {
        $params = (object) pods_sanitize( $params );

        $id = false;
        $fields = $this->fields;
        if ( empty( $fields ) || $this->pod != $params->pod ) {
            $pod = $this->load_pod( array( 'name' => $params->pod ) );
            $fields = $pod[ 'fields' ];
            if ( null === $this->pod ) {
                $this->pod = $pod[ 'name' ];
                $this->pod_id = $pod[ 'id' ];
                $this->fields = $pod[ 'fields' ];
            }
        }
        $pod = pods( $params->pod, $params->id );
        $data = $pod->data();
        if ( !empty( $data ) ) {
            $params = array(
                'pod' => $params->pod,
                'columns' => array()
            );
            foreach ( $fields as $field ) {
                $field = $field[ 'name' ];
                if ( 'pick' == $field[ 'coltype' ] ) {
                    $field = $field . '.id';
                    if ( 'wp_taxonomy' == $field[ 'pickval' ] )
                        $field = $field . '.term_id';
                }
                if ( 'file' == $field[ 'coltype' ] )
                    $field = $field . '.ID';
                $value = $pod->field( $field );
                if ( 0 < strlen( $value ) )
                    $params[ 'columns' ][ $field[ 'name' ] ] = $value;
            }
            $params = apply_filters( 'duplicate_pod_item', $params, $pod->pod, $pod->field( 'id' ) );
            $id = $this->save_pod_item( $params );
        }
        return $id;
    }

    /**
     * Export a pod item
     *
     * $params['pod'] string The Pod name
     * $params['id'] int The item's ID from the wp_pods_tbl_* table
     *
     * @param array $params An associative array of parameters
     *
     * @return int The table row ID
     * @since 1.12
     */
    public function export_pod_item ( $params ) {
        $params = (object) pods_sanitize( $params );

        $data = false;
        $fields = $this->fields;
        if ( empty( $fields ) || $this->pod != $params->pod ) {
            $pod = $this->load_pod( array( 'name' => $params->pod ) );
            $fields = $pod[ 'fields' ];
            if ( null === $this->pod ) {
                $this->pod = $pod[ 'name' ];
                $this->pod_id = $pod[ 'id' ];
                $this->fields = $pod[ 'fields' ];
            }
        }
        $pod = pods( $params->pod, $params->id );
        $data = $pod->data();
        if ( !empty( $data ) ) {
            $data = array();
            foreach ( $fields as $field ) {
                $value = $pod->field( $field[ 'name' ] );
                if ( 0 < strlen( $value ) )
                    $data[ $field[ 'name' ] ] = $value;
            }
            $data = apply_filters( 'export_pod_item', $data, $pod->pod, $pod->field( 'id' ) );
        }
        return $data;
    }

    /**
     * Reorder a Pod
     *
     * $params['pod'] string The Pod name
     * $params['field'] string The column name of the field to reorder
     * $params['order'] array The key => value array of items to reorder (key should be an integer)
     *
     * @param array $params An associative array of parameters
     *
     * @since 1.9.0
     */
    public function reorder_pod_item ( $params ) {
        $params = (object) pods_sanitize( $params );

        // @deprecated 2.0.0
        if ( isset( $params->datatype ) ) {
            pods_deprecated( '$params->pod instead of $params->datatype', '2.0.0' );
            $params->pod = $params->datatype;
            unset( $params->datatype );
        }

        if ( !is_array( $params->order ) )
            $params->order = explode( ',', $params->order );
        foreach ( $params->order as $order => $id ) {
            pods_query( "UPDATE `@wp_pods_tbl_{$params->pod}` SET `{$params->field}` = " . pods_absint( $order ) . " WHERE `id` = " . pods_absint( $id ) . " LIMIT 1" );
        }
    }

    /**
     * Delete all content for a Pod
     *
     * $params['id'] int The Pod ID
     * $params['name'] string The Pod name
     *
     * @param array $params An associative array of parameters
     *
     * @since 1.9.0
     */
    public function reset_pod ( $params ) {
        $params = (object) pods_sanitize( $params );

        $pod = $this->load_pod( $params );
        if ( false === $pod )
            return pods_error( 'Pod not found', $this );

        $params->id = $pod[ 'id' ];
        $params->name = $pod[ 'name' ];

        $field_ids = array();
        foreach ( $pod[ 'fields' ] as $field ) {
            $field_ids[] = $field[ 'id' ];
        }
        if ( !empty( $field_ids ) )
            pods_query( "UPDATE `@wp_pods_fields` SET `sister_field_id` = NULL WHERE `sister_field_id` IN (" . implode( ',', $field_ids ) . ")" );

        if ( 'pod' == $pod[ 'type' ] ) {
            pods_query( "TRUNCATE `@wp_pods_tbl_{$params->name}`" );
        }
        pods_query( "DELETE FROM `@wp_pods_rel` WHERE `pod_id` = {$params->id} OR `related_pod_id` = {$params->id}" );

        wp_cache_flush(); // only way to reliably clear out cached data across an entire group

        return true;
    }

    /**
     * Drop a Pod and all its content
     *
     * $params['id'] int The Pod ID
     * $params['name'] string The Pod name
     *
     * @param array $params An associative array of parameters
     *
     * @since 1.7.9
     */
    public function delete_pod ( $params ) {
        $params = (object) pods_sanitize( $params );

        $pod = $this->load_pod( $params );
        if ( false === $pod )
            return pods_error( 'Pod not found', $this );

        $params->id = $pod[ 'id' ];
        $params->name = $pod[ 'name' ];

        $field_ids = array();
        foreach ( $pod[ 'fields' ] as $field ) {
            $field_ids[] = $field[ 'id' ];
        }
        if ( !empty( $field_ids ) )
            pods_query( "UPDATE `@wp_pods_fields` SET `sister_field_id` = NULL WHERE `sister_field_id` IN (" . implode( ',', $field_ids ) . ")" );

        if ( 'pod' == $pod[ 'type' ] ) {
            pods_query( "DROP TABLE `@wp_pods_tbl_{$params->name}`" );
            pods_query( "UPDATE `@wp_pods_fields` SET `pick_val` = '' WHERE `pick_object` = 'pod' AND `pick_val` = '{$params->name}'" );
        }
        pods_query( "DELETE FROM `@wp_pods_rel` WHERE `pod_id` = {$params->id} OR `related_pod_id` = {$params->id}" );
        pods_query( "DELETE FROM `@wp_pods_fields` WHERE `pod_id` = {$params->id}" );
        pods_query( "DELETE FROM `@wp_pods` WHERE `id` = {$params->id} LIMIT 1" );

        wp_cache_delete( $pod[ 'name' ], 'pods_pods' );

        wp_cache_flush(); // only way to reliably clear out cached data across an entire group

        return true;
    }

    /**
     * Drop a column within a Pod
     *
     * $params['id'] int The column ID
     * $params['name'] int The column name
     * $params['pod'] string The Pod name
     * $params['pod_id'] string The Pod name
     *
     * @param array $params An associative array of parameters
     *
     * @since 1.7.9
     */
    public function delete_field ( $params ) {
        $params = (object) pods_sanitize( $params );

        if ( !isset( $params->pod ) )
            $params->pod = '';
        if ( !isset( $params->pod_id ) )
            $params->pod_id = 0;
        $pod = $this->load_pod( array( 'name' => $params->pod, 'id' => $params->pod_id ) );
        if ( false === $pod )
            return pods_error( 'Pod not found', $this );

        $params->pod_id = $pod[ 'id' ];
        $params->pod = $pod[ 'name' ];

        if ( !isset( $params->name ) )
            $params->name = '';
        if ( !isset( $params->id ) )
            $params->id = 0;
        $field = $this->load_field( array( 'name' => $params->name, 'id' => $params->id ) );
        if ( false === $field )
            return pods_error( 'Column not found', $this );

        $params->id = $field[ 'id' ];
        $params->name = $field[ 'name' ];

        if ( 'pod' == $pod[ 'type' ] && !in_array( $field[ 'type' ], array( 'file', 'pick' ) ) ) {
            pods_query( "ALTER TABLE `@wp_pods_tbl_{$params->pod}` DROP COLUMN `{$params->name}`" );
        }

        pods_query( "DELETE FROM `@wp_pods_rel` WHERE (`pod_id` = {$params->pod_id} AND `field_id` = {$params->id}) OR (`related_pod_id` = {$params->pod_id} AND `related_field_id` = {$params->id})" );
        pods_query( "DELETE FROM `@wp_pods_fields` WHERE `id` = {$params->id} LIMIT 1" );
        pods_query( "UPDATE `@wp_pods_fields` SET `sister_field_id` = NULL WHERE `sister_field_id` = {$params->id}" );

        wp_cache_delete( 'pods', 'pods' );
        wp_cache_delete( $params->pod, 'pods_pods' );
    }

    /**
     * Drop a Pod Object
     *
     * $params['id'] int The object ID
     * $params['name'] string The object name
     * $params['type'] string The object type
     *
     * @param array $params An associative array of parameters
     *
     * @since 2.0.0
     */
    public function delete_object ( $params ) {
        $object = $this->load_object( $params );

        if ( empty( $object ) )
            return pods_error( ucwords( $params->type ) . ' Object not found', $this );

        $result = pods_query( 'DELETE FROM `@wp_pods_objects` WHERE `id` = ' . (int) $object[ 'id' ] . " LIMIT 1", $this );

        if ( empty( $result ) )
            return pods_error( ucwords( $params->type ) . ' Object not deleted', $this );

        wp_cache_delete( $params->type, 'pods_objects' );
        wp_cache_delete( $params->name, 'pods_object_' . $params->type );

        return true;
    }

    /**
     * Drop a Pod Template
     *
     * $params['id'] int The template ID
     * $params['name'] string The template name
     *
     * @param array $params An associative array of parameters
     *
     * @since 1.7.9
     */
    public function delete_template ( $params ) {
        $params = (object) $params;
        $params->type = 'template';
        return $this->delete_object( $params );
    }

    /**
     * Drop a Pod Page
     *
     * $params['id'] int The page ID
     * $params['uri'] string The page URI
     *
     * @param array $params An associative array of parameters
     *
     * @since 1.7.9
     */
    public function delete_page ( $params ) {
        $params = (object) $params;
        if ( !isset( $params->name ) ) {
            $params->name = $params->uri;
            unset( $params->uri );
        }
        $params->name = trim( $params->name, '/' );
        $params->type = 'page';
        return $this->delete_object( $params );
    }

    /**
     * Drop a Pod Helper
     *
     * $params['id'] int The helper ID
     * $params['name'] string The helper name
     *
     * @param array $params An associative array of parameters
     *
     * @since 1.7.9
     */
    public function delete_helper ( $params ) {
        $params = (object) $params;
        $params->type = 'helper';
        return $this->delete_object( $params );
    }

    /**
     * Drop a single pod item
     *
     * $params['id'] int (optional) The item's ID from the wp_pod_tbl_* table (used with datatype parameter)
     * $params['pod'] string (optional) The datatype name (used with id parameter)
     * $params['pod_id'] int (optional) The datatype ID (used with id parameter)
     * $params['bypass_helpers'] bool Set to true to bypass running pre-save and post-save helpers
     *
     * @param array $params An associative array of parameters
     *
     * @since 1.7.9
     */
    public function delete_pod_item ( $params ) {
        $params = (object) pods_sanitize( $params );

        // @deprecated 2.0.0
        if ( isset( $params->datatype_id ) || isset( $params->datatype ) ) {
            if ( isset( $params->tbl_row_id ) ) {
                pods_deprecated( '$params->id instead of $params->tbl_row_id', '2.0.0' );
                $params->id = $params->tbl_row_id;
                unset( $params->tbl_row_id );
            }
            if ( isset( $params->pod_id ) ) {
                pods_deprecated( '$params->id instead of $params->pod_id', '2.0.0' );
                $params->id = $params->pod_id;
                unset( $params->pod_id );
            }
            if ( isset( $params->dataype_id ) ) {
                pods_deprecated( '$params->pod_id instead of $params->dataype_id', '2.0.0' );
                $params->pod_id = $params->dataype_id;
                unset( $params->dataype_id );
            }
            if ( isset( $params->datatype ) ) {
                pods_deprecated( '$params->pod instead of $params->datatype', '2.0.0' );
                $params->pod = $params->datatype;
                unset( $params->datatype );
            }
        }

        $params->id = pods_absint( $params->id );

        if ( !isset( $params->pod ) )
            $params->pod = '';
        if ( !isset( $params->pod_id ) )
            $params->pod_id = 0;
        $pod = $this->load_pod( array( 'name' => $params->pod, 'id' => $params->pod_id ) );
        if ( false === $pod )
            return pods_error( 'Pod not found', $this );

        $params->pod_id = $pod[ 'id' ];
        $params->pod = $pod[ 'name' ];

        // Allow Helpers to bypass subsequent helpers in recursive delete_pod_item calls
        $bypass_helpers = false;
        if ( isset( $params->bypass_helpers ) && false !== $params->bypass_helpers )
            $bypass_helpers = true;

        $pre_delete_helpers = $post_delete_helpers = array();

        if ( false === $bypass_helpers ) {
            // Plugin hook
            do_action( 'pods_pre_delete_pod_item', $params, $this );
            do_action( "pods_pre_delete_pod_item_{$params->pod}", $params, $this );

            // Call any pre-save helpers (if not bypassed)
            if ( !defined( 'PODS_DISABLE_EVAL' ) || PODS_DISABLE_EVAL ) {
                if ( !empty( $pod[ 'options' ] ) && is_array( $pod[ 'options' ] ) ) {
                    $helpers = array( 'pre_delete_helpers', 'post_delete_helpers' );
                    foreach ( $helpers as $helper ) {
                        if ( isset( $pod[ 'options' ][ $helper ] ) && !empty( $pod[ 'options' ][ $helper ] ) )
                            ${$helper} = explode( ',', $pod[ 'options' ][ $helper ] );
                    }
                }

                if ( !empty( $pre_delete_helpers ) ) {
                    foreach ( $pre_delete_helpers as $helper ) {
                        $helper = $this->load_helper( array( 'name' => $helper ) );
                        if ( false !== $helper )
                            echo eval( '?>' . $helper[ 'code' ] );
                    }
                }
            }
        }

        if ( 'pod' == $pod[ 'type' ] )
            pods_query( "DELETE FROM `@wp_pods_tbl_{$params->datatype}` WHERE `id` = {$params->id} LIMIT 1" );

        pods_query( "DELETE FROM `@wp_pods_rel` WHERE (`pod_id` = {$params->pod_id} AND `item_id` = {$params->id}) OR (`related_pod_id` = {$params->pod_id} AND `related_item_id` = {$params->id})" );

        if ( false === $bypass_helpers ) {
            // Plugin hook
            do_action( 'pods_post_delete_pod_item', $params, $this );
            do_action( "pods_post_delete_pod_item_{$params->pod}", $params, $this );

            // Call any post-save helpers (if not bypassed)
            if ( !defined( 'PODS_DISABLE_EVAL' ) || PODS_DISABLE_EVAL ) {
                if ( !empty( $post_delete_helpers ) ) {
                    foreach ( $post_delete_helpers as $helper ) {
                        $helper = $this->load_helper( array( 'name' => $helper ) );
                        if ( false !== $helper )
                            echo eval( '?>' . $helper[ 'code' ] );
                    }
                }
            }
        }

        wp_cache_delete( $params->id, 'pods_items_' . $params->pod );

        return true;
    }

    /**
     * Check if a Pod exists
     *
     * $params['id'] int The datatype ID
     * $params['name'] string The datatype name
     *
     * @param array $params An associative array of parameters
     *
     * @since 1.12
     */
    public function pod_exists ( $params ) {
        $params = (object) pods_sanitize( $params );
        if ( !empty( $params->id ) || !empty( $params->name ) ) {
            $where = empty( $params->id ) ? "name = '{$params->name}'" : "id = {$params->id}";
            $result = pods_query( "SELECT id FROM @wp_pods WHERE {$where} LIMIT 1" );
            if ( !empty( $result ) )
                return true;
        }
        return false;
    }

    /**
     * Load a Pod and all of its fields
     *
     * $params['id'] int The Pod ID
     * $params['name'] string The Pod name
     *
     * @param array $params An associative array of parameters
     *
     * @since 1.7.9
     */
    public function load_pod ( $params, $strict = true ) {
        if ( !is_array( $params ) && !is_object( $params ) )
            $params = array( 'name' => $params );
        $params = (object) pods_sanitize( $params );
        if ( ( !isset( $params->id ) || empty( $params->id ) ) && ( !isset( $params->name ) || empty( $params->name ) ) )
            return pods_error( 'Either Pod ID or Name are required', $this );

        if ( isset( $params->name ) ) {
            $pod = wp_cache_get( $params->name, 'pods_pods' );
            if ( false !== $pod )
                return $pod;
        }

        $where = ( isset( $params->id ) ? "`id` = " . pods_absint( $params->id ) : "`name` = '{$params->name}'" );
        if ( isset( $params->type ) && !empty( $params->type ) && isset( $params->object ) && !empty( $params->object ) )
            $where .= " OR (`type` = '{$params->type}' AND `object` = '{$params->object}')";
        $result = pods_query( "SELECT * FROM `@wp_pods` WHERE {$where} LIMIT 1", $this );
        if ( empty( $result ) ) {
            if ( $strict )
                return pods_error( 'Pod not found', $this );
            else
                return false;
        }

        $pod = get_object_vars( $result[ 0 ] );

        // @todo update with a method to put all options in
        $defaults = array(
            'is_toplevel' => 1,
            'label' => ucwords( str_replace( '_', ' ', $pod[ 'name' ] ) )
        );

        if ( !empty( $pod[ 'options' ] ) )
            $pod[ 'options' ] = @json_decode( $pod[ 'options' ], true );

        if ( !is_array( $pod[ 'options' ] ) )
            $pod[ 'options' ] = array();

        $pod[ 'options' ] = array_merge( $defaults, $pod[ 'options' ] );

        if ( !isset( $pod[ 'options' ][ 'label' ] ) || empty( $pod[ 'options' ][ 'label' ] ) )
            $pod[ 'options' ][ 'label' ] = ucwords( str_replace( '_', ' ', $pod[ 'name' ] ) );

        $pod[ 'fields' ] = array();

        $result = pods_query( "SELECT * FROM `@wp_pods_fields` WHERE pod_id = {$pod['id']} ORDER BY weight" );

        if ( !empty( $result ) ) {
            foreach ( $result as $row ) {
                $pod[ 'fields' ][ $row->name ] = get_object_vars( $row );

                if ( !empty( $pod[ 'fields' ][ $row->name ][ 'options' ] ) )
                    $pod[ 'fields' ][ $row->name ][ 'options' ] = (array) @json_decode( $pod[ 'fields' ][ $row->name ][ 'options' ], true );
            }
        }

        wp_cache_set( $pod[ 'name' ], $pod, 'pods_pods' );

        return $pod;
    }

    /**
     * Load Pods and filter by options
     *
     * $params['type'] string/array Pod Type(s) to filter by
     * $params['object'] string/array Pod Object(s) to filter by
     * $params['options'] array Pod Option(s) key=>value array to filter by
     * $params['orderby'] string ORDER BY clause of query
     * $params['limit'] string Number of Pods to return
     * $params['where'] string WHERE clause of query
     *
     * @param array $params An associative array of parameters
     *
     * @since 2.0.0
     */
    public function load_pods ( $params ) {
        $params = (object) pods_sanitize( $params );
        $orderby = $limit = '';
        $where = array();
        if ( isset( $params->type ) && !empty( $params->type ) ) {
            if ( !is_array( $params->type ) )
                $params->type = array( $params->type );
            $where[] = " `type` IN ('" . implode( "','", $params->type ) . "') ";
        }
        if ( isset( $params->object ) && !empty( $params->object ) ) {
            if ( !is_array( $params->object ) )
                $params->object = array( $params->object );
            $where[] = " `object` IN ('" . implode( "','", $params->object ) . "') ";
        }
        if ( isset( $params->options ) && !empty( $params->options ) && is_array( $params->options ) ) {
            $options = array();
            foreach ( $params->options as $option => $value ) {
                $options[] = pods_sanitize( trim( json_encode( array( $option => $value ) ), '{} []' ) );
            }
            if ( !empty( $options ) )
                $where[] = ' (`options` LIKE "%' . implode( '%" AND `options` LIKE "%', $options ) . '%")';
        }
        if ( isset( $params->where ) && 0 < strlen( $params->where ) ) {
            $where[] = $params->where;
        }
        $where = implode( ' AND ', $where );
        if ( !empty( $where ) )
            $where = " WHERE {$where} ";
        if ( isset( $params->orderby ) && !empty( $params->orderby ) )
            $orderby = " ORDER BY {$params->orderby} ";
        if ( isset( $params->limit ) && !empty( $params->limit ) ) {
            $params->limit = pods_absint( $params->limit );
            $limit = " LIMIT {$params->limit} ";
        }

        if ( empty( $where ) && empty( $limit ) && empty ( $orderby ) ) {
            $the_pods = wp_cache_get( 'pods', 'pods' );

            if ( false !== $the_pods )
                return $the_pods;
        }

        $result = pods_query( "SELECT id FROM `@wp_pods` {$where} {$orderby} {$limit}", $this );

        if ( empty( $result ) )
            return array();

        $the_pods = array();

        foreach ( $result as $row ) {
            $pod = $this->load_pod( array( 'id' => $row->id ) );

            $the_pods[ $pod[ 'name' ] ] = $pod;
        }

        if ( empty( $where ) && empty( $limit ) && empty ( $orderby ) )
            wp_cache_set( 'pods', $the_pods, 'pods' );

        return $the_pods;
    }

    /**
     * Load a column
     *
     * $params['pod_id'] int The Pod ID
     * $params['id'] int The field ID
     * $params['name'] string The field name
     *
     * @param array $params An associative array of parameters
     *
     * @since 1.7.9
     */
    public function load_field ( $params ) {
        $params = (object) pods_sanitize( $params );
        if ( empty( $params->id ) ) {
            if ( empty( $params->name ) )
                return pods_error( 'Column name is required', $this );
            if ( empty( $params->pod_id ) )
                return pods_error( 'Pod ID is required', $this );
            $where = "`pod_id` = " . pods_absint( $params->pod_id ) . " AND `name` = '" . $params->name . "'";
        }
        else {
            $where = '`id` = ' . pods_absint( $params->id );
        }
        $result = pods_query( "SELECT * FROM `@wp_pods_fields` WHERE {$where} LIMIT 1", $this );
        if ( empty( $result ) )
            return pods_error( 'Column not found', $this );
        $field = get_object_vars( $result[ 0 ] );
        if ( !empty( $field[ 'options' ] ) )
            $field[ 'options' ] = (array) @json_decode( $field[ 'options' ], true );
        return $field;
    }

    /**
     * Load a Pods Object
     *
     * $params['id'] int The Object ID
     * $params['name'] string The Object name
     * $params['type'] string The Object type
     *
     * @param array $params An associative array of parameters
     *
     * @since 2.0.0
     */
    public function load_object ( $params ) {
        $params = (object) pods_sanitize( $params );
        if ( !isset( $params->id ) || empty( $params->id ) ) {
            if ( !isset( $params->name ) || empty( $params->name ) )
                return pods_error( 'Name OR ID must be given to load an Object', $this );
            $where = "name = '{$params->name}'";
        }
        else
            $where = 'id = ' . pods_absint( $params->id );
        if ( !isset( $params->type ) || empty( $params->type ) )
            return pods_error( 'Type must be given to load an Object', $this );

        if ( isset( $params->name ) ) {
            $object = wp_cache_get( $params->name, 'pods_object_' . $params->type );

            if ( false !== $object )
                return $object;
        }

        $result = pods_query( "SELECT * FROM `@wp_pods_objects` WHERE $where `type` = '{$params->type}' LIMIT 1", $this );
        if ( empty( $result ) )
            return pods_error( ucwords( $params->type ) . ' Object not found', $this );

        $object = get_object_vars( $result[ 0 ] );
        if ( !empty( $object[ 'options' ] ) )
            $object[ 'options' ] = (array) @json_decode( $object[ 'options' ], true );

        wp_cache_set( $object[ 'name' ], $object, 'pods_object_' . $params->type );

        return $object;
    }

    /**
     * Load Multiple Pods Objects
     *
     * $params['type'] string The Object type
     * $params['options'] array Pod Option(s) key=>value array to filter by
     * $params['orderby'] string ORDER BY clause of query
     * $params['limit'] string Number of objects to return
     * $params['where'] string WHERE clause of query
     *
     * @param array $params An associative array of parameters
     */
    public function load_objects ( $params ) {
        $params = (object) pods_sanitize( $params );
        $orderby = $limit = '';
        $where = array();
        if ( isset( $params->type ) && !empty( $params->type ) ) {
            if ( !is_array( $params->type ) )
                $params->type = array( $params->type );
            $where[] = " `type` IN ('" . implode( "','", $params->type ) . "') ";
        }
        if ( isset( $params->options ) && !empty( $params->options ) ) {
            $options = array();
            foreach ( $params->options as $option => $value ) {
                $options[] = pods_sanitize( trim( json_encode( array( $option => $value ) ), '{} []' ) );
            }
            if ( !empty( $options ) )
                $where[] = ' (`options` LIKE "%' . implode( '%" AND `options` LIKE "%', $options ) . '%")';
        }
        if ( isset( $params->where ) && 0 < strlen( $params->where ) ) {
            $where[] = stripslashes( $params->where );
        }
        $where = implode( ' AND ', $where );
        if ( !empty( $where ) )
            $where = " WHERE {$where} ";
        if ( isset( $params->orderby ) && !empty( $params->orderby ) )
            $orderby = " ORDER BY {$params->orderby} ";
        if ( isset( $params->limit ) && !empty( $params->limit ) ) {
            $params->limit = pods_absint( $params->limit );
            $limit = " LIMIT {$params->limit} ";
        }

        if ( empty( $where ) && empty( $orderby ) && empty( $limit ) ) {
            $the_objects = wp_cache_get( $params->type, 'pods_objects' );

            if ( false !== $the_objects )
                return $the_objects;
        }

        $query = "SELECT * FROM `@wp_pods_objects` {$where} {$orderby} {$limit}";
        $result = pods_query( $query, $this );
        if ( empty( $result ) )
            return array();
        $the_objects = array();

        foreach ( $result as $row ) {
            $obj = get_object_vars( $row );

            if ( !empty( $obj[ 'options' ] ) )
                $obj[ 'options' ] = @json_decode( $obj[ 'options' ], true );

            $the_objects[ $obj[ 'name' ] ] = $obj;
        }

        if ( empty( $where ) && empty( $orderby ) && empty( $limit ) )
            wp_cache_set( $params->type, $the_objects, 'pods_objects' );

        return $the_objects;
    }

    /**
     * Load Multiple Pod Templates
     *
     * $params['where'] string The WHERE clause of query
     * $params['options'] array Pod Option(s) key=>value array to filter by
     * $params['orderby'] string ORDER BY clause of query
     * $params['limit'] string Number of templates to return
     *
     * @param array $params An associative array of parameters
     */
    public function load_templates ( $params ) {
        $params = (object) $params;
        $params->type = 'template';
        return $this->load_objects( $params );
    }

    /**
     * Load a Pod Template
     *
     * $params['id'] int The template ID
     * $params['name'] string The template name
     *
     * @param array $params An associative array of parameters
     *
     * @since 1.7.9
     */
    public function load_template ( $params ) {
        $params = (object) $params;
        $params->type = 'template';
        return $this->load_object( $params );
    }

    /**
     * Load a Pod Page
     *
     * $params['id'] int The page ID
     * $params['name'] string The page URI
     *
     * @param array $params An associative array of parameters
     *
     * @since 1.7.9
     */
    public function load_page ( $params ) {
        $params = (object) $params;
        if ( !isset( $params->name ) && isset( $params->uri ) ) {
            $params->name = $params->uri;
            unset( $params->uri );
        }
        $params->type = 'page';
        return $this->load_object( $params );
    }

    /**
     * Load Multiple Pod Pages
     *
     * $params['where'] string The WHERE clause of query
     * $params['options'] array Pod Option(s) key=>value array to filter by
     * $params['orderby'] string ORDER BY clause of query
     * $params['limit'] string Number of pages to return
     *
     * @param array $params An associative array of parameters
     */
    public function load_pages ( $params ) {
        $params = (object) $params;
        $params->type = 'page';
        return $this->load_objects( $params );
    }

    /**
     * Load a Pod Helper
     *
     * $params['id'] int The helper ID
     * $params['name'] string The helper name
     *
     * @param array $params An associative array of parameters
     *
     * @since 1.7.9
     */
    public function load_helper ( $params ) {
        $params = (object) $params;
        $params->type = 'helper';
        return $this->load_object( $params );
    }

    /**
     * Load Multiple Pod Helpers
     *
     * $params['where'] string The WHERE clause of query
     * $params['options'] array Pod Option(s) key=>value array to filter by
     * $params['orderby'] string ORDER BY clause of query
     * $params['limit'] string Number of pages to return
     *
     * @param array $params An associative array of parameters
     */
    public function load_helpers ( $params ) {
        $params = (object) $params;
        $params->type = 'helper';
        return $this->load_objects( $params );
    }

    /**
     * Load Component Information
     *
     * @since 2.0.0
     *
     * @todo Update with latest component code from RD2
     */
    public function load_components () {
        $components_root = PODS_DIR . 'components';
        $components_dir = @opendir( $components_root );
        $component_files = array();
        if ( false !== $components_dir ) {
            while (false !== ( $file = readdir( $components_dir ) )) {
                if ( '.' == substr( $file, 0, 1 ) )
                    continue;
                if ( is_dir( $components_dir . '/' . $file ) ) {
                    $components_subdir = @opendir( $components_root . '/' . $file );
                    if ( $components_subdir ) {
                        while (false !== ( $subfile = readdir( $components_subdir ) )) {
                            if ( '.' == substr( $subfile, 0, 1 ) )
                                continue;
                            if ( '.php' == substr( $subfile, -4 ) )
                                $component_files[] = $components_root . '/' . $file . '/' . $subfile;
                        }
                        closedir( $components_subdir );
                    }
                }
                elseif ( '.php' == substr( $file, -4 ) )
                    $component_files[] = $components_root . '/' . $file;
            }
            closedir( $components_dir );
        }
        $component_files = $this->do_hook( 'load_components_files', $component_files, $components_root );

        $default_headers = array(
            'ID' => 'Component ID',
            'Name' => 'Component Name',
            'ShortName' => 'Short Name',
            'ComponentURI' => 'Component URI',
            'Version' => 'Version',
            'Description' => 'Description',
            'Author' => 'Author',
            'AuthorURI' => 'Author URI',
            'HideMenu' => 'Hide from Menu'
        );
        $components = array();
        foreach ( $component_files as $component_file ) {
            if ( !is_readable( $component_file ) )
                continue;
            $component_data = get_file_data( $component_file, $default_headers, 'pods_component' );
            if ( empty( $component_data[ 'Name' ] ) )
                continue;
            if ( empty( $component_data[ 'ShortName' ] ) )
                $component_data[ 'ShortName' ] = $component_data[ 'Name' ];
            if ( empty( $component_data[ 'ID' ] ) ) {
                $component_data[ 'ID' ] = sanitize_title( str_replace( array(
                                                                           $components_root,
                                                                           '.php'
                                                                       ), '', $component_file ) );
            }
            $components[ str_replace( $components_root, '', $component_file ) ] = $component_data;
        }
        return $components;
    }

    /**
     * Load the pod item object
     *
     * $params['pod'] string The datatype name
     * $params['id'] int (optional) The item's ID
     *
     * @param array $params An associative array of parameters
     *
     * @since 2.0.0
     */
    public function load_pod_item ( $params ) {
        $params = (object) pods_sanitize( $params );

        if ( !isset( $params->pod ) || empty( $params->pod ) )
            return pods_error( 'Pod name required', $this );
        if ( !isset( $params->id ) || empty( $params->id ) )
            return pods_error( 'Item ID required', $this );

        $pod = wp_cache_get( $params->id, 'pods_items_' . $params->pod );

        if ( false !== $pod )
            return $pod;

        $pod = new Pod( $params->pod, $params->id );

        return $pod;
    }

    /**
     * Load a bi-directional (sister) column
     *
     * $params['pod'] int The Pod name
     * $params['related_pod'] string The related Pod name
     *
     * @param array $params An associative array of parameters
     * @param array $pod Array of Pod data to use (to avoid lookup)
     *
     * @since 1.7.9
     *
     * @todo Implement with load_pod / fields and use AJAX for new admin
     */
    public function load_sister_fields ( $params, $pod = null ) {
        $params = (object) pods_sanitize( $params );

        if ( empty( $pod ) ) {
            $pod = $this->load_pod( array( 'name' => $params->pod ) );

            if ( false === $pod )
                return pods_error( 'Pod not found', $this );
        }

        $params->pod_id = $pod[ 'id' ];
        $params->pod = $pod[ 'name' ];

        $related_pod = $this->load_pod( array( 'name' => $params->related_pod ) );
        if ( false === $pod )
            return pods_error( 'Related Pod not found', $this );

        $params->related_pod_id = $related_pod[ 'id' ];
        $params->related_pod = $related_pod[ 'name' ];

        if ( 'pod' == $related_pod[ 'type' ] ) {
            $sister_fields = array();
            foreach ( $related_pod[ 'fields' ] as $field ) {
                if ( 'pick' == $field[ 'type' ] && $params->pod == $field[ 'pick_val' ] ) {
                    $sister_fields[] = $field;
                }
            }
            return $sister_fields;
        }
        return false;
    }

    /**
     * Retrieve an associative array of table values
     *
     * $params['table'] string The table name (default: "types")
     * $params['columns'] string Comma-separated string of columns (default: "*")
     * $params['orderby'] string MySQL ORDER BY clause (default: "id ASC")
     * $params['where'] string MySQL WHERE clause (default: 1)
     * $params['array_key'] string The key column for the returned associative array (default: "id")
     *
     * @param array $params An associative array of parameters
     *
     * @return array The table data array
     * @since 1.8.5
     */
    public function get_table_data ( $params ) {
        $params = is_array( $params ) ? $params : array();
        $defaults = array(
            'table' => 'types',
            'columns' => '*',
            'orderby' => '`id` ASC',
            'where' => 1,
            'array_key' => 'id'
        );
        $params = (object) array_merge( $defaults, $params );
        $result = pods_query( "SELECT $params->columns FROM `@wp_pods_$params->table` WHERE $params->where ORDER BY $params->orderby", $this );
        $data = array();
        if ( !empty( $result ) ) {
            foreach ( $result as $row ) {
                $data[ $row->{$params->array_key} ] = get_object_vars( $row );
            }
        }
        return $data;
    }

    /**
     * Takes a sql column such as tinyint and returns the pods field type, such as num.
     *
     * @param type $sql_field
     *
     * @return type
     */
    public static function detect_pod_field_from_sql_data_type ( $sql_field ) {
        $sql_field = strtolower( $sql_field );

        $field_to_field_map = array(
            'tinyint' => 'number',
            'smallint' => 'number',
            'mediumint' => 'number',
            'int' => 'number',
            'bigint' => 'number',
            'float' => 'number',
            'double' => 'number',
            'decimal' => 'number',
            'date' => 'date',
            'datetime' => 'date',
            'timestamp' => 'date',
            'time' => 'date',
            'year' => 'date',
            'varchar' => 'text',
            'text' => 'paragraph',
            'mediumtext' => 'paragraph',
            'longtext' => 'paragraph'
        );

        return ( array_key_exists( $sql_field, $field_to_field_map ) ) ? $field_to_field_map[ $sql_field ] : 'paragraph';
    }

    public function get_field_types () {
        $types = array(
            'boolean',
            'date',
            'file',
            'number',
            'paragraph',
            'pick',
            'slug',
            'text'
        );

        $types = apply_filters( 'pods_field_types', $types, $this );

        $field_types = wp_cache_get( 'field_types', 'pods' );

        if ( false === $field_types || count( $types ) != count( $field_types ) ) {
            $field_types = $types;

            foreach ( $field_types as $field_type => $options ) {
                unset( $field_types[ $field_type ] );

                $field_type = $options;

                $options = array(
                    'type' => $field_type,
                    'label' => ucwords( str_replace( '_', ' ', $field_type ) ),
                    'schema' => 'VARCHAR(255)'
                );

                PodsForm::field_loader( $options );

                $class_vars = get_class_vars( PodsForm::$loaded[ $options ] ); // PHP 5.2.x workaround
                $options[ 'type' ] = $field_type = $class_vars[ 'type' ];
                $options[ 'label' ] = $class_vars[ 'label' ];
                $options[ 'schema' ] = PodsForm::$loaded[ $options ]->schema();
                $options[ 'options' ] = PodsForm::options_setup( $options[ 'type' ] );

                $field_types[ $field_type ] = $options;

                wp_cache_set( $field_type, $options, 'pods_field_types' );
            }

            wp_cache_set( 'field_types', $field_types, 'pods' );
        }

        return $field_types;
    }

    private function get_field_definition ( $type, $options = null ) {
        $definition = PodsForm::field_method( $type, 'schema', $options );

        return apply_filters( 'pods_field_definition', $definition, $type, $options, $this );
    }

    private function handle_field_validation ( $value, $field, $fields, $params ) {
        $type = $fields[ $field ][ 'type' ];
        $label = $fields[ $field ][ 'label' ];
        $label = empty( $label ) ? $field : $label;

        // Verify slug columns
        if ( 'slug' == $type ) {
            if ( empty( $value ) && isset( $fields[ 'name' ][ 'value' ] ) )
                $value = $fields[ 'name' ][ 'value' ];
            if ( !empty( $value ) )
                $value = pods_unique_slug( $value, $field, $params->pod, $params->pod_id, $params->id, $this );
        }
        // Verify required fields
        if ( 1 == $fields[ $field ][ 'required' ] ) {
            if ( '' == $value || null == $value )
                return pods_error( "{$label} is empty", $this );
            elseif ( 'num' == $type && !is_numeric( $value ) )
                return pods_error( "{$label} is not numeric", $this );
        }
        // Verify unique fields
        if ( 1 == $fields[ $field ][ 'unique' ] ) {
            if ( !in_array( $type, array( 'pick', 'file' ) ) ) {
                $exclude = '';
                if ( !empty( $params->id ) )
                    $exclude = "AND `id` != {$params->id}";

                // Trigger an error if not unique
                $check = pods_query( "SELECT `id` FROM `@wp_pods_tbl_{$params->pod}` WHERE `{$field}` = '{$value}' {$exclude} LIMIT 1", $this );
                if ( !empty( $check ) )
                    return pods_error( "$label needs to be unique", $this );
            }
            else {
                // handle rel check
            }
        }

        // @todo Run field validation

        $value = apply_filters( 'pods_field_validation', $value, $field, $fields, $this );
        return $value;
    }

    /**
     * Export a package
     *
     * $params['pod'] string Pod Type IDs to export
     * $params['template'] string Template IDs to export
     * $params['podpage'] string Pod Page IDs to export
     * $params['helper'] string Helper IDs to export
     *
     * @param array $params An associative array of parameters
     *
     * @since 1.9.0
     */
    public function export_package ( $params ) {
        $export = array(
            'meta' => array(
                'version' => PODS_VERSION,
                'build' => date( 'U' ),
            )
        );

        $pod_ids = $params[ 'pods' ];
        $template_ids = $params[ 'templates' ];
        $page_ids = $params[ 'pages' ];
        $helper_ids = $params[ 'helpers' ];

        if ( !empty( $pod_ids ) ) {
            $pod_ids = explode( ',', $pod_ids );
            foreach ( $pod_ids as $pod_id ) {
                $export[ 'pods' ][ $pod_id ] = $this->load_pod( array( 'id' => $pod_id ) );
            }
        }
        if ( !empty( $template_ids ) ) {
            $template_ids = explode( ',', $template_ids );
            foreach ( $template_ids as $template_id ) {
                $export[ 'templates' ][ $template_id ] = $this->load_template( array( 'id' => $template_id ) );
            }
        }
        if ( !empty( $page_ids ) ) {
            $page_ids = explode( ',', $page_ids );
            foreach ( $page_ids as $page_id ) {
                $export[ 'pod_pages' ][ $page_id ] = $this->load_page( array( 'id' => $page_id ) );
            }
        }
        if ( !empty( $helper_ids ) ) {
            $helper_ids = explode( ',', $helper_ids );
            foreach ( $helper_ids as $helper_id ) {
                $export[ 'helpers' ][ $helper_id ] = $this->load_helper( array( 'id' => $helper_id ) );
            }
        }

        if ( 1 == count( $export ) )
            return false;

        return $export;
    }

    /**
     * Replace an existing package
     *
     *
     * @param mixed $data (optional) An associative array containing a package, or the json encoded package
     *
     * @since 1.9.8
     */
    public function replace_package ( $data = false ) {
        return $this->import_package( $data, true );
    }

    /**
     * Import a package
     *
     *
     * @param mixed $data (optional) An associative array containing a package, or the json encoded package
     * @param bool $replace (optional) Replace existing items when found
     *
     * @since 1.9.0
     */
    public function import_package ( $data = false, $replace = false ) {
        $output = false;
        if ( false === $data || isset( $data[ 'action' ] ) ) {
            $data = get_option( 'pods_package' );
            $output = true;
        }
        if ( !is_array( $data ) ) {
            $json_data = @json_decode( $data, true );
            if ( !is_array( $json_data ) )
                $json_data = @json_decode( stripslashes( $data ), true );
            $data = $json_data;
        }
        if ( !is_array( $data ) || empty( $data ) ) {
            return false;
        }

        $found = array();

        if ( isset( $data[ 'pods' ] ) ) {
            $pod_fields = '';
            foreach ( $data[ 'pods' ] as $pod ) {
                $pod = pods_sanitize( $pod );

                $table_fields = array();
                $pod_fields = $pod[ 'fields' ];
                unset( $pod[ 'fields' ] );

                if ( false !== $replace ) {
                    $existing = $this->load_pod( array( 'name' => $pod[ 'name' ] ) );
                    if ( is_array( $existing ) )
                        $this->delete_pod( array( 'id' => $existing[ 'id' ] ) );
                }

                if ( empty( $pod_fields ) )
                    $pod_fields = implode( "`,`", array_keys( $pod ) );
                // Backward-compatibility (before/after helpers)
                $pod_fields = str_replace( 'before_helpers', 'pre_save_helpers', $pod_fields );
                $pod_fields = str_replace( 'after_helpers', 'post_save_helpers', $pod_fields );

                $values = implode( "','", $pod );
                $dt = pods_query( "INSERT INTO @wp_pod_types (`$pod_fields`) VALUES ('$values')" );

                $tupples = array();
                $field_columns = '';
                foreach ( $pod_fields as $fieldval ) {
                    // Escape the values
                    foreach ( $fieldval as $k => $v ) {
                        if ( empty( $v ) )
                            $v = 'null';
                        else
                            $v = pods_sanitize( $v );
                        $fieldval[ $k ] = $v;
                    }

                    // Store all table columns
                    if ( 'pick' != $fieldval[ 'coltype' ] && 'file' != $fieldval[ 'coltype' ] )
                        $table_fields[ $fieldval[ 'name' ] ] = $fieldval[ 'coltype' ];

                    $fieldval[ 'datatype' ] = $dt;
                    if ( empty( $field_columns ) )
                        $field_columns = implode( "`,`", array_keys( $fieldval ) );
                    $tupples[] = implode( "','", $fieldval );
                }
                $tupples = implode( "'),('", $tupples );
                $tupples = str_replace( "'null'", 'null', $tupples );
                pods_query( "INSERT INTO @wp_pod_fields (`$field_columns`) VALUES ('$tupples')" );

                // Create the actual table with any non-PICK columns
                $definitions = array( "id INT unsigned auto_increment primary key" );
                foreach ( $table_fields as $colname => $coltype ) {
                    $definitions[] = "`$colname` " . $this->get_field_definition( $coltype );
                }
                $definitions = implode( ',', $definitions );
                pods_query( "CREATE TABLE @wp_pod_tbl_{$pod['name']} ($definitions)" );
                if ( !isset( $found[ 'pods' ] ) )
                    $found[ 'pods' ] = array();
                $found[ 'pods' ][] = esc_textarea( $pod[ 'name' ] );
            }
        }

        if ( isset( $data[ 'templates' ] ) ) {
            foreach ( $data[ 'templates' ] as $template ) {
                $defaults = array( 'name' => '', 'code' => '' );
                $params = array_merge( $defaults, $template );
                if ( !defined( 'PODS_STRICT_MODE' ) || !PODS_STRICT_MODE )
                    $params = pods_sanitize( $params );
                if ( false !== $replace ) {
                    $existing = $this->load_template( array( 'name' => $params[ 'name' ] ) );
                    if ( is_array( $existing ) )
                        $params[ 'id' ] = $existing[ 'id' ];
                }
                $this->save_template( $params );
                if ( !isset( $found[ 'templates' ] ) )
                    $found[ 'templates' ] = array();
                $found[ 'templates' ][] = esc_textarea( $params[ 'name' ] );
            }
        }

        if ( isset( $data[ 'pod_pages' ] ) ) {
            foreach ( $data[ 'pod_pages' ] as $pod_page ) {
                $defaults = array(
                    'uri' => '',
                    'title' => '',
                    'phpcode' => '',
                    'precode' => '',
                    'page_template' => ''
                );
                $params = array_merge( $defaults, $pod_page );
                if ( !defined( 'PODS_STRICT_MODE' ) || !PODS_STRICT_MODE )
                    $params = pods_sanitize( $params );
                if ( false !== $replace ) {
                    $existing = $this->load_page( array( 'uri' => $params[ 'uri' ] ) );
                    if ( is_array( $existing ) )
                        $params[ 'id' ] = $existing[ 'id' ];
                }
                $this->save_page( $params );
                if ( !isset( $found[ 'pod_pages' ] ) )
                    $found[ 'pod_pages' ] = array();
                $found[ 'pod_pages' ][] = esc_textarea( $params[ 'uri' ] );
            }
        }

        if ( isset( $data[ 'helpers' ] ) ) {
            foreach ( $data[ 'helpers' ] as $helper ) {
                // backwards compatibility
                if ( isset( $helper[ 'helper_type' ] ) ) {
                    if ( 'before' == $helper[ 'helper_type' ] )
                        $helper[ 'helper_type' ] = 'pre_save';
                    if ( 'after' == $helper[ 'helper_type' ] )
                        $helper[ 'helper_type' ] = 'post_save';
                }
                $defaults = array( 'name' => '', 'helper_type' => 'display', 'phpcode' => '' );
                $params = array_merge( $defaults, $helper );
                if ( !defined( 'PODS_STRICT_MODE' ) || !PODS_STRICT_MODE )
                    $params = pods_sanitize( $params );
                if ( false !== $replace ) {
                    $existing = $this->load_helper( array( 'name' => $params[ 'name' ] ) );
                    if ( is_array( $existing ) )
                        $params[ 'id' ] = $existing[ 'id' ];
                }
                $this->save_helper( $params );
                if ( !isset( $found[ 'helpers' ] ) )
                    $found[ 'helpers' ] = array();
                $found[ 'helpers' ][] = esc_textarea( $params[ 'name' ] );
            }
        }

        if ( true === $output ) {
            if ( !empty( $found ) ) {
                echo '<br /><div id="message" class="updated fade">';
                echo '<h3 style="margin-top:10px;">Package Imported:</h3>';
                if ( isset( $found[ 'pods' ] ) ) {
                    echo '<h4>Pod(s)</h4>';
                    echo '<ul class="pretty"><li>' . implode( '</li><li>', $found[ 'pods' ] ) . '</li></ul>';
                }
                if ( isset( $found[ 'templates' ] ) ) {
                    echo '<h4>Template(s)</h4>';
                    echo '<ul class="pretty"><li>' . implode( '</li><li>', $found[ 'templates' ] ) . '</li></ul>';
                }
                if ( isset( $found[ 'pod_pages' ] ) ) {
                    echo '<h4>Pod Page(s)</h4>';
                    echo '<ul class="pretty"><li>' . implode( '</li><li>', $found[ 'pod_pages' ] ) . '</li></ul>';
                }
                if ( isset( $found[ 'helpers' ] ) ) {
                    echo '<h4>Helper(s)</h4>';
                    echo '<ul class="pretty"><li>' . implode( '</li><li>', $found[ 'helpers' ] ) . '</li></ul>';
                }
                echo '</div>';
            }
            else
                echo '<e><br /><div id="message" class="error fade"><p>Error: Package not imported, try again.</p></div></e>';
        }

        if ( !empty( $found ) )
            return true;
        return false;
    }

    /**
     * Validate a package
     *
     *
     * @param mixed $data (optional) An associative array containing a package, or the json encoded package
     *
     * @since 1.9.0
     */
    public function validate_package ( $data = false, $output = false ) {
        if ( is_array( $data ) && isset( $data[ 'data' ] ) ) {
            $data = $data[ 'data' ];
            $output = true;
        }
        if ( is_array( $data ) )
            $data = esc_textarea( json_encode( $data ) );

        $found = array();
        $warnings = array();

        update_option( 'pods_package', $data );

        $json_data = @json_decode( $data, true );
        if ( !is_array( $json_data ) )
            $json_data = @json_decode( stripslashes( $data ), true );

        if ( !is_array( $json_data ) || empty( $json_data ) ) {
            $warnings[] = "This is not a valid package. Please try again.";
            if ( true === $output ) {
                echo '<e><br /><div id="message" class="error fade"><p>This is not a valid package. Please try again.</p></div></e>';
                return false;
            }
            else
                return $warnings;
        }
        $data = $json_data;

        if ( 0 < strlen( $data[ 'meta' ][ 'version' ] ) && false === strpos( $data[ 'meta' ][ 'version' ], '.' ) && (int) $data[ 'meta' ][ 'version' ] < 1000 ) { // older style
            $data[ 'meta' ][ 'version' ] = implode( '.', str_split( $data[ 'meta' ][ 'version' ] ) );
        }
        elseif ( 0 < strlen( $data[ 'meta' ][ 'version' ] ) && false === strpos( $data[ 'meta' ][ 'version' ], '.' ) ) { // old style
            $data[ 'meta' ][ 'version' ] = pods_version_to_point( $data[ 'meta' ][ 'version' ] );
        }

        if ( isset( $data[ 'meta' ][ 'compatible_from' ] ) ) {
            if ( 0 < strlen( $data[ 'meta' ][ 'compatible_from' ] ) && false === strpos( $data[ 'meta' ][ 'compatible_from' ], '.' ) ) { // old style
                $data[ 'meta' ][ 'compatible_from' ] = pods_version_to_point( $data[ 'meta' ][ 'compatible_from' ] );
            }
            if ( version_compare( PODS_VERSION, $data[ 'meta' ][ 'compatible_from' ], '<' ) ) {
                $compatible_from = explode( '.', $data[ 'meta' ][ 'compatible_from' ] );
                $compatible_from = $compatible_from[ 0 ] . '.' . $compatible_from[ 1 ];
                $pods_version = explode( '.', PODS_VERSION );
                $pods_version = $pods_version[ 0 ] . '.' . $pods_version[ 1 ];
                if ( version_compare( $pods_version, $compatible_from, '<' ) )
                    $warnings[ 'version' ] = 'This package may only compatible with the newer <strong>Pods ' . pods_version_to_point( $data[ 'meta' ][ 'compatible_from' ] ) . '+</strong>, but you are currently running the older <strong>Pods ' . PODS_VERSION . '</strong><br />Unless the package author has specified it is compatible, it may not have been tested to work with your installed version of Pods.';
            }
        }
        if ( isset( $data[ 'meta' ][ 'compatible_to' ] ) ) {
            if ( 0 < strlen( $data[ 'meta' ][ 'compatible_to' ] ) && false === strpos( $data[ 'meta' ][ 'compatible_to' ], '.' ) ) { // old style
                $data[ 'meta' ][ 'compatible_to' ] = pods_version_to_point( $data[ 'meta' ][ 'compatible_to' ] );
            }
            if ( version_compare( $data[ 'meta' ][ 'compatible_to' ], PODS_VERSION, '<' ) ) {
                $compatible_to = explode( '.', $data[ 'meta' ][ 'compatible_to' ] );
                $compatible_to = $compatible_to[ 0 ] . '.' . $compatible_to[ 1 ];
                $pods_version = explode( '.', PODS_VERSION );
                $pods_version = $pods_version[ 0 ] . '.' . $pods_version[ 1 ];
                if ( version_compare( $compatible_to, $pods_version, '<' ) )
                    $warnings[ 'version' ] = 'This package may only compatible with the older <strong>Pods ' . $data[ 'meta' ][ 'compatible_to' ] . '</strong>, but you are currently running the newer <strong>Pods ' . PODS_VERSION . '</strong><br />Unless the package author has specified it is compatible, it may not have been tested to work with your installed version of Pods.';
            }
        }
        if ( !isset( $data[ 'meta' ][ 'compatible_from' ] ) && !isset( $data[ 'meta' ][ 'compatible_to' ] ) ) {
            if ( version_compare( PODS_VERSION, $data[ 'meta' ][ 'version' ], '<' ) ) {
                $compatible_from = explode( '.', $data[ 'meta' ][ 'version' ] );
                $compatible_from = $compatible_from[ 0 ] . '.' . $compatible_from[ 1 ];
                $pods_version = explode( '.', PODS_VERSION );
                $pods_version = $pods_version[ 0 ] . '.' . $pods_version[ 1 ];
                if ( version_compare( $pods_version, $compatible_from, '<' ) )
                    $warnings[ 'version' ] = 'This package was built using the newer <strong>Pods ' . $data[ 'meta' ][ 'version' ] . '</strong>, but you are currently running the older <strong>Pods ' . PODS_VERSION . '</strong><br />Unless the package author has specified it is compatible, it may not have been tested to work with your installed version of Pods.';
            }
            elseif ( version_compare( $data[ 'meta' ][ 'version' ], PODS_VERSION, '<' ) ) {
                $compatible_to = explode( '.', $data[ 'meta' ][ 'version' ] );
                $compatible_to = $compatible_to[ 0 ] . '.' . $compatible_to[ 1 ];
                $pods_version = explode( '.', PODS_VERSION );
                $pods_version = $pods_version[ 0 ] . '.' . $pods_version[ 1 ];
                if ( version_compare( $compatible_to, $pods_version, '<' ) )
                    $warnings[ 'version' ] = 'This package was built using the older <strong>Pods ' . $data[ 'meta' ][ 'version' ] . '</strong>, but you are currently running the newer <strong>Pods ' . PODS_VERSION . '</strong><br />Unless the package author has specified it is compatible, it may not have been tested to work with your installed version of Pods.';
            }
        }

        if ( isset( $data[ 'pods' ] ) ) {
            foreach ( $data[ 'pods' ] as $pod ) {
                $pod = pods_sanitize( $pod );
                $existing = $this->load_pod( array( 'name' => $pod[ 'name' ] ) );
                if ( is_array( $existing ) ) {
                    if ( !isset( $warnings[ 'pods' ] ) )
                        $warnings[ 'pods' ] = array();
                    $warnings[ 'pods' ][] = esc_textarea( $pod[ 'name' ] );
                }
                if ( !isset( $found[ 'pods' ] ) )
                    $found[ 'pods' ] = array();
                $found[ 'pods' ][] = esc_textarea( $pod[ 'name' ] );
            }
        }

        if ( isset( $data[ 'templates' ] ) ) {
            foreach ( $data[ 'templates' ] as $template ) {
                $template = pods_sanitize( $template );
                $existing = $this->load_template( array( 'name' => $template[ 'name' ] ) );
                if ( is_array( $existing ) ) {
                    if ( !isset( $warnings[ 'templates' ] ) )
                        $warnings[ 'templates' ] = array();
                    $warnings[ 'templates' ][] = esc_textarea( $template[ 'name' ] );
                }
                if ( !isset( $found[ 'templates' ] ) )
                    $found[ 'templates' ] = array();
                $found[ 'templates' ][] = esc_textarea( $template[ 'name' ] );
            }
        }

        if ( isset( $data[ 'pod_pages' ] ) ) {
            foreach ( $data[ 'pod_pages' ] as $pod_page ) {
                $pod_page = pods_sanitize( $pod_page );
                $existing = $this->load_page( array( 'uri' => $pod_page[ 'uri' ] ) );
                if ( is_array( $existing ) ) {
                    if ( !isset( $warnings[ 'pod_pages' ] ) )
                        $warnings[ 'pod_pages' ] = array();
                    $warnings[ 'pod_pages' ][] = esc_textarea( $pod_page[ 'uri' ] );
                }
                if ( !isset( $found[ 'pod_pages' ] ) )
                    $found[ 'pod_pages' ] = array();
                $found[ 'pod_pages' ][] = esc_textarea( $pod_page[ 'uri' ] );
            }
        }

        if ( isset( $data[ 'helpers' ] ) ) {
            foreach ( $data[ 'helpers' ] as $helper ) {
                $helper = pods_sanitize( $helper );
                $existing = $this->load_helper( array( 'name' => $helper[ 'name' ] ) );
                if ( is_array( $existing ) ) {
                    if ( !isset( $warnings[ 'helpers' ] ) )
                        $warnings[ 'helpers' ] = array();
                    $warnings[ 'helpers' ][] = esc_textarea( $helper[ 'name' ] );
                }
                if ( !isset( $found[ 'helpers' ] ) )
                    $found[ 'helpers' ] = array();
                $found[ 'helpers' ][] = esc_textarea( $helper[ 'name' ] );
            }
        }

        if ( true === $output ) {
            if ( !empty( $found ) ) {
                echo '<hr />';
                echo '<h3>Package Contents:</h3>';
                if ( isset( $warnings[ 'version' ] ) )
                    echo '<p><em><strong>NOTICE:</strong> ' . $warnings[ 'version' ] . '</em></p>';
                if ( isset( $found[ 'pods' ] ) ) {
                    echo '<h4>Pod(s)</h4>';
                    echo '<ul class="pretty"><li>' . implode( '</li><li>', $found[ 'pods' ] ) . '</li></ul>';
                }
                if ( isset( $found[ 'templates' ] ) ) {
                    echo '<h4>Template(s)</h4>';
                    echo '<ul class="pretty"><li>' . implode( '</li><li>', $found[ 'templates' ] ) . '</li></ul>';
                }
                if ( isset( $found[ 'pod_pages' ] ) ) {
                    echo '<h4>Pod Page(s)</h4>';
                    echo '<ul class="pretty"><li>' . implode( '</li><li>', $found[ 'pod_pages' ] ) . '</li></ul>';
                }
                if ( isset( $found[ 'helpers' ] ) ) {
                    echo '<h4>Helper(s)</h4>';
                    echo '<ul class="pretty"><li>' . implode( '</li><li>', $found[ 'helpers' ] ) . '</li></ul>';
                }
            }
            if ( 0 < count( $warnings ) && ( !isset( $warnings[ 'version' ] ) || 1 < count( $warnings ) ) ) {
                echo '<hr />';
                echo '<h3 class="red">WARNING: There are portions of this package that already exist</h3>';
                if ( isset( $warnings[ 'pods' ] ) ) {
                    echo '<h4>Pod(s)</h4>';
                    echo '<ul class="pretty"><li>' . implode( '</li><li>', $warnings[ 'pods' ] ) . '</li></ul>';
                }
                if ( isset( $warnings[ 'templates' ] ) ) {
                    echo '<h4>Template(s)</h4>';
                    echo '<ul class="pretty"><li>' . implode( '</li><li>', $warnings[ 'templates' ] ) . '</li></ul>';
                }
                if ( isset( $warnings[ 'pod_pages' ] ) ) {
                    echo '<h4>Pod Page(s)</h4>';
                    echo '<ul class="pretty"><li>' . implode( '</li><li>', $warnings[ 'pod_pages' ] ) . '</li></ul>';
                }
                if ( isset( $warnings[ 'helpers' ] ) ) {
                    echo '<h4>Helper(s)</h4>';
                    echo '<ul class="pretty"><li>' . implode( '</li><li>', $warnings[ 'helpers' ] ) . '</li></ul>';
                }
                echo '<p><input type="button" class="button-primary" style="background:#f39400;border-color:#d56500;" onclick="podsImport(\'replace_package\')" value=" Overwrite the existing package (Step 2 of 2) " />&nbsp;&nbsp;&nbsp;<input type="button" class="button-secondary" onclick="podsImportCancel()" value=" Cancel " /></p>';
                return false;
            }
            elseif ( !empty( $found ) ) {
                echo '<p><input type="button" class="button-primary" onclick="podsImport(\'import_package\')" value=" Import Package (Step 2 of 2) " />&nbsp;&nbsp;&nbsp;<input type="button" class="button-secondary" onclick="podsImportCancel()" value=" Cancel " /></p>';
                return false;
            }
            echo '<e><br /><div id="message" class="error fade"><p>Error: This package is empty, there is nothing to import.</p></div></e>';
            return false;
        }
        if ( 0 < count( $warnings ) )
            return $warnings;
        elseif ( !empty( $found ) )
            return true;
        return false;
    }

    /**
     * Import data
     *
     * @param mixed $data PHP associative array or CSV input
     * @param bool $numeric_mode Use IDs instead of the name field when matching
     *
     * @since 1.7.1
     */
    public function import ( $data, $numeric_mode = false ) {
        global $wpdb;
        if ( 'csv' == $this->format ) {
            $data = $this->csv_to_php( $data );
        }

        pods_query( "SET NAMES utf8" );
        pods_query( "SET CHARACTER SET utf8" );

        // Loop through the array of items
        $ids = array();

        // Test to see if it's an array of arrays
        if ( !is_array( @current( $data ) ) )
            $data = array( $data );

        foreach ( $data as $key => $data_row ) {
            $fields = array();

            // Loop through each field (use $this->fields so only valid columns get parsed)
            foreach ( $this->fields as $field_name => $field_data ) {
                $field_id = $field_data[ 'id' ];
                $type = $field_data[ 'type' ];
                $pickval = $field_data[ 'pickval' ];
                $field_value = $data_row[ $field_name ];

                if ( null != $field_value && false !== $field_value ) {
                    if ( 'pick' == $type || 'file' == $type ) {
                        $field_values = is_array( $field_value ) ? $field_value : array( $field_value );
                        $pick_values = array();
                        foreach ( $field_values as $pick_value ) {
                            if ( 'file' == $type ) {
                                $where = "`guid` = '" . pods_sanitize( $pick_value ) . "'";
                                if ( 0 < pods_absint( $pick_value ) && false !== $numeric_mode )
                                    $where = "`ID` = " . pods_absint( $pick_value );
                                $result = pods_query( "SELECT `ID` AS `id` FROM `{$wpdb->posts}` WHERE `post_type` = 'attachment' AND {$where} ORDER BY `ID`", $this );
                                if ( !empty( $result ) )
                                    $pick_values[ $field_name ] = $result[ 'id' ];
                            }
                            elseif ( 'pick' == $type ) {
                                if ( 'wp_taxonomy' == $pickval ) {
                                    $where = "`name` = '" . pods_sanitize( $pick_value ) . "'";
                                    if ( 0 < pods_absint( $pick_value ) && false !== $numeric_mode )
                                        $where = "`term_id` = " . pods_absint( $pick_value );
                                    $result = pods_query( "SELECT `term_id` AS `id` FROM `{$wpdb->terms}` WHERE {$where} ORDER BY `term_id`", $this );
                                    if ( !empty( $result ) )
                                        $pick_values[ $field_name ] = $result[ 'id' ];
                                }
                                elseif ( 'wp_page' == $pickval || 'wp_post' == $pickval ) {
                                    $pickval = str_replace( 'wp_', '', $pickval );
                                    $where = "`post_title` = '" . pods_sanitize( $pick_value ) . "'";
                                    if ( 0 < pods_absint( $pick_value ) && false !== $numeric_mode )
                                        $where = "`ID` = " . pods_absint( $pick_value );
                                    $result = pods_query( "SELECT `ID` AS `id` FROM `{$wpdb->posts}` WHERE `post_type` = '$pickval' AND {$where} ORDER BY `ID`", $this );
                                    if ( !empty( $result ) )
                                        $pick_values[ $field_name ] = $result[ 'id' ];
                                }
                                elseif ( 'wp_user' == $pickval ) {
                                    $where = "`display_name` = '" . pods_sanitize( $pick_value ) . "'";
                                    if ( 0 < pods_absint( $pick_value ) && false !== $numeric_mode )
                                        $where = "`ID` = " . pods_absint( $pick_value );
                                    $result = pods_query( "SELECT `ID` AS `id` FROM `{$wpdb->users}` WHERE {$where} ORDER BY `ID`", $this );
                                    if ( !empty( $result ) )
                                        $pick_values[ $field_name ] = $result[ 'id' ];
                                }
                                else {
                                    $where = "`name` = '" . pods_sanitize( $pick_value ) . "'";
                                    if ( 0 < pods_absint( $pick_value ) && false !== $numeric_mode )
                                        $where = "`id` = " . pods_absint( $pick_value );
                                    $result = pods_query( "SELECT `id` FROM `@wp_pods_tbl_{$pickval}` WHERE {$where} ORDER BY `id`", $this );
                                    if ( !empty( $result ) )
                                        $pick_values[ $field_name ] = $result[ 'id' ];
                                }
                            }
                        }
                        $field_value = implode( ',', $pick_values );
                    }
                    $fields[ $field_name ] = pods_sanitize( $field_value );
                }
            }
            if ( !empty( $fields ) ) {
                $params = array(
                    'pod' => $this->pod,
                    'columns' => $fields
                );
                $ids[] = $this->save_pod_item( $params );
            }
        }
        return $ids;
    }

    /**
     * Export data
     *
     * @since 1.7.1
     */
    public function export () {
        $data = array();
        $pod = pods( $this->pod, array( 'limit' => -1, 'search' => false, 'pagination' => false ) );
        while ($pod->fetch()) {
            $data[ $pod->field( 'id' ) ] = $this->export_pod_item( $pod->field( 'id' ) );
        }
        return $data;
    }

    /**
     * Convert CSV to a PHP array
     *
     * @param string $data The CSV input
     *
     * @since 1.7.1
     */
    public function csv_to_php ( $data ) {
        $delimiter = ",";
        $expr = "/{$delimiter}(?=(?:[^\"]*\"[^\"]*\")*(?![^\"]*\"))/";
        $data = str_replace( "\r\n", "\n", $data );
        $data = str_replace( "\r", "\n", $data );
        $lines = explode( "\n", $data );
        $field_names = explode( $delimiter, array_shift( $lines ) );
        $field_names = preg_replace( "/^\"(.*)\"$/s", "$1", $field_names );
        $out = array();
        foreach ( $lines as $line ) {
            // Skip the empty line
            if ( empty( $line ) )
                continue;
            $row = array();
            $fields = preg_split( $expr, trim( $line ) );
            $fields = preg_replace( "/^\"(.*)\"$/s", "$1", $fields );
            foreach ( $field_names as $key => $field ) {
                $row[ $field ] = $fields[ $key ];
            }
            $out[] = $row;
        }
        return $out;
    }

    /**
     * Handle filters / actions for the class
     *
     * @since 2.0.0
     */
    private function do_hook () {
        $args = func_get_args();
        if ( empty( $args ) )
            return false;
        $name = array_shift( $args );
        return pods_do_hook( "api", $name, $args, $this );
    }

    /**
     * Return an array of dummy data for select2 autocomplete input
     */
    public function select2_ajax() {
        return array(
            'results' => array(
                array(
                    'id' => 1,
                    'title' => 'Option 1',
                ),
                array(
                    'id' => 2,
                    'title' => 'Option 2',
                ),
                array(
                    'id' => 3,
                    'title' => 'Option 3',
                )
            ),
        );
    }

    /**
     * Handle methods that have been deprecated
     *
     * @since 2.0.0
     */
    public function __call ( $name, $args ) {
        $name = (string) $name;

        if ( !isset( $this->deprecated ) ) {
            require_once( PODS_DIR . 'deprecated/classes/PodsAPI.php' );
            $this->deprecated = new PodsAPI_Deprecated( $this );
        }

        if ( method_exists( $this->deprecated, $name ) ) {
            $arg_count = count( $args );
            if ( 0 == $arg_count )
                $this->deprecated->{$name}();
            elseif ( 1 == $arg_count )
                $this->deprecated->{$name}( $args[ 0 ] );
            elseif ( 2 == $arg_count )
                $this->deprecated->{$name}( $args[ 0 ], $args[ 1 ] );
            elseif ( 3 == $arg_count )
                $this->deprecated->{$name}( $args[ 0 ], $args[ 1 ], $args[ 2 ] );
            else
                $this->deprecated->{$name}( $args[ 0 ], $args[ 1 ], $args[ 2 ], $args[ 3 ] );
        }
        else
            pods_deprecated( "PodsAPI::{$name}", '2.0.0' );
    }
}

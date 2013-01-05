<?php
/**
 * @package Pods
 */
class PodsMigrate {

    /**
     * @var null|string
     */
    var $type = 'php';

    /**
     * @var array
     */
    var $types = array( 'php', 'json', 'sv', 'xml' );

    /**
     * @var null|string
     */
    var $delimiter = ',';

    /**
     * @var null
     */
    var $data;

    /**
     * @var
     */
    var $parsed;

    /**
     * @var
     */
    var $built;

    /**
     * Migrate Data to and from Pods
     *
     * @license http://www.gnu.org/licenses/gpl-2.0.html
     * @since 2.0.0
     */
    function __construct ( $type = null, $delimiter = null, $data = null ) {
        if ( !empty( $type ) && in_array( $type, $this->types ) )
            $this->type = $type;
        if ( !empty( $delimiter ) )
            $this->delimiter = $delimiter;
        if ( !empty( $data ) )
            $this->data = $data;
    }

    /*
    * Importing / Parsing / Validating Code
    */
    /**
     * @param null $data
     * @param null $type
     * @param null $delimiter
     */
    function import ( $data = null, $type = null, $delimiter = null ) {
        if ( !empty( $data ) )
            $this->data = $data;

        if ( !empty( $type ) && in_array( $type, $this->types ) )
            $this->type = $type;

        if ( !empty( $delimiter ) )
            $this->delimiter = $delimiter;

        if ( method_exists( $this, "parse_{$this->type}" ) )
            call_user_func( array( $this, 'parse_' . $this->type ) );

        return $this->import_pod_items();
    }

    /**
     * @param null $data
     * @param null $type
     */
    public function import_pod_items ( $data = null, $type = null ) {
        if ( !empty( $data ) )
            $this->data = $data;

        if ( !empty( $type ) && in_array( $type, $this->types ) )
            $this->type = $type;
    }

    /**
     * @param null $data
     * @param null $type
     *
     * @return null
     */
    public function parse ( $data = null, $type = null ) {
        if ( !empty( $data ) )
            $this->data = $data;

        if ( !empty( $type ) && in_array( $type, $this->types ) )
            $this->type = $type;

        if ( method_exists( $this, "parse_{$this->type}" ) )
            call_user_func( array( $this, 'parse_' . $this->type ) );

        return $this->data;
    }

    /**
     * @param null $data
     *
     * @return bool
     */
    public function parse_json ( $data = null ) {
        if ( !empty( $data ) )
            $this->data = $data;

        $items = @json_decode( $this->data, true );

        if ( !is_array( $items ) )
            return false;

        $data = array( 'columns' => array(), 'items' => array() );

        foreach ( $items as $key => $item ) {
            if ( !is_array( $item ) )
                continue;

            foreach ( $item as $column => $value ) {
                if ( !in_array( $column, $data[ 'columns' ] ) )
                    $data[ 'columns' ][] = $column;
            }

            $data[ 'items' ][ $key ] = $item;
        }

        $this->parsed = $data;

        return $this->parsed;
    }

    /**
     * @param null $data
     * @param null $delimiter
     *
     * @return bool
     */
    public function parse_sv ( $data = null, $delimiter = null ) {
        if ( !empty( $data ) )
            $this->data = $data;

        if ( !empty( $delimiter ) )
            $this->delimiter = $delimiter;


        $rows = @str_getcsv( $this->data, "\n" );

        if ( empty( $rows ) || 2 > count( $rows ) )
            return false;

        $data = array( 'columns' => array(), 'items' => array() );

        foreach ( $rows as $key => $row ) {
            if ( 0 == $key )
                $data[ 'columns' ] = @str_getcsv( $row, $this->delimiter );
            else {
                $row = @str_getcsv( $row, $this->delimiter );

                $data[ 'items' ][ $key ] = array();

                foreach ( $data[ 'columns' ] as $ckey => $column ) {
                    $data[ 'items' ][ $key ][ $column ] = ( isset( $row[ $ckey ] ) ? $row[ $ckey ] : '' );
                }
            }
        }

        $this->parsed = $data;

        return $this->parsed;
    }

    /**
     * @param null $data
     *
     * @return bool
     */
    public function parse_xml ( $data = null ) {
        if ( !empty( $data ) )
            $this->data = $data;

        $xml = new SimpleXMLElement( $this->data );

        if ( !isset( $xml->items ) )
            return false;

        $data = array( 'columns' => array(), 'items' => array() );

        if ( isset( $xml->columns ) ) {
            foreach ( $xml->columns->children() as $child ) {
                $sub = $child->getName();

                if ( empty( $sub ) || 'column' != $sub )
                    continue;

                $column = false;

                if ( isset( $child->name ) ) {
                    if ( is_array( $child->name ) )
                        $column = $child->name[ 0 ];
                    else
                        $column = $child->name;

                    $data[ 'columns' ][] = $column;
                }
            }
        }

        foreach ( $xml->items->children() as $child ) {
            $sub = $child->getName();

            if ( empty( $sub ) || 'item' != $sub )
                continue;

            $item = array();

            $attributes = $child->attributes();

            if ( !empty( $attributes ) ) {
                foreach ( $attributes as $column => $value ) {
                    if ( !in_array( $column, $data[ 'columns' ] ) )
                        $data[ 'columns' ][] = $column;

                    $item[ $column ] = $value;
                }
            }

            $item_child = $child->children();

            if ( !empty( $item_child ) ) {
                foreach ( $item_child->children() as $data_child ) {
                    $column = $data_child->getName();

                    if ( !in_array( $column, $data[ 'columns' ] ) )
                        $data[ 'columns' ][] = $column;

                    $item[ $column ] = $item_child->$column;
                }
            }

            if ( !empty( $item ) )
                $data[ 'items' ][] = $item;
        }

        $this->parsed = $data;

        return $this->parsed;
    }

    /**
     * @param null $data
     *
     * @return mixed
     *
     * @todo For much much later
     */
    public function parse_sql ( $data = null ) {
        if ( !empty( $data ) )
            $this->data = $data;

        $this->parsed = $data;

        return $this->parsed;
    }

    /*
    * Exporting / Building Code
    */
    /**
     * @param null $data
     * @param null $type
     * @param null $delimiter
     */
    public function export ( $data = null, $type = null, $delimiter = null ) {
        if ( !empty( $data ) )
            $this->data = $data;

        if ( !empty( $type ) && in_array( $type, $this->types ) )
            $this->type = $type;

        if ( !empty( $delimiter ) )
            $this->delimiter = $delimiter;

        if ( method_exists( $this, "build_{$this->type}" ) )
            call_user_func( array( $this, 'build_' . $this->type ) );

        return $this->export_pod_items();
    }

    /**
     * @param null $data
     */
    public function export_pod_items ( $data = null ) {
        if ( !empty( $data ) )
            $this->data = $data;
    }

    /**
     * @param null $data
     * @param null $type
     *
     * @return null
     */
    public function build ( $data = null, $type = null ) {
        if ( !empty( $data ) )
            $this->data = $data;

        if ( !empty( $type ) && in_array( $type, $this->types ) )
            $this->type = $type;

        if ( method_exists( $this, "build_{$this->type}" ) )
            call_user_func( array( $this, 'build_' . $this->type ) );

        return $this->data;
    }

    /**
     * @param null $data
     *
     * @return bool
     */
    public function build_json ( $data = null ) {
        if ( !empty( $data ) )
            $this->data = $data;

        if ( empty( $this->data ) || !is_array( $this->data ) )
            return false;

        $data = array( 'items' => array( 'count' => count( $this->data[ 'items' ] ), 'item' => array() ) );

        foreach ( $this->data[ 'items' ] as $item ) {
            $row = array();

            foreach ( $this->data[ 'columns' ] as $column ) {
                $row[ $column ] = $item[ $column ];
            }

            $data[ 'items' ][ 'item' ][] = $row;
        }

        $this->built = @json_encode( $data );

        return $this->built;
    }

    /**
     * @param null $data
     * @param null $delimiter
     *
     * @return bool
     */
    public function build_sv ( $data = null, $delimiter = null ) {
        if ( !empty( $data ) )
            $this->data = $data;

        if ( !empty( $delimiter ) )
            $this->delimiter = $delimiter;

        if ( empty( $this->data ) || !is_array( $this->data ) )
            return false;

        $head = $lines = '';

        foreach ( $this->data[ 'columns' ] as $column ) {
            $head .= '"' . $column . '"' . $this->delimiter;
        }

        $head = substr( $head, 0, -1 );

        foreach ( $this->data[ 'items' ] as $item ) {
            $line = '';

            foreach ( $this->data[ 'columns' ] as $column ) {
                $line .= '"' . $item[ $column ] . '"' . $this->delimiter;
            }

            $lines .= substr( $line, 0, -1 );
        }

        $this->built = $head . $lines;

        return $this->built;
    }

    /**
     * @param null $data
     *
     * @return bool
     */
    public function build_xml ( $data = null ) {
        if ( !empty( $data ) )
            $this->data = $data;

        if ( empty( $this->data ) || !is_array( $this->data ) )
            return false;

        $head = '<' . '?' . 'xml version="1.0" encoding="utf-8" ' . '?' . '>' . "\r\n<items count=\"" . count( $this->full_data ) . "\">\r\n";
        $lines = '';

        foreach ( $this->data[ 'items' ] as $item ) {
            $line = "\t<item>\r\n";

            foreach ( $this->data[ 'columns' ] as $column ) {
                $line .= "\t\t<{$column}><![CDATA[" . $item[ $column ] . "]]></{$column}>\r\n";
            }

            $line .= "\t</item>\r\n";
            $lines .= $line;
        }

        $foot = '</items>';

        $this->built = $head . $lines . $foot;

        return $this->built;
    }

    /**
     * @param null $data
     *
     * @return mixed
     */
    public function build_sql ( $data = null ) {
        if ( !empty( $data ) )
            $this->data = $data;

        $this->built = $data;

        return $this->built;
    }

    /*
    * The real enchilada!
    */
    /* EXAMPLES
    //// minimal import (if your fields match on both your pods and tables)
    $import = array('my_pod' => array('table' => 'my_table')); // if your table name doesn't match the pod name
    $import = array('my_pod'); // if your table name matches your pod name

    //// advanced import
    $import = array();
    $import['my_pod'] = array();
    $import['my_pod']['fields']['this_field'] = 'field_name_in_table'; // if the field name doesn't match on table and pod
    $import['my_pod']['fields'][] = 'that_field'; // if the field name matches on table and pod
    $import['my_pod']['fields']['this_other_field'] = array('filter' => 'wpautop'); // if you want the value to be different than is provided, set a filter function to use [filter uses = filter_name($value,$rowdata)]
    $import['my_pod']['fields']['another_field'] = array('field' => 'the_real_field_in_table','filter' => 'my_custom_function'); // if you want the value to be filtered, and the field name doesn't match on the table and pod
    $import[] = 'my_other_pod'; // if your table name matches your pod name
    $import['another_pod'] = array('update_on' => 'main_field'); // you can update a pod item if the value of this field is the same on both tables
    $import['another_pod'] = array('reset' => true); // you can choose to reset all data in a pod before importing

    //// run import
    pods_import_it($import);
    */
    /**
     * @param $import
     * @param bool $output
     */
    public function heres_the_beef ( $import, $output = true ) {
        global $wpdb;

        $api = pods_api();

        for ( $i = 0; $i < 40000; $i++ ) {
            echo "  \t"; // extra spaces
        }

        $default_data = array(
            'pod' => null,
            'table' => null,
            'reset' => null,
            'update_on' => null,
            'where' => null,
            'fields' => array(),
            'row_filter' => null,
            'pre_save' => null,
            'post_save' => null,
            'sql' => null,
            'sort' => null,
            'limit' => null,
            'page' => null,
            'output' => null,
            'page_var' => 'ipg',
            'bypass_helpers' => false
        );

        $default_field_data = array( 'field' => null, 'filter' => null );

        if ( !is_array( $import ) )
            $import = array( $import );
        elseif ( empty( $import ) )
            die( '<h1 style="color:red;font-weight:bold;">ERROR: No imports configured</h1>' );

        $import_counter = 0;
        $total_imports = count( $import );
        $paginated = false;
        $avg_time = -1;
        $total_time = 0;
        $counter = 0;
        $avg_unit = 100;
        $avg_counter = 0;

        foreach ( $import as $datatype => $data ) {
            $import_counter++;

            flush();
            @ob_end_flush();
            usleep( 50000 );

            if ( !is_array( $data ) ) {
                $datatype = $data;
                $data = array( 'table' => $data );
            }

            if ( isset( $data[ 0 ] ) )
                $data = array( 'table' => $data[ 0 ] );

            $data = array_merge( $default_data, $data );

            if ( null === $data[ 'pod' ] )
                $data[ 'pod' ] = array( 'name' => $datatype );

            if ( false !== $output )
                echo "<br />" . date( 'Y-m-d h:i:sa' ) . " - <em>" . $data[ 'pod' ][ 'name' ] . "</em> - <em>" . $data[ 'pod' ][ 'name' ] . "</em> - <strong>Loading Pod: " . $data[ 'pod' ][ 'name' ] . "</strong>\n";

            if ( 2 > count( $data[ 'pod' ] ) )
                $data[ 'pod' ] = $api->load_pod( array( 'name' => $data[ 'pod' ][ 'name' ] ) );

            if ( empty( $data[ 'pod' ][ 'fields' ] ) )
                continue;

            if ( null === $data[ 'table' ] )
                $data[ 'table' ] = $data[ 'pod' ][ 'name' ];

            if ( $data[ 'reset' ] === true ) {
                if ( false !== $output )
                    echo "<br />" . date( 'Y-m-d h:i:sa' ) . " - <em>" . $data[ 'pod' ][ 'name' ] . "</em> - <strong style='color:blue;'>Resetting Pod: " . $data[ 'pod' ][ 'name' ] . "</strong>\n";

                $api->reset_pod( array( 'id' => $data[ 'pod' ][ 'id' ], 'name' => $data[ 'pod' ][ 'name' ] ) );
            }

            if ( null === $data[ 'sort' ] && null !== $data[ 'update_on' ] && isset( $data[ 'fields' ][ $data[ 'update_on' ] ] ) ) {
                if ( isset( $data[ 'fields' ][ $data[ 'update_on' ] ][ 'field' ] ) )
                    $data[ 'sort' ] = $data[ 'fields' ][ $data[ 'update_on' ] ][ 'field' ];
                else
                    $data[ 'sort' ] = $data[ 'update_on' ];
            }

            $page = 1;

            if ( false !== $data[ 'page_var' ] && isset( $_GET[ $data[ 'page_var' ] ] ) )
                $page = absval( $_GET[ $data[ 'page_var' ] ] );

            if ( null === $data[ 'sql' ] )
                $data[ 'sql' ] = "SELECT * FROM {$data['table']}" . ( null !== $data[ 'where' ] ? " WHERE {$data['where']}" : '' ) . ( null !== $data[ 'sort' ] ? " ORDER BY {$data['sort']}" : '' ) . ( null !== $data[ 'limit' ] ? " LIMIT " . ( 1 < $page ? ( ( $page - 1 ) * $data[ 'limit' ] ) . ',' : '' ) . "{$data['limit']}" : '' );

            if ( false !== $output )
                echo "<br />" . date( 'Y-m-d h:i:sa' ) . " - <em>" . $data[ 'pod' ][ 'name' ] . "</em> - Getting Results: " . $data[ 'pod' ][ 'name' ] . "\n";

            if ( false !== $output )
                echo "<br />" . date( 'Y-m-d h:i:sa' ) . " - <em>" . $data[ 'pod' ][ 'name' ] . "</em> - Using Query: <small><code>" . $data[ 'sql' ] . "</code></small>\n";

            $result = $wpdb->get_results( $data[ 'sql' ], ARRAY_A );

            if ( false !== $output )
                echo "<br />" . date( 'Y-m-d h:i:sa' ) . " - <em>" . $data[ 'pod' ][ 'name' ] . "</em> - Results Found: " . count( $result ) . "\n";

            $avg_time = -1;
            $total_time = 0;
            $counter = 0;
            $avg_unit = 100;
            $avg_counter = 0;
            $result_count = count( $result );
            $paginated = false;

            if ( false !== $data[ 'page_var' ] && $result_count == $data[ 'limit' ] )
                $paginated = "<input type=\"button\" onclick=\"document.location=\'" . pods_ui_var_update( array( $data[ 'page_var' ] => $page + 1 ), false, false ) . "\';\" value=\"  Continue Import &raquo;  \" />";

            if ( $result_count < $avg_unit && 5 < $result_count )
                $avg_unit = number_format( $result_count / 5, 0, '', '' );
            elseif ( 2000 < $result_count && 10 < count( $data[ 'pod' ][ 'fields' ] ) )
                $avg_unit = 40;

            $data[ 'count' ] = $result_count;
            timer_start();

            if ( false !== $output && 1 == $import_counter )
                echo "<div style='width:50%;background-color:navy;padding:10px 10px 30px 10px;color:#FFF;position:absolute;top:10px;left:25%;text-align:center;'><p id='progress_status' align='center'>" . date( 'Y-m-d h:i:sa' ) . " - <em>" . $data[ 'pod' ][ 'name' ] . "</em> - Running Importer..</p><br /><small>This will automatically update every " . $avg_unit . " rows</small></div>\n";

            foreach ( $result as $k => $row ) {
                flush();
                @ob_end_flush();
                usleep( 50000 );

                if ( false !== $output )
                    echo "<br />" . date( 'Y-m-d h:i:sa' ) . " - <em>" . $data[ 'pod' ][ 'name' ] . "</em> - Processing Row #" . ( $k + 1 ) . "\n";

                if ( null !== $data[ 'row_filter' ] && function_exists( $data[ 'row_filter' ] ) ) {
                    if ( false !== $output )
                        echo "<br />" . date( 'Y-m-d h:i:sa' ) . " - <em>" . $data[ 'pod' ][ 'name' ] . "</em> - Filtering <strong>" . $data[ 'row_filter' ] . "</strong> on Row #" . ( $k + 1 ) . "\n";

                    $row = $data[ 'row_filter' ]( $row, $data );
                }

                if ( !is_array( $row ) )
                    continue;

                $params = array(
                    'datatype' => $data[ 'pod' ][ 'name' ],
                    'columns' => array(),
                    'bypass_helpers' => $data[ 'bypass_helpers' ]
                );

                foreach ( $data[ 'pod' ][ 'fields' ] as $fk => $field_info ) {
                    $field = $field_info[ 'name' ];

                    if ( !empty( $data[ 'fields' ] ) && !isset( $data[ 'fields' ][ $field ] ) && !in_array( $field, $data[ 'fields' ] ) )
                        continue;

                    if ( isset( $data[ 'fields' ][ $field ] ) ) {
                        if ( is_array( $data[ 'fields' ][ $field ] ) )
                            $field_data = $data[ 'fields' ][ $field ];
                        else
                            $field_data = array( 'field' => $data[ 'fields' ][ $field ] );
                    }
                    else
                        $field_data = array();

                    if ( !is_array( $field_data ) ) {
                        $field = $field_data;
                        $field_data = array();
                    }

                    $field_data = array_merge( $default_field_data, $field_data );

                    if ( null === $field_data[ 'field' ] )
                        $field_data[ 'field' ] = $field;

                    $data[ 'fields' ][ $field ] = $field_data;
                    $value = '';

                    if ( isset( $row[ $field_data[ 'field' ] ] ) )
                        $value = $row[ $field_data[ 'field' ] ];

                    if ( null !== $field_data[ 'filter' ] ) {
                        if ( function_exists( $field_data[ 'filter' ] ) ) {
                            if ( false !== $output )
                                echo "<br />" . date( 'Y-m-d h:i:sa' ) . " - <em>" . $data[ 'pod' ][ 'name' ] . "</em> - Filtering <strong>" . $field_data[ 'filter' ] . "</strong> on Field: " . $field . "\n";

                            $value = $field_data[ 'filter' ]( $value, $row, $data );
                        }
                        else
                            $value = '';
                    }

                    if ( 1 > strlen( $value ) && 1 == $field_info[ 'required' ] )
                        die( '<h1 style="color:red;font-weight:bold;">ERROR: Field Required for <strong>' . $field . '</strong></h1>' );

                    $params[ 'columns' ][ $field ] = $value;

                    unset( $value );
                    unset( $field_data );
                    unset( $field_info );
                    unset( $fk );
                }

                if ( empty( $params[ 'columns' ] ) )
                    continue;

                $params[ 'columns' ] = pods_sanitize( $params[ 'columns' ] );

                if ( null !== $data[ 'update_on' ] && isset( $params[ 'columns' ][ $data[ 'update_on' ] ] ) ) {
                    if ( false !== $output )
                        echo "<br />" . date( 'Y-m-d h:i:sa' ) . " - <em>" . $data[ 'pod' ][ 'name' ] . "</em> - Checking for Existing Item\n";

                    $check = new Pod( $data[ 'pod' ][ 'name' ] );
                    $check->findRecords( array(
                                             'orderby' => 't.id',
                                             'limit' => 1,
                                             'where' => "t.{$data['update_on']} = '{$params['columns'][$data['update_on']]}'",
                                             'search' => false,
                                             'page' => 1
                                         ) );

                    if ( 0 < $check->getTotalRows() ) {
                        $check->fetchRecord();

                        $params[ 'tbl_row_id' ] = $check->get_field( 'id' );
                        $params[ 'pod_id' ] = $check->get_pod_id();

                        if ( false !== $output )
                            echo "<br />" . date( 'Y-m-d h:i:sa' ) . " - <em>" . $data[ 'pod' ][ 'name' ] . "</em> - Found Existing Item w/ ID: " . $params[ 'tbl_row_id' ] . "\n";

                        unset( $check );
                    }

                    if ( !isset( $params[ 'tbl_row_id' ] ) && false !== $output )
                        echo "<br />" . date( 'Y-m-d h:i:sa' ) . " - <em>" . $data[ 'pod' ][ 'name' ] . "</em> - Existing item not found - Creating New\n";
                }

                if ( null !== $data[ 'pre_save' ] && function_exists( $data[ 'pre_save' ] ) ) {
                    if ( false !== $output )
                        echo "<br />" . date( 'Y-m-d h:i:sa' ) . " - <em>" . $data[ 'pod' ][ 'name' ] . "</em> - Running Pre Save <strong>" . $data[ 'pre_save' ] . "</strong> on " . $data[ 'pod' ][ 'name' ] . "\n";

                    $params = $data[ 'pre_save' ]( $params, $row, $data );
                }

                $id = $api->save_pod_item( $params );

                if ( false !== $output )
                    echo "<br />" . date( 'Y-m-d h:i:sa' ) . " - <em>" . $data[ 'pod' ][ 'name' ] . "</em> - <strong>Saved Row #" . ( $k + 1 ) . " w/ ID: " . $id . "</strong>\n";

                $params[ 'tbl_row_id' ] = $id;

                if ( null !== $data[ 'post_save' ] && function_exists( $data[ 'post_save' ] ) ) {
                    if ( false !== $output )
                        echo "<br />" . date( 'Y-m-d h:i:sa' ) . " - <em>" . $data[ 'pod' ][ 'name' ] . "</em> - Running Post Save <strong>" . $data[ 'post_save' ] . "</strong> on " . $data[ 'pod' ][ 'name' ] . "\n";

                    $data[ 'post_save' ]( $params, $row, $data );
                }

                unset( $params );
                unset( $result[ $k ] );
                unset( $row );

                wp_cache_flush();
                $wpdb->queries = array();

                $avg_counter++;
                $counter++;

                if ( $avg_counter == $avg_unit && false !== $output ) {
                    $avg_counter = 0;
                    $avg_time = timer_stop( 0, 10 );
                    $total_time += $avg_time;
                    $rows_left = $result_count - $counter;
                    $estimated_time_left = ( ( $total_time / $counter ) * $rows_left ) / 60;
                    $percent_complete = 100 - ( ( $rows_left * 100 ) / $result_count );

                    echo "<script type='text/javascript'>document.getElementById('progress_status').innerHTML = '" . date( 'Y-m-d h:i:sa' ) . " - <em>" . $data[ 'pod' ][ 'name' ] . "</em><br /><strong>" . $percent_complete . "% Complete</strong><br /><strong>Estimated Time Left:</strong> " . $estimated_time_left . " minute(s) or " . ( $estimated_time_left / 60 ) . " hours(s)<br /><strong>Time Spent:</strong> " . ( $total_time / 60 ) . " minute(s)<br /><strong>Rows Done:</strong> " . ( $result_count - $rows_left ) . "/" . $result_count . "<br /><strong>Rows Left:</strong> " . $rows_left . "';</script>\n";
                    echo "<br />" . date( 'Y-m-d h:i:sa' ) . " - <em>" . $data[ 'pod' ][ 'name' ] . "</em> - <strong>Updated Status:</strong> " . $percent_complete . "% Complete</strong>\n";
                }
            }

            if ( false !== $output ) {
                $avg_counter = 0;
                $avg_time = timer_stop( 0, 10 );
                $total_time += $avg_time;
                $rows_left = $result_count - $counter;
                $estimated_time_left = ( ( $total_time / $counter ) * $rows_left ) / 60;
                $percent_complete = 100 - ( ( $rows_left * 100 ) / $result_count );

                echo "<script type='text/javascript'>document.getElementById('progress_status').innerHTML = '" . date( 'Y-m-d h:i:sa' ) . " - <em>" . $data[ 'pod' ][ 'name' ] . "</em><br /><strong style=\'color:green;\'>100% Complete</strong><br /><br /><strong>Time Spent:</strong> " . ( $total_time / 60 ) . " minute(s)<br /><strong>Rows Imported:</strong> " . $result_count . ( false !== $paginated ? "<br /><br />" . $paginated : '' ) . "';</script>\n";
                echo "<br />" . date( 'Y-m-d h:i:sa' ) . " - <em>" . $data[ 'pod' ][ 'name' ] . "</em> - <strong style='color:green;'>Done Importing: " . $data[ 'pod' ][ 'name' ] . "</strong>\n";
            }

            unset( $result );
            unset( $import[ $datatype ] );
            unset( $datatype );
            unset( $data );

            wp_cache_flush();
            $wpdb->queries = array();
        }

        if ( false !== $output ) {
            $avg_counter = 0;
            $avg_time = timer_stop( 0, 10 );
            $total_time += $avg_time;
            $rows_left = $result_count - $counter;

            echo "<script type='text/javascript'>document.getElementById('progress_status').innerHTML = '" . date( 'Y-m-d h:i:sa' ) . " - <strong style=\'color:green;\'>Import Complete</strong><br /><br /><strong>Time Spent:</strong> " . ( $total_time / 60 ) . " minute(s)<br /><strong>Rows Imported:</strong> " . $result_count . ( false !== $paginated ? "<br /><br />" . $paginated : '' ) . "';</script>\n";
            echo "<br />" . date( 'Y-m-d h:i:sa' ) . " - <strong style='color:green;'>Import Complete</strong>\n";
        }
    }
}
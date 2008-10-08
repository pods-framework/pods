<?php
// Include the MySQL connection
include(realpath('../../../../wp-config.php'));

foreach ($_GET as $key => $val)
{
    ${$key} = mysql_real_escape_string(stripslashes(trim($val)));
}

$name = strtolower(str_replace(' ', '_', $name));

// Add new datatype
if ('pod' == $type)
{
    if (!empty($name))
    {
        $result = mysql_query("SELECT id FROM wp_pod_types WHERE name = '$name' LIMIT 1");
        if (0 < mysql_num_rows($result))
        {
            die('Error: Pod by this name already exists!');
        }
        mysql_query("INSERT INTO wp_pod_types (name) VALUES ('$name')") or die('Error: Problem adding new pod.');
        $pod_id = mysql_insert_id();

        mysql_query("CREATE TABLE tbl_$name (id int unsigned auto_increment primary key, name varchar(128), body text)") or die('Error: Problem adding pod database table.');
        mysql_query("INSERT INTO wp_pod_fields (datatype, name, coltype) VALUES ($pod_id, 'name', 'txt'),($pod_id, 'body', 'desc')") or die('Error: Problem adding name and body columns.');
        die($pod_id);
    }
    die('Error: Enter a pod name!');
}
// Add new column
else
{
    $result = mysql_query("SELECT id FROM wp_pod_fields WHERE datatype = $datatype AND name = '$name' LIMIT 1");
    if (0 < mysql_num_rows($result))
    {
        die('Error: Column by this name already exists!');
    }
    mysql_query("INSERT INTO wp_pod_fields (datatype, name, coltype, pickval, sister_field_id) VALUES ('$datatype', '$name', '$coltype', '$pickval', '$sister_field_id')");
    $field_id = mysql_insert_id();

    if (empty($pickval))
    {
        $result = mysql_query("SELECT name FROM wp_pod_types WHERE id = $datatype LIMIT 1");
        if (0 < mysql_num_rows($result))
        {
            $row = mysql_fetch_assoc($result);
            $datatype_name = $row['name'];
        }

        $dbtypes = array(
            'bool' => 'bool',
            'date' => 'datetime',
            'num' => 'decimal(9,2)',
            'txt' => 'varchar(128)',
            'file' => 'varchar(128)',
            'desc' => 'text'
        );
        $dbtype = $dbtypes[$coltype];
        mysql_query("ALTER TABLE tbl_$datatype_name ADD COLUMN $name $dbtype") or die('Error: Could not create column!');
    }
    else
    {
        mysql_query("UPDATE wp_pod_fields SET sister_field_id = '$field_id' WHERE id = '$sister_field_id' LIMIT 1");
    }
}


<?php
/*
==================================================
Standardize queries and error reporting

$sql                SQL query
$error              SQL failure message
$results_error      Triggered when results > 0
$no_results_error   Triggered when results = 0
==================================================
*/
function pod_query($sql, $error = 'SQL failed', $results_error = null, $no_results_error = null)
{
    $result = mysql_query($sql) or die("Error: $error; SQL: $sql; Response: " . mysql_error());
    if (0 < @mysql_num_rows($result))
    {
        if (!empty($results_error))
        {
            die("Error: $results_error");
        }
    }
    else
    {
        if (!empty($no_results_error))
        {
            die("Error: $no_results_error");
        }
    }

    if (false !== strpos($sql, 'INSERT'))
    {
        $result = mysql_insert_id();
    }
    return $result;
}


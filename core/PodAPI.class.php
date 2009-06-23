<?php
/*
==================================================
PodAPI.class.php

http://codex.uproot.us/
==================================================
*/
class PodAPI
{
    var $fields;
    var $datatype;

    function PodAPI($datatype, $fields = null)
    {
        if (!empty($fields))
        {
            $this->fields = $fields;
        }
        $this->datatype = $datatype;
    }

    function getPodFields()
    {
        $result = pod_query("SELECT * FROM @wp_pod_fields WHERE datatype = $this->datatype LIMIT 1");
    }

    function addItems($data, $fields = null)
    {
        
    }

    function editItems($data, $fields = null)
    {
        
    }

    function dropItems($data)
    {
        
    }
}

<?php
$ref = $_GET['ref'];
$version = $_GET['v'];
echo file_get_contents("http://pods.uproot.us/stats/?v=$version&ref=$ref");

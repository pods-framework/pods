<?php
$dir = urldecode($_POST['dir']);
$realpath = realpath("../../../../$dir");
$items = scandir($realpath);
natcasesort($items);

$data = '<ul class="jqueryFileTree hidden">';
foreach ($items as $item)
{
    if (!in_array($item, array('.', '..', 'base')))
    {
        $realpath .= '/';
        if (is_dir($realpath . $item))
        {
            $data .= '<li class="directory collapsed"><a href="javascript:;" rel="' . $dir . $item . '/">' . htmlentities($item) . '</a></li>';
        }
        elseif (is_file($realpath . $item))
        {
            $data .= '<li class="file"><a href="javascript:;" rel="' . $dir . $item . '">' . htmlentities($item) . '</a></li>';
        }
    }
}
echo $data . '</ul>';

<?php
if (ctype_digit($id))
{
    $type = empty($type) ? 'news' : $type;
    $Record = new Pod($type, $id);
    echo $Record->showTemplate('detail');
}


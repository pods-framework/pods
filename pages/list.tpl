<?php
$type = empty($type) ? 'news' : $type;

$Record = new Pod($type);
$Record->findRecords('id DESC');
?>

<h2><?php echo ucwords($type); ?> Listing</h2>

<?php
echo $Record->getFilters();
echo $Record->getPagination();
echo $Record->showTemplate('list');


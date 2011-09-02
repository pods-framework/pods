<?php
$page = $this->page;
$rows_per_page = $this->rpp;
$total_rows = $this->getTotalRows();
$total_pages = ceil($total_rows / $rows_per_page);
$type = $this->datatype;

// Build the $_GET string
$request_uri = explode('?', $_SERVER['REQUEST_URI']);
$request_uri = $request_uri[0] . '?';
foreach ($_GET as $key => $val) {
    if ('pg' != $key) {
        $request_uri .= "$key=$val&";
    }
}
?>
    <span class="pager"><?php echo $label; ?>
<?php
if (1 < $page) {
?>
    <a href="<?php echo $request_uri; ?>pg=1" class="pageNum firstPage">1</a>
<?php
}
if (1 < ($page - 100)) {
?>
    <a href="<?php echo $request_uri; ?>pg=<?php echo ($page - 100); ?>" class="pageNum"><?php echo ($page - 100); ?></a>
<?php
}
if (1 < ($page - 10)) {
?>
    <a href="<?php echo $request_uri; ?>pg=<?php echo ($page - 10); ?>" class="pageNum"><?php echo ($page - 10); ?></a>
<?php
}
for ($i = 2; $i > 0; $i--) {
    if (1 < ($page - $i)) {
?>
    <a href="<?php echo $request_uri; ?>pg=<?php echo ($page - $i); ?>" class="pageNum"><?php echo ($page - $i); ?></a>
<?php
    }
}
?>
    <span class="pageNum currentPage"><?php echo $page; ?></span>
<?php
for ($i = 1; $i < 3; $i++) {
    if ($total_pages > ($page + $i)) {
?>
    <a href="<?php echo $request_uri; ?>pg=<?php echo ($page + $i); ?>" class="pageNum"><?php echo ($page + $i); ?></a>
<?php
    }
}
if ($total_pages > ($page + 10)) {
?>
    <a href="<?php echo $request_uri; ?>pg=<?php echo ($page + 10); ?>" class="pageNum"><?php echo ($page + 10); ?></a>
<?php
}
if ($total_pages > ($page + 100)) {
?>
    <a href="<?php echo $request_uri; ?>pg=<?php echo ($page + 100); ?>" class="pageNum"><?php echo ($page + 100); ?></a>
<?php
}
if ($page < $total_pages) {
?>
    <a href="<?php echo $request_uri; ?>pg=<?php echo $total_pages; ?>" class="pageNum lastPage"><?php echo $total_pages; ?></a>
<?php
}
?>
    </span>

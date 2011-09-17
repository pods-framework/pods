<?php
ob_start();
require_once(preg_replace("/wp-content.*/","wp-load.php",__FILE__));
require_once(preg_replace("/wp-content.*/","/wp-admin/includes/admin.php",__FILE__));
ob_end_clean();

header('Content-Type: text/html; charset=' . get_bloginfo('charset'));

global $wpdb;

// Sanitize input
$params = stripslashes_deep($_POST);
foreach ($params as $key => $val) {
    $params[$key] = pods_sanitize(trim($val));
}

$params = (object) $params;

if ('wp_handle_upload' == $params->action || 'wp_handle_upload_advanced' == $params->action) {
    // Flash often fails to send cookies with the POST or upload, so we need to pass it in GET or POST instead
    if ( is_ssl() && empty($_COOKIE[SECURE_AUTH_COOKIE]) && !empty($_REQUEST['auth_cookie']) )
        $_COOKIE[SECURE_AUTH_COOKIE] = $_REQUEST['auth_cookie'];
    elseif ( empty($_COOKIE[AUTH_COOKIE]) && !empty($_REQUEST['auth_cookie']) )
        $_COOKIE[AUTH_COOKIE] = $_REQUEST['auth_cookie'];
    if ( empty($_COOKIE[LOGGED_IN_COOKIE]) && !empty($_REQUEST['logged_in_cookie']) )
        $_COOKIE[LOGGED_IN_COOKIE] = $_REQUEST['logged_in_cookie'];
    global $current_user;
    unset($current_user);
}

if (isset($params->_wpnonce) && false === wp_verify_nonce($params->_wpnonce, 'pods-' . $params->action))
    die('<e>Access denied');

/**
 * Access Checking
 */
$browse_disabled = false;
if (defined('PODS_DISABLE_FILE_BROWSER') && true === PODS_DISABLE_FILE_BROWSER)
    $browse_disabled = true;
elseif (defined('PODS_FILES_REQUIRE_LOGIN') && is_bool(PODS_FILES_REQUIRE_LOGIN) && true === PODS_FILES_REQUIRE_LOGIN && !is_user_logged_in())
    $browse_disabled = true;
elseif (defined('PODS_FILES_REQUIRE_LOGIN') && !is_bool(PODS_FILES_REQUIRE_LOGIN) && (!is_user_logged_in() || !current_user_can(PODS_FILES_REQUIRE_LOGIN)))
    $browse_disabled = true;
$upload_disabled = false;
if (defined('PODS_DISABLE_FILE_UPLOAD') && true === PODS_DISABLE_FILE_UPLOAD)
    $upload_disabled = true;
elseif (defined('PODS_UPLOAD_REQUIRE_LOGIN') && is_bool(PODS_UPLOAD_REQUIRE_LOGIN) && true === PODS_UPLOAD_REQUIRE_LOGIN && !is_user_logged_in())
    $upload_disabled = true;
elseif (defined('PODS_UPLOAD_REQUIRE_LOGIN') && !is_bool(PODS_UPLOAD_REQUIRE_LOGIN) && (!is_user_logged_in() || !current_user_can(PODS_UPLOAD_REQUIRE_LOGIN)))
    $upload_disabled = true;

/**
 * Load file list
 */
if ('browse_files' == $params->action && false === $browse_disabled) {
    $search = (0 < strlen($params->search)) ? "AND (post_title LIKE '%{$params->search}%' OR guid LIKE '%{$params->search}%')" : '';

    $result = pod_query("SELECT id, guid FROM {$wpdb->posts} WHERE post_type = 'attachment' {$search} ORDER BY guid ASC");

    if (0 < mysql_num_rows($result)) {
        while ($row = mysql_fetch_assoc($result)) {
            $guid = substr($row['guid'], strrpos($row['guid'], '/') + 1);
    ?>
        <div class="file_match" rel="<?php echo $row['id']; ?>"><?php echo $guid; ?></div>
    <?php
        }
    }
    else
        echo 'Nothing found.';
}

/**
 * Upload a new file
 */
elseif ('wp_handle_upload' == $params->action && false === $upload_disabled) {
    $attachment_id = media_handle_upload('Filedata', 0);
    if (is_object($attachment_id)) {
        $errors = array();
        foreach ($attachment_id->errors['upload_error'] as $error_code => $error_message) {
            $errors[] = $error_message;
        }
        echo 'Error: <div style="color:#FF0000">' . implode('</div><div>', $errors) . '</div>';
    }
    else
        echo $attachment_id;
}

/**
 * Upload a new file (advanced - returns URL and ID)
 */
elseif ('wp_handle_upload_advanced' == $params->action && false === $upload_disabled) {
    $attachment_id = media_handle_upload('Filedata', 0);
    if (is_object($attachment_id)) {
        $errors = array();
        foreach ($attachment_id->errors['upload_error'] as $error_code => $error_message) {
            $errors[] = $error_message;
        }
        echo 'Error: <div style="color:#FF0000">' . implode('</div><div>', $errors) . '</div>';
    }
    else {
        $attachment = get_post($attachment_id, ARRAY_A);
        $attachment['filename'] = basename($attachment['guid']);
        echo json_encode($attachment);
    }
}
else
    echo 'Error: <div style="color:#FF0000">Access denied. Contact your website admin to resolve.</div>';
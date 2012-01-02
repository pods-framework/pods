<?php
if (empty($_POST['pod_cols']) || !wp_verify_nonce($_POST['pods-import-create-pod-nonce'], 'pods-import-create-pod')) {
    // Handle error
    exit();
}

if (false) {
    // Create the pod first
    $podsApi = new PodsAPI();

    // New pod data from $_POST
    $newPodData = array_map('trim', $_POST['new_pod_data']);

    $podArguments = array('id'   => '',
                          'name' => strtolower($newPodData['pod_name']));

    // Save the pod
    $newlyAddedPod = $podsApi->save_pod($podArguments);

    if (!is_numeric($newlyAddedPod)) {
        // Throw fatal error, pod couldn't be created
    }


    // Save all converted columns
    $podColumns  = array_map('trim', $_POST['pod_cols']);
    $podColTypes = array_map('trim', $_POST['pod_col_types']); 

    // Loop through all new pod columns
    foreach ($podColumns as $oldCol => $newField) {
        $podColumnArgs = array('id'     => '',
                               'pod_id' => $newlyAddedPod,
                               'name'   => $newField,
                               'type'   => $podColTypes[$oldCol]);

        $podsApi->save_column($podColumnArgs);
    }

    // Copy all data from old table, to new pod.
    $podsData = new PodsData();

    // Get all the data from the table
    foreach ($podsData->select(array('from' => $_GET['table'])) as $dataRow) {
        // Save each one as a new pod item
        // Will need to use map here as well
        $podsApi->save_pod_item();
    }
}

?>
<div class="wrap pods-admin">
    <h2>Successful Table Import</h2>
    <hr />
    <div id="pods-part-left"> 
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis sit amet pellentesque tortor. Vivamus non sem sit amet metus dapibus hendrerit ac at lectus. Nunc libero neque, varius vitae luctus ac, semper molestie massa. In hendrerit, odio in lacinia bibendum, purus dolor condimentum dui, ac imperdiet quam ipsum non felis. Phasellus ornare sem ut mi varius vulputate. Aenean tempus sollicitudin felis. Aliquam sed dui ipsum, ut mattis turpis.

            Quisque tempus pretium rutrum. Aliquam vestibulum sem in nunc scelerisque feugiat. Donec vel nulla sit amet felis bibendum commodo. Suspendisse non leo erat, sit amet lacinia nunc. Sed risus erat, malesuada non faucibus vitae, tempor et est. Etiam malesuada sodales elementum. Integer dolor dui, congue quis iaculis sodales, posuere vitae nulla.</p>


        <h3><?php echo $_POST['new_pod_data']['pod_name']; ?> Pod created!</h3>
        <p>Congratulations, your data structured has been converted to a pod, and all data has been imported.</p>    
        <p>Continue by <a href="#">modifying <?php echo $_POST['new_pod_data']['pod_name']; ?></a>, or <a href="/wp-admin/admin.php?page=pods-import-table">importing another table.</a></p>
    </div>

</div>
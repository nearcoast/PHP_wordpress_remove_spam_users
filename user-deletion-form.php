<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include WordPress core
require_once('wp-load.php');

// Security check: Ensure the script is run by an administrator
if (!current_user_can('manage_options')) {
    die('Access Denied');
}

// Check if deletion has been confirmed
if (isset($_POST['confirm_deletion']) && $_POST['confirm_deletion'] === 'yes') {
    // Process deletion
    $user_ids_to_delete = isset($_POST['user_ids']) ? explode(',', $_POST['user_ids']) : [];

    foreach ($user_ids_to_delete as $user_id) {
        wp_delete_user((int)$user_id);
    }

    echo "Deleted " . count($user_ids_to_delete) . " users.<br>";

    // Option to return to the script for a new search and deletion
    echo '<a href="' . esc_url($_SERVER['PHP_SELF']) . '">Run Script Again with New Inputs</a>';
} elseif ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Search and list users based on input criteria

    $email_domains_input = isset($_POST['email_domains']) ? $_POST['email_domains'] : '';
    $specific_word = isset($_POST['specific_word']) ? $_POST['specific_word'] : '';

    // Convert comma-separated domains into an array and trim spaces
    $email_domains = array_map('trim', explode(',', $email_domains_input));
    $combined_users = [];

    foreach ($email_domains as $domain) {
        if (!empty($domain)) {
            $users = get_users(array(
                'search'         => '*' . $domain,
                'search_columns' => array('user_email'),
                'fields'         => 'all',
            ));

            foreach ($users as $user) {
                $combined_users[$user->ID] = $user->user_email;
            }
        }
    }

    if (!empty($specific_word)) {
        $users = get_users(array(
            'search'         => '*' . $specific_word . '*',
            'search_columns' => array('user_email'),
            'fields'         => 'all',
        ));

        foreach ($users as $user) {
            $combined_users[$user->ID] = $user->user_email;
        }
    }

    if (count($combined_users) > 0) {
        echo "Found " . count($combined_users) . " users for potential deletion:<br>";
        foreach ($combined_users as $user_id => $user_email) {
            echo "UserID: $user_id, Email: $user_email<br>";
        }

        // Form for confirming deletion
        ?>
        <form action="" method="post">
            <input type="hidden" name="confirm_deletion" value="yes">
            <input type="hidden" name="user_ids" value="<?php echo implode(',', array_keys($combined_users)); ?>">
            <input type="submit" value="Confirm Deletion of Listed Users">
        </form>
        <?php
    } else {
        echo "No users found with the specified criteria.";
    }
} else {
    // Initial form for specifying search criteria
    ?>
    <form action="" method="post">
        Email Domains (comma-separated):<br>
        <input type="text" name="email_domains" value="">
        <br>
        Specific Word:<br>
        <input type="text" name="specific_word" value="">
        <br><br>
        <input type="submit" value="List Users">
    </form>
    <?php
}
?>

<?php
// Path to the fix_permissions.sh script
$command = '/var/www/html/chat_app1/fix_permissions.sh'; // Adjust the path if needed

// Run the shell command
$output = shell_exec($command);

// Display the output of the script
echo "<pre>$output</pre>";
?>

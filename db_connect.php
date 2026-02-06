<?php
require 'vendor/autoload.php'; // Ensure Composer dependencies are loaded

// Your MongoDB Credentials
$uri = "mongodb+srv://hightech:hightech@cluster0.0ombj8t.mongodb.net/forms?retryWrites=true&w=majority";

function getDBConnection() {
    global $uri;
    try {
        $client = new MongoDB\Client($uri);
        // Ping the database to ensure connection is valid
        $client->selectDatabase('admin')->command(['ping' => 1]);
        
        // Return the specific collection: Database = 'forms', Collection = 'candidates'
        return $client->forms->candidates; 
    } catch (Exception $e) {
        die("<div style='color:red; font-weight:bold;'>Database Connection Failed: " . $e->getMessage() . "</div>");
    }
}
?>

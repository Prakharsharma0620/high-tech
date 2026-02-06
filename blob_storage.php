<?php
// Your Vercel Blob Token
define('BLOB_TOKEN', 'vercel_blob_rw_GwacKuSWlagObLHd_lk2mZREXNTAuVkJG3BnAqohO7VITq9');

function uploadToVercel($fileArray) {
    // Check if file exists and has no errors
    if (!isset($fileArray) || $fileArray['error'] !== UPLOAD_ERR_OK) {
        return null; 
    }

    $filename = time() . '-' . basename($fileArray['name']);
    
    // Read file content
    $content = file_get_contents($fileArray['tmp_name']);
    
    // Vercel Blob API Endpoint (Direct PUT upload)
    $url = "https://blob.vercel-storage.com/$filename";

    $ch = curl_init($url);

    // Required Headers for Vercel Blob
    $headers = [
        "Authorization: Bearer " . BLOB_TOKEN,
        "x-api-version: 1",
        "Content-Type: " . $fileArray['type']
    ];

    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT"); 
    curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if (curl_errno($ch)) {
        throw new Exception('Curl Upload Error: ' . curl_error($ch));
    }
    
    curl_close($ch);

    // Success Check (200-299 status code)
    if ($httpCode >= 200 && $httpCode < 300) {
        $data = json_decode($response, true);
        return $data['url'] ?? null; // Return the new Image URL
    } else {
        throw new Exception("Vercel Upload Failed (Status $httpCode): " . $response);
    }
}
?>

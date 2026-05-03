<?php

// Script para probar el endpoint de appointments
require_once 'vendor/autoload.php';

use Symfony\Component\HttpClient\HttpClient;

$client = HttpClient::create();

try {
    echo "Probando endpoint GET /api/appointments...\n";
    
    $response = $client->request('GET', 'http://127.0.0.1:8000/api/appointments');
    
    echo "Status Code: " . $response->getStatusCode() . "\n";
    echo "Content-Type: " . $response->getHeaders()['content-type'][0] . "\n";
    
    $content = $response->getContent();
    echo "Response length: " . strlen($content) . " bytes\n";
    
    echo "First 500 chars:\n";
    echo substr($content, 0, 500) . "\n";
    
    $data = json_decode($content, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo "\nJSON is valid\n";
        echo "Data type: " . gettype($data) . "\n";
        if (is_array($data)) {
            echo "Array length: " . count($data) . "\n";
            if (count($data) > 0) {
                echo "First item keys: " . implode(', ', array_keys($data[0])) . "\n";
            }
        }
    } else {
        echo "\nJSON decode error: " . json_last_error_msg() . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

?>

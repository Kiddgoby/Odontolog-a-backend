<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

require_once '../config/database.php';

$data = json_decode(file_get_contents('php://input'), true);
$patientId = $data['patientId'] ?? null;

if (!$patientId) {
    http_response_code(400);
    echo json_encode(['error' => 'patientId es requerido']);
    exit;
}

try {
    $conn = getDatabaseConnection();
    
    $stmt = $conn->prepare("SELECT * FROM odontograms WHERE patientId = ? LIMIT 1");
    $stmt->execute([$patientId]);
    $odontogram = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$odontogram) {
        $stmt = $conn->prepare("
            INSERT INTO odontograms (patientId, created_at, updated_at) 
            VALUES (?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
        ");
        $stmt->execute([$patientId]);
        $odontogramId = $conn->lastInsertId();
        
        $odontogram = [
            'id' => $odontogramId,
            'patientId' => $patientId,
            'teeth' => new stdClass(),
            'notes' => null,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
    } else {
        $odontogramId = $odontogram['id'];
    }
    
    echo json_encode([
        'success' => true,
        'data' => $odontogram
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Error en la base de datos',
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
}
?>

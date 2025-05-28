<?php
// System/assets/update_item_condition.php
require_once '../../includes/auth_check.php';
require_once '../config/db.php';

// Only allow admin and staff to update conditions
if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'staff') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Check if this is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['asset_id']) || !isset($input['item_number']) || !isset($input['condition'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

$asset_id = intval($input['asset_id']);
$item_number = intval($input['item_number']);
$condition = $input['condition'];

// Validate condition
$valid_conditions = ['excellent', 'good', 'fair', 'poor', 'damaged'];
if (!in_array($condition, $valid_conditions)) {
    echo json_encode(['success' => false, 'message' => 'Invalid condition value']);
    exit;
}

try {
    $pdo->beginTransaction();
    
    // Update the specific item's condition
    $stmt = $pdo->prepare("UPDATE asset_items SET `condition` = ? WHERE asset_id = ? AND item_number = ?");
    $result = $stmt->execute([$condition, $asset_id, $item_number]);
    
    if (!$result) {
        throw new Exception('Failed to update item condition');
    }
    
    // Check if any rows were affected
    if ($stmt->rowCount() === 0) {
        throw new Exception('Item not found or no changes made');
    }
    
    // Update the main asset condition based on all individual items
    updateMainAssetCondition($pdo, $asset_id);
    
    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Condition updated successfully']);
    
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

// Function to update main asset condition based on individual items
function updateMainAssetCondition($pdo, $asset_id) {
    // Get all conditions from individual items
    $stmt = $pdo->prepare("SELECT `condition`, COUNT(*) as count FROM asset_items WHERE asset_id = ? GROUP BY `condition` ORDER BY count DESC");
    $stmt->execute([$asset_id]);
    $conditions = $stmt->fetchAll();
    
    if (!empty($conditions)) {
        $mainCondition = 'good'; // Default condition
        
        // If there's only one condition type and all items have the same condition
        if (count($conditions) == 1) {
            $mainCondition = $conditions[0]['condition'];
        } else {
            // If there are mixed conditions, set to 'mixed'
            $mainCondition = 'mixed';
        }
        
        // Update main asset condition
        $updateStmt = $pdo->prepare("UPDATE assets SET `condition` = ? WHERE asset_id = ?");
        $updateStmt->execute([$mainCondition, $asset_id]);
    }
}
?>
<?php
// System/borrow/request.php
require_once '../../includes/auth_check.php';
require_once '../config/db.php';

// Initialize variables
$success_message = '';
$error_message = '';

// Handle immediate borrow request creation (when client clicks "Borrow This Item")
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['asset_id'])) {
    
    // Get parameters from URL
    $asset_id = $_GET['asset_id'];
    $item_number = isset($_GET['item_number']) ? $_GET['item_number'] : null;
    
    // Validate user session
    if (!isset($_SESSION['user_id'])) {
        $error_message = "User session not found. Please log in again.";
    } else {
        $user_id = $_SESSION['user_id'];
        
        try {
            // Verify user exists in database
            $userCheckStmt = $pdo->prepare("SELECT user_id, full_name FROM users WHERE user_id = ?");
            $userCheckStmt->execute([$user_id]);
            $userExists = $userCheckStmt->fetch();
            
            if (!$userExists) {
                $error_message = "Invalid user session. Please log in again.";
            } else {
                $quantity = 1; // Default quantity
                
                try {
                    // Check if asset exists and is available
                    $stmt = $pdo->prepare("SELECT asset_name, quantity, status FROM assets WHERE asset_id = ?");
                    $stmt->execute([$asset_id]);
                    $asset = $stmt->fetch();
                    
                    if (!$asset) {
                        throw new Exception("Asset not found.");
                    }
                    
                    if ($asset['quantity'] < 1) {
                        throw new Exception("Asset is not available for borrowing.");
                    }
                    
                    // If specific item number is provided, check if it's available
                    if ($item_number) {
                        $itemStmt = $pdo->prepare("SELECT status FROM asset_items WHERE asset_id = ? AND item_number = ?");
                        $itemStmt->execute([$asset_id, $item_number]);
                        $item = $itemStmt->fetch();
                        
                        if (!$item || $item['status'] !== 'available') {
                            throw new Exception("This specific item is not available for borrowing.");
                        }
                    }
                    
                    // Check if user already has a pending request for this asset
                    $checkStmt = $pdo->prepare("SELECT borrow_id FROM borrow_requests WHERE user_id = ? AND asset_id = ? AND status = 'pending'");
                    $checkStmt->execute([$user_id, $asset_id]);
                    $existingRequest = $checkStmt->fetch();
                    
                    if ($existingRequest) {
                        throw new Exception("You already have a pending request for this asset.");
                    }
                    
                    // Insert borrow request into database
                    $stmt = $pdo->prepare("
                        INSERT INTO borrow_requests (
                            user_id, 
                            asset_id, 
                            item_number, 
                            quantity, 
                            status, 
                            request_date
                        ) VALUES (?, ?, ?, ?, 'pending', NOW())
                    ");
                    
                    $result = $stmt->execute([
                        $user_id,
                        $asset_id,
                        $item_number,
                        $quantity
                    ]);
                    
                    if ($result) {
                        $success_message = "Borrow request submitted successfully! The admin will review your request shortly.";
                    } else {
                        $error_message = "Failed to submit borrow request. Please try again.";
                    }
                    
                } catch (Exception $e) {
                    $error_message = "Error: " . $e->getMessage();
                }
            }
        } catch (Exception $e) {
            $error_message = "Database error: " . $e->getMessage();
        }
    }
}

// Include header for consistent styling
include '../../includes/header.php';
?>

<style>
    .container { 
        max-width: 600px; 
        margin: 50px auto; 
        padding: 30px; 
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .alert { 
        padding: 20px; 
        margin: 20px 0; 
        border-radius: 8px; 
        font-size: 16px;
        text-align: center;
    }
    .alert-success { 
        background-color: #d4edda; 
        color: #155724; 
        border: 1px solid #c3e6cb; 
    }
    .alert-danger { 
        background-color: #f8d7da; 
        color: #721c24; 
        border: 1px solid #f5c6cb; 
    }
    .btn { 
        padding: 12px 24px; 
        margin: 10px 8px; 
        text-decoration: none; 
        border-radius: 6px; 
        display: inline-block;
        font-weight: bold;
        text-align: center;
        transition: all 0.3s ease;
        border: none;
        cursor: pointer;
    }
    .btn-primary { 
        background-color: #007bff; 
        color: white; 
    }
    .btn-primary:hover {
        background-color: #0056b3;
        transform: translateY(-1px);
    }
    .btn-secondary { 
        background-color: #6c757d; 
        color: white; 
    }
    .btn-secondary:hover {
        background-color: #545b62;
        transform: translateY(-1px);
    }
    h2 {
        color: #333;
        margin-bottom: 30px;
        text-align: center;
    }
    .button-group {
        text-align: center;
        margin-top: 30px;
    }
    .success-icon {
        font-size: 48px;
        color: #28a745;
        margin-bottom: 20px;
        text-align: center;
    }
    .error-icon {
        font-size: 48px;
        color: #dc3545;
        margin-bottom: 20px;
        text-align: center;
    }
</style>

<div class="container">
    <h2>Borrow Request</h2>
    
    <?php if ($success_message): ?>
        <div class="success-icon">✅</div>
        <div class="alert alert-success">
            <strong>Success!</strong><br>
            <?php echo htmlspecialchars($success_message); ?>
        </div>
        <div class="button-group">
            <a href="../assets/list.php" class="btn btn-primary">Back to Assets</a>
            
        </div>
    <?php endif; ?>
    
    <?php if ($error_message): ?>
        <div class="error-icon">❌</div>
        <div class="alert alert-danger">
            <strong>Error!</strong><br>
            <?php echo htmlspecialchars($error_message); ?>
        </div>
        <div class="button-group">
            <a href="../assets/list.php" class="btn btn-primary">Back to Assets</a>
            <?php if (strpos($error_message, 'session') !== false): ?>
                <a href="../../login.php" class="btn btn-secondary">Login Again</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <?php if (!$success_message && !$error_message): ?>
        <div class="error-icon">⚠️</div>
        <div class="alert alert-danger">
            <strong>Invalid Request!</strong><br>
            Please access this page through the asset listing.
        </div>
        <div class="button-group">
            <a href="../assets/list.php" class="btn btn-primary">Go to Assets</a>
        </div>
    <?php endif; ?>
</div>

<?php include '../../includes/footer.php'; ?>
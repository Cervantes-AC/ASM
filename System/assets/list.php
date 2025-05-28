<?php
// System/assets/list.php
require_once '../../includes/auth_check.php';
require_once '../config/db.php';

$userRole = $_SESSION['role'];

// Fetch all assets with quantity > 0
$stmt = $pdo->query("SELECT * FROM assets WHERE quantity > 0 ORDER BY date_added DESC");
$assets = $stmt->fetchAll();

// Function to get individual items for assets with multiple quantities
function getIndividualItems($pdo, $asset_id, $quantity) {
    // First, check if we have individual items stored in asset_items table
    $stmt = $pdo->prepare("SELECT * FROM asset_items WHERE asset_id = ? ORDER BY item_number");
    $stmt->execute([$asset_id]);
    $storedItems = $stmt->fetchAll();
    
    // If we don't have stored items, create them
    if (empty($storedItems)) {
        // Get the main asset condition - escape 'condition' with backticks
        $assetStmt = $pdo->prepare("SELECT `condition` FROM assets WHERE asset_id = ?");
        $assetStmt->execute([$asset_id]);
        $mainAsset = $assetStmt->fetch();
        $inheritedCondition = $mainAsset['condition'] ?? 'good';
        
        // Create individual items with inherited condition - escape 'condition' with backticks
        for ($i = 1; $i <= $quantity; $i++) {
            $insertStmt = $pdo->prepare("INSERT INTO asset_items (asset_id, item_number, `condition`, status) VALUES (?, ?, ?, 'available')");
            $insertStmt->execute([$asset_id, $i, $inheritedCondition]);
        }
        
        // Fetch the newly created items
        $stmt = $pdo->prepare("SELECT * FROM asset_items WHERE asset_id = ? ORDER BY item_number");
        $stmt->execute([$asset_id]);
        $storedItems = $stmt->fetchAll();
    }
    
    // If quantity has changed, adjust the items
    if (count($storedItems) !== $quantity) {
        if (count($storedItems) < $quantity) {
            // Add missing items - use 'good' as default condition for new items
            // Don't inherit from main asset to avoid condition propagation issues
            $defaultCondition = 'good';
            
            for ($i = count($storedItems) + 1; $i <= $quantity; $i++) {
                $insertStmt = $pdo->prepare("INSERT INTO asset_items (asset_id, item_number, `condition`, status) VALUES (?, ?, ?, 'available')");
                $insertStmt->execute([$asset_id, $i, $defaultCondition]);
            }
        } else {
            // Remove excess items (keep only the first $quantity items)
            $deleteStmt = $pdo->prepare("DELETE FROM asset_items WHERE asset_id = ? AND item_number > ?");
            $deleteStmt->execute([$asset_id, $quantity]);
        }
        
        // Fetch updated items
        $stmt = $pdo->prepare("SELECT * FROM asset_items WHERE asset_id = ? ORDER BY item_number");
        $stmt->execute([$asset_id]);
        $storedItems = $stmt->fetchAll();
    }
    
    // Update the main asset condition based on individual items
    // Only call this when we actually need to sync conditions, not on every load
    updateMainAssetCondition($pdo, $asset_id);
    
    return $storedItems;
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
        
        // Update main asset condition without affecting individual items
        $updateStmt = $pdo->prepare("UPDATE assets SET `condition` = ? WHERE asset_id = ?");
        $updateStmt->execute([$mainCondition, $asset_id]);
    }
}

// Function to update individual item condition (called via AJAX)
function updateIndividualItemCondition($pdo, $asset_id, $item_number, $new_condition) {
    // Update only the specific item's condition
    $stmt = $pdo->prepare("UPDATE asset_items SET `condition` = ? WHERE asset_id = ? AND item_number = ?");
    $result = $stmt->execute([$new_condition, $asset_id, $item_number]);
    
    if ($result) {
        // Update the main asset condition to reflect the change
        updateMainAssetCondition($pdo, $asset_id);
        return true;
    }
    return false;
}

include '../../includes/header.php';
?>

<!-- Add the asset_items table creation script if it doesn't exist -->
<script>
// This should be run once to create the asset_items table
// CREATE TABLE IF NOT EXISTS asset_items (
//     item_id INT PRIMARY KEY AUTO_INCREMENT,
//     asset_id INT NOT NULL,
//     item_number INT NOT NULL,
//     `condition` ENUM('excellent', 'good', 'fair', 'poor', 'damaged', 'mixed') DEFAULT 'good',
//     status ENUM('available', 'borrowed', 'maintenance', 'damaged') DEFAULT 'available',
//     borrowed_by INT NULL,
//     borrowed_date DATETIME NULL,
//     return_date DATETIME NULL,
//     notes TEXT,
//     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
//     FOREIGN KEY (asset_id) REFERENCES assets(asset_id) ON DELETE CASCADE,
//     FOREIGN KEY (borrowed_by) REFERENCES users(user_id) ON DELETE SET NULL,
//     UNIQUE KEY unique_asset_item (asset_id, item_number)
// );
</script>

<style>
    .table-container {
        overflow-x: auto;
        margin-top: 1rem;
    }

    .styled-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.95rem;
        min-width: 600px;
        border: 1px solid #ddd;
    }

    .styled-table thead tr {
        background-color: #009879;
        color: #ffffff;
        text-align: left;
    }

    .styled-table th, .styled-table td {
        padding: 12px 15px;
        border: 1px solid #ddd;
    }

    .styled-table tbody tr {
        border-bottom: 1px solid #dddddd;
    }

    .styled-table tbody tr:nth-of-type(even) {
        background-color: #f3f3f3;
    }

    .styled-table tbody tr:hover {
        background-color: #f1f1f1;
    }

    .action-link {
        color: #007BFF;
        text-decoration: none;
        font-weight: 500;
        margin-right: 8px;
    }

    .action-link:hover {
        text-decoration: underline;
    }

    .action-link.danger {
        color: #dc3545;
    }

    .unavailable {
        color: #999;
        font-style: italic;
    }

    .btn {
        display: inline-block;
        background-color: #009879;
        color: #fff;
        padding: 8px 12px;
        border-radius: 4px;
        text-decoration: none;
        font-weight: bold;
    }

    .btn:hover {
        background-color: #007f6d;
    }

    .expandable-category {
        cursor: pointer;
        color: #009879;
        font-weight: bold;
        text-decoration: underline;
        position: relative;
    }

    .expandable-category:hover {
        color: #007f6d;
    }

    .expandable-category::after {
        content: " ▼";
        font-size: 0.8em;
        color: #666;
    }

    .expandable-category.expanded::after {
        content: " ▲";
    }

    .detail-row {
        display: none;
        background-color: #f8f9fa;
    }

    .detail-row.show {
        display: table-row;
    }

    .detail-row td {
        padding-left: 30px;
        border-left: 3px solid #009879;
        font-size: 0.9em;
        color: #666;
    }

    .detail-table {
        width: 100%;
        margin: 10px 0;
    }

    .detail-table th,
    .detail-table td {
        padding: 8px 12px;
        border: 1px solid #ddd;
        text-align: left;
    }

    .detail-table th {
        background-color: #e9ecef;
        font-weight: bold;
    }

    .item-status-available {
        color: #28a745;
        font-weight: bold;
    }

    .item-status-borrowed {
        color: #dc3545;
        font-weight: bold;
    }

    .item-status-maintenance {
        color: #ffc107;
        font-weight: bold;
    }

    .item-status-damaged {
        color: #6c757d;
        font-weight: bold;
    }

    .condition-excellent {
        color: #28a745;
        font-weight: bold;
    }

    .condition-good {
        color: #17a2b8;
        font-weight: bold;
    }

    .condition-fair {
        color: #ffc107;
        font-weight: bold;
    }

    .condition-poor {
        color: #dc3545;
        font-weight: bold;
    }

    .condition-damaged {
        color: #6c757d;
        font-weight: bold;
    }

    .condition-mixed {
        color: #6f42c1;
        font-weight: bold;
    }

    .condition-select {
        padding: 4px 8px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 0.9em;
    }

    h2 {
        margin-top: 20px;
    }

    .zero-quantity-notice {
        background-color: #d1ecf1;
        border: 1px solid #bee5eb;
        color: #0c5460;
        padding: 10px;
        border-radius: 4px;
        margin-bottom: 20px;
    }
</style>

<script>
function toggleDetails(assetId) {
    const detailRows = document.querySelectorAll('.detail-row-' + assetId);
    const categoryCell = document.querySelector('.expandable-' + assetId);
    
    detailRows.forEach(row => {
        if (row.classList.contains('show')) {
            row.classList.remove('show');
            categoryCell.classList.remove('expanded');
        } else {
            row.classList.add('show');
            categoryCell.classList.add('expanded');
        }
    });
}

function updateItemCondition(assetId, itemNumber, newCondition) {
    // Send AJAX request to update individual item condition
    fetch('update_item_condition.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            asset_id: assetId,
            item_number: itemNumber,
            condition: newCondition
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Refresh the page to show updated conditions
            location.reload();
        } else {
            alert('Error updating condition: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error updating condition');
    });
}
</script>

<h2>Assets List</h2>

<?php if ($userRole === 'admin' || $userRole === 'staff'): ?>
    <p><a href="add.php" class="btn">Add New Asset</a></p>
<?php endif; ?>

<?php
// Check if there are assets with zero quantity
$zeroQtyStmt = $pdo->query("SELECT COUNT(*) as count FROM assets WHERE quantity = 0");
$zeroQtyCount = $zeroQtyStmt->fetch()['count'];
if ($zeroQtyCount > 0): ?>
    <div class="zero-quantity-notice">
        <strong>Notice:</strong> <?= $zeroQtyCount ?> asset(s) with zero quantity are hidden from this list. 
        <?php if ($userRole === 'admin' || $userRole === 'staff'): ?>
            <a href="list.php?show_all=1">Show all assets including zero quantity</a>
        <?php endif; ?>
    </div>
<?php endif; ?>

<div class="table-container">
    <table class="styled-table">
        <thead>
            <tr>
                <th>Asset Name</th>
                <th>Category</th>
                <th>Serial Code</th>
                <th>Quantity</th>
                <th>Condition</th>
                <th>Date Added</th>
                <?php if ($userRole === 'admin' || $userRole === 'staff' || $userRole === 'member'): ?>
                    <th>Actions</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($assets as $asset): ?>
                <tr>
                    <td><?= htmlspecialchars($asset['asset_name']) ?></td>
                    <td>
                        <?php if ($asset['quantity'] > 1 && empty($asset['serial_code'])): ?>
                            <span class="expandable-category expandable-<?= $asset['asset_id'] ?>" 
                                  onclick="toggleDetails(<?= $asset['asset_id'] ?>)">
                                <?= htmlspecialchars($asset['category']) ?>
                            </span>
                        <?php else: ?>
                            <?= htmlspecialchars($asset['category']) ?>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($asset['serial_code']) ?: '-' ?></td>
                    <td><?= (int)$asset['quantity'] ?></td>
                    <td>
                        <span class="condition-<?= $asset['condition'] ?>">
                            <?= ucfirst(htmlspecialchars($asset['condition'])) ?>
                        </span>
                    </td>
                    <td><?= htmlspecialchars($asset['date_added']) ?></td>
                    <?php if ($userRole === 'admin' || $userRole === 'staff'): ?>
                        <td>
                            <a href="edit.php?id=<?= $asset['asset_id'] ?>" class="action-link">Edit</a>
                            <a href="delete.php?id=<?= $asset['asset_id'] ?>" class="action-link danger" onclick="return confirm('Delete this asset?');">Delete</a>
                        </td>
                    <?php elseif ($userRole === 'member'): ?>
                        <td>
                            <?php if ($asset['status'] === 'available' && $asset['quantity'] > 0): ?>
                                <a href="../borrow/request.php?asset_id=<?= $asset['asset_id'] ?>" class="action-link">Request to Borrow</a>
                            <?php else: ?>
                                <span class="unavailable">Not Available</span>
                            <?php endif; ?>
                        </td>
                    <?php else: ?>
                        <td>-</td>
                    <?php endif; ?>
                </tr>
                
                <?php if ($asset['quantity'] > 1 && empty($asset['serial_code'])): ?>
                    <tr class="detail-row detail-row-<?= $asset['asset_id'] ?>">
                        <td colspan="<?= ($userRole === 'admin' || $userRole === 'staff' || $userRole === 'member') ? '7' : '6' ?>">
                            <strong>Individual Items for "<?= htmlspecialchars($asset['asset_name']) ?>":</strong>
                            <table class="detail-table">
                                <thead>
                                    <tr>
                                        <th>Item #</th>
                                        <th>Condition</th>
                                        <th>Status</th>
                                        <?php if ($userRole === 'member'): ?>
                                            <th>Action</th>
                                        <?php endif; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $individualItems = getIndividualItems($pdo, $asset['asset_id'], $asset['quantity']);
                                    foreach ($individualItems as $item): 
                                    ?>
                                        <tr>
                                            <td><?= htmlspecialchars($asset['asset_name']) ?> #<?= $item['item_number'] ?></td>
                                            <td>
                                                <span class="condition-<?= $item['condition'] ?>">
                                                    <?= ucfirst($item['condition']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="item-status-<?= $item['status'] ?>">
                                                    <?= ucfirst($item['status']) ?>
                                                </span>
                                            </td>
                                            <?php if ($userRole === 'member'): ?>
                                                <td>
                                                    <?php if ($item['status'] === 'available'): ?>
                                                        <a href="../borrow/request.php?asset_id=<?= $asset['asset_id'] ?>&item_number=<?= $item['item_number'] ?>" class="action-link">Borrow This Item</a>
                                                    <?php else: ?>
                                                        <span class="unavailable">Not Available</span>
                                                    <?php endif; ?>
                                                </td>
                                            <?php endif; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                <?php endif; ?>
            <?php endforeach; ?>
            <?php if (empty($assets)): ?>
                <tr><td colspan="7">No assets with available quantity found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include '../../includes/footer.php'; ?>
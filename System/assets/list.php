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
    
    return $storedItems;
}

// Function to generate a consistent color for a category
function getCategoryColor($category) {
    // Generate a hash from the category name to ensure consistent colors
    $hash = md5($category);
    // Extract RGB values from the hash
    $r = hexdec(substr($hash, 0, 2));
    $g = hexdec(substr($hash, 2, 2));
    $b = hexdec(substr($hash, 4, 2));
    
    // Lighten the colors to make them more pastel and readable
    $r = min(255, $r + 100);
    $g = min(255, $g + 100);
    $b = min(255, $b + 100);
    
    return "rgb($r, $g, $b)";
}

// Function to generate a lighter background color for asset names
function getCategoryBackgroundColor($category) {
    // Generate a hash from the category name to ensure consistent colors
    $hash = md5($category);
    // Extract RGB values from the hash
    $r = hexdec(substr($hash, 0, 2));
    $g = hexdec(substr($hash, 2, 2));
    $b = hexdec(substr($hash, 4, 2));
    
    // Make colors much lighter for background use (add more to make it pastel)
    $r = min(255, $r + 150);
    $g = min(255, $g + 150);
    $b = min(255, $b + 150);
    
    // Add some transparency for subtle effect
    return "rgba($r, $g, $b, 0.3)";
}

// Function to get text color based on background (for readability)
function getTextColor($bgColor) {
    // Extract RGB values
    if (preg_match('/rgba?\((\d+), (\d+), (\d+)/', $bgColor, $matches)) {
        $r = $matches[1];
        $g = $matches[2];
        $b = $matches[3];
        
        // Calculate luminance
        $luminance = (0.299 * $r + 0.587 * $g + 0.114 * $b) / 255;
        
        // Return dark text for light backgrounds, light text for dark backgrounds
        return $luminance > 0.5 ? '#333' : '#fff';
    }
    
    return '#333'; // Default to dark text
}

// Prepare individualized assets array
$individualizedAssets = [];

foreach ($assets as $asset) {
    if ($asset['quantity'] > 1 && empty($asset['serial_code'])) {
        // This asset has multiple quantities, individualize them
        $individualItems = getIndividualItems($pdo, $asset['asset_id'], $asset['quantity']);
        foreach ($individualItems as $item) {
            $individualizedAsset = $asset; // Copy the main asset data
            $individualizedAsset['item_id'] = $item['item_id'];
            $individualizedAsset['item_number'] = $item['item_number'];
            $individualizedAsset['individual_condition'] = $item['condition'];
            $individualizedAsset['individual_status'] = $item['status'];
            $individualizedAsset['is_individual'] = true;
            $individualizedAssets[] = $individualizedAsset;
        }
    } else {
        // Single quantity asset, add as is
        $asset['is_individual'] = false;
        $individualizedAssets[] = $asset;
    }
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

    .category-cell {
        padding: 8px 12px;
        border-radius: 4px;
        font-weight: bold;
        text-align: center;
        min-width: 120px;
    }

    /* New styles for category-colored asset names */
    .asset-name-cell {
        padding: 8px 12px;
        border-radius: 6px;
        font-weight: 600;
        border-left: 4px solid;
        transition: all 0.2s ease;
    }

    .asset-name-cell:hover {
        transform: translateX(2px);
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
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

    .category-legend {
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        padding: 15px;
        margin-bottom: 20px;
    }

    .category-legend h4 {
        margin-top: 0;
        margin-bottom: 10px;
        color: #495057;
    }

    .legend-item {
        display: inline-block;
        margin: 5px 10px 5px 0;
        padding: 5px 10px;
        border-radius: 4px;
        font-weight: bold;
        font-size: 0.9em;
    }

    .individual-item-row {
        background-color: #f8f9fa;
    }

    .individual-item-row:hover {
        background-color: #e9ecef !important;
    }

    /* Filter section styles */
    .filter-section {
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        padding: 15px;
        margin-bottom: 20px;
    }

    .filter-section h4 {
        margin-top: 0;
        margin-bottom: 10px;
        color: #495057;
    }

    .filter-buttons {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        align-items: center;
    }

    .filter-btn {
        padding: 6px 12px;
        border: 2px solid transparent;
        border-radius: 4px;
        font-weight: bold;
        font-size: 0.9em;
        cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none;
        display: inline-block;
    }

    .filter-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .filter-btn.active {
        border-color: #007bff;
        box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
    }

    .clear-filter-btn {
        background-color: #6c757d;
        color: white;
        padding: 6px 12px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-weight: bold;
        font-size: 0.9em;
    }

    .clear-filter-btn:hover {
        background-color: #5a6268;
    }
</style>

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

<?php
// Create category arrays for legends and filters
$allCategories = [];
foreach ($assets as $asset) {
    $allCategories[$asset['category']] = getCategoryColor($asset['category']);
}

// Separate categories for multi-quantity assets
$multiQtyCategories = [];
foreach ($assets as $asset) {
    if ($asset['quantity'] > 1 && empty($asset['serial_code'])) {
        $multiQtyCategories[$asset['category']] = getCategoryColor($asset['category']);
    }
}
?>

<!-- Category Filter Section -->
<div class="filter-section">
    <h4>Filter by Category:</h4>
    <div class="filter-buttons">
        <button class="clear-filter-btn" onclick="clearCategoryFilter()">Show All</button>
        <?php foreach ($allCategories as $category => $color): ?>
            <a href="#" class="filter-btn" 
               style="background-color: <?= getCategoryBackgroundColor($category) ?>; color: <?= getTextColor(getCategoryBackgroundColor($category)) ?>; border-left: 4px solid <?= $color ?>;"
               onclick="filterByCategory('<?= htmlspecialchars($category, ENT_QUOTES) ?>', this); return false;">
                <?= htmlspecialchars($category) ?>
            </a>
        <?php endforeach; ?>
    </div>
</div>

<?php if (!empty($multiQtyCategories)): ?>
    <div class="category-legend">
        <h4>Category Color Legend (Multi-quantity Assets):</h4>
        <?php foreach ($multiQtyCategories as $category => $color): ?>
            <span class="legend-item" style="background-color: <?= $color ?>; color: <?= getTextColor($color) ?>;">
                <?= htmlspecialchars($category) ?>
            </span>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<div class="table-container">
    <table class="styled-table">
        <thead>
            <tr>
                <th>Asset Name</th>
                <th>Category</th>
                <th>Serial Code</th>
                <th>Item #</th>
                <th>Condition</th>
                <th>Status</th>
                <th>Date Added</th>
                <?php if ($userRole === 'admin' || $userRole === 'staff' || $userRole === 'member'): ?>
                    <th>Actions</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($individualizedAssets as $asset): ?>
                <tr <?= $asset['is_individual'] ? 'class="individual-item-row"' : '' ?> data-category="<?= htmlspecialchars($asset['category']) ?>">
                    <td>
                        <?php 
                        $categoryBgColor = getCategoryBackgroundColor($asset['category']);
                        $categoryMainColor = getCategoryColor($asset['category']);
                        $textColor = getTextColor($categoryBgColor);
                        ?>
                        <div class="asset-name-cell" 
                             style="background-color: <?= $categoryBgColor ?>; color: <?= $textColor ?>; border-left-color: <?= $categoryMainColor ?>;">
                            <?php if ($asset['is_individual']): ?>
                                <?= htmlspecialchars($asset['asset_name']) ?> #<?= $asset['item_number'] ?>
                            <?php else: ?>
                                <?= htmlspecialchars($asset['asset_name']) ?>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td>
                        <?php if ($asset['is_individual']): ?>
                            <?php 
                            $categoryColor = getCategoryColor($asset['category']);
                            $textColor = getTextColor($categoryColor);
                            ?>
                            <span class="category-cell" style="background-color: <?= $categoryColor ?>; color: <?= $textColor ?>;">
                                <?= htmlspecialchars($asset['category']) ?>
                            </span>
                        <?php else: ?>
                            <?= htmlspecialchars($asset['category']) ?>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($asset['is_individual']): ?>
                            -
                        <?php else: ?>
                            <?= htmlspecialchars($asset['serial_code']) ?: '-' ?>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($asset['is_individual']): ?>
                            #<?= $asset['item_number'] ?>
                        <?php else: ?>
                            Single Item
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($asset['is_individual']): ?>
                            <span class="condition-<?= $asset['individual_condition'] ?>">
                                <?= ucfirst(htmlspecialchars($asset['individual_condition'])) ?>
                            </span>
                        <?php else: ?>
                            <span class="condition-<?= $asset['condition'] ?>">
                                <?= ucfirst(htmlspecialchars($asset['condition'])) ?>
                            </span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($asset['is_individual']): ?>
                            <span class="item-status-<?= $asset['individual_status'] ?>">
                                <?= ucfirst($asset['individual_status']) ?>
                            </span>
                        <?php else: ?>
                            <span class="item-status-<?= $asset['status'] ?>">
                                <?= ucfirst($asset['status']) ?>
                            </span>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($asset['date_added']) ?></td>
                    <?php if ($userRole === 'admin' || $userRole === 'staff'): ?>
                        <td>
                            <?php if ($asset['is_individual']): ?>
                                 <a href="edit.php?id=<?= $asset['asset_id'] ?>" class="action-link">Edit</a>
                                <a href="delete.php?id=<?= $asset['asset_id'] ?>" class="action-link danger" onclick="return confirm('Delete this asset?');">Delete</a>
                            <?php else: ?>
                                <a href="edit.php?id=<?= $asset['asset_id'] ?>" class="action-link">Edit</a>
                                <a href="delete.php?id=<?= $asset['asset_id'] ?>" class="action-link danger" onclick="return confirm('Delete this asset?');">Delete</a>
                            <?php endif; ?>
                        </td>
                    <?php elseif ($userRole === 'member'): ?>
                        <td>
                            <?php 
                            $itemAvailable = $asset['is_individual'] ? 
                                ($asset['individual_status'] === 'available') : 
                                ($asset['status'] === 'available' && $asset['quantity'] > 0);
                            ?>
                            <?php if ($itemAvailable): ?>
                                <?php if ($asset['is_individual']): ?>
                                    <a href="../borrow/request.php?asset_id=<?= $asset['asset_id'] ?>&item_number=<?= $asset['item_number'] ?>" class="action-link">Request to Borrow</a>
                                <?php else: ?>
                                    <a href="../borrow/request.php?asset_id=<?= $asset['asset_id'] ?>" class="action-link">Request to Borrow</a>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="unavailable">Not Available</span>
                            <?php endif; ?>
                        </td>
                    <?php else: ?>
                        <td>-</td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($individualizedAssets)): ?>
                <tr><td colspan="8">No assets with available quantity found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
function filterByCategory(category, element) {
    // Remove active class from all filter buttons
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Add active class to clicked button
    element.classList.add('active');
    
    // Get all table rows (excluding header)
    const rows = document.querySelectorAll('.styled-table tbody tr');
    
    rows.forEach(row => {
        const rowCategory = row.getAttribute('data-category');
        if (rowCategory === category) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

function clearCategoryFilter() {
    // Remove active class from all filter buttons
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Show all rows
    const rows = document.querySelectorAll('.styled-table tbody tr');
    rows.forEach(row => {
        row.style.display = '';
    });
}
</script>

<?php include '../../includes/footer.php'; ?>
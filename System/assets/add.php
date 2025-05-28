<?php
// System/assets/add.php
require_once '../../includes/auth_check.php';
require_once '../config/db.php';

// Only admin and staff can add assets
if (!in_array($_SESSION['role'], ['admin', 'staff'])) {
    header("Location: list.php");
    exit;
}

// Fetch existing categories
$stmt = $pdo->query("SELECT DISTINCT category FROM assets WHERE category IS NOT NULL AND category != '' ORDER BY category");
$existing_categories = $stmt->fetchAll(PDO::FETCH_COLUMN);

$errors = [];
$asset_name = $category = $serial_code = $condition = "";
$quantity = 1;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $asset_name = trim($_POST['asset_name']);
    $category = trim($_POST['category']);
    $serial_code = trim($_POST['serial_code']);
    $condition = trim($_POST['condition']);
    
    // Handle new category
    if ($category === '__new__' && !empty($_POST['new_category'])) {
        $category = trim($_POST['new_category']);
    } elseif ($category === '__new__') {
        $category = '';
    }
    
    // If serial code is provided, quantity is automatically 1
    if (!empty($serial_code)) {
        $quantity = 1;
    } else {
        $quantity = (int) $_POST['quantity'];
    }

    // Basic validation
    if (empty($asset_name)) {
        $errors[] = "Asset name is required.";
    }
    if (empty($condition)) {
        $errors[] = "Condition is required.";
    }
    if (empty($serial_code) && $quantity < 1) {
        $errors[] = "Quantity must be at least 1 when no serial code is provided.";
    }
    
    // Check if serial code already exists (if provided)
    if (!empty($serial_code)) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM assets WHERE serial_code = ?");
        $stmt->execute([$serial_code]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = "Serial code already exists. Serial codes must be unique.";
        }
    }

    if (empty($errors)) {
        // Set default status as available
        $status = 'available';
        $stmt = $pdo->prepare("INSERT INTO assets (asset_name, category, serial_code, quantity, `condition`, status) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$asset_name, $category, $serial_code, $quantity, $condition, $status]);
        header("Location: list.php?msg=Asset added successfully");
        exit;
    }
}

include '../../includes/header.php';
?>

<h2>Add New Asset</h2>

<?php if (!empty($errors)): ?>
    <div style="color:red;">
        <ul>
            <?php foreach ($errors as $err): ?>
                <li><?= htmlspecialchars($err) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="POST" action="add.php" id="assetForm">
    <label>Asset Name:<br>
        <input type="text" name="asset_name" value="<?= htmlspecialchars($asset_name) ?>" required>
    </label><br><br>

    <label>Category:<br>
        <div style="display: flex; align-items: center; gap: 10px;">
            <select name="category" id="category_select" onchange="toggleCategoryInput()" style="flex: 1;">
                <option value="">Select a category</option>
                <?php foreach ($existing_categories as $cat): ?>
                    <option value="<?= htmlspecialchars($cat) ?>" <?= ($category === $cat) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat) ?>
                    </option>
                <?php endforeach; ?>
                <option value="__new__">+ Add New Category</option>
            </select>
            <button type="button" id="add_category_btn" onclick="showNewCategoryInput()" style="background: #009879; color: white; border: none; padding: 8px 12px; border-radius: 4px; cursor: pointer;">+</button>
        </div>
        <input type="text" name="new_category" id="new_category_input" placeholder="Enter new category name" style="width: 100%; margin-top: 5px; display: none;">
    </label><br><br>

    <label>Serial Code:<br>
        <input type="text" name="serial_code" id="serial_code" value="<?= htmlspecialchars($serial_code) ?>" onchange="toggleQuantityField()">
        <small style="color: #666; display: block;">If there is no SERIAL CODE, please provide an ID</small>    
    </label><br><br>


    <label>Condition:<br>
        <input type="radio" name="condition" value="Excellent" <?= ($condition === 'Excellent') ? 'checked' : '' ?> required> Excellent<br>
        <input type="radio" name="condition" value="Good" <?= ($condition === 'Good') ? 'checked' : '' ?> required> Good<br>
        <input type="radio" name="condition" value="Fair" <?= ($condition === 'Fair') ? 'checked' : '' ?> required> Fair<br>
        <input type="radio" name="condition" value="Fair" <?= ($condition === 'Poor') ? 'checked' : '' ?> required> Poor<br>
    </label><br><br>

    <button type="submit">Add Asset</button>
    <a href="list.php">Cancel</a>
</form>

<script>
function toggleQuantityField() {
    const serialCode = document.getElementById('serial_code').value.trim();
    const quantityField = document.getElementById('quantity_field');
    const quantityInput = document.getElementById('quantity');
    
    if (serialCode !== '') {
        quantityField.style.display = 'none';
        quantityInput.value = 1;
        quantityInput.required = false;
    } else {
        quantityField.style.display = 'block';
        quantityInput.required = true;
    }
}

function toggleCategoryInput() {
    const categorySelect = document.getElementById('category_select');
    const newCategoryInput = document.getElementById('new_category_input');
    
    if (categorySelect.value === '__new__') {
        newCategoryInput.style.display = 'block';
        newCategoryInput.required = true;
        newCategoryInput.focus();
    } else {
        newCategoryInput.style.display = 'none';
        newCategoryInput.required = false;
        newCategoryInput.value = '';
    }
}

function showNewCategoryInput() {
    const categorySelect = document.getElementById('category_select');
    const newCategoryInput = document.getElementById('new_category_input');
    
    categorySelect.value = '__new__';
    newCategoryInput.style.display = 'block';
    newCategoryInput.required = true;
    newCategoryInput.focus();
}

// Initialize the form state on page load
document.addEventListener('DOMContentLoaded', function() {
    toggleQuantityField();
    toggleCategoryInput();
});
</script>

<?php include '../../includes/footer.php'; ?>
<?php
// System/borrow/request.php
require_once '../../includes/auth_check.php';  // Make sure user is authenticated
require_once '../config/db.php';

// Get logged-in user ID from session
$userId = $_SESSION['user_id'] ?? null;
if (!$userId) {
    header("Location: ../../login.php");
    exit;
}

// Get asset_id from query string and validate
$asset_id = isset($_GET['asset_id']) ? (int)$_GET['asset_id'] : 0;
if ($asset_id <= 0) {
    header("Location: ../assets/list.php?error=Invalid asset ID");
    exit;
}

// Fetch asset info from database
$stmt = $pdo->prepare("SELECT * FROM assets WHERE asset_id = ?");
$stmt->execute([$asset_id]);
$asset = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$asset) {
    echo "<p>Asset not found. <a href='../assets/list.php'>Go back</a></p>";
    exit;
}

$errors = [];
$quantity = 1;
$expected_return = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    $expected_return = $_POST['expected_return'] ?? '';

    // Validate quantity
    if ($quantity < 1) {
        $errors[] = "Quantity must be at least 1.";
    } elseif ($quantity > $asset['quantity']) {
        $errors[] = "Requested quantity exceeds available quantity.";
    }

    // Validate expected return date
    if (empty($expected_return)) {
        $errors[] = "Please specify an expected return date.";
    } elseif (strtotime($expected_return) <= time()) {
        $errors[] = "Expected return date must be in the future.";
    }

    if (empty($errors)) {
        // Insert borrow request
        $stmt = $pdo->prepare("
            INSERT INTO borrow_requests 
            (user_id, asset_id, quantity, date_borrowed, expected_return, status)
            VALUES (?, ?, ?, NOW(), ?, 'pending')
        ");
        $stmt->execute([$userId, $asset_id, $quantity, $expected_return]);

        // Get the last inserted borrow_id
        $borrow_id = $pdo->lastInsertId();

        // Insert log entry
        $log_stmt = $pdo->prepare("
            INSERT INTO logs (user_id, action, target_id, description)
            VALUES (?, 'submit_borrow_request', ?, ?)
        ");
        $log_description = "User submitted a borrow request for asset '{$asset['asset_name']}' (Asset ID: $asset_id) with quantity $quantity.";
        $log_stmt->execute([$userId, $borrow_id, $log_description]);

        // Redirect with success message
        header("Location: ../assets/list.php?msg=Borrow request submitted successfully");
        exit;
    }
}

include '../../includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Request to Borrow – <?= htmlspecialchars($asset['asset_name']) ?></title>
    <style>
        main {
            max-width: 600px;
            margin: 2rem auto;
            padding: 1.5rem;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            font-family: Arial, sans-serif;
        }
        label {
            display: block;
            margin-bottom: 1rem;
        }
        input[type="number"], input[type="date"] {
            padding: 0.5rem;
            width: 100%;
            box-sizing: border-box;
        }
        button {
            padding: 0.6rem 1.2rem;
            background-color: #007bff;
            border: none;
            color: #fff;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
        ul.errors {
            color: red;
            padding-left: 1rem;
        }
        a.back {
            display: inline-block;
            margin-top: 1rem;
            color: #007bff;
        }
    </style>
</head>
<body>

<main>
    <h2>Borrow Request for: <?= htmlspecialchars($asset['asset_name']) ?></h2>

    <?php if ($errors): ?>
        <ul class="errors">
            <?php foreach ($errors as $error): ?>
                <li><?= htmlspecialchars($error) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <form method="post" action="?asset_id=<?= urlencode($asset_id) ?>">
        <label>
            Quantity (Available: <?= (int)$asset['quantity'] ?>):
            <input type="number" name="quantity" min="1" max="<?= (int)$asset['quantity'] ?>" required value="<?= htmlspecialchars($quantity) ?>">
        </label>

        <label>
            Expected Return Date:
            <input type="date" name="expected_return" required value="<?= htmlspecialchars($expected_return) ?>">
        </label>

        <button type="submit">Submit Borrow Request</button>
    </form>

    <a class="back" href="../assets/list.php">← Back to Asset List</a>
</main>

</body>
</html>

<?php include '../../includes/footer.php'; ?>

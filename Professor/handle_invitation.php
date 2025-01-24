<?php
// Include database connection
include 'db.php';

// Start session to get logged-in user's email
session_start();

if (!isset($_SESSION['user_email'])) {
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

$professorEmail = $_SESSION['user_email'];

// Get POST data
$thesisId = (int) $_POST['thesisId'] ?? null;
$action = $_POST['action'] ?? null;

// Validate input
if (!$thesisId || !is_numeric($thesisId)) {
    echo json_encode(['error' => 'Invalid thesis ID']);
    exit;
}

$responseValue = null;
if ($action === 'Accept') {
    $responseValue = 'Accepted';
} elseif ($action === 'Decline') {
    $responseValue = 'Declined';
} else {
    echo json_encode(['error' => 'Invalid action']);
    exit;
}

try {
    // Debugging: Log input values to ensure correctness
    error_log("Professor Email: $professorEmail");
    error_log("Thesis ID: $thesisId");
    error_log("Action: $responseValue");

    // Prepare the query to call the stored procedure
    $query = "CALL UpdateResponse(?, ?, ?)";
    $stmt = $conn->prepare($query);

    if (!$stmt) {
        echo json_encode(['error' => 'Failed to prepare query: ' . $conn->error]);
        exit;
    }

    // Bind parameters: professorEmail (string), thesisId (int), responseValue (string)
    $stmt->bind_param("sis", $professorEmail, $thesisId, $responseValue);

    // Execute the query
    if ($stmt->execute()) {
        echo json_encode(['success' => "Invitation successfully $responseValue."]);
    } else {
        echo json_encode(['error' => 'Failed to execute the stored procedure']);
    }

    $stmt->close();
} catch (Exception $e) {
    echo json_encode(['error' => 'An error occurred: ' . $e->getMessage()]);
}

$conn->close();
?>

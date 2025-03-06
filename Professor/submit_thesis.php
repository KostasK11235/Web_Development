<?php
header('Content-Type: application/json; charset=utf-8');
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_email'])) {
    die(json_encode(['error' => 'Not logged in']));
}

$supervisorEmail = $_SESSION['user_email'];
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['thesisTitle'] ?? '';
    $description = $_POST['thesisDescription'] ?? '';

    // Debugging
    error_log("Title: $title");
    error_log("Description: $description");

    // Validate input lengths
    if (strlen($title) > 255 || strlen($description) > 65535) {
        echo json_encode(['error' => 'Input exceeds allowed length']);
        exit;
    }

    $newFilePath = null;

    // Handle file upload
    if (isset($_FILES['thesisAttachment']) && $_FILES['thesisAttachment']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['thesisAttachment']['tmp_name'];
        $fileName = basename($_FILES['thesisAttachment']['name']);
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        // Debugging
        error_log("File uploaded: $fileName");

        // Allow only PDF files
        if ($fileExtension !== 'pdf') {
            echo json_encode(['error' => 'Only PDF files are allowed']);
            exit;
        }

        $uploadDir = '/opt/lampp/htdocs/mydir/Uploaded_Files_for_Thesis/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Generate a unique file name to avoid conflicts
        $newFilePath = $uploadDir . uniqid() . '_' . $fileName;

        // Move the uploaded file to the target directory
        if (!move_uploaded_file($fileTmpPath, $newFilePath)) {
            echo json_encode(['error' => 'Failed to upload file']);
            exit;
        }

        // Debugging
        error_log("File saved to: $newFilePath");
    } else {
        error_log("No file uploaded or file upload error");
    }

    // Insert thesis into the database
    $stmt = $conn->prepare("INSERT INTO Thesis (th_title, th_description, th_pdf_attachment, th_supervisor) VALUES (?, ?, ?, ?)");
    if (!$stmt) {
        error_log("Database query preparation failed: " . $conn->error);
        echo json_encode(['error' => 'Database query preparation failed: ' . $conn->error]);
        exit;
    }

    $stmt->bind_param("ssss", $title, $description, $newFilePath, $supervisorEmail);
    if ($stmt->execute()) {
        echo json_encode(['success' => 'Thesis submitted successfully']);
    } else {
        error_log("Failed to insert thesis into database: " . $stmt->error);
        echo json_encode(['error' => 'Failed to insert thesis into database']);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['error' => 'Invalid request method']);
}
?>
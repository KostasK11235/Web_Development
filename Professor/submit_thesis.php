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

    if (strlen($title) > 255 || strlen($description) > 65535) {
        echo json_encode(['error' => 'Input exceeds allowed length']);
        exit;
    }

    if (isset($_FILES['thesisAttachment']) && $_FILES['thesisAttachment']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['thesisAttachment']['tmp_name'];
        $fileName = basename($_FILES['thesisAttachment']['name']);
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if ($fileExtension !== 'pdf') {
            echo json_encode(['error' => 'Only PDF files are allowed']);
            exit;
        }

        $uploadDir = './Uploaded_Files_for_Theses/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $newFilePath = $uploadDir . uniqid() . '_' . $fileName;

        if (!move_uploaded_file($fileTmpPath, $newFilePath)) {
            echo json_encode(['error' => 'Failed to upload file']);
            exit;
        }
    } else {
        echo json_encode(['error' => 'File upload failed or no file provided']);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO Thesis (th_title, th_description, th_pdf_attachment, th_supervisor) VALUES (?, ?, ?, ?)");
    if (!$stmt) {
        echo json_encode(['error' => 'Database query preparation failed: ' . $conn->error]);
        exit;
    }

    $stmt->bind_param("ssss", $title, $description, $newFilePath, $supervisorEmail);
    if ($stmt->execute()) {
        echo json_encode(['success' => 'Thesis submitted successfully']);
    } else {
        echo json_encode(['error' => 'Failed to insert thesis into database']);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['error' => 'Invalid request method']);
}
?>

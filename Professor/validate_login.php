<?php
// Include the database connection
include 'db.php';

// Start the session
session_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if username and password are provided
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Validate input
    if (empty($username) || empty($password)) {
        echo json_encode(['error' => 'Username or password is missing']);
        exit;
    }

    // Prepare and execute the SQL query
    $stmt = $conn->prepare("SELECT usr_username, usr_password, usr_property, usr_email FROM Users WHERE usr_username = ?");
    if (!$stmt) {
        echo json_encode(['error' => 'Database query preparation failed: ' . $conn->error]);
        exit;
    }
    
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Debugging: Log fetched user details
        error_log(print_r($user, true));

        // Verify the password
        if ($password === $user['usr_password']) {
            // Store email in the session
            $_SESSION['user_email'] = $user['usr_email'];

            // Redirect based on usr_property
            switch ($user['usr_property']) {
                case 'PROFESSOR':
                    echo json_encode(['redirect' => 'professor.html']);
                    break;
                case 'STUDENT':
                    echo json_encode(['redirect' => 'student.html']);
                    break;
                case 'SECRETARIAT':
                    echo json_encode(['redirect' => 'secretariat.html']);
                    break;
                default:
                    echo json_encode(['error' => 'Invalid user property']);
                    break;
            }
        } else {
            echo json_encode(['error' => 'Incorrect password']);
        }
    } else {
        echo json_encode(['error' => 'User not found']);
    }
    $stmt->close();
} else {
    echo json_encode(['error' => 'Invalid request method']);
}

$conn->close();
?>

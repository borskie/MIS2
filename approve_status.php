<?php
session_start();

// Database connection setup
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "school_db";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Sanitize input function
function sanitizeInput($input) {
    return htmlspecialchars(stripslashes(trim($input)));
}

// Redirect based on user role
function redirectBasedOnRole($role) {
    $redirectPages = [
        'admin' => 'admin_promoted_lists.php',
        'ict_faculty' => 'academic_promoted_lists.php',
        'teacher' => 'teacher_promoted_lists.php',
    ];
    if (isset($redirectPages[$role])) {
        header("Location: " . $redirectPages[$role]);
        exit;
    }
}

// Check if user is logged in
$userId = $_SESSION['user_id'] ?? null;
if (!$userId) {
    header("Location: login.php?error=session_expired");
    exit;
}

// Fetch user role
$query = "SELECT role FROM users WHERE id = '$userId'";
$result = $conn->query($query);
if ($result->num_rows > 0) {
    $userRole = $result->fetch_assoc()['role'];
} else {
    header("Location: login.php?error=role_not_found");
    exit;
}

// Process proID
$proID = sanitizeInput($_GET['proID'] ?? '');
if ($proID) {
    // Check if the proID exists in the promoted_student_tbl
    $checkQuery = "SELECT learnersID FROM promoted_student_tbl WHERE proID = '$proID'";
    $checkResult = $conn->query($checkQuery);

    if ($checkResult->num_rows > 0) {
        $learnersID = $checkResult->fetch_assoc()['learnersID'];

        // Get the current grade level of the learner
        $learnerQuery = "SELECT grade_level FROM learners WHERE lrn = '$learnersID'";
        $learnerResult = $conn->query($learnerQuery);

        if ($learnerResult->num_rows > 0) {
            $currentGrade = (int)$learnerResult->fetch_assoc()['grade_level'];
            $promotedTo = $currentGrade + 1;

            // Update the promoted_student_tbl
            $updateQuery = "UPDATE promoted_student_tbl 
                            SET promotedStatus = 1, promotedTo = '$promotedTo' 
                            WHERE proID = '$proID'";
            $conn->query($updateQuery);

            $updateQueryLists = "UPDATE learners 
                        SET grade_level = '$promotedTo' 
                        WHERE lrn = '$learnersID'";
            $conn->query($updateQueryLists);
        }
    }
}

// Redirect based on user role
redirectBasedOnRole($userRole);

// Close the database connection
$conn->close();
?>

<?php
// Include your database connection
include 'db_connection.php';

$studentDetails = null;
$error = null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $lrn = $_POST['lrn'];
    $lastName = $_POST['lastName'];

    // Prepare and execute the query to prevent SQL injection
    $stmt = $conn->prepare("SELECT * FROM learners WHERE lrn = ? AND last_name = ?");
    $stmt->bind_param("ss", $lrn, $lastName);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Fetch the student details
        $studentDetails = $result->fetch_assoc();
    } else {
        $error = "No student found with LRN: $lrn and Last Name: $lastName.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Application Form</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  
  <style>
 body {
  background: linear-gradient(135deg, 
    rgba(0, 180, 70, 0.8), 
    rgba(0, 220, 90, 0.6), 
    rgba(255, 140, 0, 0.6), 
    rgba(0, 150, 255, 0.6), 
    rgba(200, 0, 200, 0.6) /* New additional color */
  ); 
  background-size: 400% 400%; /* This helps create a smooth animation */
  animation: gradientAnimation 15s ease infinite; /* Animation for a flowing effect */
  font-family: 'Arial', sans-serif;
  color: #333;
}

@keyframes gradientAnimation {
  0% { background-position: 0% 50%; }
  50% { background-position: 100% 50%; }
  100% { background-position: 0% 50%; }
}

    .content {
  padding: 20px;
  border-radius: 8px;
  background-color: white;
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2), inset 0 1px 2px rgba(255, 255, 255, 0.5), inset 0 -1px 2px rgba(0, 0, 0, 0.2);
  margin: 20px auto; /* Center the content with auto margins */
  max-width: 900px; /* Set maximum width for the content */
  position: relative; /* For pseudo-element positioning */
}

.content::before {
  content: '';
  position: absolute;
  top: 5px;
  left: 5px;
  right: 0;
  bottom: 0;
  border-radius: 8px;
  background: rgba(255, 255, 255, 0.1);
  box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
  z-index: -1; /* Position behind the content */
}

    .footer {
      color: black;
      text-align: center;
      padding: 10px;
      font-size: 0.9rem;
      margin-top: 20px;
    }
    .requirements {
      margin-top: 20px; /* Add some space above the requirements section */
    }

    .btn-effect {
    transition: all 0.3s ease-in-out;
  }

  .btn-effect:hover {
    transform: scale(1.1); /* Grow effect on hover */
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Larger shadow on hover */
  }

  /* Button click effect */
  .btn-effect:active {
    transform: scale(1.15); /* Bigger on click */
    transition: all 0.1s ease-in-out;
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.3); /* Even larger shadow on click */
  }

  /* Make Requirements Button Bigger */
  .btn-bigger {
    font-size: 1.5rem; /* Larger text */
    padding: 15px 40px; /* Bigger padding */
  }
  .custom-width {
        width: 250px; /* Adjust the width as needed */
    }


    .student-details-card {
    background-color: #f9f9f9; /* Light background */
    padding: 20px; /* Padding around the content */
    border-radius: 8px; /* Rounded corners */
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1); /* Add shadow for a lifted effect */
    margin-top: 20px;
  }

  .student-details-card h4 {
    color: black; /* Change header color */
    text-transform: uppercase; /* Make header text uppercase */
    font-weight: bold; /* Bold header */
    letter-spacing: 1px; /* Add some letter spacing */
    margin-bottom: 15px; /* Space below the header */
  }

  .student-details-card .list-group-item {
    border: none; /* Remove borders */
    background-color: transparent; /* Transparent background */
    font-size: 1.1rem; /* Increase font size */
    padding: 10px 15px; /* Adjust padding */
  }

  .student-details-card .list-group-item strong {
    color: #555; /* Strong tags colored slightly darker */
  }

  .student-details-card .list-group-item:not(:last-child) {
    border-bottom: 1px solid #ddd; /* Add border between list items */
  }

  .alert-danger {
    background-color: #ffcccc; /* Light red background for error */
    color: #b80000; /* Darker red text */
    border: 1px solid #b80000; /* Border matching the text color */
  }
  </style>
</head>
<body>
  
  <header class="main-header">
   
  </header>

  <div class="container">
    <div class="content">
      <img id="sidebar-logo" src="dist/img/macayo_logo.png" alt="DepEd Logo" style="max-width: 100px; margin-left: auto; margin-right: auto; display: block;">
      
      <br>
      <img id="sidebar-logo" src="dist/img/macayo_title.png" alt="DepEd Logo" style="max-width: 550px; margin-left: auto; margin-right: auto; display: block;">
      <h3 class="mb-4 text-center">Admission Portal</h3>
      
        
<!-- Bootstrap Buttons in One Line -->
<!-- Enhanced Bootstrap Buttons with Bigger Requirements Button -->
<div class="container mt-3 text-center">
  <div class="btn-group" role="group">
    <!-- Bigger Requirements Button -->
    <a href="admission_portal.php" class="btn btn-info btn-lg rounded-pill shadow-lg mx-2 btn-effect">Requirements</a>
    
    <!-- Other Buttons -->
    <a href="admission_status.php" class="btn btn-success btn-lg rounded-pill shadow-lg mx-2 btn-effect">Application Status</a>
    <a href="admission_contactus.php" class="btn btn-info btn-lg rounded-pill shadow-lg mx-2 btn-effect">Contact Us</a>
  </div>
</div>

<!-- Add this Bootstrap CDN if it's not already included in your project -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">


<!-- Add this Bootstrap CDN if it's not already included in your project -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<br><br>
      <div class="alert alert-info" role="alert">
      <strong>Verify</strong> your status.
      </div>

          <!-- Search Form -->
          <div class="card requirements">
        <div class="card-body">
          <h6>S.Y. 2024 - 2025 Admission Form</h6>
          <form action="" method="POST" class="row g-3 align-items-end">
            <div class="col-md-4">
              <label for="lrn" class="form-label">LRN No.</label>
              <input type="text" class="form-control" id="lrn" name="lrn" required>
            </div>

            <div class="col-md-4">
              <label for="lastName" class="form-label">Last Name</label>
              <input type="text" class="form-control" id="lastName" name="lastName" required>
            </div>

            <div class="col-md-4 d-flex justify-content-center">
              <button type="submit" class="btn btn-primary btn-sm py-2 px-3 custom-width">Search</button>
            </div>
          </form>
        </div>
      </div>


   <!-- Display search results -->
<?php if ($studentDetails): ?>
  <div style="margin-top: 16px; border: 1px solid #ddd; border-radius: 8px; padding: 16px; background-color: #f9f9f9;">
    <h4 style="text-align: center; font-size: 2.25rem; font-weight: bold;">Student Details</h4>

    <!-- First Group: Profile and Basic Info -->
    <div style="margin-bottom: 16px; display: flex; flex-wrap: wrap;">
        <div style="flex: 1 1 50%; padding-right: 16px; padding-bottom: 16px;">
            <ul style="list-style-type: none; padding: 0;">
                <li style="border-bottom: 1px solid #ddd; padding: 8px 0;">
                    <strong>Profile:</strong> 
                    <div style="display: flex; justify-content: center; align-items: center; padding: 8px;">
                        <img src="<?php echo $studentDetails['image_file']; ?>" alt="" style="width: 120px; height: 120px; border-radius: 50%; border: 4px solid #007bff; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);">
                    </div>
                </li>
            </ul>
        </div>
        <div style="flex: 1 1 50%; padding-left: 16px; padding-bottom: 16px;">
            <ul style="list-style-type: none; padding: 0;">
                <li style="border-bottom: 1px solid #ddd; padding: 8px 0;"><strong>LRN:</strong> <?php echo $studentDetails['lrn']; ?></li>
                <li style="border-bottom: 1px solid #ddd; padding: 8px 0;"><strong>Last Name:</strong> <?php echo $studentDetails['last_name']; ?></li>
                <li style="border-bottom: 1px solid #ddd; padding: 8px 0;"><strong>First Name:</strong> <?php echo $studentDetails['first_name']; ?></li>
                <li style="border-bottom: 1px solid #ddd; padding: 8px 0;"><strong>Middle Name:</strong> <?php echo $studentDetails['middle_name']; ?></li>
                <li style="border-bottom: 1px solid #ddd; padding: 8px 0;"><strong>Extension Name:</strong> <?php echo $studentDetails['name_extension']; ?></li>
                <li style="border-bottom: 1px solid #ddd; padding: 8px 0;"><strong>Gender:</strong> <?php echo $studentDetails['gender']; ?></li>
                <li style="border-bottom: 1px solid #ddd; padding: 8px 0;"><strong>Status:</strong> <?php echo $studentDetails['status']; ?></li>
            </ul>
        </div>
    </div>

    <!-- Second Group: Birth, Contact, and Other Details -->
    <div style="display: flex; flex-wrap: wrap;">
        <div style="flex: 1 1 50%; padding-right: 16px; padding-bottom: 16px;">
            <ul style="list-style-type: none; padding: 0;">
                <li style="border-bottom: 1px solid #ddd; padding: 8px 0;"><strong>Date of Birth:</strong> <?php echo $studentDetails['dob']; ?></li>
                <li style="border-bottom: 1px solid #ddd; padding: 8px 0;"><strong>Age:</strong> <?php echo $studentDetails['age']; ?></li>
                <li style="border-bottom: 1px solid #ddd; padding: 8px 0;"><strong>Address:</strong> <?php echo $studentDetails['address']; ?></li>
                <li style="border-bottom: 1px solid #ddd; padding: 8px 0;"><strong>Contact Number:</strong> <?php echo $studentDetails['cont_num']; ?></li>
                <li style="border-bottom: 1px solid #ddd; padding: 8px 0;"><strong>Religion:</strong> <?php echo $studentDetails['religion']; ?></li>
            </ul>
        </div>
        <div style="flex: 1 1 50%; padding-left: 16px; padding-bottom: 16px;">
            <ul style="list-style-type: none; padding: 0;">
                <li style="border-bottom: 1px solid #ddd; padding: 8px 0;"><strong>Student Type:</strong> <?php echo $studentDetails['student_type']; ?></li>
                <li style="border-bottom: 1px solid #ddd; padding: 8px 0;"><strong>School Last Attended:</strong> <?php echo $studentDetails['school_attended']; ?></li>
                <li style="border-bottom: 1px solid #ddd; padding: 8px 0;"><strong>Grade Level:</strong> <?php echo $studentDetails['grade_level']; ?></li>
                <li style="border-bottom: 1px solid #ddd; padding: 8px 0;"><strong>Curriculum:</strong> <?php echo $studentDetails['curriculum']; ?></li>
                <li style="border-bottom: 1px solid #ddd; padding: 8px 0;"><strong>Guardian:</strong> <?php echo $studentDetails['guardian_name']; ?></li>
                <li style="border-bottom: 1px solid #ddd; padding: 8px 0;"><strong>Guardian Relationship:</strong> <?php echo $studentDetails['guardian_relationship']; ?></li>
            </ul>
        </div>
    </div>
    <div style="width: 100%; height: 500px; border: 2px solid;">
      <iframe src="<?php echo $studentDetails['sf10_file']; ?>" style="width: 100%; height: 500px; " frameborder="0"></iframe>
    </div>
</div>


<?php elseif ($error): ?>
    <div class="alert alert-danger mt-4">
        <?php echo $error; ?>
    </div>
<?php endif; ?>

    </div>
  </div>
<!-- Add this Bootstrap CDN if it's not already included in your project -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    </div>
  </div>

  <footer class="footer">
    <p>© 2024 Macayo Integrated School. All Rights Reserved.</p>
  </footer>

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js"></script>
  
</body>
</html>

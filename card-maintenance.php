<?php
session_start(); 
require 'db_connection.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect to login page if not logged in
    exit();
}

$userId = $_SESSION['user_id']; // Assuming user ID is stored in session upon login

// Fetch user role only
$query = "SELECT role FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user) {
    $userRole = $user['role']; // Fetch the user's role
} else {
    $userRole = "No Role"; // Default role if not found
}

$stmt->close();
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $behavior_type = $_POST['behavior_type'];
    $first_behavior_statement = $_POST['first_behavior_statement'];
    $second_behavior_statement = $_POST['second_behavior_statement'];

    $check_duplicate = "SELECT * FROM core_values_db WHERE coreName = '$behavior_type'";
    $result = $conn->query($check_duplicate);

    if ($result->num_rows > 0) {
        echo "<script>alert('Error: Behavior type already exists'); window.location.href='card-maintenance.php';</script>";
    } else {
        $sql = "INSERT INTO core_values_db (coreName, behaviour_one, behavior_two) 
                VALUES ('$behavior_type', '$first_behavior_statement', '$second_behavior_statement')";

        if ($conn->query($sql) === TRUE) {
            echo "<script>alert('New record created successfully'); window.location.href='card-maintenance.php';</script>";
        } else {
            echo "<script>alert('Error: " . addslashes($conn->error) . "'); window.location.href='card-maintenance.php';</script>";
        }
    }

    $conn->close();
}
?>


<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Account Maintenance</title>
    <link rel="icon" href="../img/favicon2.png">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <link rel="stylesheet" href="bower_components/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="bower_components/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="bower_components/Ionicons/css/ionicons.min.css">
    <link rel="stylesheet" href="dist/css/AdminLTE.min.css">
    <link rel="stylesheet" href="dist/css/skins/_all-skins.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
</head>

<style>

.content-wrapper {
  position: relative;
  z-index: 1; /* Ensures that the content is on top of the watermark */
}
.sidebar-logo img {
  display: block;
  transition: all 0.9s ease; /* Smooth transition */
}
.content-wrapper::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: url('dist/img/deped_logo.png') no-repeat center center;
  background-size: 500px 500px; /* Adjust watermark size */
  opacity: 0.1; /* Make it subtle */
  z-index: -1; /* Push the watermark behind the content */
  pointer-events: none; /* Ensure the watermark doesn’t interfere with interactions */
}
/* Default Logo Style */
.sidebar-logo img {
  display: block;
  transition: all 0.9s ease; /* Smooth transition */
}

/* When the sidebar is collapsed */
.sidebar-collapse .sidebar-logo img {
  display: none; /* Hide the logo when collapsed */
}

.navbar-nav > li > a.btn {
        transition: none; /* Remove transition effect */
    }

    .navbar-nav > li > a.btn:hover {
        background-color: transparent; /* Remove background color on hover */
        color: inherit; /* Keep the text color the same on hover */
    }

    /* New styles to remove hover effect for logout button */
    .navbar-nav > li > a.btn.logout {
        background-color: transparent; /* Default background color */
        color: inherit; /* Default text color */
    }

    .navbar-nav > li > a.btn.logout:hover {
        background-color: transparent; /* Keep background transparent on hover */
        color: inherit; /* Keep text color same on hover */
    }
</style>
<body class="hold-transition skin-green sidebar-mini">
<div class="wrapper">

  <!-- Main Header -->
   <header class="main-header">

    <!-- Logo -->
    <a href="#" class="logo">
      <!-- mini logo for sidebar mini 50x50 pixels -->
      <span class="logo-mini"><b>MIS</b></span>
      <!-- logo for regular state and mobile devices -->
      <span class="logo-lg"><b>Student</b> Grading</span>
    </a>

    <!-- Header Navbar -->
    <nav class="navbar navbar-static-top" role="navigation">
      <!-- Sidebar toggle button-->
        <span class="sr-only">Toggle navigation</span>
      </a>
      <!-- Navbar Right Menu -->
      <div class="navbar-custom-menu">
    <ul class="nav navbar-nav">
        <li class="dropdown user user-menu">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                <span class="hidden-xs">Hey! <?php echo htmlspecialchars($userRole); ?></span>
            </a>
        </li>
        <li>
            <a href="#" class="btn btn-default btn-flat logout" onclick="confirmLogout()">
                <i class="fa fa-sign-out"></i> Logout
            </a>
        </li>
    </ul>
</div>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </nav>
    </header>

    <aside class="main-sidebar">
        <section class="sidebar">
            <div class="sidebar-logo" style="text-align: center; padding: 10px;">
            <img id="sidebar-logo" src="dist/img/macayo_logo.png" alt="DepEd Logo" style="max-width: 100px; margin-left: 50px; transition: all 0.9s ease;">
            </div>
            <ul class="sidebar-menu" data-widget="tree">
                <li id="dashboard"><a href="./"><i class="fa fa-dashboard"></i> <span>Dashboard</span></a></li>
                <li class="treeview">
                    <a href="#">
                        <i class="fa fa-folder"></i> <span>Student Status</span>
                        <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
                    </a>
                    <ul class="treeview-menu">
    <li class="treeview">
                <a href="#">
                    <i class="fa fa-cogs"></i> <span>Junior HS Student</span>
                    <span class="pull-right-container">
                        <i class="fa fa-angle-left pull-right"></i>
                    </span>
                </a>
                <ul class="treeview-menu">
                <li id="student-maintenance-7"><a href="grade7_student.php"><i class="fa fa-user"></i> Grade 7</a></li>
<li id="student-maintenance-8"><a href="grade8_student.php"><i class="fa fa-user"></i> Grade 8</a></li>
<li id="student-maintenance-9"><a href="grade9_student.php"><i class="fa fa-user"></i> Grade 9</a></li>
<li id="student-maintenance-10"><a href="grade10_student.php"><i class="fa fa-user"></i> Grade 10</a></li>

                </ul>
            </li>
            <li class="treeview">
                <a href="#">
                    <i class="fa fa-cogs"></i> <span>Senior HS Student</span>
                    <span class="pull-right-container">
                        <i class="fa fa-angle-left pull-right"></i>
                    </span>
                </a>
                <ul class="treeview-menu">
                <li id="student-maintenance-11"><a href="grade11_student.php"><i class="fa fa-user"></i> Grade 11</a></li>
                <li id="student-maintenance-12"><a href="grade12_student.php"><i class="fa fa-user"></i> Grade 12</a></li>

                </ul>
            </li>
      </ul>
</li>
                
                <li class="treeview">
                <a href="#">
                    <i class="fa fa-cogs"></i> <span>Maintenance</span>
                    <span class="pull-right-container">
                        <i class="fa fa-angle-left pull-right"></i>
                    </span>
                </a>
                <ul class="treeview-menu">
                <li class="treeview">
                <a href="#">
                    <i class="fa fa-clipboard"></i> <span>Subject</span>
                    <span class="pull-right-container">
                        <i class="fa fa-angle-left pull-right"></i>
                    </span>
                </a>
                <ul class="treeview-menu">
                    <li id="subject-maintenance"><a href="subject-maintenance.php"><i class="fa fa-book"></i> Junior High School</a></li>
                    <li id="subject-maintenance1"><a href="subject-maintenance1.php"><i class="fa fa-graduation-cap"></i> Senior High School</a></li>

                </ul>
            </li>
                    <li id="user-maintenance"><a href="account-maintenance.php"><i class="fa fa-user"></i> Account Maintenance</a></li>
                </ul>
            </li>
                <li id="about"><a href="about.php"><i class="fa fa-info-circle"></i> <span>About</span></a></li>
                <li id="about"><a href="card-maintenance.php"><i class="fa fa-info-circle"></i> <span>Card Maintenance</span></a></li>

            </ul>
        </section>
    </aside>

    <div class="content-wrapper">
        <section class="content-header">
            <h1>
                Card Maintenance

            </h1>
        </section>

        <section class="content">
            <form method='POST' style="max-width: 600px; margin: auto; padding: 20px; border: 1px solid #ccc; border-radius: 5px; background-color: #f9f9f9;">
                <div style="margin-bottom: 15px;">
                    <label for="behavior_type" style="font-weight: bold; display: block; margin-bottom: 5px;">Behavior Type:</label>
                    <select name="behavior_type" id="behavior_type" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
                        <option value="Maka-Diyos">Maka-Diyos</option>
                        <option value="Maka-Tao">Maka-Tao</option>
                        <option value="Maka-Kalikasan">Maka-Kalikasan</option>
                        <option value="Maka-Bansa">Maka-Bansa</option>
                    </select>
                </div>
                
                <div style="margin-bottom: 15px;">
                    <label for="first_behavior_statement" style="font-weight: bold; display: block; margin-bottom: 5px;">First Behavior Statement:</label>
                    <textarea name="first_behavior_statement" id="first_behavior_statement" placeholder="Enter the first behavior statement here..." style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; height: 100px;"></textarea>
                </div>
                
                <div style="margin-bottom: 15px;">
                    <label for="second_behavior_statement" style="font-weight: bold; display: block; margin-bottom: 5px;">Second Behavior Statement:</label>
                    <textarea name="second_behavior_statement" id="second_behavior_statement" placeholder="Enter the second behavior statement here..." style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; height: 100px;"></textarea>
                </div>
                
                <input type="submit" value="Submit" style="background-color: #4CAF50; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer;">
            </form>
            
            <div class="fetch-section" style="margin-top: 20px;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background-color: #f2f2f2;">
                            <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Core Name</th>
                            <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Behavior 1</th>
                            <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Behavior 2</th>
                            <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Fetch data from database
                        $fetch_query = "SELECT * FROM core_values_db";
                        $result = $conn->query($fetch_query);

                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . htmlspecialchars($row['coreName']) . "</td>";
                                echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . htmlspecialchars($row['behaviour_one']) . "</td>";
                                echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . htmlspecialchars($row['behavior_two']) . "</td>";
                                echo "<td style='border: 1px solid #ddd; padding: 8px;'>
                                        <form method='POST' action='process_edit.php' style='display:inline;'>
                                            <input type='hidden' name='id' value='" . $row['coreID'] . "'>
                                            <button type='submit' style='background:none; border:none; color:blue; cursor:pointer;'>Edit</button>
                                        </form>
                                        <form method='POST' action='process_delete.php' style='display:inline;' onsubmit='return confirm(\"Are you sure you want to delete this record?\");'>
                                            <input type='hidden' name='id' value='" . $row['coreID'] . "'>
                                            <button type='submit' style='background:none; border:none; color:red; cursor:pointer;'>Delete</button>
                                        </form>
                                    </td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='4' style='text-align: center; padding: 10px;'>No records found</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>




    <footer class="main-footer">
        <div class="pull-right hidden-xs">
            <b>Version</b> 2.4.0
        </div>
        <strong>&copy; 2024 <a href="#">Your School</a>.</strong> All rights reserved.
    </footer>
</div>

<script src="bower_components/jquery/dist/jquery.min.js"></script>
<script src="bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
<script src="bower_components/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>
<script src="dist/js/adminlte.min.js"></script>




<script>
function loadEditUser(userId) {
    console.log("Loading user with ID:", userId); // Add this line
    $.ajax({
        url: 'get_user.php',
        type: 'GET',
        data: { id: userId },
        success: function(data) {
            console.log("Data received:", data); // Log the received data
            const user = JSON.parse(data);
            $('#editUserId').val(user.id);
            $('#editEmail').val(user.email);
            $('#editRole').val(user.role);
        },
        error: function() {
            alert('Error fetching user data.');
        }
    });
}

function setDeleteUserId(userId) {
    $('#deleteUserId').val(userId);
}
</script>

<script>
    $(document).ready(function () {
        // Initialize DataTable
        $('#userTable').DataTable({
            "searching": true,
            "ordering": true,
            "paging": true
        });
    });
</script>

<script>
function confirmLogout() {
    if (confirm("Are you sure you want to log out?")) {
        window.location.href = "login_page.php"; // Redirect to the logout page if confirmed
    }
}

// Existing JavaScript code for sidebar toggle and other features
$('.sidebar-toggle').on('click', function () {
    $('body').toggleClass('sidebar-collapse'); // Toggle the collapse class
});


    function toggleGradeDropdown() {
        var role = document.getElementById("role").value;
        var gradeDropdown = document.getElementById("grade-dropdown");

        // Show grade dropdown if teacher is selected
        if (role === "teacher") {
            gradeDropdown.style.display = "block";
        } else {
            gradeDropdown.style.display = "none";
        }
    }
</script>
</body>
</html>
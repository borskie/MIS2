<?php
session_start();

// Check if LRN is provided
if (!isset($_GET['lrn'])) {
    echo "No student selected.";
    exit;
}

$lrn = $_GET['lrn'];

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "school_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch learner details including guardian name and school attended
$sqlLearner = "SELECT first_name, middle_name, last_name, guardian_name, school_attended, grade_level, gender, dob FROM learners WHERE lrn = ?";
$stmtLearner = $conn->prepare($sqlLearner);
$stmtLearner->bind_param("s", $lrn);
$stmtLearner->execute();
$resultLearner = $stmtLearner->get_result();

// Check if learner is found
if ($resultLearner->num_rows == 0) {
    echo "No records found for the selected student.";
    exit;
}

$learner = $resultLearner->fetch_assoc();

// Concatenate the full name
$fullName = $learner['first_name'] . 
    (isset($learner['middle_name']) && !empty($learner['middle_name']) ? ' ' . $learner['middle_name'] : '') . 
    ' ' . $learner['last_name'];

    $sqlGrades = "
    SELECT DISTINCT shs_subjects.subject_name, shs_grades.first_grading, shs_grades.second_grading, shs_grades.third_grading, 
           shs_grades.fourth_grading, shs_grades.final_grade, shs_grades.status, shs_grades.general_average, shs_grades.section, 
           shs_grades.school_year, shs_grades.adviser, learners.grade_level
    FROM shs_grades
    LEFT JOIN learners ON shs_grades.lrn = learners.lrn
    LEFT JOIN shs_subjects ON learners.grade_level = shs_subjects.grade_level
    
    WHERE shs_grades.lrn = ?";
$stmtGrades = $conn->prepare($sqlGrades);
$stmtGrades->bind_param("s", $lrn);
$stmtGrades->execute();
$resultGrades = $stmtGrades->get_result();


$grades = [];
if ($resultGrades->num_rows > 0) {
    while ($row = $resultGrades->fetch_assoc()) {
        $grades[] = $row;
    }
}


$sql = "SELECT * FROM core_values_db";
$result = $conn->query($sql);

$coreValues = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $coreValues[] = $row;
    }
} else {
    // Default static values if no data found
    $coreValues = [
        [
            'coreName' => 'Maka-Diyos',
            'behaviour_one' => "Expresses one's spiritual beliefs while respecting others'",
            'behavior_two' => 'Shows adherence to ethical principles by upholding truth',
        ],
        [
            'coreName' => 'Maka-Tao',
            'behaviour_one' => 'Demonstrates a caring attitude towards others',
            'behavior_two' => 'Acts with kindness and compassion',
        ],
        [
            'coreName' => 'Maka-Kalikasan',
            'behaviour_one' => 'Shows care for the environment',
            'behavior_two' => 'Participates in activities that promote environmental awareness',
        ],
        [
            'coreName' => 'Maka-Bansa',
            'behaviour_one' => 'Demonstrates love for country',
            'behavior_two' => 'Participates in community service activities',
        ],
    ];
}
$query = "SELECT from_year, to_year FROM school_years ORDER BY created_at DESC LIMIT 1";
$result = $conn->query($query);

if ($result && $row = $result->fetch_assoc()) {
    $defaultFromYear = $row['from_year'];
    $defaultToYear = $row['to_year'];
    $fullyear = $defaultFromYear . ' - ' . $defaultToYear;
}


function loadStudentGrades($conn, $lrn) {
    // Query to fetch grades along with subject and semester details
    $sql = "SELECT 
                s.subject_name, 
                s.semester,
                sg.first_grading, 
                sg.second_grading, 
                sg.third_grading, 
                sg.fourth_grading,
                sg.final_grade,
                s.curriculum
            FROM shs_grades sg
            JOIN shs_subjects s ON sg.subject_id = s.id
            WHERE sg.lrn = '$lrn'
            ORDER BY s.curriculum, s.semester, s.subject_name";

    $result = $conn->query($sql);

    if ($result === false) {
        die("Error in query: " . $conn->error);
    }

    // Separate subjects into first and second semester
    $firstSemesterSubjects = [];
    $secondSemesterSubjects = [];

    while ($row = $result->fetch_assoc()) {
        if ($row['semester'] === '1') {
            $firstSemesterSubjects[] = $row;
        } elseif ($row['semester'] === '2') {
            $secondSemesterSubjects[] = $row;
        }
    }

    return [
        'first_semester' => $firstSemesterSubjects,
        'second_semester' => $secondSemesterSubjects
    ];
}


$gradesData = loadStudentGrades($conn, $lrn);

// Calculate semester averages
$firstSemesterAverage = calculateSemesterAverage($gradesData['first_semester']);
$secondSemesterAverage = calculateSemesterAverage($gradesData['second_semester']);

function calculateSemesterAverage($semesterSubjects) {
    if (empty($semesterSubjects)) return 0;
    
    $totalGrade = 0;
    $subjectCount = count($semesterSubjects);
    
    foreach ($semesterSubjects as $subject) {
        $totalGrade += ($subject['first_grading'] + $subject['second_grading']) / 2;
    }
    
    return round($totalGrade / $subjectCount, 2);
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>SF9 Report Card</title>
    <style>
           body {
            font-family: Arial, sans-serif;
            font-size: 13px;
            margin: 0;
            padding: 20px;
        }
        .report-card { max-width: 952px;margin: 0 auto; border: 1px solid #000; padding: 20px; }
        .header, .footer { text-align: center; }
        h2, h3, h4 { margin: 5px 0; }
        .learner-info { text-align: center; margin-bottom: 20px; }
        .learner-info h3 { font-size: 1.2em; }
        .table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .table th, .table td { border: 1px solid #000; padding: 8px; text-align: center; font-size: 0.9em; }
        .subject-row { text-align: left; padding-left: 10px; }
        .footer-section { display: flex; justify-content: space-between; margin-top: 20px; }
        .core-values { font-size: 0.9em; margin-top: 10px; }
        .descriptor-table { border-collapse: collapse; width: 100%; margin-top: 10px; }
        .descriptor-table th, .descriptor-table td { border: 1px solid #000; padding: 6px; text-align: center; }

        .section-title { 
        text-align: center; /* Center the section title */
        font-size: 1.0em; 
        margin-top: 20px; 
        font-weight: normal; /* Remove bold styling */
    }

    .buttons {
            display: flex;
            margin: 20px 0;
        }
        .buttons button {
            padding: 10px 10px;
            font-size: 14px;
            margin: 0 5px;
            cursor: pointer;
            border: none;
            border-radius: 5px;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .buttons .print-btn {
            background-color: #4CAF50; /* Green for Print */
        }

        .buttons .cancel-btn {
            background-color: #f44336; /* Red for Cancel */
        }

        .buttons button i {
            font-size: 16px;
        }

        /* Hide buttons when printing */
        @media print {
            .buttons {
                display: none;
            }
        }

        .container {
            display: flex;
            justify-content: space-between;
            max-width: 1000px;
            margin: auto;
            border: 1px solid black;
            padding: 20px;
            box-sizing: border-box;
        }
        .left, .right {
            width: 48%;
        }
        .header, .section-title {
            text-align: center;
            font-weight: bold;
            font-size: 14px;
            margin: 0;
            padding: 0;
            line-height: 1.5;
        }
        .section-title {
            margin-top: 15px;
        }
        .small-text {
            font-size: 12px;
            margin: 0;
            padding: 0;
            line-height: 1.5;
        }
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th, td {
            border: 1px solid black;
            padding: 4px;
            text-align: center;
        }
        .no-border td {
            border: none;
            padding: 2px 4px;
        }
        .signature-table td {
            padding: 5px;
            border: none;
        }
        .underline {
            display: inline-block;
            border-bottom: 1px solid black;
            width: 150px;
            height: 1px;
            margin-top: 5px;
            margin-bottom: 5px;
        }
        .indent {
            margin-left: 20px;
        }
    </style>
</head>
<body>

<div class="buttons">
    <button class="print-btn" onclick="window.print()">
        <i class="fas fa-print"></i> Print
    </button>
    <button class="cancel-btn" onclick="window.history.back()">
        <i class="fas fa-times-circle"></i> Cancel
    </button>
</div>

<div class="container">
    <!-- Left Side: Attendance and Parent Signature -->
    <div class="left">
        <div class="header">Attendance Record</div>
        <table>
            <tr>
                <th rowspan="2">No. of School Days</th>
                <th colspan="12">Months</th>
                <th rowspan="2">Total</th>
            </tr>
            <tr>
                <th>Jun</th><th>Jul</th><th>Aug</th><th>Sept</th><th>Oct</th><th>Nov</th>
                <th>Dec</th><th>Jan</th><th>Feb</th><th>Mar</th><th>Apr</th><th>May</th>
            </tr>
            <tr><td>No. of Days</td><td></td><td></td><td></td><td></td><td></td><td></td>
                <td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
            <tr><td>Present</td><td></td><td></td><td></td><td></td><td></td><td></td>
                <td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
            <tr><td>No. of Times Absent</td><td></td><td></td><td></td><td></td><td></td><td></td>
                <td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
        </table>

        <div class="section-title">PARENT/GUARDIAN'S SIGNATURE</div>
        <table class="signature-table">
            <tr>
           <td class="small-text">1st Quarter:</td><td>__________________________</td>
            </tr>
            <tr>
                <td class="small-text">2nd Quarter:</td><td>__________________________</td>
            </tr>
            <tr>
                <td class="small-text">3rd Quarter:</td><td>__________________________</td>
            </tr>
            <tr>
                <td class="small-text">4th Quarter:</td><td>__________________________</td>
            </tr>
        </table>
    </div>

    <!-- Right Side: Report Card and Certification -->
    <div class="right" style="position: relative;">
    <img src="https://hrms-jshs.edu.ph/wp-content/uploads/2021/07/DepEd.png" style="width: 100px; position: absolute; top: 0; left: 0;">
        <div style="text-align: center; font-size: 12px; font-weight: bold; position: absolute; top: -10px; left: 23px;">JHS 9 - ES</div>
        <div class="header">Republic of the Philippines<br>DEPARTMENT OF EDUCATION</div>
        <table class="no-border">
            <tr>
                <td class="small-text">Region: __________________________</td>
            </tr>
            <tr>
                <td class="small-text">Division: __________________________</td> 
            </tr>
            <tr>
                <td class="small-text">District: __________________________</td>
            </tr>
            <tr>
                <td class="small-text">School: __________________________</td>
            </tr>
        </table>
<br>
<div class="header">LEARNER'S PROGRESS REPORT CARD<br>School Year <?= isset($fullyear) ? $fullyear : '__________' ?></div>

        
<div style='padding: 12px;'>
    Name: <span style="display: inline-block; border-bottom: 1px solid #000; width: 200px;">
        <?= 
            (isset($learner['first_name']) ? $learner['first_name'] . ' ' : '') .
            (isset($learner['middle_name']) && !empty($learner['middle_name']) ? $learner['middle_name'] . ' ' : '') .
            (isset($learner['last_name']) ? $learner['last_name'] : '')
        ?>
    </span><br>
    Age: <span style="display: inline-block; border-bottom: 1px solid #000; width: 50px;">
        <?= isset($learner['age']) ? $learner['age'] : '' ?>
    </span>
    Sex: <span style="display: inline-block; border-bottom: 1px solid #000; width: 100px;">
        <?= isset($learner['gender']) ? $learner['gender'] : '' ?>
    </span><br>
    Grade: <span style="display: inline-block; border-bottom: 1px solid #000; width: 50px;">
        <?= isset($learner['grade_level']) ? $learner['grade_level'] : '' ?>
    </span>
    Section: <span style="display: inline-block; border-bottom: 1px solid #000; width: 100px;">
        <?= isset($grades[0]['section']) ? $grades[0]['section'] : '' ?>
    </span>
    LRN: <span style="display: inline-block; border-bottom: 1px solid #000; width: 150px;">
        <?= isset($lrn) ? $lrn : '' ?>
    </span>
</div>


        <br>
        <p class="indent small-text">Dear Parent,</p>
        <p class="indent small-text">This report card shows the ability and the progress your child has made in the different learning areas as well as his/her progress in core values.</p>
        <p class="indent small-text">The school welcomes you should you desire to know more about your child’s progress.</p>
        
        <br>
        <div ><?= isset($grades[0]['adviser']) ? $grades[0]['adviser'] : '__________' ?></div>
        <p class="indent small-text">Adviser</p>
        <br>
        <div class="underline"></div>
        <p class="indent small-text"> Principal</p>

        <div class="section-title">Certificate of Transfer</div>
        <p class="indent small-text">Admitted to Grade ______ Section ______ Room ______</p>
        <p class="indent small-text">Eligible for Admission to Grade ______</p>
        <p class="indent small-text">Approved:</p>
        <div class="underline"></div>
        <p class="indent small-text">Head Teacher / Principal</p>
        <div class="underline"></div>
        <p class="indent small-text">Teacher</p>

        <div class="section-title">Cancellation of Eligibility to Transfer</div>
        <p class="indent small-text">Admitted in ________________________</p>
        <p class="indent small-text">Date:_____________________________</p>
        <div class="underline"></div>
        <p class="indent small-text">Principal</p>
    </div>
</div>

<br><br><br>
<div class="container">
        <div class='left'>
            <h2>LEARNER'S PROGRESS REPORT CARD</h2>
            
            <!-- First Semester Table -->
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <th colspan="4" style="border: 1px solid #000; padding: 5px; text-align: left; background-color: #f0f0f0;">First Semester</th>
                </tr>
                <tr>
                    <th style="border: 1px solid #000; padding: 5px; text-align: left; background-color: #f0f0f0;">Subjects</th>
                    <th style="border: 1px solid #000; padding: 5px; text-align: left; background-color: #f0f0f0;">Quarter 1</th>
                    <th style="border: 1px solid #000; padding: 5px; text-align: left; background-color: #f0f0f0;">Quarter 2</th>
                    <th style="border: 1px solid #000; padding: 5px; text-align: left; background-color: #f0f0f0;">Semester Final Grade</th>
                </tr>
                <?php foreach ($gradesData['first_semester'] as $subject): ?>
                    <tr>
                        <td style="border: 1px solid #000; padding: 5px;"><?php echo htmlspecialchars($subject['subject_name']); ?></td>
                        <td style="border: 1px solid #000; padding: 5px;"><?php echo number_format($subject['first_grading'], 2); ?></td>
                        <td style="border: 1px solid #000; padding: 5px;"><?php echo number_format($subject['second_grading'], 2); ?></td>
                        <td style="border: 1px solid #000; padding: 5px;"><?php echo number_format(($subject['first_grading'] + $subject['second_grading']) / 2, 2); ?></td>
                    </tr>
                <?php endforeach; ?>
                <tfoot>
                    <tr>
                        <td colspan="3" style="border: 1px solid #000; padding: 5px; text-align: right; background-color: #f9f9f9; font-weight: bold;">General Average</td>
                        <td style="border: 1px solid #000; padding: 5px; text-align: center; background-color: #f9f9f9; font-weight: bold;"><?php echo $firstSemesterAverage; ?></td>
                    </tr>
                </tfoot>
            </table>

            <!-- Second Semester Table -->
            <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
                <tr>
                    <th colspan="4" style="border: 1px solid #000; padding: 5px; text-align: left; background-color: #f0f0f0;">Second Semester</th>
                </tr>
                <tr>
                    <th style="border: 1px solid #000; padding: 5px; text-align: left; background-color: #f0f0f0;">Subjects</th>
                    <th style="border: 1px solid #000; padding: 5px; text-align: left; background-color: #f0f0f0;">Quarter 3</th>
                    <th style="border: 1px solid #000; padding: 5px; text-align: left; background-color: #f0f0f0;">Quarter 4</th>
                    <th style="border: 1px solid #000; padding: 5px; text-align: left; background-color: #f0f0f0;">Semester Final Grade</th>
                </tr>
                <?php foreach ($gradesData['second_semester'] as $subject): ?>
                    <tr>
                        <td style="border: 1px solid #000; padding: 5px;"><?php echo htmlspecialchars($subject['subject_name']); ?></td>
                        <td style="border: 1px solid #000; padding: 5px;"><?php echo number_format($subject['third_grading'], 2); ?></td>
                        <td style="border: 1px solid #000; padding: 5px;"><?php echo number_format($subject['fourth_grading'], 2); ?></td>
                        <td style="border: 1px solid #000; padding: 5px;"><?php echo number_format(($subject['third_grading'] + $subject['fourth_grading']) / 2, 2); ?></td>
                    </tr>
                <?php endforeach; ?>
                <tfoot>
                    <tr>
                        <td colspan="3" style="border: 1px solid #000; padding: 5px; text-align: right; background-color: #f9f9f9; font-weight: bold;">General Average</td>
                        <td style="border: 1px solid #000; padding: 5px; text-align: center; background-color: #f9f9f9; font-weight: bold;"><?php echo $secondSemesterAverage; ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <div class='right'>
        <h2>REPORT ON LEARNER'S OBSERVED VALUES</h2>
                <table style="width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 10px;">
                    <tr>
                        <th style="border: 1px solid #000; padding: 5px; text-align: center; background-color: #f0f0f0;">Core Values</th>
                        <th style="border: 1px solid #000; padding: 5px; text-align: center; background-color: #f0f0f0;">Behavior Statements</th>
                        <th style="border: 1px solid #000; padding: 5px; text-align: center; background-color: #f0f0f0;">Quarter 1</th>
                        <th style="border: 1px solid #000; padding: 5px; text-align: center; background-color: #f0f0f0;">Quarter 2</th>
                        <th style="border: 1px solid #000; padding: 5px; text-align: center; background-color: #f0f0f0;">Quarter 3</th>
                        <th style="border: 1px solid #000; padding: 5px; text-align: center; background-color: #f0f0f0;">Quarter 4</th>
                    </tr>
                    <tr>
                        <td style="border: 1px solid #000; padding: 5px; text-align: left;">1. Maka-Diyos</td>
                        <td style="border: 1px solid #000; padding: 5px; text-align: left;">Expresses one's spiritual beliefs while respecting the spiritual beliefs of others</td>
                        <td style="border: 1px solid #000; padding: 5px; text-align: center;">SO</td>
                        <td style="border: 1px solid #000; padding: 5px; text-align: center;"></td>
                        <td style="border: 1px solid #000; padding: 5px; text-align: center;"></td>
                        <td style="border: 1px solid #000; padding: 5px; text-align: center;"></td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid #000; padding: 5px; text-align: left;"></td>
                        <td style="border: 1px solid #000; padding: 5px; text-align: left;">Shows adherence to ethical principles by upholding truth in all understandings</td>
                        <td style="border: 1px solid #000; padding: 5px; text-align: center;">SO</td>
                        <td style="border: 1px solid #000; padding: 5px; text-align: center;"></td>
                        <td style="border: 1px solid #000; padding: 5px; text-align: center;"></td>
                        <td style="border: 1px solid #000; padding: 5px; text-align: center;"></td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid #000; padding: 5px; text-align: left;">2. Makatao</td>
                        <td style="border: 1px solid #000; padding: 5px; text-align: left;">Is sensitive to individual, social, and cultural differences; resists stereotyping people</td>
                        <td style="border: 1px solid #000; padding: 5px; text-align: center;">SO</td>
                        <td style="border: 1px solid #000; padding: 5px; text-align: center;"></td>
                        <td style="border: 1px solid #000; padding: 5px; text-align: center;"></td>
                        <td style="border: 1px solid #000; padding: 5px; text-align: center;"></td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid #000; padding: 5px; text-align: left;"></td>
                        <td style="border: 1px solid #000; padding: 5px; text-align: left;">Demonstrates contributions towards solidarity</td>
                        <td style="border: 1px solid #000; padding: 5px; text-align: center;">SO</td>
                        <td style="border: 1px solid #000; padding: 5px; text-align: center;"></td>
                        <td style="border: 1px solid #000; padding: 5px; text-align: center;"></td>
                        <td style="border: 1px solid #000; padding: 5px; text-align: center;"></td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid #000; padding: 5px; text-align: left;">3. Makakalikasan</td>
                        <td style="border: 1px solid #000; padding: 5px; text-align: left;">Cares for the environment and utilizes resources wisely, judiciously, and economically</td>
                        <td style="border: 1px solid #000; padding: 5px; text-align: center;">SO</td>
                        <td style="border: 1px solid #000; padding: 5px; text-align: center;"></td>
                        <td style="border: 1px solid #000; padding: 5px; text-align: center;"></td>
                        <td style="border: 1px solid #000; padding: 5px; text-align: center;"></td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid #000; padding: 5px; text-align: left;">4. Makabansa</td>
                        <td style="border: 1px solid #000; padding: 5px; text-align: left;">Demonstrates pride in being a Filipino; exercises the rights and responsibilities of a Filipino citizen</td>
                        <td style="border: 1px solid #000; padding: 5px; text-align: center;">SO</td>
                        <td style="border: 1px solid #000; padding: 5px; text-align: center;"></td>
                        <td style="border: 1px solid #000; padding: 5px; text-align: center;"></td>
                        <td style="border: 1px solid #000; padding: 5px; text-align: center;"></td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid #000; padding: 5px; text-align: left;"></td>
                        <td style="border: 1px solid #000; padding: 5px; text-align: left;">Demonstrates appropriate behavior in carrying out activities in the school, community, and country</td>
                        <td style="border: 1px solid #000; padding: 5px; text-align: center;">SO</td>
                        <td style="border: 1px solid #000; padding: 5px; text-align: center;"></td>
                        <td style="border: 1px solid #000; padding: 5px; text-align: center;"></td>
                        <td style="border: 1px solid #000; padding: 5px; text-align: center;"></td>
                    </tr>
                </table>
                Observed Values

                <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
                    <tr>
                        <th style="border: 1px solid #000; padding: 5px; text-align: center;">Marking</th>
                        <th style="border: 1px solid #000; padding: 5px; text-align: center;">Non-numerical Rating</th>
                    </tr>
                    <tr>
                        <td style="border: 1px solid #000; padding: 5px; text-align: center;">AO</td>
                        <td style="border: 1px solid #000; padding: 5px; text-align: center;">Always Observed</td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid #000; padding: 5px; text-align: center;">SO</td>
                        <td style="border: 1px solid #000; padding: 5px; text-align: center;">Sometimes Observed</td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid #000; padding: 5px; text-align: center;">RO</td>
                        <td style="border: 1px solid #000; padding: 5px; text-align: center;">Rarely Observed</td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid #000; padding: 5px; text-align: center;">NO</td>
                        <td style="border: 1px solid #000; padding: 5px; text-align: center;">Not Observed</td>
                    </tr>
                </table>
                <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
                    <tr>
                        <th style="border: 1px solid #000; padding: 5px; text-align: center;">Descriptors</th>
                        <th style="border: 1px solid #000; padding: 5px; text-align: center;">Grading Scale</th>
                        <th style="border: 1px solid #000; padding: 5px; text-align: center;">Remarks</th>
                    </tr>
                    <tr>
                        <td style="border: 1px solid #000; padding: 5px; text-align: center;">Outstanding</td>
                        <td style="border: 1px solid #000; padding: 5px; text-align: center;">90-100</td>
                        <td style="border: 1px solid #000; padding: 5px; text-align: center;">Passed</td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid #000; padding: 5px; text-align: center;">Very Satisfactory</td>
                        <td style="border: 1px solid #000; padding: 5px; text-align: center;">85-89</td>
                        <td style="border: 1px solid #000; padding: 5px; text-align: center;">Passed</td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid #000; padding: 5px; text-align: center;">Satisfactory</td>
                        <td style="border: 1px solid #000; padding: 5px; text-align: center;">80-84</td>
                        <td style="border: 1px solid #000; padding: 5px; text-align: center;">Passed</td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid #000; padding: 5px; text-align: center;">Fairly Satisfactory</td>
                        <td style="border: 1px solid #000; padding: 5px; text-align: center;">75-79</td>
                        <td style="border: 1px solid #000; padding: 5px; text-align: center;">Passed</td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid #000; padding: 5px; text-align: center;">Did Not Meet Expectations</td>
                        <td style="border: 1px solid #000; padding: 5px; text-align: center;">Below 75</td>
                        <td style="border: 1px solid #000; padding: 5px; text-align: center;">Failed</td>
                    </tr>
                </table>

        </div>

</div>
</body>
</html>

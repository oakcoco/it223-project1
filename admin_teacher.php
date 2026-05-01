<?php
session_start();
if (!isset($_SESSION["admin"])) {
    header("Location: login.php");
    exit;
    }  

include 'config/db_connection.php';
$totalStudents = $conn -> query("SELECT COUNT(*) as count FROM students") -> fetch_assoc()['count'];
$totalSubjects =  $conn -> query("SELECT COUNT(*) as count FROM subjects") -> fetch_assoc()['count'];

$avgResult = $conn->query("
    SELECT student_id, AVG(grade) as avg_grade
    FROM grades
    GROUP BY student_id
    HAVING avg_grade < 75
");

$studentsFailed = $avgResult->num_rows;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css"> 
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <aside class="sidebar" id="sidebar">
        <div class = "greetings">
             <h5>Student Information and Grade Management</h5>
        </div>
        <nav class="sidebar-nav">
            <h6 class="nav-header"> MENU</h6>
            <a href="admin_teacher.php" class="nav-item">&nbsp;&nbsp;Dashboard</a>
            <a href="student_info.php" class="nav-item">&nbsp;&nbsp;Student Information Management</a>
            <a href="student_grades.php" class="nav-item">&nbsp;&nbsp;Student Grade Management</a>
        </nav>
        <footer class = "logout">
            &nbsp;&nbsp;<a href="config/logout.php"class="btn btn-danger"><i class="bi bi-box-arrow-right"></i> Log Out</a>
        </footer>
    </aside>

    <main class="main-content">
        <div class="container-fluid">

            <!-- dashboard default landing -->
            <h4 class="mb-4 fw-semibold">Dashboard</h4>
        
            <!-- upper cards for dashboard  -->
            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <div class="card card-metric card-students">
                        <div class="card-body d-flex align-items-center">
                            <div class="icon-box me-3">
                                <i class="bi bi-people-fill"></i>
                            </div>
                            <div>
                                <div class="total-students"><?php echo $totalStudents ?></div>
                                <div class="metric-label">Total Students

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-metric card-failed">
                        <div class="card-body d-flex align-items-center">
                            <div class="icon-box me-3">
                                <i class="bi bi-exclamation-triangle-fill"></i>
                            </div>
                            <div>
                                <div class="total-students"><?php echo $studentsFailed ?></div>
                                <div class="metric-label">Students with Failing Average</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-metric card-subjects">
                        <div class="card-body d-flex align-items-center">
                            <div class="icon-box me-3">
                                <i class="bi bi-book-fill"></i>
                            </div>
                            <div>
                                <div class="total-students"><?php echo $totalSubjects?></div>
                                <div class="metric-label">Total subjects</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
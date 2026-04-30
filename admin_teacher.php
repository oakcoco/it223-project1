<?php
  session_start();
  if (!isset($_SESSION["admin"])) {
      header("Location: login.php");
      exit;
      }  
    //   header("Pragma: no-cache");
    //   header("Expires: 0");
    //   header("Cache-Control: no-cache, no-store, must-revalidate");

  
include 'config/db_connection.php';
$totalStudents = $conn -> query("SELECT COUNT(*) as count FROM students") -> fetch_assoc()['count'];
$totalSubjects =  $conn -> query("SELECT COUNT(*) as count FROM subjects") -> fetch_assoc()['count'];

//vvv STILL ON DEVELOPMENT VVV
// $studentsFailed = $conn->query("SELECT COUNT(DISTINCT student_id) as count FROM grades WHERE grade <
//   75")->fetch_assoc()['count'];



  
$page = $_GET['page'] ?? 'dashboard';

switch ($page){
    case 'student_info':
        include 'student_info.php';
        exit;
    case 'student_grades':
        include 'student_grades.php';
        exit;
}
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
            <!-- make this update with the system -->
             <h5>Goodmorning Teacher!</h5>
        </div>
        <nav class="sidebar-nav">
            <h6 class="nav-header"> MENU</h6>

            <a href="?page=dashboard" class="nav-item">&nbsp;&nbsp;Dashboard</a>
            <a href="?page=student_info" class="nav-item">&nbsp;&nbsp;Student Information Management</a>
            <a href="?page=student_grades" class="nav-item">&nbsp;&nbsp;Student Grade Management</a>
        </nav>
        <footer class = "logout">
            &nbsp;&nbsp;<a href="config/logout.php"class="btn btn-danger"><i class="bi bi-box-arrow-right"></i> Log Out</a>
        </footer>
    </aside>
    
    <!-- content choose logic -->
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
                                <div class="total-students">NOT WORKING Line 12 to 14</div>
                                <div class="metric-label">Students with Failed Grade</div>
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

            <!-- recent activity -->
            <div class="card card-metric">
                <div class="card-body">
                    <h6 class="section-title">Recent Activity (not working)</h6>
                    <div class="activity-item">

                        <div class="activity-icon" style="background:#e3f9ff;color:#00915c;">
                            <i class="bi bi-pencil-square"></i>
                        </div>

                        <div class="activity-content">
                            <div class="activity-text">Maria Garcia submitted grades for Mathematics</div>
                            <div class="activity-time">2 hours ago</div>
                        </div>
                        <span class="badge bg-success badge-activity">Completed</span>

                    </div>

                    <div class="activity-item">
                        <div class="activity-icon" style="background:#fff3e3;color:#fd7e14;">
                            <i class="bi bi-person-plus-fill"></i>
                        </div>
                        <div class="activity-content">
                            <div class="activity-text">James Wilson enrolled in Science class</div>
                            <div class="activity-time">4 hours ago</div>
                        </div>
                        <span class="badge bg-primary badge-activity">New</span>
                    </div>

                    <div class="activity-item">
                        <div class="activity-icon" style="background:#ffe3e3;color:#dc3545;">
                            <i class="bi bi-exclamation-circle-fill"></i>
                        </div>
                        <div class="activity-content">
                            <div class="activity-text">20 students failed in History exam</div>
                            <div class="activity-time">Yesterday, 3:45 PM</div>
                        </div>
                        <span class="badge bg-danger badge-activity">Alert</span>
                    </div>

                    <div class="activity-item">
                        <div class="activity-icon" style="background:#e3f9ff;color:#00915c;">
                            <i class="bi bi-check-circle-fill"></i>
                        </div>
                        <div class="activity-content">
                            <div class="activity-text">Class section B completed all assessments</div>
                            <div class="activity-time">Yesterday, 11:20 AM</div>
                        </div>
                        <span class="badge bg-success badge-activity">Done</span>
                    </div>

                    <div class="activity-item">
                        <div class="activity-icon" style="background:#fff3e3;color:#fd7e14;">
                            <i class="bi bi-calendar-event-fill"></i>
                        </div>
                        <div class="activity-content">
                            <div class="activity-text">New quiz scheduled for English class</div>
                            <div class="activity-time">2 days ago</div>
                        </div>
                        <span class="badge bg-warning text-dark badge-activity">Scheduled</span>
                    </div>
                    
                </div>
            </div>
        </div>
    </main>
</body>
</html>
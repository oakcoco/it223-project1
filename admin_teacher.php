<?php
  session_start();
  if (!isset($_SESSION["admin"])) {
      header("Location: login.php");
      exit;
  }
  $page = $_GET['page'] ?? 'dashboard'; 
  ?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css"> 

<style>
    body{
        font-family: system-ui, -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Oxygen, 'Ubuntu', Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
    }
    .main-content{
        margin-left: 18%;
        padding: 30px;
    }
    /* aside */
    .sidebar{
        background: #00915c;
        color:1a1a1a;
        width: 18%;
        height: 100vh;
        position: fixed;
        top: 0;
        left: 0;
    }
    .greetings{
        font-size: large;
        color: white;
        padding: 5px;
        align-items: center;
        padding: 20px 20px 40px;
        border-bottom: 1px solid rgba(0,0,0,0.2);
    }
    .nav-header{
        padding: 10px 20px 5px;
        font-size: 0.75rem;
        color: #ffffffbb;
    }
    .nav-item {
        display: block;
        padding: 15px 20px;
        text-decoration: none;
        color:white;
        border-top: 1px solid rgba(0,0,0,0.2);
        border-bottom: 1px solid rgba(0,0,0,0.2);
    }

    .nav-item:nth-child(3) {
        border: none;
    }
    .nav-item:last-child {
        border-top: 1px solid rgba(0,0,0,0.2);
        border-bottom: 1px solid rgba(0,0,0,0.2);
    }
    .nav-item:hover {   
        background: rgba(0,0,0,0.1);
    }
    .logout {
        bottom: 90px;
        left: 150px;
        padding: 175% 20px 20px;
    }

    /* cards within main contenet */
    .card-metric{
        border: none;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        transition: transform 0.2s;
    }
    .card-metric .card-body{
        padding: 25px;
    }
    .card-metric .icon-box{
        width: 50px;
        height: 50px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }
    .card-students .icon-box{
        background: #e3f9ff;
        color: #00915c;
    }
    .card-failed .icon-box{
        background: #ffe3e3;
        color: #dc3545;
    }
    .card-subjects .icon-box{
        background: #fff3e3;
        color: #fd7e14;
    }
    .total-students{
        font-size: 2rem;
        font-weight: 700;
        color: #1a1a1a;
    }
    .metric-label{
        font-size: 0.85rem;
        color: #6c757d;
        margin-top: 5px;
    }
    .section-title{
        font-size: 1.1rem;
        font-weight: 600;
        color: #1a1a1a;
        margin-bottom: 15px;
    }
    .activity-item{
        display: flex;
        align-items: center;
        padding: 12px 0;
        border-bottom: 1px solid #f0f0f0;
    }
    .activity-item:last-child{
        border-bottom: none;
    }
    .activity-icon{
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 15px;
        font-size: 1rem;
    }
    .activity-content{
        flex: 1;
    }
    .activity-text{
        font-size: 0.9rem;
        color: #1a1a1a;
        margin-bottom: 2px;
    }
    .activity-time{
        font-size: 0.75rem;
        color: #adb5bd;
    }
    .badge-activity{
        font-size: 0.7rem;
        padding: 4px 8px;
    }

</style>
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
            <?php switch($page): 
                    case 'student_info': ?>              
                     <h4 class="mb-4 fw-semibold">Student Information Management</h4>                                                   
                    <p>Student information content goes here.</p>         
             <?php break;  
                    case 'student_grades': ?>                  
                     <h4 class="mb-4 fw-semibold">Student Grade Management</h4>                                                           
                     <p>Student grades content goes here.</p>              
                <?php break; 
                    default: ?>
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
                                <div class="total-students">123</div>
                                <div class="metric-label">Total Students</div>
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
                                <div class="total-students">48</div>
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
                                <div class="total-students">12</div>
                                <div class="metric-label">Total Subjects Being Taught</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- recent activity -->
            <div class="card card-metric">
                <div class="card-body">
                    <h6 class="section-title">Recent Activity</h6>
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
             <?php endswitch; ?>
        </div>
    </main>
</body>
</html>
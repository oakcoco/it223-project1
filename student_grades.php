<?php
session_start();
if (!isset($_SESSION["admin"])) {
    header("Location: login.php");
    exit;
    } 
include 'config/db_connection.php';

//for sort
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : '';

$sql = "SELECT * FROM students WHERE 1=1";
$params = [];
$types = '';

if ($search !== '') {
    $search_term = '%' . $conn->real_escape_string($search) . '%';
    $sql .= " AND (first_name LIKE ? OR middle_name LIKE ? OR last_name LIKE ?)";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $types = 'sss';
}

$sql .= " ORDER BY last_name ASC";

if (count($params) > 0) {
    $statement = $conn->prepare($sql);
    $statement->bind_param($types, ...$params);
    $statement->execute();
    $students_result = $statement->get_result();
} else {
    $students_result = $conn->query($sql);
}

// fetch all students to compute average grades
$students = [];
while ($student = $students_result->fetch_assoc()) {
    $student_grades = [];
    $grades_stmt = $conn->prepare("SELECT g.grade, s.subject_name FROM grades g JOIN subjects s ON g.subject_id = s.id WHERE g.student_id = ?");
    $grades_stmt->bind_param("i", $student['id']);
    $grades_stmt->execute();
    $grades_result = $grades_stmt->get_result();
    $total_grade = 0;
    $grade_count = 0;
    while ($g = $grades_result->fetch_assoc()) {
        $student_grades[$g['subject_name']] = $g['grade'];
        $total_grade += (float)$g['grade'];
        $grade_count++;
    }
    $grades_stmt->close();

    $student['grades'] = $student_grades;
    $student['average'] = $grade_count > 0 ? $total_grade / $grade_count : null;
    $students[] = $student;
}

// sort students in PHP based on computed averages
if ($sort === 'grade_asc') {
    usort($students, function($a, $b) {
        if ($a['average'] === null) return 1;
        if ($b['average'] === null) return -1;
        return $a['average'] - $b['average'];
    });
} elseif ($sort === 'grade_desc') {
    usort($students, function($a, $b) {
        if ($a['average'] === null) return 1;
        if ($b['average'] === null) return -1;
        return $b['average'] - $a['average'];
    });
} else {
    // default to sort name by asc
    usort($students, function($a, $b) {
        return strcmp($a['last_name'], $b['last_name']);
    });
}

// fetch all subjects for modals
$subjects_result = $conn->query("SELECT * FROM subjects ORDER BY subject_name");

// handle grade update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_grades') {
    $student_id = (int) $_POST['student_id'];
    $grade_level = trim($_POST['grade_level']);
    $subjects = $_POST['subjects'];


    $existing = [];
    $statement = $conn->prepare("SELECT id, subject_id FROM grades WHERE student_id = ?");
    $statement->bind_param("i", $student_id);
    $statement->execute();
    $result = $statement->get_result();
    while ($row = $result->fetch_assoc()) {
        $existing[$row['subject_id']] = $row['id'];
    }
    $statement->close();

    foreach ($subjects as $subject_id => $grade_value) {
        $subject_id = (int) $subject_id;
        $grade_value = trim($grade_value);

        if ($grade_value !== '') {
            if (isset($existing[$subject_id])) {
                // Update existing grade
                $statement = $conn->prepare("UPDATE grades SET grade = ? WHERE id = ?");
                $statement->bind_param("di", $grade_value, $existing[$subject_id]);
                $statement->execute();
                $statement->close();
            } else {
                // Insert new grade
                $statement = $conn->prepare("INSERT INTO grades (student_id, subject_id, grade, grade_level) VALUES (?, ?, ?, ?)");
                $statement->bind_param("iids", $student_id, $subject_id, $grade_value, $grade_level);
                $statement->execute();
                $statement->close();
            }
        }
    }

    header("Location: student_grades.php?success=1");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Grades</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
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

        <div class = "logout">
            &nbsp;&nbsp;<a href="config/logout.php"class="btn btn-danger"><i class="bi bi-box-arrow-right"></i> Log Out</a>
        </div>
    </aside>
    <main class = "main-content">
        <div class = "container-fluid">
            <h4 class="mb-4 fw-semibold">Student Grade Management <br><br></h4>
                                <form method="GET" class="d-flex gap-2 align-items-center mb-3">
                    <div class="col">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0">
                            <i class="bi bi-search"></i>
                            </span>
                            <input type="text" name="search" class="form-control border-start-0 ps-0"
                            placeholder="Search students..." value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                    </div>
                    <div class="d-flex gap-2 align-items-center">
                        <select name="sort" class="form-select" onchange="this.form.submit()">
                            <option value="">Sort by</option>
                            <option value="name" <?php if ($sort === 'name') echo 'selected'; ?>>Name (Alphabetical)</option>
                            <option value="grade_asc" <?php if ($sort === 'grade_asc') echo 'selected'; ?>>Average Grade (Lowest First)</option>
                            <option value="grade_desc" <?php if ($sort === 'grade_desc') echo 'selected'; ?>>Average Grade (Highest First)</option>
                        </select>
                        <div class="col-auto">
                        <a href="student_grades.php" class="btn btn-primary">Show All Students</a>
                        </div>
                    </div>
                </form>
            
            <div class="card student-container">
                <div class="card-body">
                    <h6 class="card-title mb-4"><br>My Students:</h6>

                    <div class="d-flex align-items-center p-2 fw-bold bg-light rounded">
                        <div class="me-3" style="width: 50px"></div>
                        <div style="flex: 1;">Student Name</div>
                        <div class="d-flex gap-2" style="flex: 2; justify-content: center;">
                            <div class="text-center" style="width: 70px;">Math</div>
                            <div class="text-center" style="width: 70px;">Science</div>
                            <div class="text-center" style="width: 70px;">English</div>
                            <div class="text-center" style="width: 80px;">Average</div>
                        </div>
                        <div class="ms-2" style="width: 150px;">Actions</div>
                    </div>


                    <?php
                        foreach ($students as $student):
                            $average = number_format($student['average'], 2);
                                if($average >= 75){
                                    $status_class = 'bg-success';
                                    $status = 'PASSED';
                                }
                                else{
                                    $status_class = 'bg-danger';
                                    $status = 'FAILED';
                                }
                            ?>

                            <div class="d-flex align-items-center mb-3 p-3 border rounded">

                                <div class="me-3" style="width: 50px;">
                                    <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                        <i class="bi bi-person-fill text-white fs-4"></i>
                                    </div>
                                </div>

                                <div style="flex: 1;">
                                    <div class="fw-semibold">
                                    <?php echo
                                            $student["last_name"]
                                            . ', '
                                            . $student["first_name"]
                                            . ' '
                                            . $student["middle_name"]
                                            ?>
                                    </div>
                                </div>

                            <div class="d-flex gap-2" style="flex: 2; justify-content: center;">
                                <div class="text-center" style="width: 70px;"><?php echo isset($student['grades']['Math']) ? $student['grades']['Math'] : '—'; ?></div>
                                <div class="text-center" style="width: 70px;"><?php echo isset($student['grades']['Science']) ? $student['grades']['Science'] : '—'; ?></div>
                                <div class="text-center" style="width: 70px;"><?php echo isset($student['grades']['English']) ? $student['grades']['English'] : '—'; ?></div>
                                <div class="text-center" style="width: 80px;"><span class="fw-bold"><?php echo $average;?></span>
                                <div class="text-right"  style="width: 340px; margin-top: -25px;">
                                            <span class="badge <?php echo $status_class; ?>"
                                                style="font-size: 0.9rem; padding: 0.50em 1em;">
                                                <?php echo $status; ?>
                                            </span>
                                    </div>
                                </div>
                            </div>

                            <div class="ms-2" style="width: 150px;">
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-primary btn-sm"
                                        data-bs-toggle="modal" data-bs-target="#viewGradesModal<?php echo $student['id']; ?>">
                                        <i class="bi bi-eye"></i> View
                                    </button>
                                    <button type="button" class="btn btn-warning btn-sm"
                                        data-bs-toggle="modal" data-bs-target="#editGradesModal<?php echo $student['id']; ?>">
                                        <i class="bi bi-pencil"></i> Edit
                                    </button>
                                </div>
                            </div>

                        </div>
                    <?php endforeach; ?>
            </div>
        </div>
    </main>


    <!-- view grades modal -->
    <?php
    // reset and fetch students again for modal generation
    $students_for_modal = $conn->query("SELECT * FROM students ORDER BY last_name");
    while ($modal_student = $students_for_modal->fetch_assoc()):
        $student_id = $modal_student['id'];
        $student_name = htmlspecialchars($modal_student['first_name'] . ' ' . $modal_student['middle_name'] . ' ' . $modal_student['last_name']);
        $grade_level = htmlspecialchars($modal_student['grade_level']);

        // get grades for this student
        $student_grades = [];
        $grades_stmt = $conn->prepare("SELECT g.grade, s.subject_name FROM grades g JOIN subjects s ON g.subject_id = s.id WHERE g.student_id = ?");
        $grades_stmt->bind_param("i", $student_id);
        $grades_stmt->execute();
        $grades_result = $grades_stmt->get_result();
        $has_any_grade = false;
        $total_grade = 0;
        $grade_count = 0;
        while ($g = $grades_result->fetch_assoc()) {
            $student_grades[] = $g;
            $has_any_grade = true;
            $total_grade += (float)$g['grade'];
            $grade_count++;
        }
        $grades_stmt->close();
    ?>
    <div class="modal fade" id="viewGradesModal<?php echo $student_id; ?>" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">View Grades - <?php echo $student_name; ?></h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <p class="text-muted mb-3">Grade Level: <?php echo $grade_level; ?></p>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Subject</th>
                            <th>Grade</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $subjects_result->data_seek(0);
                        while ($subj = $subjects_result->fetch_assoc()):
                            $grade_display = '—';
                            foreach ($student_grades as $sg) {
                                if ($sg['subject_name'] === $subj['subject_name']) {
                                    $grade_display = $sg['grade'];
                                    break;
                                }
                            }
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($subj['subject_name']); ?></td>
                            <td><?php echo $grade_display !== '—' ? htmlspecialchars($grade_display) : '<span class="text-muted">—</span>'; ?></td>
                        </tr>
                        <?php endwhile; ?>
                        <tr class="table-primary fw-bold">
                            <td>Average Grade</td>
                            <td><?php echo $grade_count > 0 ? number_format($total_grade / $grade_count, 2) : '<span class="text-muted">—</span>'; ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <?php if (!$has_any_grade): ?>
            <div class="text-center text-muted py-4">
                <p>No grades recorded for this student.</p>
            </div>
            <?php endif; ?>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>
    <?php endwhile; ?>

    <!-- edit grades modal -->
    <?php
    $students_for_modal->data_seek(0);
    while ($modal_student = $students_for_modal->fetch_assoc()):
        $student_id = $modal_student['id'];
        $student_name = htmlspecialchars($modal_student['first_name'] . ' ' . $modal_student['middle_name'] . ' ' . $modal_student['last_name']);
        $grade_level = htmlspecialchars($modal_student['grade_level']);

        // get existing grades for this student
        $existing_grades = [];
        $grades_stmt = $conn->prepare("SELECT g.subject_id, g.grade FROM grades g WHERE g.student_id = ?");
        $grades_stmt->bind_param("i", $student_id);
        $grades_stmt->execute();
        $grades_result = $grades_stmt->get_result();
        while ($g = $grades_result->fetch_assoc()) {
            $existing_grades[$g['subject_id']] = $g['grade'];
        }
        $grades_stmt->close();
    ?>

    <div class="modal fade" id="editGradesModal<?php echo $student_id; ?>" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Edit Grades - <?php echo $student_name; ?></h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <p class="text-muted mb-3">Grade Level: <?php echo $grade_level; ?></p>
            <form action="student_grades.php" method="POST">
                <input type="hidden" name="action" value="update_grades">
                <input type="hidden" name="student_id" value="<?php echo $student_id; ?>">
                <input type="hidden" name="grade_level" value="<?php echo $grade_level; ?>">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Subject</th>
                                <th>Grade</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $subjects_result->data_seek(0);
                            while ($subj = $subjects_result->fetch_assoc()):
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($subj['subject_name']); ?></td>
                                <td>
                                    <input type="number" step="0.01" class="form-control"
                                           name="subjects[<?php echo $subj['id']; ?>]"
                                           value=""
                                           min="0" max="100" placeholder="0">
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer px-0 pb-0 mt-3">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
          </div>
        </div>
      </div>
    </div>
    <?php endwhile; ?>

</body>
</html>
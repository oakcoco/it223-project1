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

if ($search !== '') {
    $search_term = '%' . $conn->real_escape_string($search) . '%';
    $sql .= " AND (first_name LIKE ? OR middle_name LIKE ? OR last_name LIKE ? OR email LIKE ?)";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
}

$order_by = 'last_name ASC';
if ($sort === 'name') {
    $order_by = 'last_name ASC, first_name ASC';
} elseif ($sort === 'id') {
    $order_by = 'id ASC';
} elseif ($sort === 'grade_asc') {
    $order_by = 'grade_level ASC';
} elseif ($sort === 'grade_desc') {
    $order_by = 'grade_level DESC';
}
$sql .= " ORDER BY " . $order_by;

if (count($params) > 0) {
    $types = str_repeat('s', count($params));
    $statement = $conn->prepare($sql);
    $statement->bind_param($types, ...$params);
    $statement->execute();
    $students = $statement->get_result();
} else {
    $students = $conn->query($sql);
}

// Fetch all subjects for modals
$subjects_result = $conn->query("SELECT * FROM subjects ORDER BY subject_name");

// Handle grade update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_grades') {
    $student_id = (int) $_POST['student_id'];
    $grade_level = trim($_POST['grade_level']);
    $subjects = $_POST['subjects']; // array of subject_id => grade_value

    // First, get existing grade IDs for this student
    $existing = [];
    $stmt = $conn->prepare("SELECT id, subject_id FROM grades WHERE student_id = ?");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $existing[$row['subject_id']] = $row['id'];
    }
    $stmt->close();

    foreach ($subjects as $subject_id => $grade_value) {
        $subject_id = (int) $subject_id;
        $grade_value = trim($grade_value);

        if ($grade_value !== '') {
            if (isset($existing[$subject_id])) {
                // Update existing grade
                $stmt = $conn->prepare("UPDATE grades SET grade = ? WHERE id = ?");
                $stmt->bind_param("di", $grade_value, $existing[$subject_id]);
                $stmt->execute();
                $stmt->close();
            } else {
                // Insert new grade
                $stmt = $conn->prepare("INSERT INTO grades (student_id, subject_id, grade, grade_level) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("iids", $student_id, $subject_id, $grade_value, $grade_level);
                $stmt->execute();
                $stmt->close();
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
    <title></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css"> 
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <aside class="sidebar" id="sidebar">
        <div class = "greetings">
             <h5>Goodmorning Teacher!</h5>
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
                            <option value="id" <?php if ($sort === 'id') echo 'selected'; ?>>Student ID (Specific)</option>
                            <option value="grade_asc" <?php if ($sort === 'grade_asc') echo 'selected'; ?>>Average Grade (Ascending)</option>
                            <option value="grade_desc" <?php if ($sort === 'grade_desc') echo 'selected'; ?>>Average Grade (Descending)</option>
                        </select>
                        <div class="col-auto">
                        <a href="student_grades.php" class="btn btn-primary">Show All Students</a>
                        </div>
                    </div>
                </form>
            
            <div class="card student-container">
                <div class="card-body">
                    <h6 class="card-title mb-4"><br>My Students:</h6>

                    <?php while ($studentName = $students->fetch_assoc()): ?>     
                        <div class="d-flex align-items-center mb-3 p-3 border rounded">
                            
                            <div class="me-3">
                                <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                    <i class="bi bi-person-fill text-white fs-4"></i>
                                </div>
                            </div>
                            
                            <div class="flex-grow-1">
                                <div class="fw-semibold">
                                <?php echo 
                                        $studentName["last_name"] 
                                        . ', ' 
                                        . $studentName["first_name"]
                                        . ' ' 
                                        . $studentName["middle_name"]
                                        ?> 
                                    
                                </div>
                            </div>
    
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-primary btn-sm view-grades-btn"
                                    data-id="<?php echo $studentName['id']; ?>"
                                    data-name="<?php echo htmlspecialchars($studentName['first_name'] . ' ' . $studentName['middle_name'] . ' ' . $studentName['last_name']); ?>"
                                    data-grade-level="<?php echo htmlspecialchars($studentName['grade_level']); ?>"
                                    data-bs-toggle="modal" data-bs-target="#viewGradesModal">
                                    <i class="bi bi-eye"></i> View Grades
                                </button>
                                <button type="button" class="btn btn-warning btn-sm edit-grades-btn"
                                    data-id="<?php echo $studentName['id']; ?>"
                                    data-name="<?php echo htmlspecialchars($studentName['first_name'] . ' ' . $studentName['middle_name'] . ' ' . $studentName['last_name']); ?>"
                                    data-grade-level="<?php echo htmlspecialchars($studentName['grade_level']); ?>"
                                    data-bs-toggle="modal" data-bs-target="#editGradesModal">
                                    <i class="bi bi-pencil"></i> Edit Grades
                                </button>
                            </div>
                            
                        </div>
                    <?php endwhile; ?>
            </div>
        </div>
    </main>

    <!-- Hidden data for grades -->
    <?php
    // Fetch all grades with subject names for JavaScript to use
    $all_grades = [];
    $grades_result = $conn->query("
        SELECT g.id, g.student_id, g.subject_id, g.grade, g.grade_level, s.subject_name, s.subject_code
        FROM grades g
        JOIN subjects s ON g.subject_id = s.id
        ORDER BY s.subject_name
    ");
    while ($g = $grades_result->fetch_assoc()) {
        $all_grades[$g['student_id']][] = $g;
    }
    ?>

    <!-- View Grades Modal -->
    <div class="modal fade" id="viewGradesModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">View Grades - <span id="view-student-name"></span></h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <p class="text-muted mb-3">Grade Level: <span id="view-grade-level"></span></p>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Subject</th>
                            <th>Grade</th>
                        </tr>
                    </thead>
                    <tbody id="view-grades-body">
                    </tbody>
                </table>
            </div>
            <div id="view-no-grades" class="text-center text-muted py-4 d-none">
                <p>No grades recorded for this student.</p>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Edit Grades Modal -->
    <div class="modal fade" id="editGradesModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Edit Grades - <span id="edit-student-name"></span></h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <p class="text-muted mb-3">Grade Level: <span id="edit-grade-level"></span></p>
            <form action="student_grades.php" method="POST">
                <input type="hidden" name="action" value="update_grades">
                <input type="hidden" name="student_id" id="edit-student-id">
                <input type="hidden" name="grade_level" id="edit-grade-level-input">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Subject</th>
                                <th>Grade</th>
                            </tr>
                        </thead>
                        <tbody id="edit-grades-body">
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

<script>
// Grades data and subjects from PHP
const gradesData = <?php echo json_encode($all_grades); ?>;
const subjectsData = <?php
$subjects_list = [];
while ($s = $subjects_result->fetch_assoc()) {
    $subjects_list[] = $s;
}
echo json_encode($subjects_list); ?>;

document.addEventListener('DOMContentLoaded', function() {
    // View Grades button handler
    document.querySelectorAll('.view-grades-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const studentId = this.dataset.id;
            const studentName = this.dataset.name;
            const gradeLevel = this.dataset.gradeLevel;

            document.getElementById('view-student-name').textContent = studentName;
            document.getElementById('view-grade-level').textContent = gradeLevel;

            const tbody = document.getElementById('view-grades-body');
            const noGrades = document.getElementById('view-no-grades');
            tbody.innerHTML = '';

            // Create a map of existing grades for this student
            const studentGrades = {};
            if (gradesData[studentId]) {
                gradesData[studentId].forEach(function(g) {
                    studentGrades[g.subject_id] = g.grade;
                });
            }

            // Show all subjects
            let hasAnyGrade = false;
            let totalGrade = 0;
            let gradeCount = 0;
            subjectsData.forEach(function(subject) {
                const grade = studentGrades[subject.id];
                const row = document.createElement('tr');
                if (grade !== undefined) {
                    hasAnyGrade = true;
                    totalGrade += parseFloat(grade);
                    gradeCount++;
                    row.innerHTML = `
                        <td>${subject.subject_name}</td>
                        <td>${grade}</td>
                    `;
                } else {
                    row.innerHTML = `
                        <td>${subject.subject_name}</td>
                        <td class="text-muted">—</td>
                    `;
                }
                tbody.appendChild(row);
            });

            // Add average row
            const avgRow = document.createElement('tr');
            avgRow.className = 'table-primary fw-bold';
            if (gradeCount > 0) {
                const avg = (totalGrade / gradeCount).toFixed(2);
                avgRow.innerHTML = `
                    <td>Average Grade</td>
                    <td>${avg}</td>
                `;
            } else {
                avgRow.innerHTML = `
                    <td>Average Grade</td>
                    <td class="text-muted">—</td>
                `;
            }
            tbody.appendChild(avgRow);

            if (hasAnyGrade) {
                noGrades.classList.add('d-none');
            } else {
                noGrades.classList.remove('d-none');
            }
        });
    });

    // Edit Grades button handler
    document.querySelectorAll('.edit-grades-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const studentId = this.dataset.id;
            const studentName = this.dataset.name;
            const gradeLevel = this.dataset.gradeLevel;

            document.getElementById('edit-student-name').textContent = studentName;
            document.getElementById('edit-grade-level').textContent = gradeLevel;
            document.getElementById('edit-student-id').value = studentId;
            document.getElementById('edit-grade-level-input').value = gradeLevel;

            const tbody = document.getElementById('edit-grades-body');
            tbody.innerHTML = '';

            // Create a map of existing grades for this student
            const studentGrades = {};
            if (gradesData[studentId]) {
                gradesData[studentId].forEach(function(g) {
                    studentGrades[g.subject_id] = g.grade;
                });
            }

            // Show all subjects with inputs
            let totalGrade = 0;
            let gradeCount = 0;
            subjectsData.forEach(function(subject) {
                const grade = studentGrades[subject.id] !== undefined ? studentGrades[subject.id] : '';
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${subject.subject_name}</td>
                    <td>
                        <input type="number" step="0.01" class="form-control" name="subjects[${subject.id}]" value="${grade}" min="0" max="100" placeholder="0">
                    </td>
                `;
                tbody.appendChild(row);
                if (grade !== '') {
                    totalGrade += parseFloat(grade);
                    gradeCount++;
                }
            });

            // Add average row
            const avgRow = document.createElement('tr');
            avgRow.className = 'table-primary fw-bold';
            if (gradeCount > 0) {
                const avg = (totalGrade / gradeCount).toFixed(2);
                avgRow.innerHTML = `
                    <td>Average Grade</td>
                    <td>${avg}</td>
                `;
            } else {
                avgRow.innerHTML = `
                    <td>Average Grade</td>
                    <td class="text-muted">—</td>
                `;
            }
            tbody.appendChild(avgRow);
        });
    });
});
</script>
</body>
</html>
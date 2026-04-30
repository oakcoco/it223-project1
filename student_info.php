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
    $sql .= " AND (first_name LIKE ? OR middle_name LIKE ? OR last_name LIKE ? OR email LIKE ?)";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $types = 'ssss';
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
    $statement = $conn->prepare($sql);
    $statement->bind_param($types, ...$params);
    $statement->execute();
    $students = $statement->get_result();
} else {
    $students = $conn->query($sql);
}

//for delete
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $conn->query("DELETE FROM grades WHERE student_id = $id");
    $conn->query("DELETE FROM students WHERE id = $id");
    header("Location: student_info.php?success=1");
    exit();
}

//for update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['action']) && $_POST['action'] === 'update') {

        $student_id = (int) $_POST['student_id'];
        $first_name = trim($_POST['first_name']);
        $middle_name = trim($_POST['middle_name']);
        $last_name = trim($_POST['last_name']);

        $email = trim($_POST['email']);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            header("Location: student_info.php?error=invalid_email");
            exit();
        }

        $grade_level = (int) trim($_POST['grade_level']);
        if ($grade_level < 1 || $grade_level > 12) {
            header("Location: student_info.php?error=invalid_grade");
            exit();
        }

        $age = (int) trim($_POST['age']);
        if ($age <= 3 || $age >= 90){
          header("Location:student_info.php?error=invalid_age");
          exit();
        }

        $sex = trim($_POST['sex']);
        if ($sex != "Male" && $sex != "Female" && $sex != "Others"){
          header("Location:student_info.php?error=invalid_sex");
          exit;
        }
        $address = trim($_POST['address']);

        $statement = $conn->prepare("UPDATE students SET first_name=?, middle_name=?, last_name=?, email=?, grade_level=?, age=?, sex=?, address=? WHERE id=?");
        $statement->bind_param("ssssiissi", $first_name, $middle_name, $last_name, $email, $grade_level, $age, $sex, $address, $student_id);
        $statement->execute();
        $statement->close();
        header("Location: student_info.php?success=1");
        exit();
    }
//for view
    elseif (isset($_POST['first_name'])) {

        $first_name =   trim($_POST['first_name']);
        $middle_name =  trim($_POST['middle_name']);
        $last_name =    trim($_POST['last_name']);
        $email =        trim($_POST['email']);
        $grade_level =  (int) trim($_POST['grade_level']);
        $age =          trim($_POST['age']);
        $sex =          trim($_POST['sex']);
        $address =      trim($_POST['address']);
        

        $statement = $conn->prepare
        ("INSERT INTO students (first_name, middle_name, last_name, email, grade_level, age, sex, address)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

        $statement->bind_param("ssssiiss", $first_name, $middle_name, $last_name, $email, $grade_level, $age, $sex, $address);
        $statement->execute();
        $statement->close();

        header("Location: student_info.php?success=1");
        exit();
    }
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
            <h4 class="mb-4 fw-semibold">Student Information Management <br><br></h4>

                <?php if (isset($_GET['error'])): ?>
                    <?php
                    $error_msgs = [
                        'invalid_email' => 'Invalid email format. Please try again.',
                        'invalid_grade' => 'Grade level must be between 1 and 12.',
                        'invalid_age' => 'Age must be between 3 and 90.',
                        'invalid_sex' => 'Sex must be Male, Female, or Others.',
                    ];
                    $error_msg = $error_msgs[$_GET['error']] ?? 'Invalid input. Please try again.';
                    ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo $error_msg; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php elseif (isset($_GET['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        Student record updated/added successfully.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

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
                        <a href="student_info.php" class="btn btn-primary">Show All Students</a>
                        </div>
                    </div>
                </form>
                
            <div class="student-info-card">   
                <div class="card student-container">
                    <div class="card-body">
                        <div class="title-side d-flex justify-content-between align-items-center">
                            <h6 class="card-title mb-4"><br>My Students:</h6>                   
                            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addStudentModal">
                                <i class="bi bi-plus-circle"></i> Add Student 
                            </button>
                        </div>
                        
                        <?php if (isset($students) && $students instanceof mysqli_result && $students->num_rows > 0): ?>
                        <?php while ($studentName = $students->fetch_assoc()): ?>        
                        
                        <div class="d-flex align-items-center mb-3 p-3 border rounded">
                            
                            <div class="me-3">
                                <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                    <i class="bi bi-person-fill text-white fs-4"></i>
                                </div>
                            </div>
                            
                            <div class="flex-grow-1">
                                <div class="fw-semibold">
                                <?php echo $studentName["last_name"] 
                                        . ', ' 
                                        . $studentName["first_name"]
                                        . ' ' 
                                        . $studentName["middle_name"]
                                        ?> 
                                    
                                </div>
                            </div>
                            
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-primary btn-sm view-btn"
                                    data-id="<?php echo $studentName['id']; ?>"
                                    data-first="<?php echo htmlspecialchars($studentName['first_name']); ?>"
                                    data-middle="<?php echo htmlspecialchars($studentName['middle_name']); ?>"
                                    data-last="<?php echo htmlspecialchars($studentName['last_name']); ?>"
                                    data-email="<?php echo htmlspecialchars($studentName['email']); ?>"
                                    data-grade="<?php echo $studentName['grade_level']; ?>"
                                    data-age="<?php echo  htmlspecialchars($studentName['age']); ?>"
                                    data-sex="<?php echo htmlspecialchars($studentName['sex']); ?>"
                                    data-address="<?php echo htmlspecialchars($studentName['address']); ?>"
                                    data-bs-toggle="modal" data-bs-target="#viewStudentModal">
                                    <i class="bi bi-eye"></i> View
                                </button>
                                <button type="button" class="btn btn-warning btn-sm edit-btn"
                                    data-id="<?php echo $studentName['id']; ?>"
                                    data-first="<?php echo htmlspecialchars($studentName['first_name']); ?>"
                                    data-middle="<?php echo htmlspecialchars($studentName['middle_name']); ?>"
                                    data-last="<?php echo htmlspecialchars($studentName['last_name']); ?>"
                                    data-email="<?php echo htmlspecialchars($studentName['email']); ?>"
                                    data-grade="<?php echo $studentName['grade_level']; ?>"
                                    data-age="<?php echo htmlspecialchars($studentName['age']); ?>"
                                    data-sex="<?php echo htmlspecialchars($studentName['sex']); ?>"
                                    data-address="<?php echo htmlspecialchars($studentName['address']); ?>"
                                    data-bs-toggle="modal" data-bs-target="#editStudentModal">
                                    <i class="bi bi-pencil"></i> Edit Information
                                </button>
                                <a href="student_info.php?delete=<?php echo $studentName['id']; ?>"
                                class="btn btn-danger btn-sm" 
                                onclick="return confirm('Are you sure you want to delete this student?');">
                                <i class="bi bi-trash"></i> Delete Student
                                </a>
                            </div>
                        </div>
                        <?php endwhile; ?>
                        <?php else: ?>
                            <div class="text-center text-muted p-4">No students found.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.view-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.getElementById('view-student-id').textContent = this.dataset.id;
            document.getElementById('view-first-name').textContent = this.dataset.first;
            document.getElementById('view-middle-name').textContent = this.dataset.middle;
            document.getElementById('view-last-name').textContent = this.dataset.last;
            document.getElementById('view-email').textContent = this.dataset.email;
            document.getElementById('view-grade').textContent = this.dataset.grade;
            document.getElementById('view-age').textContent = this.dataset.age;
            document.getElementById('view-sex').textContent = this.dataset.sex;
            document.getElementById('view-address').textContent = this.dataset.address;
            
        });
    });

    document.querySelectorAll('.edit-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.getElementById('edit-student-id').value = this.dataset.id;
            document.getElementById('edit-first-name').value = this.dataset.first;
            document.getElementById('edit-middle-name').value = this.dataset.middle;
            document.getElementById('edit-last-name').value = this.dataset.last;
            document.getElementById('edit-email').value = this.dataset.email;
            document.getElementById('edit-grade').value = this.dataset.grade;
            document.getElementById('edit-age').value = this.dataset.age;
            document.getElementById('edit-sex').value = this.dataset.sex;
            document.getElementById('edit-address').value = this.dataset.address;
          });
    });
});
</script>
</body>

<!-- View Student Modal -->
<div class="modal fade" id="viewStudentModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Student Information</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-12 mb-3">
            <label class="fw-semibold">Student ID</label>
            <p class="form-control-plaintext" id="view-student-id"></p>
          </div>
          <div class="col-md-6 mb-3">
            <label class="fw-semibold">First Name</label>
            <p class="form-control-plaintext" id="view-first-name"></p>
          </div>
          <div class="col-md-6 mb-3">
            <label class="fw-semibold">Middle Name</label>
            <p class="form-control-plaintext" id="view-middle-name"></p>
          </div>
          <div class="col-md-6 mb-3">
            <label class="fw-semibold">Last Name</label>
            <p class="form-control-plaintext" id="view-last-name"></p>
          </div>
          <div class="col-md-6 mb-3">
            <label class="fw-semibold">Email</label>
            <p class="form-control-plaintext" id="view-email"></p>
          </div>
          <div class="col-md-6 mb-3">
            <label class="fw-semibold">Grade Level</label>
            <p class="form-control-plaintext" id="view-grade"></p>
          </div>
          <div class="col-md-6 mb-3">
            <label class="fw-semibold">Age</label>
            <p class="form-control-plaintext" id="view-age"></p>
          </div>
          <div class="col-md-6 mb-3">
            <label class="fw-semibold">Sex</label>
            <p class="form-control-plaintext" id="view-sex"></p>
          </div>
          <div class="col-md-6 mb-3">
            <label class="fw-semibold">Address</label>
            <p class="form-control-plaintext" id="view-address"></p>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Edit Student Modal -->
<div class="modal fade" id="editStudentModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit Student Information</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form action="student_info.php" method="POST">
          <input type="hidden" name="action" value="update">
          <input type="hidden" name="student_id" id="edit-student-id">

          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">First Name</label>
              <input type="text" class="form-control" name="first_name" id="edit-first-name" pattern="^[A-Z][a-zA-Z]*$" title="First letter must be capitalized, letters only" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Middle Name</label>
              <input type="text" class="form-control" name="middle_name" id="edit-middle-name" pattern="^[A-Z][a-zA-Z]*$" title="First letter must be capitalized, letters only" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Last Name</label>
              <input type="text" class="form-control" name="last_name" id="edit-last-name" pattern="^[A-Z][a-zA-Z]*$" title="First letter must be capitalized, letters only" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Email</label>
              <input type="email" class="form-control" name="email" id="edit-email" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Grade Level</label>
              <input type="number" class="form-control" name="grade_level" id="edit-grade" min="1" max="12" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Age</label>
              <input type="number" class="form-control" name="age" id="edit-age" min="3" max="90" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Sex</label>
              <select class="form-control" name="sex" id="edit-sex" required>
                <option value="">Select Sex</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
                <option value="Others">Others</option>
              </select>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Address</label>
              <input type="text" class="form-control" name="address" id="edit-address" required>
            </div>
          </div>

          <div class="modal-footer px-0 pb-0">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">Save Changes</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Add Student Modal -->
  <div class="modal fade" id="addStudentModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      
      <div class="modal-header">
        <h5 class="modal-title">Add New Student</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">

        <form action="student_info.php" method="POST">     
          <div class="mb-3">
            <label class="form-label">First Name</label>
            <input type="text" class="form-control" name="first_name" 
            pattern="^[A-Z][a-zA-Z]*$" title="First letter must be capitalized, letters only" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Middle Name</label>
            <input type="text" class="form-control" name="middle_name" 
            pattern="^[A-Z][a-zA-Z]*$" title="First letter must be capitalized, letters only" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Last Name</label>
            <input type="text" class="form-control" name="last_name" 
            pattern="^[A-Z][a-zA-Z]*$" title="First letter must be capitalized, letters only" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" class="form-control" name="email" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Grade Level</label>
            <input type="number" class="form-control" name="grade_level" min="1" max="12" required>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Age</label>
            <input type="number" class="form-control" name="age" min="3" max="90" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Sex</label>
            <input type="text" class="form-control" name="sex" pattern="^[A-Z][a-zA-Z]*$" title="First letter must be capitalized Male, Female, Others only" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Address</label>
            <input type="text" class="form-control" name="address" required>
          </div>

          <div class="modal-footer px-0 pb-0">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">Save Student</button>
          </div>

        </form>
      </div>

    </div>
  </div>
</div>
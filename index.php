<?php
require_once 'config.php';

// Handle CRUD Operations
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];
    
    // CREATE
    if ($action == 'create') {
        $student_id = clean($_POST['student_id']);
        $name = clean($_POST['name']);
        $email = clean($_POST['email']);
        $phone = clean($_POST['phone']);
        $course = clean($_POST['course']);
        $gender = clean($_POST['gender']);
        $dob = clean($_POST['dob']);
        $address = clean($_POST['address']);
        
        $sql = "INSERT INTO students (student_id, name, email, phone, course, gender, dob, address) 
                VALUES ('$student_id', '$name', '$email', '$phone', '$course', '$gender', '$dob', '$address')";
        
        if ($conn->query($sql)) {
            $message = "Student added successfully!";
            $messageType = "success";
        } else {
            $message = "Error: " . $conn->error;
            $messageType = "error";
        }
    }
    
    // UPDATE
    if ($action == 'update') {
        $id = (int)$_POST['id'];
        $student_id = clean($_POST['student_id']);
        $name = clean($_POST['name']);
        $email = clean($_POST['email']);
        $phone = clean($_POST['phone']);
        $course = clean($_POST['course']);
        $gender = clean($_POST['gender']);
        $dob = clean($_POST['dob']);
        $address = clean($_POST['address']);
        $status = clean($_POST['status']);
        
        $sql = "UPDATE students SET student_id='$student_id', name='$name', email='$email', 
                phone='$phone', course='$course', gender='$gender', dob='$dob', address='$address', status='$status' 
                WHERE id=$id";
        
        if ($conn->query($sql)) {
            $message = "Student updated successfully!";
            $messageType = "success";
        } else {
            $message = "Error: " . $conn->error;
            $messageType = "error";
        }
    }
    
    // DELETE
    if ($action == 'delete') {
        $id = (int)$_POST['id'];
        $sql = "DELETE FROM students WHERE id=$id";
        
        if ($conn->query($sql)) {
            $message = "Student deleted successfully!";
            $messageType = "success";
        } else {
            $message = "Error: " . $conn->error;
            $messageType = "error";
        }
    }
    
    // DELETE ALL
    if ($action == 'delete_all') {
        $sql = "DELETE FROM students";
        if ($conn->query($sql)) {
            $conn->query("ALTER TABLE students AUTO_INCREMENT = 1");
            $message = "All students deleted successfully!";
            $messageType = "success";
        } else {
            $message = "Error: " . $conn->error;
            $messageType = "error";
        }
    }
}

// Sorting logic
$allowedColumns = ['student_id', 'name', 'email', 'phone', 'dob', 'course', 'gender', 'status', 'created_at'];
$orderBy = isset($_GET['order']) && in_array($_GET['order'], $allowedColumns) ? $_GET['order'] : 'created_at';
$orderDir = isset($_GET['dir']) && strtolower($_GET['dir']) == 'asc' ? 'ASC' : 'DESC';
$nextDir = $orderDir == 'ASC' ? 'desc' : 'asc';

// Get all students
$search = isset($_GET['search']) ? clean($_GET['search']) : '';
$sql = "SELECT * FROM students";
if ($search) {
    $sql .= " WHERE name LIKE '%$search%' OR student_id LIKE '%$search%' OR email LIKE '%$search%' OR course LIKE '%$search%'";
}
$sql .= " ORDER BY $orderBy $orderDir";
$result = $conn->query($sql);

// Get student for edit
$editStudent = null;
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $editResult = $conn->query("SELECT * FROM students WHERE id=$editId");
    $editStudent = $editResult->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Management System</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #143357ff 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1600px;
            margin: 0 auto;
        }

        /* Header */
        .header {
            background: white;
            padding: 35px;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
            margin-bottom: 30px;
            text-align: center;
            animation: slideDown 0.6s ease;
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .header h1 {
            font-size: 2.5rem;
            color: #1e293b;
            margin-bottom: 8px;
            font-weight: 700;
        }

        .header h1 i {
            color: #667eea;
        }

        .header p {
            color: #64748b;
            font-size: 1.05rem;
        }

        /* Stats Cards */
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            animation: fadeInUp 0.6s ease;
            border-left: 4px solid transparent;
        }

        .stat-card:nth-child(1) { border-left-color: #3b82f6; animation-delay: 0.1s; }
        .stat-card:nth-child(2) { border-left-color: #10b981; animation-delay: 0.2s; }
        .stat-card:nth-child(3) { border-left-color: #f59e0b; animation-delay: 0.3s; }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .stat-card h3 {
            color: #64748b;
            font-size: 0.85rem;
            margin-bottom: 10px;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .stat-card .number {
            font-size: 2.2rem;
            font-weight: 700;
            color: #1e293b;
        }

        /* Main Content */
        .main-content {
            display: grid;
            grid-template-columns: 380px 1fr;
            gap: 25px;
        }

        /* Form Card */
        .form-card {
            background: white;
            padding: 28px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            height: fit-content;
            animation: slideRight 0.6s ease;
            position: sticky;
            top: 20px;
        }

        @keyframes slideRight {
            from { opacity: 0; transform: translateX(-30px); }
            to { opacity: 1; transform: translateX(0); }
        }

        .form-card h2 {
            color: #1e293b;
            margin-bottom: 22px;
            font-size: 1.5rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-card h2 i {
            color: #667eea;
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-group label {
            display: block;
            color: #475569;
            font-weight: 600;
            margin-bottom: 6px;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .form-group label i {
            color: #94a3b8;
            font-size: 0.85rem;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px 12px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            font-family: inherit;
            background: #f8fafc;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 75px;
        }

        /* Buttons */
        .btn {
            padding: 11px 24px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.95rem;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            text-align: center;
            justify-content: center;
        }

        .btn-primary {
            background: #414571ff;
            color: white;
            width: 100%;
        }

        .btn-primary:hover {
            background: #5568d3;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.3);
        }

        .btn-success {
            background: #10b981;
            color: white;
        }

        .btn-success:hover {
            background: #059669;
        }

        .btn-danger {
            background: #ef4444;
            color: white;
        }

        .btn-danger:hover {
            background: #dc2626;
        }

        .btn-warning {
            background: #f59e0b;
            color: white;
        }

        .btn-warning:hover {
            background: #d97706;
        }

        .btn:hover {
            transform: translateY(-2px);
        }

        .btn-sm {
            padding: 7px 14px;
            font-size: 0.85rem;
        }

        /* Students List Card */
        .students-card {
            background: white;
            padding: 28px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            animation: slideLeft 0.6s ease;
        }

        @keyframes slideLeft {
            from { opacity: 0; transform: translateX(30px); }
            to { opacity: 1; transform: translateX(0); }
        }

        .students-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 22px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .students-header h2 {
            color: #1e293b;
            font-size: 1.6rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .students-header h2 i {
            color: #667eea;
        }

        /* Search Box */
        .search-box {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .search-box input[type="text"] {
            flex: 1;
            min-width: 250px;
            padding: 10px 14px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 0.95rem;
            background: #f8fafc;
        }

        .search-box input[type="text"]:focus {
            outline: none;
            border-color: #667eea;
            background: white;
        }

        .search-box button {
            padding: 10px 20px;
        }

        /* Table */
        .table-container {
            overflow-x: auto;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: #f1f5f9;
        }

        th {
            padding: 14px 12px;
            text-align: left;
            font-weight: 600;
            color: #475569;
            cursor: pointer;
            user-select: none;
            transition: background 0.2s ease;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        th:hover {
            background: #e2e8f0;
        }

        th i.sort-icon {
            margin-left: 5px;
            font-size: 0.75rem;
            opacity: 0.5;
        }

        td {
            padding: 14px 12px;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
            font-size: 0.9rem;
        }

        tbody tr {
            transition: all 0.2s ease;
        }

        tbody tr:hover {
            background: #f8fafc;
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

        .status-badge {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 600;
            display: inline-block;
        }

        .status-active {
            background: #d1fae5;
            color: #065f46;
        }

        .status-inactive {
            background: #fee2e2;
            color: #991b1b;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        /* Alert */
        .alert {
            padding: 14px 18px;
            border-radius: 8px;
            margin-bottom: 18px;
            animation: slideDown 0.5s ease;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.9rem;
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border-left: 3px solid #10b981;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border-left: 3px solid #ef4444;
        }

        /* Delete All Button */
        .delete-all-btn {
            background: #ef4444;
            color: white;
            padding: 9px 18px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
        }

        .delete-all-btn:hover {
            background: #dc2626;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(239, 68, 68, 0.3);
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .modal-content {
            background: white;
            margin: 15% auto;
            padding: 30px;
            border-radius: 15px;
            width: 90%;
            max-width: 450px;
            animation: scaleIn 0.3s ease;
            text-align: center;
        }

        @keyframes scaleIn {
            from { transform: scale(0.9); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }

        .modal-content h2 {
            color: #ef4444;
            margin-bottom: 15px;
            font-size: 1.5rem;
        }

        .modal-content p {
            color: #64748b;
            margin-bottom: 25px;
            line-height: 1.6;
        }

        .modal-buttons {
            display: flex;
            gap: 12px;
            justify-content: center;
            margin-top: 25px;
        }

        /* Responsive */
        @media (max-width: 1200px) {
            .main-content {
                grid-template-columns: 1fr;
            }
            
            .form-card {
                position: static;
            }
        }

        @media (max-width: 768px) {
            .header h1 {
                font-size: 1.8rem;
            }

            .stats {
                grid-template-columns: 1fr;
            }

            .action-buttons {
                flex-direction: column;
            }

            .table-container {
                font-size: 0.85rem;
            }
            
            th, td {
                padding: 10px 8px;
            }
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 50px 20px;
            color: #64748b;
        }

        .empty-state i {
            font-size: 3.5rem;
            color: #cbd5e1;
            display: block;
            margin-bottom: 15px;
        }

        .empty-state strong {
            display: block;
            font-size: 1.2rem;
            color: #475569;
            margin-bottom: 8px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1><i class="fas fa-graduation-cap"></i> Student Management System</h1>
            <p>Efficiently manage student records with a modern interface</p>
        </div>

        <!-- Stats -->
        <div class="stats">
            <div class="stat-card">
                <h3><i class="fas fa-users"></i> Total Students</h3>
                <div class="number"><?php echo $result->num_rows; ?></div>
            </div>
            <div class="stat-card">
                <h3><i class="fas fa-user-check"></i> Active Students</h3>
                <div class="number">
                    <?php
                    $activeCount = $conn->query("SELECT COUNT(*) as count FROM students WHERE status='Active'")->fetch_assoc();
                    echo $activeCount['count'];
                    ?>
                </div>
            </div>
            <div class="stat-card">
                <h3><i class="fas fa-book"></i> Total Courses</h3>
                <div class="number">
                    <?php
                    $courseCount = $conn->query("SELECT COUNT(DISTINCT course) as count FROM students")->fetch_assoc();
                    echo $courseCount['count'];
                    ?>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Form Card -->
            <div class="form-card">
                <h2>
                    <i class="fas fa-<?php echo $editStudent ? 'edit' : 'user-plus'; ?>"></i>
                    <?php echo $editStudent ? 'Edit Student' : 'Add New Student'; ?>
                </h2>
                
                <?php if (isset($message)): ?>
                    <div class="alert alert-<?php echo $messageType; ?>">
                        <i class="fas fa-<?php echo $messageType == 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <input type="hidden" name="action" value="<?php echo $editStudent ? 'update' : 'create'; ?>">
                    <?php if ($editStudent): ?>
                        <input type="hidden" name="id" value="<?php echo $editStudent['id']; ?>">
                    <?php endif; ?>

                    <div class="form-group">
                        <label><i class="fas fa-id-card"></i> Student ID *</label>
                        <input type="text" name="student_id" value="<?php echo $editStudent['student_id'] ?? ''; ?>" required 
                               placeholder="e.g., STD001">
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-user"></i> Full Name *</label>
                        <input type="text" name="name" value="<?php echo $editStudent['name'] ?? ''; ?>" required 
                               placeholder="Enter student name">
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-envelope"></i> Email *</label>
                        <input type="email" name="email" value="<?php echo $editStudent['email'] ?? ''; ?>" required 
                               placeholder="student@example.com">
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-phone"></i> Phone *</label>
                        <input type="tel" name="phone" value="<?php echo $editStudent['phone'] ?? ''; ?>" required 
                               placeholder="1234567890">
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-birthday-cake"></i> Date of Birth *</label>
                        <input type="date" name="dob" value="<?php echo $editStudent['dob'] ?? ''; ?>" required 
                               max="<?php echo date('Y-m-d'); ?>">
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-book-open"></i> Course *</label>
                        <input type="text" name="course" value="<?php echo $editStudent['course'] ?? ''; ?>" required 
                               placeholder="e.g., Computer Science">
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-venus-mars"></i> Gender *</label>
                        <select name="gender" required>
                            <option value="">Select Gender</option>
                            <option value="Male" <?php echo ($editStudent['gender'] ?? '') == 'Male' ? 'selected' : ''; ?>>Male</option>
                            <option value="Female" <?php echo ($editStudent['gender'] ?? '') == 'Female' ? 'selected' : ''; ?>>Female</option>
                            <option value="Other" <?php echo ($editStudent['gender'] ?? '') == 'Other' ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>

                    <?php if ($editStudent): ?>
                    <div class="form-group">
                        <label><i class="fas fa-toggle-on"></i> Status *</label>
                        <select name="status" required>
                            <option value="Active" <?php echo $editStudent['status'] == 'Active' ? 'selected' : ''; ?>>Active</option>
                            <option value="Inactive" <?php echo $editStudent['status'] == 'Inactive' ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>
                    <?php endif; ?>

                    <div class="form-group">
                        <label><i class="fas fa-map-marker-alt"></i> Address *</label>
                        <textarea name="address" required placeholder="Enter full address"><?php echo $editStudent['address'] ?? ''; ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-<?php echo $editStudent ? 'save' : 'plus'; ?>"></i>
                        <?php echo $editStudent ? 'Update Student' : 'Add Student'; ?>
                    </button>

                    <?php if ($editStudent): ?>
                        <a href="index.php" class="btn btn-warning" style="margin-top: 10px; width: 100%;">
                            <i class="fas fa-times"></i> Cancel Edit
                        </a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Students List Card -->
            <div class="students-card">
                <div class="students-header">
                    <h2><i class="fas fa-list"></i> Students List</h2>
                    <?php if ($result->num_rows > 0): ?>
                        <button class="delete-all-btn" onclick="confirmDeleteAll()">
                            <i class="fas fa-trash-alt"></i> Delete All
                        </button>
                    <?php endif; ?>
                </div>

                <form method="GET" class="search-box">
                    <input type="text" name="search" placeholder="Search by name, ID, email, or course..." value="<?php echo $search; ?>">
                    <input type="hidden" name="order" value="<?php echo $orderBy; ?>">
                    <input type="hidden" name="dir" value="<?php echo $orderDir; ?>">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Search
                    </button>
                    <?php if ($search): ?>
                        <a href="?order=<?php echo $orderBy; ?>&dir=<?php echo $orderDir; ?>" class="btn btn-warning">
                            <i class="fas fa-times"></i> Clear
                        </a>
                    <?php endif; ?>
                </form>

                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th onclick="sortTable('student_id')">
                                    ID <?php if($orderBy == 'student_id') echo $orderDir == 'ASC' ? '<i class="fas fa-sort-up sort-icon"></i>' : '<i class="fas fa-sort-down sort-icon"></i>'; else echo '<i class="fas fa-sort sort-icon"></i>'; ?>
                                </th>
                                <th onclick="sortTable('name')">
                                    Name <?php if($orderBy == 'name') echo $orderDir == 'ASC' ? '<i class="fas fa-sort-up sort-icon"></i>' : '<i class="fas fa-sort-down sort-icon"></i>'; else echo '<i class="fas fa-sort sort-icon"></i>'; ?>
                                </th>
                                <th onclick="sortTable('email')">
                                    Email <?php if($orderBy == 'email') echo $orderDir == 'ASC' ? '<i class="fas fa-sort-up sort-icon"></i>' : '<i class="fas fa-sort-down sort-icon"></i>'; else echo '<i class="fas fa-sort sort-icon"></i>'; ?>
                                </th>
                                <th onclick="sortTable('phone')">
                                    Phone <?php if($orderBy == 'phone') echo $orderDir == 'ASC' ? '<i class="fas fa-sort-up sort-icon"></i>' : '<i class="fas fa-sort-down sort-icon"></i>'; else echo '<i class="fas fa-sort sort-icon"></i>'; ?>
                                </th>
                                <th onclick="sortTable('dob')">
                                    Date of Birth <?php if($orderBy == 'dob') echo $orderDir == 'ASC' ? '<i class="fas fa-sort-up sort-icon"></i>' : '<i class="fas fa-sort-down sort-icon"></i>'; else echo '<i class="fas fa-sort sort-icon"></i>'; ?>
                                </th>
                                <th onclick="sortTable('course')">
                                    Course <?php if($orderBy == 'course') echo $orderDir == 'ASC' ? '<i class="fas fa-sort-up sort-icon"></i>' : '<i class="fas fa-sort-down sort-icon"></i>'; else echo '<i class="fas fa-sort sort-icon"></i>'; ?>
                                </th>
                                <th onclick="sortTable('gender')">
                                    Gender <?php if($orderBy == 'gender') echo $orderDir == 'ASC' ? '<i class="fas fa-sort-up sort-icon"></i>' : '<i class="fas fa-sort-down sort-icon"></i>'; else echo '<i class="fas fa-sort sort-icon"></i>'; ?>
                                </th>
                                <th onclick="sortTable('status')">
                                    Status <?php if($orderBy == 'status') echo $orderDir == 'ASC' ? '<i class="fas fa-sort-up sort-icon"></i>' : '<i class="fas fa-sort-down sort-icon"></i>'; else echo '<i class="fas fa-sort sort-icon"></i>'; ?>
                                </th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><strong><?php echo $row['student_id']; ?></strong></td>
                                        <td><?php echo $row['name']; ?></td>
                                        <td><?php echo $row['email']; ?></td>
                                        <td><?php echo $row['phone']; ?></td>
                                        <td><?php echo date('M d, Y', strtotime($row['dob'])); ?></td>
                                        <td><?php echo $row['course']; ?></td>
                                        <td><?php echo $row['gender']; ?></td>
                                        <td>
                                            <span class="status-badge status-<?php echo strtolower($row['status']); ?>">
                                                <?php echo $row['status']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="?edit=<?php echo $row['id']; ?>&order=<?php echo $orderBy; ?>&dir=<?php echo $orderDir; ?><?php echo $search ? '&search='.$search : ''; ?>" class="btn btn-success btn-sm">
                                                    <i class="fas fa-edit"></i> Edit
                                                </a>
                                                <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this student?');">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                                    <button type="submit" class="btn btn-danger btn-sm">
                                                        <i class="fas fa-trash"></i> Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9">
                                        <div class="empty-state">
                                            <i class="fas fa-inbox"></i>
                                            <strong>No students found</strong>
                                            <p><?php echo $search ? 'Try a different search term' : 'Add your first student using the form'; ?></p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete All Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <h2>
                <i class="fas fa-exclamation-triangle"></i> Confirm Delete All
            </h2>
            <p>
                Are you sure you want to delete all students? This action cannot be undone!
            </p>
            <div class="modal-buttons">
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="action" value="delete_all">
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Yes, Delete All
                    </button>
                </form>
                <button class="btn btn-warning" onclick="closeModal()">
                    <i class="fas fa-times"></i> Cancel
                </button>
            </div>
        </div>
    </div>

    <script>
        // Sort table function
        function sortTable(column) {
            const urlParams = new URLSearchParams(window.location.search);
            const currentOrder = urlParams.get('order');
            const currentDir = urlParams.get('dir') || 'desc';
            const search = urlParams.get('search') || '';
            
            let newDir = 'asc';
            if (currentOrder === column && currentDir === 'asc') {
                newDir = 'desc';
            }
            
            let url = '?order=' + column + '&dir=' + newDir;
            if (search) {
                url += '&search=' + encodeURIComponent(search);
            }
            
            window.location.href = url;
        }

        // Delete all confirmation
        function confirmDeleteAll() {
            document.getElementById('deleteModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('deleteModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('deleteModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }

        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                alert.style.animation = 'fadeOut 0.5s ease';
                setTimeout(function() {
                    alert.style.display = 'none';
                }, 500);
            });
        }, 5000);
    </script>
</body>
</html>
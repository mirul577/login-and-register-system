<?php
// Configuration
$db_host = 'localhost';
$db_username = 'root';
$db_password = '';
$db_name = 'auth_system_alt';

// Connect to the database
$conn = new mysqli($db_host, $db_username, $db_password, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to validate password strength
function validate_password($password) {
    $errors = array();
    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters long.';
    }
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'Password must contain at least one uppercase letter.';
    }
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = 'Password must contain at least one lowercase letter.';
    }
    if (!preg_match('/[^A-Za-z0-9]/', $password)) {
        $errors[] = 'Password must contain at least one special character.';
    }
    return $errors;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate username and password
    $errors = array();
    if (empty($username)) {
        $errors[] = 'Username is required.';
    }
    if (empty($password)) {
        $errors[] = 'Password is required.';
    }
    if ($password != $confirm_password) {
        $errors[] = 'Passwords do not match.';
    }
    $password_errors = validate_password($password);
    $errors = array_merge($errors, $password_errors);

    // Check if username already exists
    $query = "SELECT * FROM users WHERE username = '$username'";
    $result = $conn->query($query);
    if ($result->num_rows > 0) {
        $errors[] = 'Username already exists.';
    }

    // Display errors or create user account
    if (!empty($errors)) {
        echo "<div id='notification' style='position: fixed; top: 20px; left: 50%; transform: translateX(-50%); background-color: #f8d7da; color: #721c24; padding: 20px; border: 1px solid #f5c6cb; border-radius: 5px; z-index: 9999; text-align: left; width: 80%; max-width: 600px;'>
                <ul style='margin: 0; padding-left: 20px;'>";
        foreach ($errors as $error) {
            echo "<li>$error</li>";
        }
        echo "  </ul>
                <button onclick='dismiss()' style='margin-top: 10px; background-color: #721c24; color: white; border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer;'>OK</button>
                
              </div>";
    } else {
        // Hash password and create user account
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $query = "INSERT INTO users (username, password) VALUES ('$username', '$hashed_password')";
        if ($conn->query($query) === TRUE) {
            echo "<div id='notification' style='position: fixed; top: 20px; left: 50%; transform: translateX(-50%); background-color: #d4edda; color: #155724; padding: 20px; border: 1px solid #c3e6cb; border-radius: 5px; z-index: 9999; text-align: center; width: 80%; max-width: 600px;'>
                    User account created successfully. Redirecting...
                    <button onclick='dismiss()' style='margin-top: 10px; background-color: #155724; color: white; border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer;'>OK</button>
                    
                  </div>";
            echo "<script>setTimeout(function() { window.location.href = 'welcome_enhanced.php'; }, 3000);</script>";
            exit;
        } else {
            echo "<div id='notification' style='position: fixed; top: 20px; left: 50%; transform: translateX(-50%); background-color: #f8d7da; color: #721c24; padding: 20px; border: 1px solid #f5c6cb; border-radius: 5px; z-index: 9999; text-align: center; width: 80%; max-width: 600px;'>
                    Error creating user account.
                    <button onclick='dismiss()' style='margin-top: 10px; background-color: #721c24; color: white; border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer;'>OK</button>
                  </div>";
        }
    }
}
    

// Close database connection
$conn->close();
?>
    <script>
    function dismiss() {
        const notification = document.getElementById('notification');
        if (notification) {
            notification.style.display = 'none';
        }
    }
    </script>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
<link rel="stylesheet" href="../Assignment 2/Assignment 2/styles.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>


</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-success text-white">Sign Up</div>
                <div class="card-body">
                    <form action="" method="post">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" id="username" name="username" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" id="password" name="password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-success w-100">Sign Up</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
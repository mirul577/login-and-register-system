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

// Function to check if account is frozen
function is_account_frozen($username, $conn) {
    $query = "SELECT account_status, failed_attempts, TIMESTAMPDIFF(MINUTE, last_attempts, NOW()) as time_diff FROM users WHERE username = '$username'";
    $result = $conn->query($query);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if ($row['account_status'] == 'frozen' && $row['time_diff'] < 15) {
            return true;
        } elseif ($row['account_status'] == 'frozen' && $row['time_diff'] >= 15) {
            $query = "UPDATE users SET account_status = 'active', failed_attempts = 0, last_attempts = NULL WHERE username = '$username'";
            $conn->query($query);
            return false;
        } else {
            return false;
        }
    } else {
        return false;
    }
}

// Function to update failed login attempts
function update_failed_attempts($username, $conn) {
    $query = "SELECT failed_attempts FROM users WHERE username = '$username'";
    $result = $conn->query($query);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $failed_attempts = $row['failed_attempts'] + 1;
        $query = "UPDATE users SET failed_attempts = $failed_attempts, last_attempts = NOW() WHERE username = '$username'";
        $conn->query($query);
        if ($failed_attempts >= 5) {
            $query = "UPDATE users SET account_status = 'frozen' WHERE username = '$username'";
            $conn->query($query);
        }
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Check if account is frozen
    if (is_account_frozen($username, $conn)) {
        $query = "SELECT TIMESTAMPDIFF(MINUTE, last_attempts, NOW()) as time_diff FROM users WHERE username = '$username'";
        $result = $conn->query($query);
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $time_diff = 15 - $row['time_diff'];
            echo "<div id='notification' style='position: fixed; top: 20px; left: 50%; transform: translateX(-50%); background-color: #f8d7da; color: #721c24; padding: 20px; border: 1px solid #f5c6cb; border-radius: 5px; z-index: 9999; width: 80%; max-width: 600px; text-align: center;'>
                    Account is frozen. Please try again after $time_diff minutes.
                    <button onclick='dismiss()' style='margin-top: 10px; background-color: #721c24; color: white; border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer;'>OK</button>
                  </div>";
        }
    } else {
        // Validate username and password
        $query = "SELECT password FROM users WHERE username = '$username'";
        $result = $conn->query($query);
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if (password_verify($password, $row['password'])) {
                // Reset failed login counter
                $query = "UPDATE users SET failed_attempts = 0, last_attempts = NULL WHERE username = '$username'";
                $conn->query($query);
                // Redirect to welcome page
                echo "<div id='notification' style='position: fixed; top: 20px; left: 50%; transform: translateX(-50%); background-color: #d4edda; color: #155724; padding: 20px; border: 1px solid #c3e6cb; border-radius: 5px; z-index: 9999; width: 80%; max-width: 600px; text-align: center;'>
                        Login successful. Redirecting...
                        <button onclick='dismiss()' style='margin-top: 10px; background-color: #155724; color: white; border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer;'>OK</button>
                      </div>";
                echo "<script>setTimeout(function() { window.location.href = 'welcome_enhanced.php'; }, 3000);</script>";
                exit;
            } else {
                // Update failed login attempts
                update_failed_attempts($username, $conn);
                echo "<div id='notification' style='position: fixed; top: 20px; left: 50%; transform: translateX(-50%); background-color: #f8d7da; color: #721c24; padding: 20px; border: 1px solid #f5c6cb; border-radius: 5px; z-index: 9999; width: 80%; max-width: 600px; text-align: center;'>
                        Invalid username or password.
                        <button onclick='dismiss()' style='margin-top: 10px; background-color: #721c24; color: white; border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer;'>OK</button>
                      </div>";
            }
        } else {
            // Update failed login attempts
            update_failed_attempts($username, $conn);
            echo "<div id='notification' style='position: fixed; top: 20px; left: 50%; transform: translateX(-50%); background-color: #f8d7da; color: #721c24; padding: 20px; border: 1px solid #f5c6cb; border-radius: 5px; z-index: 9999; width: 80%; max-width: 600px; text-align: center;'>
                    Invalid username or password.
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
    <title>Login</title>
<link rel="stylesheet" href="../Assignment 2/Assignment 2/styles.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>

</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">Login</div>
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
                        <button type="submit" class="btn btn-primary w-100">Login</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
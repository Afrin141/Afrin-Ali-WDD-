<?php
// Enable error reporting for troubleshooting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Initialize variables for form validation
$first_name_err = $last_name_err = $email_err = $password_err = $confirm_password_err = $role_err = "";
$first_name = $last_name = $email = $password = $confirm_password = $role = "";
$registration_success = false;

// Process form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Validate first name
    if (empty(trim($_POST["first_name"]))) {
        $first_name_err = "Please enter your first name";
    } else {
        $first_name = trim($_POST["first_name"]);
    }

    // Validate last name
    if (empty(trim($_POST["last_name"]))) {
        $last_name_err = "Please enter your last name";
    } else {
        $last_name = trim($_POST["last_name"]);
    }
    
    // Validate email
    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter your email";
    } else {
        // Include database connection
        require_once 'db_connect.php';
        
        // Check if email already exists
        $sql = "SELECT id FROM users WHERE email = ?";
        
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $param_email);
            $param_email = trim($_POST["email"]);
            
            if ($stmt->execute()) {
                $stmt->store_result();
                
                if ($stmt->num_rows > 0) {
                    $email_err = "This email is already registered";
                } else {
                    $email = trim($_POST["email"]);
                }
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }
            
            $stmt->close();
        }
    }
    
    // Validate password
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter a password";
    } elseif (strlen(trim($_POST["password"])) < 6) {
        $password_err = "Password must have at least 6 characters";
    } else {
        $password = trim($_POST["password"]);
    }
    
    // Validate confirm password
    if (empty(trim($_POST["confirm_password"]))) {
        $confirm_password_err = "Please confirm password";
    } else {
        $confirm_password = trim($_POST["confirm_password"]);
        if ($password != $confirm_password) {
            $confirm_password_err = "Passwords did not match";
        }
    }
    
    // Validate role
    if (empty(trim($_POST["role"]))) {
        $role_err = "Please select a role";
    } else {
        $role = trim($_POST["role"]);
    }
    
    // Check input errors before inserting into database
    if (empty($first_name_err) && empty($last_name_err) && empty($email_err) && empty($password_err) && empty($confirm_password_err) && empty($role_err)) {
        
        // Include database connection
        require_once 'db_connect.php';
        
        // Prepare an insert statement
        $sql = "INSERT INTO users (first_name, last_name, email, password, role) VALUES (?, ?, ?, ?, ?)";
        
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("sssss", $param_first_name, $param_last_name, $param_email, $param_password, $param_role);
            
            // Set parameters
            $param_first_name = $first_name;
            $param_last_name = $last_name;
            $param_email = $email;
            $param_password = password_hash($password, PASSWORD_DEFAULT); // Creates a password hash
            $param_role = $role;
            
            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                // Registration successful
                $registration_success = true;
                
                // Reset form fields
                $first_name = $last_name = $email = $password = $confirm_password = $role = "";
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }
            
            // Close statement
            $stmt->close();
        }
    }
    
    // Close connection if it exists
    if (isset($conn)) {
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ALI Enterprise - Sign Up</title>
    <link rel="stylesheet" href="HOME.css">
    <link rel="stylesheet" href="signup.css">
</head>

<body>
    <!-- Header Section -->
    <header>
        <div class="logo">ALI Enterprise</div>
        
        
    </header>

    <!-- Main Content -->
    <main>
        <section class="signup-container">
            <div class="form-container">
                <h2>Create Account</h2>
                
                <?php if ($registration_success): ?>
                <div class="success-message">
                    <p>Registration successful! You can now <a href="login.php">login</a> to your account.</p>
                </div>
                <?php endif; ?>
                
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <div class="form-group <?php echo (!empty($first_name_err)) ? 'has-error' : ''; ?>">
                        <label for="first_name">First Name</label>
                        <input type="text" id="first_name" name="first_name" value="<?php echo $first_name; ?>">
                        <span class="error-text"><?php echo $first_name_err; ?></span>
                    </div>

                    <div class="form-group <?php echo (!empty($last_name_err)) ? 'has-error' : ''; ?>">
                        <label for="last_name">Last Name</label>
                        <input type="text" id="last_name" name="last_name" value="<?php echo $last_name; ?>">
                        <span class="error-text"><?php echo $last_name_err; ?></span>
                    </div>
                    
                    <div class="form-group <?php echo (!empty($email_err)) ? 'has-error' : ''; ?>">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" value="<?php echo $email; ?>">
                        <span class="error-text"><?php echo $email_err; ?></span>
                    </div>
                    
                    <div class="form-group <?php echo (!empty($password_err)) ? 'has-error' : ''; ?>">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" value="<?php echo $password; ?>">
                        <span class="error-text"><?php echo $password_err; ?></span>
                    </div>
                    
                    <div class="form-group <?php echo (!empty($confirm_password_err)) ? 'has-error' : ''; ?>">
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" value="<?php echo $confirm_password; ?>">
                        <span class="error-text"><?php echo $confirm_password_err; ?></span>
                    </div>
                    
                    <div class="form-group <?php echo (!empty($role_err)) ? 'has-error' : ''; ?>">
                        <label>Account Type</label>
                        <div class="role-selection">
                            <label class="role-option">
                                <input type="radio" name="role" value="customer" <?php if($role == "customer") echo "checked"; ?>>
                                <span class="role-label">Customer</span>
                            </label>
                            <label class="role-option">
                                <input type="radio" name="role" value="admin" <?php if($role == "admin") echo "checked"; ?>>
                                <span class="role-label">Admin</span>
                            </label>
                        </div>
                        <span class="error-text"><?php echo $role_err; ?></span>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="signup-button">Create Account</button>
                    </div>
                    
                    <div class="login-link">
                        <p>Already have an account? <a href="login.php">Login here</a></p>
                    </div>
                </form>
            </div>
        </section>
    </main>

    <!-- Footer Section -->
    <footer>
        <div class="footer-content">
            <div class="footer-section">
                <ul>
                    <h3>Contact Us</h3>
                    <address>
                        <p>101, MPCS Street Akkaraipattu 02</p>
                        <p>Hardware City, HC 15470</p>
                        <p>Email: info@alienterprise.com</p>
                        <p>Phone: (+94) 77838133</p>
                    </address>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Follow Us</h3>
                <div class="social-icons">
                    <a href="#" aria-label="Facebook"><img src="icons/facebook.svg" alt="Facebook"></a>
                    <a href="#" aria-label="Twitter"><img src="icons/twitter.svg" alt="Twitter"></a>
                    <a href="#" aria-label="Instagram"><img src="icons/instagram.svg" alt="Instagram"></a>
                </div>
            </div>
        </div>
        <div class="copyright">
            <p>&copy; 2025 ALI Enterprise. All Rights Reserved.</p>
        </div>
    </footer>

    <script src="home.js"></script>
</body>

</html>

<?php
// Initialize the session
session_start();

// Check if the user is already logged in, if yes then redirect to home page
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    header("location: home.php");
    exit;
}

// Include database connection
require_once "db_connect.php";

// Define variables and initialize with empty values
$email = $password = "";    
$email_err = $password_err = $login_err = "";

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){

    // Check if email is empty
    if(empty(trim($_POST["email"]))){
        $email_err = "Please enter your email.";
    } else{
        $email = trim($_POST["email"]);
    }
    
    // Check if password is empty
    if(empty(trim($_POST["password"]))){
        $password_err = "Please enter your password.";
    } else{
        $password = trim($_POST["password"]);
    }
    
    // Validate credentials
    if(empty($email_err) && empty($password_err)){
        // Prepare a select statement
        $sql = "SELECT id, first_name, last_name, email, password, role FROM users WHERE email = ?";
        
        if($stmt = $conn->prepare($sql)){
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("s", $param_email);
            
            // Set parameters
            $param_email = $email;
            
            // Attempt to execute the prepared statement
            if($stmt->execute()){
                // Store result
                $stmt->store_result();
                
                // Check if email exists, if yes then verify password
                if($stmt->num_rows == 1){                    
                    // Bind result variables
                    $stmt->bind_result($id, $first_name, $last_name, $email, $hashed_password, $role);
                    if($stmt->fetch()){
                        if(password_verify($password, $hashed_password)){
                            // Password is correct, start a new session
                            session_start();
                            
                            // Store data in session variables
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["first_name"] = $first_name;
                            $_SESSION["last_name"] = $last_name;
                            $_SESSION["email"] = $email;
                            $_SESSION["role"] = $role;
                            
                            // Redirect user based on role
                            if($role === 'admin'){
                                header("location: product_manager.php");
                            } else {
                                header("location: home.php");
                            }
                        } else{
                            // Password is not valid
                            $login_err = "Invalid email or password.";
                        }
                    }
                } else{
                    // Email doesn't exist
                    $login_err = "Invalid email or password.";
                }
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }

            // Close statement
            $stmt->close();
        }
    }
    
    // Close connection
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ALI Enterprise - Login</title>
    <link rel="stylesheet" href="HOME.css">
    <link rel="stylesheet" href="signup.css">
</head>

<body>
    <!-- Header Section -->
    <header>
        <div class="logo">ALI Enterprise</div>
        <nav>
            
    </header>
    <!-- Main Content -->
    <main>
        <section class="signup-container">
            <div class="form-container">
                <h2>Login to Your Account</h2>
                
                <?php 
                if(!empty($login_err)){
                    echo '<div class="error-message">' . $login_err . '</div>';
                }        
                ?>
                
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <div class="form-group <?php echo (!empty($email_err)) ? 'has-error' : ''; ?>">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" value="<?php echo $email; ?>">
                        <span class="error-text"><?php echo $email_err; ?></span>
                    </div>
                    
                    <div class="form-group <?php echo (!empty($password_err)) ? 'has-error' : ''; ?>">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password">
                        <span class="error-text"><?php echo $password_err; ?></span>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="signup-button">Login</button>
                    </div>
                    
                    <div class="login-link">
                        <p>Don't have an account? <a href="signup.php">Register here</a></p>
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
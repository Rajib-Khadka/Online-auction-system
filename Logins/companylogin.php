<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company Login</title>
    <link rel="stylesheet" href="../css/Navigation.css">
    <link rel="stylesheet" href="../css/footer.css">
    <link rel="stylesheet" href="../css/companylogin.css">

    <script>
    // JavaScript to handle active link state
    document.addEventListener('DOMContentLoaded', () => {
        const links = document.querySelectorAll('.nav-links a');
        links.forEach(link => {
            if (link.href === window.location.href) {
                link.classList.add('active');
            }
        });
    });
    </script>
</head>

<body>
    <?php
    require('../Config/dbconnect.php');
    session_start();
    $error_message = '';
    // When form submitted, check and create user session.
    if (isset($_POST['company_email'])) {
        $company_email = stripslashes($_REQUEST['company_email']);    // removes backslashes
        $company_email = mysqli_real_escape_string($conn, $company_email);
        $password = stripslashes($_REQUEST['password']);
        $password = mysqli_real_escape_string($conn, $password);
        // Check if user exists in the database
        $query = "SELECT * FROM `company` WHERE email='$company_email' AND password='" . md5($password) . "'";
        $result = mysqli_query($conn, $query);
        if ($result && mysqli_num_rows($result) > 0) {
            $company = mysqli_fetch_assoc($result);
            $_SESSION['company_name'] = $company['name'];
            $_SESSION['company_email'] = $company['email'];
            // Redirect to user dashboard page
            header("Location: ../company/dashboard.php");
        } else {
            $error_message = "Incorrect Email or Password.";
        }
    }
    ?>
    <nav class="navbar">
        <div class="navbar-container">
            <div class="logo">
                <a href="#"><img src="../logo/logo3.png" alt="Logo"></a>
            </div>
            <ul class="nav-links">
                <li><a href="../index.php">Home</a></li>
                <li><a href="../category.php">Category</a></li>
                <li><a href="../job.php">Jobs</a></li>
                <li><a href="../login.php">Login</a></li>
            </ul>
        </div>
    </nav>
    <section class="signup-section">
        <div class="signup-container">
            <h2>Login For Company</h2>
            <p>Login to continue your account<br>and post new jobs.</p>
            <form action="companylogin.php" method="POST">
                Email<input type="email" name="company_email" required>
                Password<input type="password" name="password" required>
                <?php if (!empty($error_message)) : ?>
                <div class="error-message" style="color: red; padding: 10px; margin-bottom: 15px; border-radius: 4px;">
                    <?php echo $error_message; ?></div>
                <?php endif; ?>
                <button type="submit">Log In</button>
            </form>
            <div class="login-link">
                Don't have an account?<a href="companysignup.php">Create a free account</a>
            </div>
        </div>
    </section>
    <?php include '../Assets/footer.php';?>
</body>

</html>
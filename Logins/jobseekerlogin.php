<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Login</title>
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
    // Function to validate the form
    function validateForm(event) {
        const userId = document.querySelector('input[name="user_id"]').value.trim();
        const userEmail = document.querySelector('input[name="user_email"]').value.trim();
        const password = document.querySelector('input[name="password"]').value.trim();
        let isValid = true;

        // Clear any existing error messages
        const errorElements = document.querySelectorAll('.error');
        errorElements.forEach(el => el.textContent = '');

        // Email regex pattern
        const emailPattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;

        // Validate userId
        if (!userId) {
            displayError('user_id', 'User ID is required.');
            isValid = false;
        }

        // Validate email
        if (!userEmail || !emailPattern.test(userEmail)) {
            displayError('user_email', 'Please enter a valid email address.');
            isValid = false;
        }

        // Validate password
        if (!password) {
            displayError('password', 'Password is required.');
            isValid = false;
        }

        // Prevent form submission if validation fails
        if (!isValid) {
            event.preventDefault();
        }
    }

    // Function to display error messages
    function displayError(inputName, message) {
        const inputField = document.querySelector(`input[name="${inputName}"]`);
        let errorElement = inputField.nextElementSibling;

        if (!errorElement || !errorElement.classList.contains('error')) {
            errorElement = document.createElement('div');
            errorElement.classList.add('error');
            errorElement.style.color = 'red';
            inputField.parentNode.insertBefore(errorElement, inputField.nextSibling);
        }

        errorElement.textContent = message;
    }
    </script>
</head>

<body>
    <?php
    require('../Config/dbconnect.php');
    session_start();
    $error_message = '';
    // When form submitted, check and create user session.
    if (isset($_POST['user_email'])) {
        $user_email = stripslashes($_REQUEST['user_email']);    // removes backslashes
        $user_email = mysqli_real_escape_string($conn, $user_email);
        $user_id = stripslashes($_REQUEST['user_id']);    // removes backslashes
        $user_id = mysqli_real_escape_string($conn, $user_id);
        $password = stripslashes($_REQUEST['password']);
        $password = mysqli_real_escape_string($conn, $password);
        // Check if user exists in the database
        $query = "SELECT * FROM `user` WHERE email='$user_email' AND userid='$user_id' AND password='" . md5($password) . "'";
        $result = mysqli_query($conn, $query);
        if ($result && mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);
            $_SESSION['user_id'] = $user['userid'];
            $_SESSION['user_email'] = $user['email'];
            // Redirect to user dashboard page
            header("Location: ../jobseeker/dashboard.php");
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
            <h2>Login For Jobseeker</h2>
            <p>Login to continue your account<br>and apply for a new jobs.</p>
            <form action="jobseekerlogin.php" method="POST">
                UserID<input type="text" name="user_id" required>
                Email<input type="email" name="user_email" required>
                Password<input type="password" name="password" required>
                <?php if (!empty($error_message)) : ?>
                <div class="error-message" style="color: red; padding: 10px; margin-bottom: 15px; border-radius: 4px;">
                    <?php echo $error_message; ?></div>
                <?php endif; ?>
                <button type="submit">Log In</button>
            </form>
            <div class="login-link">
                Don't have an account?<a href="jobseekersignup.php">Create a free account</a>
            </div>
        </div>
    </section>
    <?php include '../Assets/footer.php';?>
</body>

</html>
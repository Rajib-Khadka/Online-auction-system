<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company Signup</title>
    <link rel="stylesheet" href="../css/Navigation.css">
    <link rel="stylesheet" href="../css/footer.css">
    <link rel="stylesheet" href="../css/companylogin.css">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <style>
    /* General styles for signup section */
    .signup-section {
        text-align: center;
        padding: 20px;
        background-color: #f0f0f0;
        /* Light grey background */
    }

    /* Styles for success message */
    .success-message {
        background-color: #d1ecf1;
        /* Light blue background */
        color: #0c5460;
        /* Dark blue text */
        border: 1px solid #bee5eb;
        /* Border color */
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
        text-align: center;
        display: none;
        /* Initially hidden */
        position: relative;
        /* Relative positioning */
    }

    .success-message.active {
        display: block;
        /* Display when active */
    }

    .success-message p {
        margin: 0;
    }

    .success-message .close-btn {
        position: absolute;
        top: 5px;
        right: 10px;
        font-size: 18px;
        cursor: pointer;
        color: #007bff;
        /* Blue color */
    }

    .success-message .close-btn:hover {
        color: #0056b3;
        /* Darker blue on hover */
    }

    /* Styles for signup container */
    .signup-container {
        background-color: white;
        margin: 0 auto;
        /* Centering the container */
        padding: 40px;
        border: none;
        border-radius: 8px;
        text-align: left;
        max-width: 470px;
        /* Limit width */
    }

    .signup-container h2 {
        font-size: 1.5em;
        margin-bottom: 10px;
        font-family: Arial, sans-serif;
        position: relative;
        display: inline-block;
    }

    .signup-container h2::after {
        content: '';
        display: block;
        width: 30%;
        height: 2px;
        background-color: #007bff;
        margin: 10px auto 0;
    }

    .signup-container p {
        font-size: 0.9em;
        color: #666;
        margin-bottom: 20px;
    }

    .signup-container form {
        display: flex;
        flex-direction: column;
        align-items: left;
    }

    .signup-container input {
        width: calc(100% - 20px);
        /* Adjusted width for better alignment */
        padding: 10px;
        margin: 10px 0;
        border: 1px solid #ccc;
        border-radius: 4px;
    }

    .signup-container button {
        width: 100%;
        padding: 10px;
        background-color: #007bff;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    .signup-container button:hover {
        background-color: #0056b3;
    }

    .signup-container .login-link {
        margin-top: 20px;
        font-size: 0.9em;
    }

    .signup-container .login-link a {
        color: #007bff;
        text-decoration: none;
    }

    .signup-container .login-link a:hover {
        text-decoration: underline;
    }
    </style>
</head>

<body>
    <?php
    require('../config/dbconnect.php');

    // Function to sanitize input data
    function sanitize($conn, $input) {
        return mysqli_real_escape_string($conn, stripslashes($input));
    }

    // When form submitted, insert values into the database.
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $company_name = sanitize($conn, $_POST['company_name']);
        $company_email = sanitize($conn, $_POST['company_email']);
        $password = sanitize($conn, $_POST['password']);
        $create_datetime = date("Y-m-d H:i:s");

        $query = "INSERT INTO `company` (name, email, password, create_datetime)
                  VALUES ('$company_name', '$company_email', '" . md5($password) . "', '$create_datetime')";

        $result = mysqli_query($conn, $query);

        if ($result) {
            echo '<div class="success-message active">
                    <span class="close-btn" onclick="closeMessage()"><i class="fas fa-times"></i></span>
                    <p>You are registered successfully!</p>
                  </div>';
        } else {
            echo '<div class="error-message">Registration failed. Please try again later.</div>';
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
        <div class="success-message">
            <span class="close-btn" onclick="closeMessage()"><i class="fas fa-times"></i></span>
            <p>You are registered successfully!</p>
        </div>

        <div class="signup-container">
            <h2>Create a Free Account<br>For Company</h2>
            <p>Create a company account to continue<br>and post new jobs.</p>

            <form action="companysignup.php" method="POST" onsubmit="validateForm(event)">
                <label for="company_name">Company Name</label>
                <input type="text" id="company_name" name="company_name" required>
                <div id="company-name-error" class="error"></div> <!-- Error after company name -->


                <label for="company_email">Email</label>
                <input type="email" id="company_email" name="company_email" required>
                <div id="email-error" class="error"></div> <!-- Error after email -->


                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
                <div id="password-error" class="error"></div> <!-- Error after password -->


                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
                <div id="confirm-password-error" class="error"></div> <!-- Error after confirm password -->

                <button type="submit">Sign Up</button>
            </form>

            <div class="login-link">
                Already have an account? <a href="companylogin.php">Login to Company Account</a>
            </div>
        </div>
    </section>

    <?php include '../Assets/footer.php';?>

    <script>
    function closeMessage() {
        document.querySelector('.success-message').classList.remove('active');
    }

    // Function to validate form on submission
    // Function to validate form on submission
    function validateForm(event) {
        const companyName = document.querySelector('input[name="company_name"]').value.trim();
        const companyEmail = document.querySelector('input[name="company_email"]').value.trim();
        const password = document.querySelector('input[name="password"]').value;
        const confirmPassword = document.querySelector('input[name="confirm_password"]').value;

        const nameErrorElement = document.getElementById('company-name-error');
        const emailErrorElement = document.getElementById('email-error');
        const passwordErrorElement = document.getElementById('password-error');
        const confirmPasswordErrorElement = document.getElementById('confirm-password-error');

        let isValid = true;

        // Clear any existing error messages
        nameErrorElement.textContent = '';
        emailErrorElement.textContent = '';
        passwordErrorElement.textContent = '';
        confirmPasswordErrorElement.textContent = '';

        // Validate email format using regex
        const emailPattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
        if (!emailPattern.test(companyEmail)) {
            event.preventDefault();
            emailErrorElement.textContent = 'Please enter a valid email address.';
            isValid = false;
        }

        // Validate if passwords match
        if (password !== confirmPassword) {
            event.preventDefault();
            confirmPasswordErrorElement.textContent = 'Passwords do not match.';
            isValid = false;
        }

        if (!isValid) {
            event.preventDefault();
        }
    }
    </script>
</body>

</html>
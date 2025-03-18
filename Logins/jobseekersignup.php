<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Signup</title>
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

    /* Styles for success and error messages */
    .message {
        background-color: #d1ecf1;
        /* Light blue background */
        color: #0c5460;
        /* Dark blue text */
        border: 1px solid #bee5eb;
        /* Border color */
        padding: 5px;
        padding-top: 20px;
        margin-bottom: 20px;
        text-align: center;
        display: none;
        /* Initially hidden */
        position: relative;
        /* Relative positioning */
    }

    .message.active {
        display: block;
        /* Display when active */
    }

    .message .close-btn {
        position: absolute;
        top: 5px;
        right: 10px;
        font-size: 18px;
        cursor: pointer;
        color: #007bff;
        /* Blue color */
    }

    .message .close-btn:hover {
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

    $error_message = "";
    $success_message = "";

    // When form submitted, insert values into the database.
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $user_id = sanitize($conn, $_POST['user_id']);
        $user_name = sanitize($conn, $_POST['user_name']);
        $user_email = sanitize($conn, $_POST['user_email']);
        $phone_no = sanitize($conn, $_POST['phone_no']);
        $password = sanitize($conn, $_POST['password']);
        $create_datetime = date("Y-m-d H:i:s");

        // Check if user_id already exists
        $check_query = "SELECT userid FROM user WHERE userid = '$user_id'";
        $check_result = mysqli_query($conn, $check_query);

        if (mysqli_num_rows($check_result) > 0) {
            // User ID already exists
            $error_message = "User ID already exists. Please choose a different User ID.";
        } else {
            // Insert new user record
            $query = "INSERT INTO `user` (userid, name, email, Phone_no, password, create_datetime)
                      VALUES ('$user_id', '$user_name', '$user_email', '$phone_no', '" . md5($password) . "', '$create_datetime')";

            $result = mysqli_query($conn, $query);

            if ($result) {
                $success_message = "You are registered successfully!";
            } else {
                $error_message = "Registration failed. Please try again later.";
            }
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
            <?php if ($success_message): ?>
            <div class="message success-message active">
                <span class="close-btn" onclick="closeMessage('success-message')"><i class="fas fa-times"></i></span>
                <p><?php echo $success_message; ?></p>
            </div>
            <?php endif; ?>

            <?php if ($error_message): ?>
            <div class="message error-message active">
                <span class="close-btn" onclick="closeMessage('error-message')"><i class="fas fa-times"></i></span>
                <p><?php echo $error_message; ?></p>
            </div>
            <?php endif; ?>

            <h2>Create a Free Account<br>For finding a job</h2>
            <p>Create a jobseeker account to continue<br>and apply for new jobs.</p>

            <form action="jobseekersignup.php" method="POST" onsubmit="validateForm(event)">
                <label for="user_name">Full Name</label>
                <input type="text" id="user_name" name="user_name" required>

                <label for="user_id">User Id</label>
                <input type="text" id="user_id" name="user_id" required>

                <label for="user_email">Email</label>
                <input type="email" id="user_email" name="user_email" required>

                <label for="phone_no">Phone No.</label>
                <input type="number" id="phone_no" name="phone_no" required>

                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>


                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required>

                <div id="password-error" class="error"></div>

                <button type="submit">Sign Up</button>
            </form>

            <div class="login-link">
                Already have an account? <a href="jobseekerlogin.php">Login to Jobseeker Account</a>
            </div>
        </div>
    </section>

    <?php include '../Assets/footer.php';?>

    <script>
    function closeMessage(messageType) {
        const messageElement = document.querySelector(`.${messageType}.active`);
        if (messageElement) {
            messageElement.classList.remove('active');
        }
    }

    // Function to validate form on submission
    function validateForm(event) {
        // Clear any existing error messages
        const errorElements = document.querySelectorAll('.error');
        errorElements.forEach(el => el.textContent = '');

        let isValid = true;

        // Input field values
        const userName = document.querySelector('input[name="user_name"]').value.trim();
        const userId = document.querySelector('input[name="user_id"]').value.trim();
        const userEmail = document.querySelector('input[name="user_email"]').value.trim();
        const phoneNo = document.querySelector('input[name="phone_no"]').value.trim();
        const password = document.querySelector('input[name="password"]').value;
        const confirmPassword = document.querySelector('input[name="confirm_password"]').value;

        // Validation logic
        if (!userName) {
            displayError('user_name', 'Full Name is required.');
            isValid = false;
        }

        if (!userId) {
            displayError('user_id', 'User ID is required.');
            isValid = false;
        }

        const emailPattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
        if (!userEmail || !emailPattern.test(userEmail)) {
            displayError('user_email', 'Please enter a valid email address.');
            isValid = false;
        }

        if (!phoneNo || phoneNo.length < 10 || phoneNo.length > 14) {
            displayError('phone_no', 'Phone number must be between 10 and 14 digits.');
            isValid = false;
        }

        if (!password) {
            displayError('password', 'Password is required.');
            isValid = false;
        }

        if (!confirmPassword || password !== confirmPassword) {
            displayError('confirm_password', 'Passwords do not match.');
            isValid = false;
        }

        // Prevent form submission if validation fails
        if (!isValid) {
            event.preventDefault();
        }
    }

    // Function to display error messages after each input field
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
</body>

</html>
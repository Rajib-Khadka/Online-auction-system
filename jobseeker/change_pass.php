<?php
require('../Config/dbconnect.php'); // Adjust the path as per your file structure

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../Logins/jobseekerlogin.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company Dashboard</title>
    <link rel="stylesheet" href="../css/Navigation.css">
    <link rel="stylesheet" href="../css/postjob.css">
    <link rel="stylesheet" href="../css/footer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
    .update-form {
        background-color: white;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    label {
        font-weight: bold;
        margin-bottom: 5px;
        font-weight: normal;
    }


    .form-column {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .update-form input[type="password"] {
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 5px;
        font-size: 16px;
    }

    .update-form button {
        background-color: #007bff;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 5px;
        cursor: pointer;
        font-size: 16px;
        transition: background-color 0.3s ease;
    }

    .update-form button:hover {
        background-color: #0056b3;
    }

    .message,
    .error-message {
        color: white;
        font-size: 1.3em;
        font-weight: 400;
        margin-bottom: 20px;
        position: relative;
        padding: 0px;
        border-radius: 5px;
        background-color: #3498db;
        text-align: center;
    }

    .message .close-button,
    .error-message .close-button {
        position: absolute;
        top: 5px;
        right: 10px;
        cursor: pointer;
        font-size: 1.3em;
        color: white;
        background: transparent;
        border: none;
    }

    .action-container {
        display: flex;
        justify-content: flex-end;
    }

    .go-back-button {
        font-size: 1em;
        font-family: Georgia;
        font-weight: normal;
        padding: 6px 12px;
        background-color: rgb(77, 77, 236);
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 16px;
    }

    .go-back-button:hover {
        background-color: #0056b3;
    }
    </style>
</head>

<body>
    <nav class="navbar">
        <div class="navbar-container">
            <div class="logo">
                <a href="http://localhost/JobPortal/jobseeker/dashboard.php"><img
                        src="http://localhost/JobPortal/Logo/logo3.png" alt="Logo"></a>
            </div>
            <ul class="nav-links">
                <li><a href="dashboard.php">Home</a></li>
                <li><a href="category.php">Category</a></li>
                <li><a href="job.php">Jobs</a></li>
                <li>
                    <?php $user_id = $_SESSION['user_id'];
                        $query = "SELECT name FROM user WHERE userid = ?";
                        $stmt = $conn->prepare($query);
                        $stmt->bind_param('s', $user_id);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $user = $result->fetch_assoc();
                        $user_name = htmlspecialchars($user['name']);
                    ?>
                    <a href="profile.php" class="username">
                        <i class="fa fa-lock" aria-hidden="true" style="padding-left: 0px; font-size: 14px;"></i>
                        <?php echo $user_name; ?>
                    </a>
                </li>
            </ul>
        </div>
    </nav>


    <section class="banner-section">
        <div class="banner-overlay">
            <div class="banner-container">
                <div class="banner-content">
                    <h2 class="banner-heading">Post Job</h2>
                    <p class="banner-paragraph">Business plan draws on a wide range of knowledge from different business
                        <br>
                        disciplines. Business draws on a wide range of different business.
                    </p>
                    <div class="breadcrumb">
                        <a href="dashboard.php">Home</a> | <a href="change_pass.php">Update Profile</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="dashboard-section">
        <div class="dashboard-container">

            <div class="dashboard-nav">
                <h3>Manage Account</h3>
                <div class="nav-item" onclick="location.href='profile.php'"><i class="fas fa-file-alt"></i> My Resume
                </div>
                <div class="nav-item" onclick="location.href='bookmarkedjobs.php'"><i class="fas fa-heart"></i>
                    Bookmarked Jobs</div>
                <div class="nav-item" onclick="location.href='appliedjobs.php'"><i class="fas fa-briefcase"></i> Applied
                    Jobs</div>

                <div class="nav-item" onclick="location.href='message.php'"><i class="fas fa-comments"></i> Messages
                </div>
                <div class="nav-item" onclick="location.href='setting.php'"><i class="fas fa-cog"></i> Settings</div>
                <div class="nav-item" onclick="location.href='../Logins/logout.php'"><i class="fas fa-sign-out-alt"></i>
                    Sign Out</div>
            </div>
            <div class="dashboard-content">
                <?php
                    // Display success or error messages if available
                    if (isset($_SESSION['user_pass_changed'])) {
                        echo '<div class="message" style="padding: 10px;">';
                        echo $_SESSION['user_pass_changed'];
                        echo '<button class="close-button" style="margin: 0px; padding: 10px;" onclick="this.parentElement.style.display=\'none\'">&times;</button>';
                        echo '</div>';
                        unset($_SESSION['user_pass_changed']);
                    }

                    if (isset($_SESSION['user_pass_notchanged'])) {
                        echo '<div class="error-message" style="padding: 10px;">';
                        echo $_SESSION['user_pass_notchanged'];
                        echo '<button class="close-button" style="margin: 0px; padding: 10px;" onclick="this.parentElement.style.display=\'none\'">&times;</button>';
                        echo '</div>';
                        unset($_SESSION['user_pass_notchanged']);
                    }
                    ?>
                <div class="action-container">
                    <button onclick="window.history.back()" class="go-back-button">Go Back</button>
                </div>
                <form action="process.php" method="POST" class="update-form">
                    <h3>Change Password</h3>
                    <div class="form-column">
                        <label for="old_password">Old Password</label>
                        <input type="password" id="old_password" name="old_password" required>
                    </div>
                    <div class="form-column">
                        <label for="new_password">New Password</label>
                        <input type="password" id="new_password" name="new_password" required>
                    </div>
                    <div class="form-column">
                        <label for="confirm_new_password">Confirm New Password</label>
                        <input type="password" id="confirm_new_password" name="confirm_new_password" required>
                    </div>

                    <button type="submit" name="update_password">Update Password</button>
                </form>

            </div>
        </div>
    </section>
    <?php include '../Assets/footer.php';?>
</body>

</html>
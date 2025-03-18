<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Check if the user is logged in, if not redirect to login page
if (!isset($_SESSION['user_id'])) {
    header("Location: ../Logins/jobseekerlogin.php");
    exit();
}

require('../Config/dbconnect.php'); // Adjust the path as per your file structure

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Setting</title>
    <link rel="stylesheet" href="../css/Navigation.css">
    <link rel="stylesheet" href="../css/postjob.css">
    <link rel="stylesheet" href="../css/footer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f4f4f4;
        margin: 0;
        padding: 0;
    }

    .settings-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 40px 20px;
    }

    .settings-container div {
        width: 100%;
        max-width: 800px;
        background-color: #ffffff;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        margin-bottom: 20px;
        padding: 30px;
        position: relative;
    }

    .settings-container div h3 {
        margin-top: 0;
        color: #333;
        font-size: 1.5em;
    }

    .settings-container div p {
        margin: 15px 0;
        color: #555;
        line-height: 1.6;
        font-size: 0.90em;
    }

    .settings-container a {
        color: black;
        text-decoration: none;
        font-size: 1.2em;
    }

    .settings-container form {
        margin-top: 20px;
    }

    .settings-container input[type="file"] {
        margin-top: 10px;
    }

    .settings-container button {
        background-color: #007bff;
        color: white;
        border: none;
        border-radius: 5px;
        padding: 5px 8px;
        cursor: pointer;
        font-size: 15px;
        margin-top: 20px;
    }

    .settings-container button:hover {
        background-color: #0056b3;
    }

    .message,
    .error-message {
        color: black;
        font-size: 1.3em;
        font-weight: 400;
        margin-bottom: 20px;
        position: relative;
        padding: 0px;
        border-radius: 5px;
        background-color: #3498db;
        text-align: center;
    }

    .error-message {
        color: red;
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
    </style>
    <script>
    // JavaScript to handle active link state
    document.addEventListener('DOMContentLoaded', () => {
        // Show the 'Post a Job' form by default
        showForm('setting');
    });

    function showForm(formId) {
        console.log('Showing form:', formId); // Debugging line
        document.querySelectorAll('.content-section').forEach(section => {
            if (section.id === formId) {
                section.style.display = 'block'; // Show the selected section
            } else {
                section.style.display = 'none'; // Hide all other sections
            }
        });
    }
    </script>

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
                <li><a href="#">Companies</a></li>
                <li><a href="#">Jobs</a></li>
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
                        <a href="dashboard.php">Home</a> | <a href="setting.php">Setting</a>
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

                <div class="nav-item" onclick="showForm('setting')"><i class="fas fa-cog"></i> Settings</div>
                <div class="nav-item" onclick="location.href='../Logins/logout.php'"><i class="fas fa-sign-out-alt"></i>
                    Sign Out</div>
            </div>

            <div class="dashboard-content">
                <div id="setting" class="content-section">
                    <div class="settings-container">
                        <!-- Display Success or Error Messages -->
                        <?php
                            if (isset($_SESSION['pp_update_success'])) {
                                echo '<div class="message" style="padding: 10px;">';
                                echo $_SESSION['pp_update_success'];
                                echo '<button class="close-button" style="margin: 0px; padding: 10px;" onclick="this.parentElement.style.display=\'none\'">&times;</button>';
                                echo '</div>';
                                unset($_SESSION['pp_update_success']);
                            }

                            if (isset($_SESSION['pp_update_error'])) {
                                echo '<div class="error-message" style="padding: 10px;">';
                                echo $_SESSION['pp_update_error'];
                                echo '<button class="close-button" style="margin: 0px; padding: 10px;" onclick="this.parentElement.style.display=\'none\'">&times;</button>';
                                echo '</div>';
                                unset($_SESSION['pp_update_error']);
                            }
                            
                            ?>

                        <!-- User photo Section -->
                        <div id="user-photo">
                            <h3>Profile Picture</h3>
                            <p>
                                <br>Everyone knows that first impressions have a significant impact on how people
                                perceive you.<br><br>
                                A good profile picture lets your personality shine through, so that grainy,
                                low-resolution selfie is not a very accurate representation of your personality,
                                wouldn't you say? Especially if you are in the job market. Recruiters really don't want
                                to see a pic of you on holiday or doing something that has nothing to do with what you
                                do for a living.<br><br>
                                Remember that your profile picture is a representation of Your Personal Brand, whether
                                you realise it or not, you're constantly selling yourself online.
                            </p>
                            <form action="upload_pp.php" method="POST" enctype="multipart/form-data">
                                <input type="file" name="pp" accept="image/*" required> <br>
                                <button type="submit">Change Profile Picture</button>
                            </form>
                        </div>

                        <!-- user Profile Section -->
                        <div id="user-profile">
                            <a href="update_profile.php">Profile</a>
                        </div>

                        <!-- user resume Section -->
                        <div id="user-resume">
                            <a href="resume.php">Make Resume</a>
                        </div>

                        <!-- Change Password Section -->
                        <div id="change-password">
                            <a href="change_pass.php">Change Password</a>
                        </div>

                        <!-- Delete Account Section -->
                        <div id="delete-account">
                            <a href="delete_account.php">Delete Account</a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>
    <?php include '../Assets/footer.php';?>
</body>

</html>
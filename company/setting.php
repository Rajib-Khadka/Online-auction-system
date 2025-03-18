<?php
require('../Config/dbconnect.php'); // Adjust the path as per your file structure

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in, if not redirect to login page
if (!isset($_SESSION['company_name'])) {
    header("Location: ../Logins/companylogin.php");
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
                <a href="#"><img src="../logo/logo3.png" alt="Logo"></a>
            </div>
            <ul class="nav-links">
                <li><a href="dashboard.php">Home</a></li>
                <li><a href="postjob.php" class="search-button"
                        style="font-size: 1em; color: white; font-family: Georgia; font-weight: normal; padding: 6px 12px;">Post
                        a
                        Job</a>
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
                        <a href="dashboard.php">Home</a> | <a href="postjob.php">Post Job</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="dashboard-section">
        <div class="dashboard-container">

            <div class="dashboard-nav">
                <h3>Manage Account</h3>
                <div class="nav-item" onclick="location.href='postjob.php'"><i class="fas fa-plus"></i> Post a Job
                </div>
                <div class="nav-item" onclick="location.href='managejob.php'"><i class="fas fa-tasks"></i> Manage Job
                </div>
                <div class="nav-item" onclick="location.href='message.php'"><i class=" fas fa-comments"></i>
                    Messages
                </div>
                <div class="nav-item" onclick="showForm('setting')"><i class=" fas fa-cog"></i> Settings</div>
                <div class="nav-item" onclick="location.href='../Logins/logout.php'"><i class="fas fa-sign-out-alt"></i>
                    Sign Out</div>
            </div>
            <div class="dashboard-content">
                <div id="setting" class="content-section">
                    <div class="settings-container">
                        <!-- Display Success or Error Messages -->
                        <?php
                            if (isset($_SESSION['logo_update_success'])) {
                                echo '<div class="message" style="padding: 10px;">';
                                echo $_SESSION['logo_update_success'];
                                echo '<button class="close-button" style="margin: 0px; padding: 10px;" onclick="this.parentElement.style.display=\'none\'">&times;</button>';
                                echo '</div>';
                                unset($_SESSION['logo_update_success']);
                            }

                            if (isset($_SESSION['logo_update_error'])) {
                                echo '<div class="error-message" style="padding: 10px;">';
                                echo $_SESSION['logo_update_error'];
                                echo '<button class="close-button" style="margin: 0px; padding: 10px;" onclick="this.parentElement.style.display=\'none\'">&times;</button>';
                                echo '</div>';
                                unset($_SESSION['logo_update_error']);
                            }
                            ?>

                        <!-- Company Logo Section -->
                        <div id="company-logo">
                            <h3>Company Logo</h3>
                            <p>
                                <br>Your company logo is a crucial aspect of your brand identity.<br><br>
                                It represents your company's visual presence and should align with your overall branding
                                strategy. A
                                well-designed logo helps in building trust and recognition among your clients and
                                stakeholders.<br><br>
                                By updating your logo, you ensure that it stays current and reflective of your company’s
                                values and
                                goals.
                            </p>
                            <form action="upload_logo.php" method="POST" enctype="multipart/form-data">
                                <input type="file" name="logo" accept="image/*" required> <br>
                                <button type="submit">Change Company Logo</button>
                            </form>
                        </div>

                        <!-- Company Profile Section -->
                        <div id="company-profile">
                            <a href="update_profile.php">Update Profile</a>
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
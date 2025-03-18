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
// Fetch company information from the database
$company_name = $_SESSION['company_name'];
$query = "SELECT * FROM company WHERE name = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $company_name);
$stmt->execute();
$result = $stmt->get_result();
$company_info = $result->fetch_assoc();

$company_email = $company_info['email'] ?? '';
$fax_no = $company_info['fax_no'] ?? '';
$company_website = $company_info['website'] ?? '';
$company_ceo = $company_info['company_ceo'] ?? '';
$established_in = $company_info['established_in'] ?? '';
$industry = $company_info['industry'] ?? '';
$no_of_office = $company_info['no_of_office'] ?? '1';
$company_details = $company_info['company_details'] ?? '';
$aboutus = $company_info['aboutus'] ?? '';
$country = $company_info['country'] ?? '';
$state = $company_info['state'] ?? '';
$city = $company_info['city'] ?? '';
$location = $company_info['location'] ?? '';
$fb_url = $company_info['fb_url'] ?? '';
$linkedin_url = $company_info['linkedin_url'] ?? '';

$stmt->close();

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
        font-weight: normal;
    }

    .form-row {
        display: flex;
        gap: 20px;
    }

    .form-column {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .dashboard-content {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .action-container {
        display: flex;
        justify-content: flex-end;
    }

    .go-back-button {
        font-size: 1.2em;
        font-family: Georgia;
        font-weight: normal;
        padding: 8px 16px;
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
                <div class="nav-item" onclick="location.href='setting.php'"><i class=" fas fa-cog"></i> Settings</div>
                <div class="nav-item" onclick="location.href='../Logins/logout.php'"><i class="fas fa-sign-out-alt"></i>
                    Sign Out</div>
            </div>
            <div class="dashboard-content">
                <div class="message-container">
                    <?php
                    
                     // Display success or error messages if available
                     if (isset($_SESSION['company_update_success'])) {
                        echo '<div class="message" style="padding: 10px;">';
                        echo $_SESSION['company_update_success'];
                        echo '<button class="close-button" style="margin: 0px; padding: 10px;" onclick="this.parentElement.style.display=\'none\'">&times;</button>';
                        echo '</div>';
                        unset($_SESSION['company_update_success']);
                    }

                    if (isset($_SESSION['company_update_error'])) {
                        echo '<div class="error-message" style="padding: 10px;">';
                        echo $_SESSION['company_update_error'];
                        echo '<button class="close-button" style="margin: 0px; padding: 10px;" onclick="this.parentElement.style.display=\'none\'">&times;</button>';
                        echo '</div>';
                        unset($_SESSION['company_update_error']);
                    }
                    ?>
                </div>
                <div class="action-container">
                    <button onclick="window.history.back()" class="go-back-button">Go Back</button>
                </div>
                <form action="process.php" method="POST" class="update-form">
                    <h3>Basic Information</h3>
                    <div class="form-row">
                        <div class="form-column">
                            <label for="company_name">Company Name</label>
                            <input type="text" id="company_name" name="company_name"
                                value="<?php echo htmlspecialchars($company_name); ?>" required>
                        </div>
                        <div class="form-column">
                            <label for="company_email">Company Email</label>
                            <input type="email" id="company_email" name="company_email"
                                value="<?php echo htmlspecialchars($company_email); ?>" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-column">
                            <label for="fax_no">VAT No</label>
                            <input type="text" id="fax_no" name="fax_no"
                                value="<?php echo htmlspecialchars($fax_no); ?>">
                        </div>
                        <div class="form-column">
                            <label for="company_website">Company Website</label>
                            <input type="text" id="company_website" name="company_website"
                                value="<?php echo htmlspecialchars($company_website); ?>">
                        </div>
                    </div>


                    <h3>Company Profile</h3>
                    <div class="form-row">
                        <div class="form-column">
                            <label for="company_ceo">Company CEO</label>
                            <input type="text" id="company_ceo" name="company_ceo"
                                value="<?php echo htmlspecialchars($company_ceo); ?>">
                        </div>
                        <div class="form-column">
                            <label for="established_in">Established in</label>
                            <input type="text" id="established_in" name="established_in"
                                value="<?php echo htmlspecialchars($established_in); ?>">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-column">
                            <label for="industry">Industry</label>
                            <input type="text" id="industry" name="industry"
                                value="<?php echo htmlspecialchars($industry); ?>" required>
                        </div>
                        <div class="form-column">
                            <label for="no_of_office">No. of offices</label>
                            <input type="text" id="no_of_office" name="no_of_office"
                                value="<?php echo htmlspecialchars($no_of_office); ?>">
                        </div>
                    </div>


                    <label for="company_details">Company Detail</label>
                    <div class="form-row">
                        <textarea id="company_details" name="company_details" rows="5"
                            required><?php echo htmlspecialchars($company_details); ?></textarea>
                    </div>

                    <label for="aboutus">About us</label>
                    <div class="form-row">
                        <textarea id="aboutus" name="aboutus"
                            rows="4"><?php echo htmlspecialchars($aboutus); ?></textarea>
                    </div>

                    <h3>Adress Information</h3>
                    <div class="form-row">
                        <div class="form-column">
                            <label for="country">Country</label>
                            <input type="text" id="country" name="country"
                                value="<?php echo htmlspecialchars($country); ?>" required>
                        </div>
                        <div class="form-column">
                            <label for="state">State</label>
                            <input type="text" id="state" name="state" value="<?php echo htmlspecialchars($state); ?>">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-column">
                            <label for="city">City</label>
                            <input type="text" id="city" name="city" value="<?php echo htmlspecialchars($city); ?>">
                        </div>
                        <div class="form-column">
                            <label for="location">Location</label>
                            <input type="text" id="location" name="location"
                                value="<?php echo htmlspecialchars($location); ?>" required>
                        </div>
                    </div>


                    <h3>Social Links</h3>
                    <div class="form-row">
                        <div class="form-column">
                            <label for="fb_url">Facebook URL</label>
                            <input type="text" id="fb_url" name="fb_url"
                                value="<?php echo htmlspecialchars($fb_url); ?>">
                        </div>
                        <div class="form-column">
                            <label for="linkedin_url">Linkedin URL</label>
                            <input type="text" id="linkedin_url" name="linkedin_url"
                                value="<?php echo htmlspecialchars($linkedin_url); ?>">
                        </div>
                    </div>

                    <button type="submit" name="update_info">Update Information</button>
                </form>
            </div>
        </div>
    </section>
    <?php include '../Assets/footer.php';?>
</body>

</html>
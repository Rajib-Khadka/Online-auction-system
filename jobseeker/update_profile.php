<?php
require('../Config/dbconnect.php'); // Adjust the path as per your file structure

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in, if not redirect to login page
if (!isset($_SESSION['user_id'])) {
    header("Location: ../Logins/jobseekerlogin.php");
    exit();
}
// Fetch company information from the database
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM user WHERE userid = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user_info = $result->fetch_assoc();

$user_name = $user_info['name'] ?? '';
$user_email = $user_info['email'] ?? '';
$gender = $user_info['gender'] ?? '';
$maritalstatus = $user_info['marital_status'] ?? '';
$immediateavailable = $user_info['immediate_available'] ?? '';
$user_dob = $user_info['date_of_birth'] ?? '';
$skills = $user_info['skill'] ?? '';
$language = $user_info['language'] ?? '';
$nationality = $user_info['nationality'] ?? '';
$NID = $user_info['national_id_card'] ?? '';
$experience = $user_info['experience'] ?? '';
$career_level = $user_info['career_level'] ?? '';
$industry = $user_info['industry'] ?? '';
$functional_area = $user_info['functional_area'] ?? '';
$currentsalary = $user_info['current_salary'] ?? '';
$expectsalary = $user_info['expected_salary'] ?? '';
$country = $user_info['country'] ?? '';
$state = $user_info['state'] ?? '';
$city = $user_info['city'] ?? '';
$location = $user_info['address'] ?? '';
$fb_url = $user_info['facebook_url'] ?? '';
$linkedin_url = $user_info['linkedin_url'] ?? '';

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
                        <a href="dashboard.php">Home</a> | <a href="update_profile.php">Update Profile</a>
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
                <div class="message-container">
                    <?php
                    
                     // Display success or error messages if available
                     if (isset($_SESSION['user_update_success'])) {
                        echo '<div class="message" style="padding: 10px;">';
                        echo $_SESSION['user_update_success'];
                        echo '<button class="close-button" style="margin: 0px; padding: 10px;" onclick="this.parentElement.style.display=\'none\'">&times;</button>';
                        echo '</div>';
                        unset($_SESSION['user_update_success']);
                    }

                    if (isset($_SESSION['user_update_error'])) {
                        echo '<div class="error-message" style="padding: 10px;">';
                        echo $_SESSION['user_update_error'];
                        echo '<button class="close-button" style="margin: 0px; padding: 10px;" onclick="this.parentElement.style.display=\'none\'">&times;</button>';
                        echo '</div>';
                        unset($_SESSION['user_update_error']);
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
                            <label for="user_name">Full Name</label>
                            <input type="text" id="user_name" name="user_name"
                                value="<?php echo htmlspecialchars($user_name); ?>">
                        </div>

                        <div class="form-column">
                            <label for="user_email">Email</label>
                            <input type="email" id="user_email" name="user_email"
                                value="<?php echo htmlspecialchars($user_email); ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-column">
                            <label for="gender">Gender</label>
                            <div class="gender-selection">
                                <div>
                                    <input type="radio" id="male" name="gender" value="Male"
                                        <?php echo $gender == 'Male' ? 'checked' : ''; ?>>
                                    <label for="male">Male</label>
                                </div>
                                <div>
                                    <input type="radio" id="female" name="gender" value="Female"
                                        <?php echo $gender == 'Female' ? 'checked' : ''; ?>>
                                    <label for="female">Female</label>
                                </div>
                                <div>
                                    <input type="radio" id="others" name="gender" value="Others"
                                        <?php echo $gender == 'Others' ? 'checked' : ''; ?>>
                                    <label for="others">Others</label>
                                </div>
                            </div>
                        </div>
                        <div class="form-column">
                            <label for="maritalstatus">Marital Status</label>
                            <div class="marital-selection">
                                <div>
                                    <input type="radio" id="married" name="maritalstatus" value="Married"
                                        <?php echo $maritalstatus == 'Married' ? 'checked' : ''; ?>>
                                    <label for="married">Married</label>
                                </div>
                                <div>
                                    <input type="radio" id="unmarried" name="maritalstatus" value="Unmarried"
                                        <?php echo $maritalstatus == 'Unmarried' ? 'checked' : ''; ?>>
                                    <label for="unmarried">Unmarried</label>
                                </div>
                            </div>
                        </div>
                        <div class="form-column">
                            <label for="immediateavailable">Immediate Available</label>
                            <div class="immediate-selection">
                                <div>
                                    <input type="radio" id="yes" name="immediateavailable" value="Yes"
                                        <?php echo $immediateavailable == 'Yes' ? 'checked' : ''; ?>>
                                    <label for="yes">Yes</label>
                                </div>
                                <div>
                                    <input type="radio" id="no" name="immediateavailable" value="No"
                                        <?php echo $immediateavailable == 'No' ? 'checked' : ''; ?>>
                                    <label for="no">No</label>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="form-row">
                        <div class="form-column">
                            <label for="user_dob">Date of birth</label>
                            <input type="date" id="user_dob" name="user_dob"
                                value="<?php echo htmlspecialchars($user_dob); ?>">
                        </div>
                    </div>

                    <h3>Detail Information</h3>
                    <div class="form-row">
                        <div class="form-column">
                            <label for="skills">Skills*</label>
                            <input type="text" id="skills" name="skills"
                                value="<?php echo htmlspecialchars($skills); ?>"></input>
                        </div>
                        <div class="form-column">
                            <label for="language">Language</label>
                            <input type="text" id="language" name="language"
                                value="<?php echo htmlspecialchars($language); ?>"></input>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-column">
                            <label for="nationality">Nationality</label>
                            <input type="text" id="nationality" name="nationality"
                                value="<?php echo htmlspecialchars($nationality); ?>">
                        </div>
                        <div class="form-column">
                            <label for="NID">National Id Card*</label>
                            <input type="number" id="NID" name="NID" value="<?php echo htmlspecialchars($NID); ?>">
                        </div>
                    </div>

                    <h3>Industry Information</h3>
                    <div class="form-row">
                        <div class="form-column">
                            <label for="experience">Experience*</label>
                            <input type="number" id="experience" name="experience"
                                value="<?php echo htmlspecialchars($experience); ?>"></input>
                        </div>
                        <div class="form-column">
                            <label for="career_level">Career Level</label>
                            <select id="career_level" name="career_level">
                                <option value="">Select Career Level</option>
                                <option value="entry_level"
                                    <?php echo ($user_info['career_level'] == 'entry_level') ? 'selected' : ''; ?>>
                                    Entry Level
                                </option>
                                <option value="mid_level"
                                    <?php echo ($user_info['career_level'] == 'mid_level') ? 'selected' : ''; ?>>
                                    Mid Level
                                </option>
                                <option value="senior_level"
                                    <?php echo ($user_info['career_level'] == 'senior_level') ? 'selected' : ''; ?>>
                                    Senior Level
                                </option>
                                <option value="managerial"
                                    <?php echo ($user_info['career_level'] == 'managerial') ? 'selected' : ''; ?>>
                                    Managerial
                                </option>
                                <option value="executive"
                                    <?php echo ($user_info['career_level'] == 'executive') ? 'selected' : ''; ?>>
                                    Executive
                                </option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-column">
                            <label for="industry">Industry</label>
                            <select id="industry" name="industry">
                                <option value="">Select Industry</option>
                                <option value="technology" <?php echo $industry == 'technology' ? 'selected' : ''; ?>>
                                    Technology</option>
                                <option value="finance" <?php echo $industry == 'finance' ? 'selected' : ''; ?>>
                                    Finance
                                </option>
                                <option value="healthcare" <?php echo $industry == 'healthcare' ? 'selected' : ''; ?>>
                                    Healthcare</option>
                                <option value="education" <?php echo $industry == 'education' ? 'selected' : ''; ?>>
                                    Education</option>
                                <option value="manufacturing"
                                    <?php echo $industry == 'manufacturing' ? 'selected' : ''; ?>>
                                    Manufacturing</option>
                                <option value="retail" <?php echo $industry == 'retail' ? 'selected' : ''; ?>>
                                    Retail
                                </option>
                                <!-- Add more industries as needed -->
                            </select>
                        </div>
                        <div class="form-column">
                            <label for="functional_area">Functional Area</label>
                            <input type="text" id="functional_area" name="functional_area"
                                value="<?php echo htmlspecialchars($functional_area); ?>">
                        </div>

                    </div>
                    <div class="form-row">
                        <div class="form-column">
                            <label for="currentsalary">Current Salary</label>
                            <input type="number" id="currentsalary" name="currentsalary"
                                value="<?php echo htmlspecialchars($currentsalary); ?>">
                        </div>
                        <div class="form-column">
                            <label for="expectsalary">Expected Salary</label>
                            <input type="number" id="expectsalary" name="expectsalary"
                                value="<?php echo htmlspecialchars($expectsalary); ?>">
                        </div>
                    </div>

                    <h3>Adress Information</h3>
                    <div class="form-row">
                        <div class="form-column">
                            <label for="country">Country</label>
                            <input type="text" id="country" name="country"
                                value="<?php echo htmlspecialchars($country); ?>">
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
                                value="<?php echo htmlspecialchars($location); ?>">
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
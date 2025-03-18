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
$functional_area = $user_info['functional_area'] ?? '';
$country = $user_info['country'] ?? '';
$state = $user_info['state'] ?? '';
$city = $user_info['city'] ?? '';
$location = $user_info['address'] ?? '';
$linkedin_url = $user_info['linkedin_url'] ?? '';
$profile_picture = $user_info['pp'] ?? 'default.jpg'; // Default image if no profile picture is uploaded
$stmt->close();

// Fetch the user CV data
$sql = "SELECT * FROM cv WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $cvData = $result->fetch_assoc();
} else {
    $cvData = null; // No CV data found
}

// Check if CV data is null before accessing it
if ($cvData) {
    // Decode the JSON field for experience IDs (assuming it's a JSON array of IDs)
    $experienceIds = json_decode($cvData['experienceid'], true); // `true` makes it an array

    // Prepare an empty array to store experience data
    $experiences = [];

    if (!empty($experienceIds)) {
        // Create a query to fetch all experiences based on the IDs
        $experienceIdsList = implode(',', array_map('intval', $experienceIds)); // Make sure to safely handle IDs
        $experienceQuery = "SELECT * FROM experience WHERE id IN ($experienceIdsList)";
        $experienceResult = mysqli_query($conn, $experienceQuery);
        
        // Fetch all experiences
        while ($row = mysqli_fetch_assoc($experienceResult)) {
            $experiences[] = $row;
        }
    }

    // Decode the JSON field for education IDs (assuming it's a JSON array of IDs)
    $educationIds = json_decode($cvData['educationid'], true); // `true` makes it an array

    // Prepare an empty array to store education data
    $educations = [];

    if (!empty($educationIds)) {
        // Create a query to fetch all education based on the IDs
        $educationIdsList = implode(',', array_map('intval', $educationIds)); // Make sure to safely handle IDs
        $educationQuery = "SELECT * FROM education WHERE id IN ($educationIdsList)";
        $educationResult = mysqli_query($conn, $educationQuery);
        
        // Fetch all educations
        while ($row = mysqli_fetch_assoc($educationResult)) {
            $educations[] = $row;
        }
    }
} else {
    // Handle case where there is no CV data found
    $experiences = []; // No experiences
    $educations = [];  // No educations
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

    .profile-container {
        display: flex;
        align-items: flex-start;
        gap: 100px;
        margin-top: 20px;
    }

    .profile-left {
        flex: 1;
        display: flex;
        justify-content: left;
        align-items: center;
    }

    .profile-pic {
        width: 200px;
        height: 200px;
        object-fit: cover;
    }

    .profile-right {
        flex: 2;
        display: flex;
        flex-direction: column;
        justify-content: left;
    }

    .profile-name {
        font-size: 24px;
        font-weight: Normal;
        margin-bottom: 10px;
    }

    .profile-divider {
        width: 100%;
        border: 0.75px solid #ccc;
        margin-bottom: 20px;
    }

    .profile-details {
        display: flex;
        justify-content: space-between;

    }

    .profile-details p {
        margin: 5px 0;
        font-size: 16px;
        color: black;
        font-family: sans-serif;
        font-weight: normal;
        line-height: 1.4;
    }

    .full-line {
        width: 100%;
        border: 1px solid #ccc;
        margin: 20px auto;
    }

    /* Title Styling */
    .section-title {
        font-size: 24px;
        font-weight: bold;
        margin-bottom: 10px;
        text-align: left;
        margin-left: 2.5%;
    }

    /* Textarea for About Section */
    .about-textarea {
        width: 100%;
        height: 150px;
        padding: 10px;
        margin: 0 auto;
        display: block;
        font-size: 16px;
        border: 1px solid #ccc;
        border-radius: 5px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        resize: vertical;
    }

    /* Skills Section */
    .skills-section {
        display: flex;
        justify-content: space-between;
        align-items: center;
        width: 100%;
        margin: 20px auto;
    }

    .skills-title {
        font-size: 24px;
        font-weight: bold;
    }

    .add-skill-btn {
        font-size: 24px;
        cursor: pointer;
        color: #007bff;
    }

    /* Skills Container */
    .skills-container {
        width: 100%;
        margin: 10px auto;
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .skill-box {
        display: flex;
        align-items: center;
        padding: 5px 10px;
        border: 1px solid #ccc;
        border-radius: 5px;
        background-color: #f9f9f9;
    }

    .skill-box input {
        border: none;
        background: transparent;
        outline: none;
        font-size: 16px;
        width: 150px;
    }

    .skill-box .delete-skill {
        margin-left: 10px;
        cursor: pointer;
        color: red;
    }

    .section-title {
        font-size: 25px;
        font-weight: 500;
    }

    .skills-title {
        font-size: 25px;
        font-weight: 500;
    }


    /* Work Experience Section */
    .work-experience-section {
        display: flex;
        justify-content: space-between;
        align-items: center;
        width: 100%;
        margin: 20px 0;
    }

    .work-experience-title {
        font-size: 25px;
        font-weight: 500;
    }

    .add-experience-btn {
        font-size: 24px;
        cursor: pointer;
        color: #007bff;
    }

    /* Work Experience Container */
    .experience-container {
        width: 100%;
        margin: 20px 0;
    }

    /* Individual Work Experience Box */
    .experience-box {
        border: 1px solid #ccc;
        border-radius: 5px;
        padding: 15px;
        margin-bottom: 20px;
        background-color: #f9f9f9;
    }

    .experience-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }

    .experience-title {
        font-size: 18px;
        font-weight: bold;
    }

    .delete-experience {
        font-size: 18px;
        cursor: pointer;
        color: red;
    }

    /* Form Styling */
    .experience-form {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    /* Education Section */
    .education-section {
        display: flex;
        justify-content: space-between;
        align-items: center;
        width: 100%;
        margin: 20px 0;
    }

    .education-title {
        font-size: 25px;
        font-weight: 500;
    }

    .add-education-btn {
        font-size: 24px;
        cursor: pointer;
        color: #007bff;
    }

    /* Education Container */
    .education-container {
        width: 100%;
        margin: 20px 0;
    }

    /* Individual Education Box */
    .education-box {
        border: 1px solid #ccc;
        border-radius: 5px;
        padding: 15px;
        margin-bottom: 20px;
        background-color: #f9f9f9;
    }

    .education-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }

    .education-subtitle {
        font-size: 18px;
        font-weight: bold;
    }

    .delete-education {
        font-size: 18px;
        cursor: pointer;
        color: red;
    }

    /* Form Styling */
    .education-form {
        display: flex;
        flex-direction: column;
        gap: 15px;
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

    .form-column label {
        font-size: 14px;
        font-weight: bold;
        margin-bottom: 5px;
    }

    .form-column input,
    .form-column textarea {
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 5px;
        font-size: 16px;
        width: 100%;
    }

    .form-column input[type="file"] {
        padding: 5px;
    }

    .form-column textarea {
        height: 100px;
        resize: vertical;
    }

    .make-resume-button {
        font-size: 1.2em;
        padding: 10px 20px;
        background-color: rgb(77, 77, 236);
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-family: Arial, sans-serif;
    }

    .make-resume-button:hover {
        background-color: #0056b3;
    }

    .container {
        max-width: 600px;
        margin: auto;
        padding: 20px;
    }

    h2 {
        margin-top: 20px;
    }

    .add-button {
        margin: 10px 0;
        padding: 10px 15px;
        background-color: #007bff;
        color: white;
        border: none;
        cursor: pointer;
    }

    .add-button:hover {
        background-color: #0056b3;
    }

    .experience-box,
    .education-box {
        border: 1px solid #ccc;
        padding: 15px;
        margin: 10px 0;
    }

    input,
    textarea {
        width: 100%;
        margin: 5px 0;
        padding: 8px;
    }

    button[type="submit"] {
        margin-top: 20px;
        padding: 10px 15px;
        background-color: #28a745;
        color: white;
        border: none;
        cursor: pointer;
    }

    button[type="submit"]:hover {
        background-color: #218838;
    }

    .remove-btn {
        cursor: pointer;
        color: red;
        font-size: 40px;
        float: right;
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
                        <a href="dashboard.php">Home</a> | <a href="resume.php">Resume</a>
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
                     if (isset($_SESSION['resume_success'])) {
                        echo '<div class="message" style="padding: 10px;">';
                        echo $_SESSION['resume_success'];
                        echo '<button class="close-button" style="margin: 0px; padding: 10px;" onclick="this.parentElement.style.display=\'none\'">&times;</button>';
                        echo '</div>';
                        unset($_SESSION['resume_success']);
                    }

                    if (isset($_SESSION['resume_error'])) {
                        echo '<div class="error-message" style="padding: 10px;">';
                        echo $_SESSION['resume_error'];
                        echo '<button class="close-button" style="margin: 0px; padding: 10px;" onclick="this.parentElement.style.display=\'none\'">&times;</button>';
                        echo '</div>';
                        unset($_SESSION['resume_error']);
                    }
                    ?>
                </div>
                <div class="action-container">
                    <button onclick="window.history.back()" class="go-back-button">Go Back</button>
                </div>
                <div class="profile-container">
                    <div class="profile-left">
                        <!-- Placeholder for profile picture -->

                        <img src="data:image/jpeg;base64,<?php echo base64_encode($profile_picture); ?>"
                            alt="User Photo" class="profile-pic"
                            onerror="this.onerror=null; this.src='userprofile.png';">
                    </div>
                    <div class="profile-right">
                        <h2 class="profile-name"><?php echo $user_name; ?></h2>
                        <hr class="profile-divider">
                        <div class="profile-details">
                            <div class="profile-left-details" style="justify-content: left;">
                                <p>Location</p>
                                <p style="color: rgb(102, 102, 102);">
                                    <?php echo $city . ', ' . $state . ', ' . $country; ?></p><br>
                                <p>Phone</p>
                                <p style="color: rgb(102, 102, 102);">
                                    <?php echo $user_info['Phone_no'] ?? 'Not provided'; ?></p>
                            </div>
                            <div class=" profile-right-details" style="justify-content: left; margin-right:120px;">
                                <p>Email</p>
                                <p style="color: rgb(102, 102, 102);"><?php echo $user_email; ?></p><br>
                                <p>Functional Area</p>
                                <p style="color: rgb(102, 102, 102);"><?php echo $functional_area; ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                <form action="process1.php" method="POST" enctype="multipart/form-data">
                    <section class="profile-section">
                        <!-- Horizontal Line -->
                        <hr class="full-line">

                        <!-- About Section -->
                        <h3 class="section-title" style="margin: 0; margin-bottom:8px;">About</h3>
                        <textarea class="about-textarea" name="about"
                            placeholder="Write something about yourself..."><?php echo isset($cvData['about']) ? $cvData['about'] : ''; ?></textarea>

                        <!-- Horizontal Line -->
                        <hr class="full-line">

                        <!-- Skills Section -->
                        <div class="skills-section">
                            <h3 class="skills-title">Skills</h3>
                            <i class="fas fa-plus-circle add-skill-btn" id="addSkillBtn"></i>
                        </div>

                        <!-- Skills Container -->
                        <div class="skills-container" id="skillsContainer">
                            <?php
                                if (isset($cvData['skills'])) {
                                    // Decode the JSON-encoded array of skills
                                    $skills = json_decode($cvData['skills'], true);
                                    
                                    // Check if $skills is an array
                                    if (is_array($skills)) {
                                        foreach ($skills as $skill) {
                                            echo '<div class="skill-box">
                                                    <input type="text" name="skills[]" value="' . htmlspecialchars($skill) . '" />
                                                    <i class="fas fa-times delete-skill"></i>
                                                </div>';
                                        }
                                    }
                                }
                                ?>
                            <!-- Skills are dynamically added here -->
                        </div>
                        <!-- Horizontal Line -->
                        <hr class="full-line">
                    </section>

                    <form action="process1.php" method="POST" enctype="multipart/form-data">
                        <div class="skills-section">
                            <h3 class="skills-title">Experience</h3>
                            <i class="fas fa-plus-circle add-skill-btn" id="addExperienceBtn"></i>
                        </div>
                        <div id="experienceContainer">
                            <?php
                            if (!empty($experiences)) {
                                $i = 0;
                                foreach ($experiences as $experience) {
                                    // Format starting and leaving times for display
                                    $startingTime = $experience['starting_time'] !== '0000-00-00' ? date("Y-m", strtotime($experience['starting_time'])) : ''; // Use 'Y-m' for month input
                                    $leavingTime = $experience['leaving_time'] !== '0000-00-00' ? date("Y-m", strtotime($experience['leaving_time'])) : ''; // Use 'Y-m' for month input
                                    
                                    echo '
                                    <div class="experience-box">
                                        <input type="text" name="experience[' . $i . '][job_title]" value="' . htmlspecialchars($experience['job_title']) . '" placeholder="Job Title" />
                                        <input type="text" name="experience[' . $i . '][company_name]" value="' . htmlspecialchars($experience['company_name']) . '" placeholder="Company Name" />
                                        <input type="month" name="experience[' . $i . '][starting_time]" value="' . htmlspecialchars($startingTime) . '" placeholder="Starting Time" />
                                        <input type="month" name="experience[' . $i . '][leaving_time]" value="' . htmlspecialchars($leavingTime) . '" placeholder="Leaving Time" />
                                        <textarea name="experience[' . $i . '][achievement]" placeholder="Achievements">' . htmlspecialchars($experience['achievement']) . '</textarea>
                                        <input type="file" name="experience_certificate_' . $i . '" accept="image/*" />
                                        ';
                                        if (!empty($experience['image'])) { // Check if the path to the existing image is not empty
                                            echo '<div class="uploaded-image">';
                                            echo '<p>Uploaded Certificate:</p>';
                                            echo '<img src="' . htmlspecialchars($experience['image']) . '" alt="Certificate" style="max-width: 150px; max-height: 150px;" />'; // Display the image
                                            echo '</div>';
                                        }
                                    echo ' </div>
                                    ';
                                    $i++;
                                }
                            }
                            ?>
                        </div>

                        <!-- Horizontal Line -->
                        <hr class="full-line">


                        <div class="skills-section">
                            <h3 class="skills-title">Education</h3>
                            <i class="fas fa-plus-circle add-skill-btn" id="addEducationBtn"></i>
                        </div>
                        <div id="educationContainer">
                            <?php
    if (!empty($educations)) {
        $i = 0;
        foreach ($educations as $education) {
            // Format starting and leaving times for display
            $startingTime = $education['starting_time'] !== '0000-00-00' ? date("Y-m", strtotime($education['starting_time'])) : ''; // Use 'Y-m' for month input
            $leavingTime = $education['leaving_time'] !== '0000-00-00' ? date("Y-m", strtotime($education['leaving_time'])) : ''; // Use 'Y-m' for month input
            
            echo '
            <div class="education-box">
                <input type="text" name="education[' . $i . '][degree]" value="' . htmlspecialchars($education['degree']) . '" placeholder="Degree Name" />
                <input type="text" name="education[' . $i . '][university_name]" value="' . htmlspecialchars($education['university_name']) . '" placeholder="University Name" />
                <input type="month" name="education[' . $i . '][starting_time]" value="' . htmlspecialchars($startingTime) . '" placeholder="Starting Time" />
                <input type="month" name="education[' . $i . '][leaving_time]" value="' . htmlspecialchars($leavingTime) . '" placeholder="Leaving Time" />
                <textarea name="education[' . $i . '][achievement]" placeholder="Achievements">' . htmlspecialchars($education['achievement']) . '</textarea>
                <input type="file" name="education_certificate_' . $i . '" accept="image/*" />
                ';
                                        if (!empty($education['image'])) { // Check if the path to the existing image is not empty
                                            echo '<div class="uploaded-image">';
                                            echo '<p>Uploaded Certificate:</p>';
                                            echo '<img src="' . htmlspecialchars($education['image']) . '" alt="Certificate" style="max-width: 150px; max-height: 150px;" />'; // Display the image
                                            echo '</div>';
                                        }
                                    echo ' </div>
                                    ';
        
            $i++;
        }
    }
    ?>
                        </div>


                        <button type="submit">Submit CV</button>
                    </form>


            </div>
    </section>

    <?php include '../Assets/footer.php';?>
</body>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const addSkillBtn = document.getElementById('addSkillBtn');
    const skillsContainer = document.getElementById('skillsContainer');

    addSkillBtn.addEventListener('click', function() {
        const skillBox = document.createElement('div');
        skillBox.className = 'skill-box';

        const input = document.createElement('input');
        input.type = 'text';
        input.name = 'skills[]'; // Name attribute for skills
        input.placeholder = 'Enter a skill...';

        const deleteIcon = document.createElement('i');
        deleteIcon.className = 'fas fa-times delete-skill';

        skillBox.appendChild(input);
        skillBox.appendChild(deleteIcon);
        skillsContainer.appendChild(skillBox);

        deleteIcon.addEventListener('click', function() {
            skillBox.remove();
        });
    });
});
document.addEventListener('DOMContentLoaded', function() {
    let experienceCount = 0;
    let educationCount = 0;

    document.getElementById('addExperienceBtn').addEventListener('click', function() {
        experienceCount++;
        const experienceContainer = document.getElementById('experienceContainer');
        const experienceHTML = `
            <div class="experience-box">
             <span class="remove-btn" onclick="this.parentElement.remove();">&times;</span>
                <input type="text" name="experience[${experienceCount}][job_title]" placeholder="Job Title" />
                <input type="text" name="experience[${experienceCount}][company_name]" placeholder="Company Name" />
                <input type="month" name="experience[${experienceCount}][starting_time]" placeholder="Starting Time" />
                <input type="month" name="experience[${experienceCount}][leaving_time]" placeholder="Leaving Time" />
                <textarea name="experience[${experienceCount}][achievement]" placeholder="Achievements"></textarea>
                <input type="file" name="experience_certificate_${experienceCount}" accept="image/*" />
            </div>`;
        experienceContainer.insertAdjacentHTML('beforeend', experienceHTML);

    });

    document.getElementById('addEducationBtn').addEventListener('click', function() {
        educationCount++;
        const educationContainer = document.getElementById('educationContainer');
        const educationHTML = `
            <div class="education-box">
             <span class="remove-btn" onclick="this.parentElement.remove();">&times;</span>
                <input type="text" name="education[${educationCount}][degree_name]" placeholder="Degree Name" />
                <input type="text" name="education[${educationCount}][university_name]" placeholder="University Name" />
                <input type="month" name="education[${educationCount}][starting_time]" placeholder="Starting Time" />
                <input type="month" name="education[${educationCount}][leaving_time]" placeholder="Leaving Time" />
                <textarea name="education[${educationCount}][achievement]" placeholder="Achievements"></textarea>
                <input type="file" name="education_certificate_${educationCount}" accept="image/*" />
            </div>`;
        educationContainer.insertAdjacentHTML('beforeend', educationHTML);
    });

});
</script>

</html>
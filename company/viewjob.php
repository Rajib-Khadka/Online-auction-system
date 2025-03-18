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
// Check if job ID is set in the URL
if (!isset($_GET['id'])) {
    die("Job ID is not provided.");
}

// Get the job ID from the URL
$job_id = $_GET['id'];

// Escape the job ID to prevent SQL injection
$job_id = $conn->real_escape_string($job_id);

// Fetch job details from the database
$sql_job = "SELECT * FROM jobs WHERE id = '$job_id'";
$result_job = $conn->query($sql_job);

// Check if query succeeded
if (!$result_job) {
    die("Query failed: " . $conn->error);
}

if ($result_job->num_rows > 0) {
    $job = $result_job->fetch_assoc();
} else {
    die("Job not found.");
}

// Fetch company details from the database
$company_name = $job['company_name']; // Assuming company_name is stored in the jobs table
$sql_company = "SELECT companyid, logo, location, state, country FROM company WHERE name = '$company_name'";
$result_company = $conn->query($sql_company);

// Check if query succeeded
if (!$result_company) {
    die("Query failed: " . $conn->error);
}

if ($result_company->num_rows > 0) {
    $company = $result_company->fetch_assoc();
} else {
    die("Company not found.");
}

// Get the job ID from the URL
$jobId = $_GET['id'];

// Fetch user details for the particular job
$sql = "
    SELECT u.userid, u.name, u.pp, cv.about, ja.status
    FROM user u
    JOIN appliedjob ja ON u.userid = ja.user_id
    JOIN jobs j ON ja.job_id = j.id
    LEFT JOIN cv ON u.userid = cv.user_id
    WHERE j.id = ?
";

// Prepare and execute the statement
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $jobId); // Assuming job ID is an integer
$stmt->execute();
$result = $stmt->get_result();

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
    .btn-message {
        display: inline-flex;
        align-items: center;
        padding: 5px;
        color: #007bff;
        border: none;
        border-radius: 5px;
        text-decoration: none;
        transition: background-color 0.3s;
        margin-left: 5px;
    }

    .btn-message:hover {
        background-color: rgba(11, 49, 213, 0.3);
        /* Darker shade on hover */
    }

    .btn-message i {
        margin-right: 5px;
        /* Space between icon and text */
    }

    .header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background-color: white;
        border-bottom: 1px solid #ccc;
        border: none;
        padding: 0 20px;
    }

    .company-info {
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .company-logo1 img {
        height: 50px;
        width: auto;
        border-radius: 5px;
    }

    .company-details {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .company-details p {
        margin: 0;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .company-details i {
        color: #007bff;
        /* Adjust icon color if needed */
    }

    .job-details {
        text-align: right;
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .job-details p {
        padding: 3px;
        font-family: cursive;
    }

    .morning-shift {
        color: blue;
        padding: 3px;
    }


    hr {
        width: 95%;
        margin: 5px auto;
        border: 0.3px solid #ccc;
    }


    .action-container {
        display: flex;
        justify-content: flex-end;
        padding-right: 20px;
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

    .content {
        display: flex;
        gap: 25px;
        padding: 20px;
        line-height: 1.7;
    }

    .left-side {
        flex: 1.5;
        padding: 10px;
    }

    .right-side {
        flex: 1;
    }

    .left-side h3,
    .right-side h3 {
        font-size: 1.2em;
        margin-bottom: 10px;
    }

    .left-side p,
    .right-side p {
        color: black;
        margin-bottom: 5px;
    }

    .left-side p strong,
    .right-side p strong {
        color: black;
    }

    .job-description ul {
        list-style-type: circle;
        margin: 0;
        padding-left: 20px;
    }

    .dashboard-section {
        background-color: #f0f0f0;
        /* Light grey background */
        padding: 40px 0;
        padding-top: 30px;
    }

    /* Dashboard container styling */
    .dashboard-container {
        max-width: 1200px;
        margin: 0 auto;
        /* Centers the container horizontally */
        /* Ensure padding aligns with content */
    }

    /* Other existing styles */
    .job-listings {
        display: flex;
        flex-direction: column;
        /* Stack job listings vertically */
        justify-content: flex-start;
        gap: 20px;
        /* Space between job listings */
    }

    .job {
        display: flex;
        width: 100%;
        /* Full width for each job listing */
        padding: 20px;
        margin-bottom: 25px;
        border: 1px solid #ccc;
        border-radius: 5px;
        cursor: pointer;
        background-color: #f9f9f9;
        min-height: 160px;
    }

    .company-logo {
        width: 70px;
        /* Increase logo size */
        height: auto;
        /* Increase logo size */
        margin-right: 25px;
    }

    .job-details1 {
        flex-grow: 1;
        margin-top: 15px;
    }

    .job-title {
        font-size: 20px;
        /* Slightly increase font size for better readability */
        font-weight: bold;
        margin-bottom: 16px;
        text-align: left;
    }


    .divider {
        border: none;
        height: 1px;
        background-color: #ccc;
        margin: 10px 0;
    }

    .job-description {
        color: rgba(11, 49, 213, 0.8);
        margin: 10px 0;
        text-align: left;
        margin-top: 0px;
    }

    .job-info {
        display: flex;
        justify-content: space-between;
        gap: 1px;
        /* Adds 5px space between the info boxes */
        margin-top: 15px;
        /* Ensure horizontal alignment */
    }

    .info-box {
        background-color: rgba(11, 49, 213, 0.1);
        /* Light blue with transparency */
        width: 30%;
        font-size: 14px;
        color: #333;
        padding: 4px;
        color: rgba(11, 49, 213, 0.6);
    }

    .button-group {
        display: flex;
        gap: 10px;
        /* Space between buttons */
    }

    .action-button {
        padding: 5px;
        background-color: rgba(11, 49, 213, 0.7);
        /* Bootstrap primary color */
        color: white;
        text-decoration: none;
        border-radius: 5px;
        border: none;
        transition: background-color 0.3s;
    }

    .action-button:hover {
        background-color: #0056b3;
        /* Darker shade on hover */
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
                <div class="action-container">
                    <button onclick="window.history.back()" class="go-back-button">Go Back</button>
                </div>
                <div class="header">
                    <div class="company-info">
                        <div class="company-logo1">
                            <img src="<?php echo htmlspecialchars($company['logo']); ?>" alt="Company Logo">
                        </div>
                        <div class="company-details">
                            <h2><?php echo htmlspecialchars($company_name); ?></h2>
                            <p style="color: rgb(172, 171, 171);"><i
                                    class="fas fa-map-marker-alt"></i><?php echo htmlspecialchars($company['location']) . ', ' . htmlspecialchars($company['state']) . ', ' . htmlspecialchars($company['country']); ?>
                            </p>
                        </div>
                    </div>
                    <div class="job-details">
                        <p>
                            Rs.
                            <?php echo htmlspecialchars($job['salary_from']) . ' - ' . htmlspecialchars($job['salary_to']); ?>
                        </p>
                        <p class="morning-shift"><?php echo htmlspecialchars($job['shift']); ?>
                        </p>
                    </div>
                </div>
                <hr>
                <div class="content">
                    <div class="left-side">
                        <p><strong style="font-weight:500;">Job Type:</strong>
                            <?php echo htmlspecialchars($job['type']); ?></p>
                        <p><strong style="font-weight:500;">Required Skills:</strong>
                            <?php echo htmlspecialchars($job['skills']); ?>
                        </p>
                        <p><strong style="font-weight:500;">Recruiter Name:</strong>
                            <?php echo htmlspecialchars($job['Rname']); ?></p>
                        <p><strong style="font-weight:500;">Recruiter Email:</strong>
                            <?php echo htmlspecialchars($job['Remail']); ?>
                        </p>
                        <h3 style="font-weight:500;">Job Description</h3>
                        <div class="job-description">
                            <ul>
                                <?php
                        $description_points = explode("\n", htmlspecialchars($job['job_desc']));
                        foreach ($description_points as $point) {
                            echo "<li>{$point}</li>";
                        }
                        ?>
                            </ul>
                        </div>
                    </div>
                    <div class="right-side">
                        <h3>Job Overview</h3>
                        <p><strong style="font-weight:500;">Published On:</strong>
                            <?php echo htmlspecialchars($job['posted_date']); ?>
                        </p>
                        <p><strong style="font-weight:500;">Employee Shift:</strong>
                            <?php echo htmlspecialchars($job['shift']); ?></p>
                        <p><strong style="font-weight:500;">Experience:</strong>
                            <?php echo htmlspecialchars($job['min_experience']) . ' - ' . htmlspecialchars($job['max_experience']); ?>
                        <p><strong style="font-weight:500;">Salary:</strong> Rs.
                            <?php echo htmlspecialchars($job['salary_from']) . ' - ' . htmlspecialchars($job['salary_to']); ?>
                        </p>
                        <p><strong style="font-weight:500;">Gender:</strong>
                            <?php echo htmlspecialchars($job['gender']); ?></p>
                        <p><strong style="font-weight:500;">Application Deadline:</strong>
                            <?php echo htmlspecialchars($job['expiry_date']); ?></p>
                    </div>
                </div>
                <section class="dashboard-section">
                    <div class="dashboard-container">
                        <div class="job-listings">
                            <?php 
                            if (isset($_SESSION['Application_accept'])) {
                                echo '<div class="success-message" style="padding: 0px;">';
                                echo $_SESSION['Application_accept'];
                                echo '<button class="close-button" style="margin: 0px; padding: 10px;" onclick="this.parentElement.style.display=\'none\'">&times;</button>';
                                echo '</div>';
                                // Unset the session variable after displaying
                                unset($_SESSION['Application_accept']);
                                }
                                
                                if (isset($_SESSION['Application_reject'])) {
                                    echo '<div class="error-message" style="padding: 0px;">';
                                    echo $_SESSION['Application_reject'];
                                    echo '<button class="close-button" style="margin: 0px; padding: 10px;" onclick="this.parentElement.style.display=\'none\'">&times;</button>';
                                    echo '</div>';
                                    unset($_SESSION['Application_reject']);
                                }
                            ?>
                            <h3>Applicants</h3>

                            <?php
                            if ($result->num_rows > 0) {
                                // Loop through each job listing
                                while($row = $result->fetch_assoc()) {

                                    echo '<div class="job">';
                                    echo '<div class="company-logo">';
                                    if (!empty($row['pp'])) {
                                        // Display the logo as a base64 image
                                        echo '<img src="data:image/jpeg;base64,' . base64_encode($row['pp']) . '" alt="Company Logo" class="company-logo">';
                                    } else {
                                        // Display a default logo if none exists
                                        echo '<img src="../uploads/userphoto/default.jpg" alt="Default Logo" class="company-logo">';
                                    }                                    echo '</div>';
                                    echo '<div class="job-details1">';
                                    echo '<span class="job-title">' . $row['name'] . '</span>';
                                    echo '<a class="btn-message" href="message.php?user_id=' . urlencode($row['userid']) . '">
                                    <i class="fas fa-comments"></i>
                                  </a>';
                            
                                    echo '<hr class="divider">'; // This will be below the job title
                                    echo '<p class="job-description">' . substr($row['about'], 0, 100) . '...</p>';
                                    // Assuming you have the necessary variables and data available
                                    echo '<div class="button-group">';
                                        echo '<a href="view_resume.php?id=' . $row['userid'] . '"
                                            class="action-button">Resume</a>';

                                $status = $row['status'];
                                if ($status === 'accepted') {
                                echo '<button class="action-button" disabled>Accepted</button>';
                                } elseif ($status === 'rejected') {
                                echo '<button class="action-button" disabled>Rejected</button>';
                                } else {
                                echo '<a href="accept_job.php?userid=' . $row['userid'] . '&job_id=' . $job_id . '" class="action-button">Accept</a>';
                                echo '<a href="reject_job.php?userid=' . $row['userid'] . '&job_id=' . $job_id . '" class="action-button">Reject</a>';

                                }

                                echo '</div>';
                            echo '
                        </div>';
                        echo '
                    </div>';
                    }
                    } else {
                    echo '<p>No job listings available at the moment.</p>';
                    }
                    ?>

                        </div>
                    </div>
                </section>
            </div>

        </div>
    </section>
    <?php include '../Assets/footer.php';?>
</body>

</html>
<?php
session_start();

include '../Config/dbconnect.php';

// Check if the user is logged in, if not redirect to login page
if (!isset($_SESSION['user_id'])) {
    header("Location: ../Logins/jobseekerlogin.php");
    exit();
}

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['apply'])) {
    // Check if the user is logged in by checking session
    if (!isset($_SESSION['user_id'])) {
        // If the user is not logged in, redirect to login page
        header("Location: http://localhost/JobPortal/Logins/jobseekerlogin.php");
        exit();
    } else {
        // If the user is logged in, proceed to apply for the job
        $user_id = $_SESSION['user_id'];
        $job_id = $_POST['job_id']; // This should be passed in the form or retrieved from the job details page

        // First, check if the user has already applied for this job
        $check_sql = "SELECT * FROM appliedjob WHERE user_id = ? AND job_id = ?";
        $check_stmt = $conn->prepare($check_sql);
        if ($check_stmt) {
            $check_stmt->bind_param('si', $user_id, $job_id); // 'si' means string, integer
            $check_stmt->execute();
            $check_stmt->store_result();

            if ($check_stmt->num_rows > 0) {
                // If a record is found, the user has already applied for this job
                $_SESSION['applied_error'] = "You have already applied for this job.";
            } else {
                // If no record is found, proceed to apply for the job
                $apply_sql = "INSERT INTO appliedjob (user_id, job_id) VALUES (?, ?)";
                $apply_stmt = $conn->prepare($apply_sql);
                if ($apply_stmt) {
                    $apply_stmt->bind_param('si', $user_id, $job_id); // 'si' means string, integer
                    $apply_stmt->execute();

                    if ($apply_stmt->affected_rows > 0) {
                        $_SESSION['applied_success'] = "You have successfully applied for the job.";
                    } else {
                        $_SESSION['applied_error'] = "Could not apply for the job.";
                    }

                    $apply_stmt->close();
                } else {
                    echo "Error: Could not prepare the SQL statement for applying.";
                }
            }

            $check_stmt->close();
        } else {
            echo "Error: Could not prepare the SQL statement for checking.";
        }
    }
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
// Check if the job's expiry date has passed
$current_date = date('Y-m-d');
$expiry_date = $job['expiry_date'];

if ($current_date > $expiry_date) {
    $job_expired = true; // Flag to prevent the application form from showing
} else {
    $job_expired = false; // Allow application
}

// Fetch company details from the database
$company_name = $job['company_name']; // Assuming company_name is stored in the jobs table
$sql_company = "SELECT companyid, logo, location, state, country, company_details FROM company WHERE name = '$company_name'";
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
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Listings</title>
    <link rel="stylesheet" href="../css/index.css">
    <link rel="stylesheet" href="../css/Navigation.css">
    <link rel="stylesheet" href="../css/footer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
    .banner-section {
        position: relative;
        background-image: url('http://localhost/JobPortal/Banners/banner3.png');
        /* Replace with your banner image path */
        background-size: cover;
        background-position: center;
        height: 300px;
        /* Adjust height as needed */
        display: flex;
        align-items: center;
    }

    .banner-container {
        max-width: 1200px;
        /* Adjust to your desired max-width */
        margin: 0 auto;
        padding: 0 15px;
        /* This should match your .navbar-container padding/margin */
        width: 100%;
    }

    .banner-overlay {
        background-color: rgba(11, 49, 213, 0.8);
        /* Light blue color with transparency */
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        color: white;
        padding: 20px;
        text-align: left;
        /* Align text to the left */
    }

    .banner-content {
        width: 100%;
    }

    .banner-heading {
        font-size: 1.8em;
        margin-bottom: 20px;
        position: relative;
        /* Ensure positioning context for the after element */
    }

    .banner-heading:after {
        content: '';
        display: block;
        width: 6%;
        /* You can adjust this width as needed */
        height: 2.2px;
        background-color: white;
        margin-top: 15px;
        position: absolute;
        /* Use absolute positioning */
        left: 0;
        /* Align to the left */
    }

    .banner-paragraph {
        font-size: 1em;
        line-height: 1.5;
        max-width: 800px;
        margin: 0;
    }

    .breadcrumb {
        margin-top: 20px;
    }

    .breadcrumb a {
        color: white;
        /* Or any color that fits your design */
        text-decoration: none;
        font-size: 1em;
        align-items: left;

    }

    .breadcrumb a:hover {
        text-decoration: underline;
    }

    /* Navbar styling */
    .navbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        /* Align items vertically in the center */
        background-color: white;
        /* Navbar background */
        padding: 10px 20px;
        /* Add padding for spacing */
        border-bottom: 1px solid #ccc;
    }

    .navbar-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        /* Align items vertically in the center */
        width: 100%;
        /* Ensure it takes the full width */
    }

    /* Dashboard section styling */
    .dashboard-section {
        /* Light grey background */
        padding: 40px 20px;
        /* Adjust padding for consistency */
        /* Add some space above the section */
    }

    /* Content alignment */
    .dashboard-content {
        background-color: #f0f0f0;

        max-width: 1200px;
        /* Centered max-width */
        margin: 0 auto;
        /* Center the content horizontally */
        padding: 20px;
        /* Ensure padding aligns with content */
    }


    .header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid #ccc;
        border: none;
        padding: 0 20px;
    }

    .company-info {
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .company-logo img {
        height: 80px;
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
        color: white;
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
                <a href="http://localhost/JobPortal/index.php"><img src="http://localhost/JobPortal/Logo/logo3.png"
                        alt="Logo"></a>
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
                    <h2 class="banner-heading">Job List</h2>
                    <p class="banner-paragraph">Find the best opportunities and grow your career with us.</p>
                    <div class="breadcrumb">
                        <a href="dashboard.php">Home</a> | <a href="job.php">Job List</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="dashboard-section">
        <div class="dashboard-content">
            <div class="message-container">
                <?php
                    
                     // Display success or error messages if available
                     if (isset($_SESSION['applied_success'])) {
                        echo '<div class="message" style="padding: 10px;">';
                        echo $_SESSION['applied_success'];
                        echo '<button class="close-button" style="margin: 0px; padding: 10px;" onclick="this.parentElement.style.display=\'none\'">&times;</button>';
                        echo '</div>';
                        unset($_SESSION['applied_success']);
                    }

                    if (isset($_SESSION['applied_error'])) {
                        echo '<div class="error-message" style="padding: 10px;">';
                        echo $_SESSION['applied_error'];
                        echo '<button class="close-button" style="margin: 0px; padding: 10px;" onclick="this.parentElement.style.display=\'none\'">&times;</button>';
                        echo '</div>';
                        unset($_SESSION['applied_error']);
                    }
                    ?>
            </div>
            <div class="action-container">
                <button onclick="window.history.back()" class="go-back-button">Go Back</button>
            </div>
            <div class="header">
                <div class="company-info">
                    <div class="company-logo">
                        <img src="../uploads/<?php echo htmlspecialchars($company['logo']); ?>" alt="Company Logo">
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
                    <p class="morning-shift">
                        <a href="message.php?company_id=<?php echo urlencode($company['companyid']); ?>">
                            Send a message to the company
                        </a>
                    </p>
                    </p>
                </div>
            </div>
            <hr>
            <div class="content">

                <div class="left-side">
                    <p><strong style="font-weight:500;">Job Position:</strong>
                        <?php echo htmlspecialchars(string: $job['title']); ?></p>
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
                    <br>
                    <h3 style="font-weight:500;">Company Description</h3>
                    <p><?php echo htmlspecialchars($company['company_details']); ?></p><br>

                    <h3 style="font-weight:500;">Role Description</h3>
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
                    </p>
                    <p><strong style="font-weight:500;">Salary:</strong> Rs.
                        <?php echo htmlspecialchars($job['salary_from']) . ' - ' . htmlspecialchars($job['salary_to']); ?>
                    </p>
                    <p><strong style="font-weight:500;">Gender:</strong>
                        <?php echo htmlspecialchars($job['gender']); ?></p>
                    <p><strong style="font-weight:500;">Application Deadline:</strong>
                        <?php echo htmlspecialchars($job['expiry_date']); ?></p>
                </div>
            </div>
            <hr>


            <?php if ($job_expired): ?>
            <div class="error-message">
                <p>The deadline for this job has passed. You cannot apply for this job.</p>
            </div>
            <?php else: ?>
            <!-- Application Form -->
            <div class="button-container"
                style="text-align: center; margin-top: 20px; display: flex; justify-content: center; gap: 10px;">
                <form action="" method="POST">
                    <!-- Passing the job_id in a hidden field -->
                    <input type="hidden" name="job_id" value="<?php echo htmlspecialchars($job['id']); ?>">
                    <button type="submit" name="apply" class="apply-button"
                        style="padding: 10px 20px; background-color: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer;">Apply
                        Now</button>
                </form>
                <!-- Save Job Button -->
                <form action="save_job.php" method="POST">
                    <input type="hidden" name="job_id" value="<?php echo htmlspecialchars($job['id']); ?>">
                    <button type="submit" name="save_job" class="save-button"
                        style="padding: 10px 20px; background-color: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer;">Save
                        Job</button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </section>
    <?php include '../Assets/footer.php'; ?>

</body>

</html>

<?php
// Close the database connection
$conn->close();
?>
<?php
session_start();

// Check if the user is logged in, if not redirect to login page
if (!isset($_SESSION['user_id'])) {
    header("Location: ../Logins/jobseekerlogin.php");
    exit();
}

require('../Config/dbconnect.php'); // Adjust the path as per your file structure

$user_id = $_SESSION['user_id'];

// Fetch applied job IDs and status for the logged-in user
$sql = "SELECT job_id, status FROM appliedjob WHERE user_id = ? ORDER BY applied_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $user_id);
$stmt->execute();
$result = $stmt->get_result();

$applied_jobs = [];
while ($row = $result->fetch_assoc()) {
    $applied_jobs[] = [
        'job_id' => $row['job_id'],
        'status' => $row['status']
    ];
}

// If there are no applied jobs, show a message
if (empty($applied_jobs)) {
    $applied_job_listings_data = [];
} else {
    // Create a string of job IDs for the IN clause
    $applied_job_ids_string = implode(',', array_map(function($job) {
        return intval($job['job_id']);
    }, $applied_jobs));

    // Fetch job details
    $sql_jobs = "SELECT id, title, job_desc, salary_from, salary_to, type, company_name FROM jobs WHERE id IN ($applied_job_ids_string)";
    $stmt_jobs = $conn->prepare($sql_jobs);
    $stmt_jobs->execute();
    $applied_job_listings = $stmt_jobs->get_result();

    // Fetch company details
    $applied_job_listings_data = []; // Initialize an array to store job listings

    while ($job = $applied_job_listings->fetch_assoc()) {
        $company_name = $job['company_name'];

        if (!isset($companies[$company_name])) {
            $sql_company = "SELECT name, logo, goggle_url, location, country FROM company WHERE name = ?";
            $stmt_company = $conn->prepare($sql_company);
            $stmt_company->bind_param('s', $company_name);
            $stmt_company->execute();
            $company_result = $stmt_company->get_result();
            $companies[$company_name] = $company_result->fetch_assoc();
        }

        // Add company details to the job
        $job['company_details'] = $companies[$company_name];

        // Add the status from the applied jobs array
        foreach ($applied_jobs as $applied_job) {
            if ($applied_job['job_id'] == $job['id']) {
                $job['status'] = $applied_job['status'];
                break;
            }
        }

        // Store the job with company details in a new array
        $applied_job_listings_data[] = $job;
    }
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
    .banner-section {
        position: relative;
        background-image: url('http://localhost/JobPortal/Banners/banner4.png');
        background-size: cover;
        background-position: center;
        height: 300px;
        display: flex;
        align-items: center;
    }

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
        height: auto;
        margin-right: 25px;
    }

    .job-title {
        font-size: 20px;
        font-weight: bold;
        margin-bottom: 16px;
    }

    .job-type {
        background-color: blue;
        color: white;
        border: none;
        padding: 7px 12px;
        border-radius: 5px;
        margin-left: 15px;
    }

    .divider {
        border: none;
        height: 1px;
        background-color: #ccc;
        margin: 10px 0;
    }

    .job-info {
        display: flex;
        justify-content: space-between;
        gap: 1px;
        margin-top: 15px;
    }

    .info-box {
        background-color: rgba(11, 49, 213, 0.1);
        width: 30%;
        font-size: 14px;
        color: #333;
        padding: 4px;
        color: rgba(11, 49, 213, 0.8);
    }
    </style>
    <script>
    // JavaScript to handle active link state
    document.addEventListener('DOMContentLoaded', () => {
        const links = document.querySelectorAll('.nav-links a');
        links.forEach(link => {
            if (link.href === window.location.href) {
                link.classList.add('active');
            }
        });
    });

    function showForm(formId) {
        document.querySelectorAll('.content-section').forEach(section => {
            section.style.display = section.id === formId ? 'block' : 'none';
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
                <li><a href="category.php">Category</a></li>
                <li><a href="job.php">Jobs</a></li>
                <li>
                    <?php 
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
                    <h2 class="banner-heading">Bookmarked Jobs</h2>
                    <p class="banner-paragraph">View your saved job listings.</p>
                    <div class="breadcrumb">
                        <a href="dashboard.php">Home</a> | <a href="appliedjobs.php">Applied Jobs</a>
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
                    Bookmarked
                    Jobs
                </div>
                <div class="nav-item" onclick="showForm('appliedjobs.php')"><i class="fas fa-briefcase"></i> Applied
                    Jobs</div>

                <div class="nav-item" onclick="location.href='message.php'"><i class="fas fa-comments"></i> Messages
                </div>
                <div class="nav-item" onclick="location.href='setting.php'"><i class="fas fa-cog"></i> Settings</div>
                <div class="nav-item" onclick="location.href='../Logins/logout.php'"><i class="fas fa-sign-out-alt"></i>
                    Sign Out</div>
            </div>
            <section class="dashboard-content">
                <h3 style="margin-bottom: 30px; font-size: 25px;">Applied Jobs</h3>
                <div class="job-listings">
                    <?php
                    // Check if there are any applied jobs
    if (empty($applied_job_listings_data)) {
        // If no jobs are found, display a message
        echo '<div class="no-jobs">';
        echo '<p>No applied jobs available at the moment. Please browse job listings and apply to available jobs.</p>';
        echo '</div>';
    } else {

        foreach ($applied_job_listings_data as $job) {
            echo '<div class="job" onclick="window.location.href=\'job_details.php?id=' . $job['id'] . '\'">';
            echo '<div class="img-logo">';
            if (isset($job['company_details'])) {
                echo '<img src="../uploads/' . htmlspecialchars($job['company_details']['logo']) . '" alt="Company Logo" class="company-logo">';
            } else {
                echo '<img src="../uploads/default_logo.png" alt="Default Logo" class="company-logo">'; // Placeholder logo
            }
            echo '</div>';
            echo '<div class="job-details">';
            echo '<span class="job-title">' . htmlspecialchars($job['title']) . '</span>';

            echo '<button class="job-type">' . htmlspecialchars(ucfirst($job['status'])) . '</button>';
            echo '<hr class="divider">';
            echo '<p class="job-description">' . substr($job['job_desc'], 0, 150) . '...</p>';

            echo '<div class="job-info">';
            if (isset($job['company_details'])) {
                echo '<p class="info-box">Company: ' . htmlspecialchars($job['company_details']['name']) . '</p>';
                echo '<div class="info-box">Salary: ' . htmlspecialchars($job['salary_from']) . ' - ' . htmlspecialchars($job['salary_to']) . '</div>';
                echo '<div class="info-box">Location: ' . htmlspecialchars($job['company_details']['location']) . '</div>';
            } else {
                echo '<div class="info-box">Company: Not Available</div>';
                echo '<div class="info-box">Salary: ' . htmlspecialchars($job['salary_from']) . ' - ' . htmlspecialchars($job['salary_to']) . '</div>';
                echo '<div class="info-box">Location: Not Available</div>';
            }

            echo '</div>';
            echo '</div>';
            echo '</div>';
        }
    }
        ?>
                </div>
            </section>

        </div>
    </section>
</body>

</html>
<?php
include 'Config/dbconnect.php';

// Check if job ID is set in the URL
if (!isset($_GET['id'])) {
    die("Job ID is not provided.");
}

// Get the job category ID from the URL
$category_id = $_GET['id'];

// Escape the category ID to prevent SQL injection
$category_id = $conn->real_escape_string($category_id);

// Fetch job listings from the jobs table
$sql = "SELECT * FROM jobs WHERE category = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $category_id);
$stmt->execute();
$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Listings</title>
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="css/Navigation.css">
    <link rel="stylesheet" href="css/footer.css">
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

    .dashboard-section {
        background-color: #f0f0f0;
        /* Light grey background */
        padding: 40px 0;
        padding-top: 65px;

    }

    /* Dashboard container styling */
    .dashboard-container {
        max-width: 1200px;
        margin: 0 auto;
        /* Centers the container horizontally */
        padding: 0 20px;
        /* Ensure padding aligns with content */
    }

    /* Other existing styles */
    .job-listings {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
    }

    .job {
        display: flex;
        width: 48%;
        padding: 20px;
        /* Increase padding for better spacing */
        margin-bottom: 25px;
        /* Adjust bottom margin for better spacing between job cards */
        border: 1px solid #ccc;
        border-radius: 5px;
        cursor: pointer;
        background-color: #f9f9f9;
        min-height: 160px;
        /* Adjust the minimum height */
    }

    .company-logo {
        width: 70px;
        /* Increase logo size */
        height: auto;
        /* Increase logo size */
        margin-right: 25px;
    }

    .job-details {
        flex-grow: 1;
        margin-top: 15px;
    }

    .job-title {
        font-size: 20px;
        /* Slightly increase font size for better readability */
        font-weight: bold;
        margin-right: 150px;
        margin-bottom: 16px;

    }

    .job-type {
        background-color: blue;
        color: white;
        border: none;
        padding: 7px 12px;
        /* Slightly increase padding */
        border-radius: 5px;
        margin-bottom: 15px;
    }

    .divider {
        border: none;
        height: 1px;
        background-color: #ccc;
        margin: 10px 0;
    }

    .job-description {
        color: rgba(11, 49, 213, 0.8);
        margin: 30px 0;
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
        color: rgba(11, 49, 213, 0.8);

    }

    .filter-form {
        display: flex;
        align-items: center;
        /* Align items vertically center */
        background-color: #f8f9fa;
        /* Light background for contrast */
        padding: 10px;
        /* Adjust padding */
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        margin-bottom: 20px;
        width: 100%;
        /* Make the form take full width */
    }

    .form-group {
        margin-right: 15px;
        /* Add space between items */
        flex-grow: 1;
        /* Allow form groups to grow and fill space */
    }

    .salary-range {
        display: flex;
        align-items: center;
        /* Align label and range input */
    }

    .search-input {
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 5px;
        width: 100%;
        /* Make the input take full width */
        box-sizing: border-box;
        /* Ensure padding is included in width */
        font-size: 15px;
    }

    input[type="range"] {
        margin-left: 5px;
        /* Space between label and range slider */
        width: 150px;
        /* Fixed width for the range slider */
    }

    .filter-button {
        padding: 10px;
        background-color: #007bff;
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        transition: background-color 0.3s;
    }

    .filter-button:hover {
        background-color: #0056b3;
    }
    </style>

</head>

<body>

    <nav class="navbar">
        <div class="navbar-container">
            <div class="logo">
                <a href="http://localhost/JobPortal/index.php">
                    <img src="http://localhost/JobPortal/Logo/logo3.png" alt="Logo">
                </a>
            </div>
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="category.php">Category</a></li>
                <li><a href="job.php">Jobs</a></li>
                <li><a href="login.php">Login</a></li>
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
                        <a href="index.php">Home</a> | <a href="category.php">Category List</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="dashboard-section">
        <div class="dashboard-container">
            <form method="GET" action="" class="filter-form">
                <div class="form-group">
                    <input type="text" name="search" placeholder="Search by job title or location"
                        class="search-input" />
                </div>
                <div class="form-group salary-range">
                    <label for="salary-range">Salary Range:</label>
                    <input type="range" id="salary-range" name="salary" min="0" max="100000" step="1000" value="50000"
                        oninput="this.nextElementSibling.value = this.value">
                    <output>50000</output>
                </div>
                <button type="submit" class="filter-button">Filter</button>
            </form>

            <div class="job-listings">
                <?php
    // Fetching job listings with filters
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    $salary = isset($_GET['salary']) ? (int)$_GET['salary'] : 100000;

    // Prepare the base SQL query with category filtering
    $sql = "SELECT * FROM jobs WHERE category = ? AND (title LIKE ? OR city LIKE ?) AND salary_from <= ? ORDER BY CASE WHEN expiry_date >= CURDATE() THEN 0 ELSE 1 END, expiry_date ASC";
    $stmt = $conn->prepare($sql);

    // Bind the parameters
    $like_search = "%" . $search . "%";
    $stmt->bind_param("issi", $category_id, $like_search, $like_search, $salary);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Loop through each job listing
        while ($row = $result->fetch_assoc()) {
            // Fetch the corresponding company details
            $company_id = $row['company_name'];
            $company_sql = "SELECT * FROM company WHERE name = ?";
            $company_stmt = $conn->prepare($company_sql);
            $company_stmt->bind_param("s", $company_id);
            $company_stmt->execute();
            $company_result = $company_stmt->get_result();
            $company = $company_result->fetch_assoc();

            echo '<div class="job" onclick="window.location.href=\'job_details.php?id=' . $row['id'] . '\'">';
            echo '<div class="img-logo">';
            // Ensure the logo exists
            $logo_src = isset($company['logo']) ? 'uploads/' . $company['logo'] : 'uploads/default_logo.png';
            echo '<img src="' . htmlspecialchars($logo_src) . '" alt="Company Logo" class="company-logo">';
            echo '</div>';
            echo '<div class="job-details">';
            echo '<span class="job-title">' . htmlspecialchars($row['title']) . '</span>';
            echo '<button class="job-type">' . ucfirst(htmlspecialchars($row['type'])) . '</button>';
            echo '<hr class="divider">';
            echo '<p class="job-description">' . htmlspecialchars(substr($row['job_desc'], 0, 100)) . '...</p>';
            echo '<div class="job-info">';
            echo '<p class="info-box">' . htmlspecialchars(substr($company['goggle_url'], 0, 15)) . '...</p>';
            echo '<div class="info-box">Rs ' . htmlspecialchars($row['salary_from']) . "-" . htmlspecialchars($row['salary_to']) . '</div>';
            echo '<div class="info-box">' . htmlspecialchars($row['city']) . ", " . htmlspecialchars($company['country']) . '</div>';
            echo '</div>'; // job-info
            echo '</div>'; // job-details
            echo '</div>'; // job
        }
    } else {
        echo '<p>No job listings available at the moment for this category.</p>';
    }
    ?>
            </div>

        </div>
    </section>


    <?php include 'Assets/footer.php'; ?>

</body>

</html>

<?php
// Close the database connection
$conn->close();
?>
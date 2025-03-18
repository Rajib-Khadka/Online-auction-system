<?php include 'Assets/header.php'; 
include 'Config/dbconnect.php';

// Fetch the last 7 categories
$sql = "SELECT category FROM categories ORDER BY id ASC LIMIT 7"; // Adjust the table name and column as necessary
$result = $conn->query($sql);

// Store categories in an array
$categories = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row['category'];
    }
}

// Fetch job listings from the jobs table
$sql1 = "SELECT * FROM jobs ORDER BY CASE WHEN expiry_date >= CURDATE() THEN 0 ELSE 1 END, expiry_date ASC LIMIT 2";  // Adjust the table name if necessary
$result1 = $conn->query($sql1);
?>
<section class="hero">
    <div class="hero-container">
        <div class="hero-left">
            <div class="hero-text">
                <h1>Find your career <br> to make a better life</h1>
            </div>
            <form action="job.php">

                <div class="search-box">
                    <div class="input-group">
                        <label for="what">What</label>
                        <input type="text" id="what" placeholder="What jobs you want?" class="search-input">
                    </div>
                    <div class="input-group">
                        <label for="where">Where</label>
                        <input type="text" id="where" placeholder="Location" class="search-input">
                    </div>
                    <button class="search-button">Search</button>
                </div>
            </form>
        </div>

        <div class="hero-image">
            <img src="Banners/banner1.png" alt="Career Image">
        </div>
    </div>
</section>

<section class="steps-container">
    <div class="step">
        <i class="fa fa-user-circle" aria-hidden="true"></i>
        <div class="step-text">
            <h2>Register Your Account</h2>
            <p>Start by creating your personal profile to access countless job opportunities.</p>
        </div>
    </div>
    <div class="step">
        <i class="fa fa-upload" aria-hidden="true"></i>
        <div class="step-text">
            <h2>Upload Your Resume</h2>
            <p>Showcase your skills and experience by uploading your resume.</p>
        </div>
    </div>
    <div class="step">
        <i class="fa fa-briefcase" aria-hidden="true"></i>
        <div class="step-text">
            <h2>Apply for Your Dream Job</h2>
            <p>Browse through job listings and apply directly for the positions
                that suit your career goals.</p>
        </div>
    </div>
</section>


<section class="category-section">
    <a href="category.php" style="text-decoration: none;">
        <button class="category-button">JOB CATEGORY</button>
    </a>
    <h2 class="category-heading">Choose Your Desire Category</h2>
    <p class="category-paragraph">
        Find the perfect career path by selecting from a wide range of categories. Whether you're exploring new
        industries or deepening your expertise, we've got options tailored to your goals.
    </p>
    <div class="category-box-container">
        <?php
            foreach ($categories as $category) {

                $icon = 'fa-briefcase'; // Default icon
        
                // Set specific icons based on the category name
                switch ($category) {
                    case 'Accounting':
                        $icon = 'fa-calculator';
                        break;
                    case 'Auditing':
                        $icon = 'fa-chart-line';
                        break;
                    case 'Banking and financial Services':
                        $icon = 'fa-university';
                        break;
                    
                    case 'CEO and General Management':
                        $icon = 'fa-building';
                        break;
                    case 'Community and Social Department':
                        $icon = 'fa-users';
                        break;
                    case 'Creative and Design':
                        $icon = 'fa-paint-brush';
                        break;
                    case 'Education and Training':
                        $icon = 'fa-graduation-cap';
                        break;
                }
        
    // Loop through the categories and create boxes
        echo '<div class="category-box">';
        echo '<a href="category.php?category=' . urlencode($category) . '" style="text-decoration: none;">';

        echo '<i class="fa ' . $icon . '" aria-hidden="true"></i>'; // Use the selected icon
        echo '<p>' . htmlspecialchars($category) . '</p>'; // Escape output for safety
        echo '</div>';
    }
    ?>
        <div class="category-box">
            <i class="fa fa-plus" aria-hidden="true"></i> <!-- Example icon -->
            <p><a href="category.php">Others</a></p>
        </div>
    </div>
</section>

<section class="banner-section">
    <div class="banner-overlay">
        <button class="get-started-button" disabled>Get Started To Work</button>
        <h2 class="banner-heading">Don't just find. Be found. Put your cv in <br> fornt of great employers.</h2>
        <p class="banner-paragraph">It helps you to increase your chances of finding a suitable job and
            let recruiters contact you about jobs that are <br>not needed to pay for advertising.
        </p>
        <a href="login.php" style="text-decoration: none;">
            <button class="upload-cv-button">
                <i class="fa fa-upload" aria-hidden="true"></i>Upload Your Resume
            </button>
        </a>
    </div>
</section>

<section class="hot-jobs-section">
    <div class="container">
        <button class="hot-jobs-button" disabled>HOT JOBS</button>
        <h2 class="recent-jobs-heading">Browse Recent Jobs</h2>
        <p class="recent-jobs-paragraph">
            Stay ahead of the competition by exploring the latest job listings. Whether you're looking for your next
            big break or a fresh start, our updated job board has a range of opportunities just for you.
        </p>
        <div class="category-box-container">
            <div class="job-listings">

                <?php
if ($result1->num_rows > 0) {
    // Loop through each job listing
    while($row = $result1->fetch_assoc()) {
        // Fetch the corresponding company details using the company_id
        $company_id = $row['company_name'];
        $company_sql = "SELECT * FROM company WHERE name = '$company_id'";
        $company_result = $conn->query($company_sql);
        $company = $company_result->fetch_assoc();

        echo '<div class="job" onclick="window.location.href=\'job_details.php?id='.$row['id'].'\'">';
        echo '<div class="img-logo">';
        echo '<img src="uploads/' . $company['logo'] . '" alt="Company Logo" class="company-logo">'; // Logo will overlap
        echo '</div>';
        echo '<div class="job-details">';
        echo '<span class="job-title">' . $row['title'] . '</span>';
        echo '<button class="job-type">' . ucfirst($row['type']) . '</button>';
        echo '<hr class="divider">'; // This will be below the job title
        echo '<p class="job-description">' . substr($row['job_desc'], 0, 100) . '...</p>';
        echo '<div class="job-info">';
        echo '<p class="info-box">' . substr($company['website'], 0, 15) . '...</p>';

        echo '<div class="info-box">Rs ' . $row['salary_from'] . "-" . $row['salary_to'] . '</div>';
        echo '<div class="info-box">' . $company['location'] . "," . $company['country'] . '</div>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
    }
} else {
    echo '<p>No job listings available at the moment.</p>';
}
?>

            </div>
        </div>
    </div>
</section>

<?php include 'Assets/footer.php'; ?>
</body>

</html>
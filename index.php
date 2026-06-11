<?php
require_once 'config/constants.php';
?>

<?php include 'includes/header.php'; ?>

<?php include 'includes/navbar.php'; ?>

<main>

    <!-- Hero Section -->
    <section class="hero-section">

        <div class="hero-content">

            <h1>
                Welcome to <?= APP_NAME; ?>
            </h1>

            <p>
                Preserving Heritage, Strengthening Family, Building the Future.
            </p>

            <div class="hero-buttons">

                <a href="#" class="btn btn-primary">
                    Explore Family Tree
                </a>

                <a href="login.php" class="btn btn-secondary">
                    Login
                </a>

            </div>

        </div>

    </section>

    <!--ABOUT SECTION-->

<section class="about-section">

<div class="container">

    <div class="section-title">

        <h2>About Our Clan</h2>

        <p>
            Preserving our history, strengthening our unity, and building a better future together.
        </p>

    </div>

    <div class="about-grid">

        <div class="about-card">

            <i class="fa-solid fa-book-open"></i>

            <h3>Our History</h3>

            <p>
                This section will contain the history of the clan as provided by the Chairperson or Administrator.
            </p>

        </div>

        <div class="about-card">

            <i class="fa-solid fa-bullseye"></i>

            <h3>Our Mission</h3>

            <p>
                Bringing family members together and preserving our heritage for future generations.
            </p>

        </div>

        <div class="about-card">

            <i class="fa-solid fa-eye"></i>

            <h3>Our Vision</h3>

            <p>
                Building a united, connected and digitally empowered family community.
            </p>

        </div>

    </div>

</div>

</section>

</main>

<?php include 'includes/footer.php'; ?>

<!-- CLAN STATISTICS -->

<section class="statistics-section">

    <div class="container">

        <div class="section-title">

            <h2>Clan Statistics</h2>

            <p>
                A quick overview of our growing family.
            </p>

        </div>

        <div class="statistics-grid">

            <div class="stat-card">

                <i class="fa-solid fa-users"></i>

                <h3>0</h3>

                <p>Family Members</p>

            </div>

            <div class="stat-card">

                <i class="fa-solid fa-tree"></i>

                <h3>0</h3>

                <p>Generations</p>

            </div>

            <div class="stat-card">

                <i class="fa-solid fa-calendar-days"></i>

                <h3>0</h3>

                <p>Events</p>

            </div>

            <div class="stat-card">

                <i class="fa-solid fa-hand-holding-dollar"></i>

                <h3>0</h3>

                <p>Active Contributions</p>

            </div>

        </div>

    </div>

</section>

<!--LEADERSHIP SECTION-->

<section class="leadership-section">

    <div class="container">

        <div class="section-title">

            <h2>Clan Leadership</h2>

            <p>
                Meet the leaders serving our family community.
            </p>

        </div>

        <div class="leadership-grid">

            <div class="leader-card">

                <div class="leader-image">

                    <i class="fa-solid fa-user"></i>

                </div>

                <h3>Chairperson</h3>

                <p>Profile will appear here.</p>

            </div>

            <div class="leader-card">

                <div class="leader-image">

                    <i class="fa-solid fa-user"></i>

                </div>

                <h3>Secretary</h3>

                <p>Profile will appear here.</p>

            </div>

            <div class="leader-card">

                <div class="leader-image">

                    <i class="fa-solid fa-user"></i>

                </div>

                <h3>Treasurer</h3>

                <p>Profile will appear here.</p>

            </div>

            <div class="leader-card">

                <div class="leader-image">

                    <i class="fa-solid fa-user"></i>

                </div>

                <h3>Clan Elder</h3>

                <p>Profile will appear here.</p>

            </div>

        </div>

    </div>

</section>

<!--FAMILY TREE PREVIEW-->

<section class="family-tree-preview">

    <div class="container">

        <div class="section-title">

            <h2>Our Family Tree</h2>

            <p>
                Discover our roots and see how generations are connected.
            </p>

        </div>

        <div class="tree-preview-card">

            <div class="tree-icon">

                <i class="fa-solid fa-tree"></i>

            </div>

            <h3>Explore Our Heritage</h3>

            <p>

                Our digital family tree helps preserve our history and
                connects every generation in one place.

            </p>

            <a href="login.php" class="btn btn-primary">

                Explore Family Tree

            </a>

        </div>

    </div>

</section>


<!-- ===========================
     UPCOMING EVENTS
=========================== -->

<section class="events-section">

    <div class="container">

        <div class="section-title">

            <h2>Upcoming Events</h2>

            <p>
                Stay informed about our upcoming family activities and gatherings.
            </p>

        </div>

        <div class="events-grid">

            <div class="event-card">

                <div class="event-date">

                    15 JUL

                </div>

                <h3>Family Meeting</h3>

                <p>
                    Annual family gathering and discussion.
                </p>

            </div>

            <div class="event-card">

                <div class="event-date">

                    20 AUG

                </div>

                <h3>Wedding Ceremony</h3>

                <p>
                    Join us in celebrating a family wedding.
                </p>

            </div>

            <div class="event-card">

                <div class="event-date">

                    05 SEP

                </div>

                <h3>Family Development Day</h3>

                <p>
                    Community development and networking event.
                </p>

            </div>

        </div>

    </div>

</section>
<?php
require_once __DIR__ . '/../../includes/page_bootstrap.php';

$showSplash = !$isAdmin;
$search = isset($_GET['search']) ? trim((string) $_GET['search']) : '';
$assets = jt_fetch_assets($conn, $search);
$images = jt_fetch_hero_images($conn, 19, 5);
$pageTitle = 'Explore - Tourism_Jasaan';

$assetSectionId = 'jt-explore-assets';
$assetSectionLabel = 'ASSETS LIST';
$assetSectionTitle = 'EXPLORE TOURISM AND INFORMATIONS';
$assetSearchPlaceholder = 'Search Tourism...';
$assetEmptyMessage = 'No results found.';
$assetShowMoreLabel = 'Show More Assets';
?>

<!DOCTYPE html>
<html lang="en">

<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css">
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

<?php include __DIR__ . '/../../includes/user_head.php'; ?>
<?php include __DIR__ . '/../../includes/page_state.php'; ?>
<?php if ($showSplash): ?>
    <script src="<?= $BASE_URL ?>/frontend/assets/js/splash.js?v=<?= time(); ?>"></script>
    <script>
        try {
            if (localStorage.getItem("jtSplashShown") === "true") {
                document.documentElement.classList.add("skip-splash");
            }
        } catch (error) {
        }
    </script>
<?php endif; ?>

<body>
    <?php $includeAuthMessage = true; ?>
    <?php include __DIR__ . '/../../includes/page_alerts.php'; ?>

    <?php if ($showSplash): ?>
        <?php include __DIR__ . '/../../includes/splash.php'; ?>
    <?php endif; ?>

    <div id="mainContent" class="main-content" style="display: <?= $showSplash ? 'none' : 'block' ?>;">
        <?php include $navbarInclude; ?>

        <main>
            <section class="jt-hero-carousel">
                <?php foreach ($images as $index => $img): ?>
                    <div class="jt-hero-slide <?= $index === 0 ? 'jt-hero-active' : '' ?>" style="background-image: url('<?= $BASE_URL ?>/uploads/<?= htmlspecialchars($img) ?>');"></div>
                <?php endforeach; ?>

                <div class="jt-hero-content">
                    <div class="jt-hero-badge">DISCOVER JASAAN HIGHLIGHTS</div>

                    <h1 class="jt-hero-title" id="jt-hero-title">
                        One Jasaan  <span class="jt-hero-blue">Wow Jasaan</span>
                    </h1>

                    <p class="jt-hero-typing">
                        Find tourist Attractions, Resorts, Food Products, and Markets in Jasaan
                    </p>

                    <div class="jt-hero-actions">
                        <button class="jt-hero-btn jt-hero-btn-primary">WHY JASAAN?</button>
                        <button class="jt-hero-btn jt-hero-btn-outline jt-history-btn">OUR HISTORY</button>
                    </div>
                </div>

                <div class="jt-hero-dots">
                    <?php foreach ($images as $index => $img): ?>
                        <div class="jt-hero-dot <?= $index === 0 ? 'jt-hero-active' : '' ?>"></div>
                    <?php endforeach; ?>
                </div>
            </section>

            <section id="jasaan-why-section">
                <div class="jasaan-why-wrapper">
                    <p class="jasaan-why-subtitle"><span class="jasaan-highlight-bar"></span>THE ULTIMATE DESTINATION</p>

                    <h2 class="jasaan-why-title">
                        WHY CHOOSE <br />
                        <span class="jasaan-why-accent">JASAAN?</span>
                    </h2>

                    <p class="jasaan-why-description">
                        With its natural sights and local culture, Jasaan offers something special.
                    </p>

                    <div class="jasaan-why-cards">
                        <div class="jasaan-why-card">
                            <div class="jasaan-why-icon jasaan-why-icon--blue" aria-hidden="true">
                                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#1db5a3" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M12 2C12 2 7 9 7 13a5 5 0 0 0 10 0c0-4-5-11-5-11z"></path>
                                    <circle cx="12" cy="14" r="1"></circle>
                                </svg>
                            </div>
                            <h3 class="jasaan-why-card-title">Natural Springs</h3>
                            <p class="jasaan-why-card-text">
                                Enjoy the clear, cold water of our natural springs and flowing waterfalls.
                            </p>
                        </div>

                        <div class="jasaan-why-card">
                            <div class="jasaan-why-icon jasaan-why-icon--green" aria-hidden="true">
                                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#5bb96e" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="11" width="18" height="10" rx="2" ry="2"></rect>
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                                    <line x1="12" y1="16" x2="12" y2="16"></line>
                                    <line x1="16" y1="16" x2="16" y2="16"></line>
                                </svg>
                            </div>
                            <h3 class="jasaan-why-card-title">Historic Path</h3>
                            <p class="jasaan-why-card-text">
                                Visit the Immaculate Conception Church, an old stone church from the colonial period.
                            </p>
                        </div>

                        <div class="jasaan-why-card">
                            <div class="jasaan-why-icon jasaan-why-icon--yellow" aria-hidden="true">
                                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#c6d76e" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="5"></circle>
                                    <line x1="12" y1="1" x2="12" y2="3"></line>
                                    <line x1="12" y1="21" x2="12" y2="23"></line>
                                    <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
                                    <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
                                    <path d="M2 16s3 0 4-2 4 2 6 2 4-3 6-2 4 0 4 0"></path>
                                </svg>
                            </div>
                            <h3 class="jasaan-why-card-title">Seaside Escape</h3>
                            <p class="jasaan-why-card-text">
                                Rest and unwind near the calm blue waters.
                            </p>
                        </div>

                        <div class="jasaan-why-card jasaan-why-card--highlight">
                            <div class="jasaan-why-icon jasaan-why-icon--teal" aria-hidden="true">
                                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 12c0 3.31-3.58 6-8 6s-8-2.69-8-6"></path>
                                    <path d="M4 12h16"></path>
                                    <path d="M9 16a3 3 0 0 0 6 0"></path>
                                </svg>
                            </div>
                            <h3 class="jasaan-why-card-title">Town Specialties</h3>
                            <p class="jasaan-why-card-text">
                                Try well-loved local foods made with care.
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <section id="jt-heritage-section" class="jt-heritage-hero">
                <div class="jt-heritage-bg" style="background-image: url('<?= $BASE_URL ?>/frontend/assets/images/immaculate-conception-jasaan-bg.jpeg');"></div>

                <div class="jt-heritage-container">
                    <div class="jt-heritage-text">
                        <p class="jt-heritage-sub">
                            <span class="jt-line"></span>
                            THE LEGACY OF JASAAN
                        </p>

                        <h1 class="jt-heritage-title">
                            A JOURNEY <br>
                            THROUGH <span>TIME</span>
                        </h1>

                        <p class="jt-heritage-desc">
                            Jasaan began as a small Jesuit mission in the 1600s. It later became a religious center for nearby areas and slowly grew into a town, officially formed in the early 1800s.
                            <br><br>
                            At the heart of Jasaan’s history is the Immaculate Conception Church. Built from coral stone, the church has stood strong for hundreds of years and is now recognized as a National Cultural Treasure.
                            <br><br>
                            Today, Jasaan blends progress with nature. It honors its past while growing as a lively gateway to Northern Mindanao.
                        </p>

                        <div class="jt-heritage-stats">
                            <div>
                                <h2>1830</h2>
                                <span>Established</span>
                            </div>

                            <div>
                                <h2>Heritage</h2>
                                <span>National Treasure</span>
                            </div>
                        </div>
                    </div>

                    <div class="jt-heritage-card">
                        <iframe
                            width="560"
                            height="315"
                            src="https://www.youtube.com/embed/yiSs_NCInLQ?si=Gpf3GDJxXKQUbF8x&start=8&autoplay=1&mute=1"
                            title="YouTube video player"
                            frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                            allowfullscreen
                        ></iframe>
                    </div>
                </div>
            </section>

            <?php include __DIR__ . '/../../includes/asset_grid_section.php'; ?>
        </main>
    </div>

    <?php include __DIR__ . '/../../includes/footer.php'; ?>
    <?php include __DIR__ . '/../../includes/kuya_jha.php'; ?>
    <?php include __DIR__ . '/../../includes/page_modals.php'; ?>
    <?php $includeCarouselScript = true; ?>
    <?php include __DIR__ . '/../../includes/public_scripts.php'; ?>
</body>

</html>

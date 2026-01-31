<?php
// Start session first before any output
session_start();

// Fetch dynamic data from database
require_once __DIR__ . '/db.php';

// Check if user is admin
$isAdmin = false;
if (isset($_SESSION['admin_id'])) {
    $isAdmin = true;
}

// Handle image deletion from public page
if ($isAdmin && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_about_image']) && $pdo) {
    try {
        $stmt = $pdo->prepare("DELETE FROM about_images WHERE id = ?");
        $stmt->execute([(int)$_POST['delete_about_image']]);
        header('Location: about.php');
        exit;
    } catch (PDOException $e) {}
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>About Us | PulseTech Solutions</title>
  <link rel="stylesheet" href="css/styles.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</head>
<body>

<?php
$teamMembers = $stats = $aboutImages = $partners = [];
if ($pdo) {
    try {
        $teamMembers = $pdo->query("SELECT * FROM team_members ORDER BY display_order ASC")->fetchAll(PDO::FETCH_ASSOC);
        $stats = $pdo->query("SELECT * FROM about_stats ORDER BY display_order ASC")->fetchAll(PDO::FETCH_ASSOC);
        $aboutImages = $pdo->query("SELECT * FROM about_images ORDER BY display_order ASC, id ASC")->fetchAll(PDO::FETCH_ASSOC);
        $partners = $pdo->query("SELECT * FROM about_partners ORDER BY display_order ASC")->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Database error in about.php: " . $e->getMessage());
    }
}
// Only use fallback data if database returned empty AND we have no data
// This prevents showing old fallback when new members are added
if (empty($teamMembers)) {
    $teamMembers = [
        ['name' => 'Mayanja Justus', 'position' => 'Founder & CEO', 'bio' => 'Previously led design at a YC-backed fintech. Passionate about conversion-first UX.', 'image_url' => '/images/team1.jpeg', 'display_order' => 1, 'id' => 0],
        ['name' => 'Awongo Fahadi Rashid', 'position' => 'Lead Developer', 'bio' => 'Full-stack engineer specializing in Next.js, headless commerce, and performance.', 'image_url' => '/images/team2.jpeg', 'display_order' => 2, 'id' => 0],
        ['name' => 'Kamwada Alex', 'position' => 'Brand Strategist', 'bio' => '15 years in branding for Fortune 500s. Now helping startups punch above their weight.', 'image_url' => '/images/team3.jpeg', 'display_order' => 3, 'id' => 0],
        ['name' => 'Namayanja Mackline', 'position' => 'E-commerce Lead', 'bio' => 'Built 40+ Shopify stores. Expert in checkout optimization and growth funnels.', 'image_url' => '/images/team4.jpeg', 'display_order' => 4, 'id' => 0]
    ];
}
if (empty($stats)) {
    $stats = [
        ['stat_value' => '200+', 'stat_label' => 'Projects Shipped'],
        ['stat_value' => '50+', 'stat_label' => 'Active Clients'],
        ['stat_value' => '2.4s', 'stat_label' => 'Avg. Site Load Time'],
        ['stat_value' => '98%', 'stat_label' => 'Client Satisfaction']
    ];
}
if (empty($aboutImages)) {
    $aboutImages = [
        ['image_url' => 'https://images.unsplash.com/photo-1519389950473-47ba0277781c?w=1200&h=440&fit=crop', 'alt_text' => 'Team strategy session'],
        ['image_url' => 'https://images.unsplash.com/photo-1517694712202-14dd9538aa97?w=600&h=440&fit=crop', 'alt_text' => 'Developer coding a website'],
        ['image_url' => 'https://images.unsplash.com/photo-1561070791-2526d30994b5?w=600&h=440&fit=crop', 'alt_text' => 'Design desk with brand assets'],
        ['image_url' => 'https://images.unsplash.com/photo-1551836022-4c4c79ecde51?w=600&h=440&fit=crop', 'alt_text' => 'UX workshop with sticky notes'],
        ['image_url' => 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=600&h=440&fit=crop', 'alt_text' => 'Smartwatch and accessories']
    ];
}
if (empty($partners)) {
    $partners = [
        ['name' => 'Shopify Partner', 'logo_url' => null],
        ['name' => 'Webflow Expert', 'logo_url' => null],
        ['name' => 'Meta Blueprint', 'logo_url' => null],
        ['name' => 'Google Partner', 'logo_url' => null],
        ['name' => 'Figma Community', 'logo_url' => null],
        ['name' => 'AWS Certified', 'logo_url' => null]
    ];
}
?>
<header>
  <div class="container navbar">
    <div class="logo">
      <img src="images/PulseTech__2_-removebg-preview.png" alt="PulseTech Solutions logo" class="logo-image" />
      <div>
        <div>PulseTech Solutions</div>
        <div class="subtle">Bringing innovation to grow your business</div>
      </div>
    </div>
    <nav class="nav-links">
      <a href="index.html">Home</a>
      <div class="dropdown">
        <a href="services.html">Services ▼</a>
        <div class="dropdown-menu">
          <a href="services/website-development.html">Website Development</a>
          <a href="services/graphic-branding.html">Graphic Design & Branding</a>
          <a href="services/ui-ux.html">Systems & App Development</a>
          <a href="services/ecommerce.html">E-commerce Solutions</a>
        </div>
      </div>
      <a href="portfolio.html">Portfolio</a>
      <a href="/shop.php">Shop</a>
      <a class="active" href="about.php">About Us</a>
      <a href="contact.php">Contact Us</a>
    </nav>
    <div class="mobile-nav">
      <button class="menu-toggle" data-menu-toggle>☰</button>
    </div>
  </div>
</header>

<main>
  <section class="hero" style="background: linear-gradient(rgba(10, 26, 47, 0.85), rgba(10, 26, 47, 0.85)), url('https://images.unsplash.com/photo-1522071820081-009f0129c71c?w=1600&h=600&fit=crop') center/cover; color: white; padding: 100px 0;">
    <div class="container">
      <div class="badge">About PulseTech</div>
      <h1>Building digital futures since 2020</h1>
      <p>We're a hybrid digital agency and tech accessories shop. We design websites, craft brand identities, and sell premium tech gear—all under one roof.</p>
    </div>
  </section>

  <section class="section">
    <div class="container">
      <div class="grid grid-2">
        <div>
          <h2>Our Story: From a Senior Six Vacation Dream to Empowering Businesses with Tech</h2>
          <p>It all began during my Senior Six vacation in the bustling streets of Kampala. Surrounded by vibrant markets and ambitious entrepreneurs, I noticed a growing need: businesses everywhere were struggling to thrive in a digital world. Many lacked professional online presence, stunning branding, or reliable tech tools to reach more customers. That spark ignited something in me—I decided to take action.</p>
          <p>Inspired, I dove headfirst into learning website development. What started as late-night coding sessions turned into a full passion. This drive led me to pursue a Bachelor of Science in Software Engineering at the prestigious Makerere University, where I honed my skills in building powerful, user-friendly digital solutions.</p>
          <p>Along the way, I saw another opportunity: the demand for quality gadgets and phone accessories in our local markets. Combining my tech expertise with this everyday need, I expanded into curating premium phone cases, chargers, earbuds, smart watches, and more—delivered with the same reliability and innovation as our digital services.</p>
          <p>Today, PulseTech Solutions is more than a company—it's a mission to uplift Ugandan businesses and individuals through cutting-edge technology. We craft beautiful, high-performing websites, eye-catching graphics and branding, custom software and mobile app solutions, and provide top-tier tech accessories to keep you connected.</p>
        </div>
        <div>
          <div class="image-collage" style="display: grid; grid-template-columns: 1fr 1fr; grid-template-rows: 220px 220px 220px; gap: 12px; min-height: 684px;">
            <?php foreach ($aboutImages as $index => $img): ?>
              <div class="image-item" data-image-id="<?php echo $img['id']; ?>" style="<?php echo $index === 0 ? 'grid-column: 1 / span 2; ' : ''; ?>overflow: hidden; border-radius: 12px; position: relative;">
                <img src="<?php echo htmlspecialchars($img['image_url']); ?>" alt="<?php echo htmlspecialchars($img['alt_text'] ?? 'About image'); ?>" style="width:100%; height:100%; object-fit:cover; display:block;" loading="lazy" decoding="async" onerror="this.src='https://via.placeholder.com/1200x600?text=No+Image'" />
                <?php if ($isAdmin): ?>
                  <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.7); display: none; align-items: center; justify-content: center; gap: 8px;" class="admin-controls" onclick="event.stopPropagation()">
                    <button onclick="editImageFromGallery(<?php echo htmlspecialchars(json_encode($img)); ?>)" style="padding: 8px 12px; background: #2563eb; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 12px;"><i class="fas fa-edit"></i> Edit</button>
                    <form method="POST" style="margin: 0;" onsubmit="event.stopPropagation(); return confirm('Delete this image?')">
                      <input type="hidden" name="delete_about_image" value="<?php echo $img['id']; ?>">
                      <button type="submit" style="padding: 8px 12px; background: #dc2626; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 12px;"><i class="fas fa-trash"></i> Delete</button>
                    </form>
                  </div>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          </div>
          <?php if ($isAdmin): ?>
            <script>
              document.querySelectorAll('.image-item').forEach(item => {
                item.addEventListener('mouseenter', function() {
                  this.querySelector('.admin-controls').style.display = 'flex';
                });
                item.addEventListener('mouseleave', function() {
                  this.querySelector('.admin-controls').style.display = 'none';
                });
              });
            </script>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </section>

  <section class="section" style="background: var(--gray-100);">
    <div class="container">
      <h2>Meet the Team</h2>
      <p class="subtle">A tight-knit crew of designers, developers, and growth experts.</p>
      <div class="grid grid-4">
        <?php foreach ($teamMembers as $member): ?>
        <div class="card" style="position: relative; display: flex; flex-direction: column; gap: 8px;">
          <?php if (!empty($member['image_url'])): ?>
            <img src="<?php echo htmlspecialchars($member['image_url']); ?>" alt="<?php echo htmlspecialchars($member['name']); ?>" style="border-radius: 12px; width: 100%; height: 220px; object-fit: cover; object-position: top; display: block; visibility: visible;" />
          <?php else: ?>
            <div style="border-radius: 12px; width: 100%; height: 220px; background-color: #e5e7eb; display: flex; align-items: center; justify-content: center; color: #6b7280;">
              <i class="fas fa-image" style="font-size: 48px;"></i>
            </div>
          <?php endif; ?>
          <h4 style="margin: 0;"><?php echo htmlspecialchars($member['name']); ?></h4>
          <p class="subtle" style="margin: 0;"><?php echo htmlspecialchars($member['position']); ?></p>
          <p style="margin: 0;"><?php echo htmlspecialchars($member['bio']); ?></p>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="section">
    <div class="container">
      <h2>Why Choose PulseTech?</h2>
      <div class="grid grid-3">
        <div class="mini-card">
          <h4>Hybrid Model</h4>
          <p>One partner for design, development, and accessories. Streamlined collaboration.</p>
        </div>
        <div class="mini-card">
          <h4>Fast Turnaround</h4>
          <p>Most projects ship in 2-4 weeks. We value speed without sacrificing quality.</p>
        </div>
        <div class="mini-card">
          <h4>Conversion-First</h4>
          <p>Every design decision backed by UX research, A/B testing, and analytics.</p>
        </div>
        <div class="mini-card">
          <h4>Device Mockups</h4>
          <p>All web projects showcased on realistic iPhone/MacBook/iPad mockups.</p>
        </div>
        <div class="mini-card">
          <h4>Transparent Pricing</h4>
          <p>Clear packages with no hidden fees. Know exactly what you're getting.</p>
        </div>
        <div class="mini-card">
          <h4>Post-Launch Support</h4>
          <p>We don't ghost. 3-12 months support included depending on package.</p>
        </div>
      </div>
    </div>
  </section>

  <section class="section" style="background: var(--gray-100);">
    <div class="container">
      <h2>Certifications & Partners</h2>
      <div class="client-logos">
        <?php foreach ($partners as $partner): ?>
          <div class="logo-tile">
            <?php if (!empty($partner['logo_url'])): ?>
              <img src="<?php echo htmlspecialchars($partner['logo_url']); ?>" alt="<?php echo htmlspecialchars($partner['name']); ?>" style="max-height: 36px; object-fit: contain;" onerror="this.replaceWith(document.createTextNode('<?php echo htmlspecialchars($partner['name']); ?>'))" />
            <?php else: ?>
              <?php echo htmlspecialchars($partner['name']); ?>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="section">
    <div class="container">
      <h2>By the Numbers</h2>
      <div class="grid grid-4">
        <?php foreach ($stats as $stat): ?>
        <div class="mini-card">
          <h2><?php echo htmlspecialchars($stat['stat_value']); ?></h2>
          <p><?php echo htmlspecialchars($stat['stat_label']); ?></p>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="section" style="background: var(--gray-100);">
    <div class="container">
      <h2>Ready to work together?</h2>
      <p>Let's build something exceptional.</p>
      <a class="btn btn-primary" href="contact.html">Get in Touch</a>
    </div>
  </section>
</main>

<footer class="footer">
  <div class="container footer-grid">
    <div>
      <div class="logo">
        <img src="images/PulseTech__2_-removebg-preview.png" alt="PulseTech Solutions logo" class="logo-image" />
        <div>PulseTech Solutions © 2025</div>
      </div>
      <p class="subtle">PulseTech Solutions is a leading digital agency based in Kampala, Uganda. We specialize in web development, systems & app development, branding, and e-commerce solutions while offering premium phone and computer accessories to elevate your digital experience.</p>
    </div>
    <div>
      <h4>Explore</h4>
      <div class="grid">
        <a href="index.html">Home</a>
        <a href="services.html">Services</a>
        <a href="portfolio.html">Portfolio</a>
        <a href="/shop.php">Shop</a>
        <a href="about.php">About Us</a>
        <a href="contact.html">Contact Us</a>
      </div>
    </div>
    <div>
      <h4>Contact Us</h4>
      <div class="grid">
        <p class="subtle" style="margin: 0 0 8px 0;">📍 Kampala, Uganda</p>
        <p class="subtle" style="margin: 0 0 8px 0;">📧 hello@pulsetechsolutions.com</p>
        <p class="subtle" style="margin: 0 0 16px 0;">📱 +256752895268</p>
      </div>
      <div class="social-row">
        <a class="social" href="https://www.instagram.com" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
        <a class="social" href="https://www.linkedin.com" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
        <a class="social" href="https://twitter.com" aria-label="X (Twitter)"><i class="fa-brands fa-x-twitter"></i></a>
        <a class="social" href="https://www.tiktok.com" aria-label="TikTok"><i class="fab fa-tiktok"></i></a>
      </div>
    </div>
  </div>
</footer>

<?php if ($isAdmin): ?>
<!-- Admin Edit Image Modal for Public Page -->
<div id="editGalleryImageModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 50; align-items: center; justify-content: center;" class="fixed-modal">
  <div style="background: white; border-radius: 12px; padding: 24px; max-width: 500px; width: 90%; max-height: 90vh; overflow-y: auto;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
      <h3 style="font-size: 20px; font-weight: bold; margin: 0;">Edit Gallery Image</h3>
      <button onclick="closeGalleryImageModal()" style="background: none; border: none; cursor: pointer; font-size: 20px;">×</button>
    </div>
    
    <form method="POST" action="/admin/aboutus/index.php" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 16px;">
      <input type="hidden" name="image_id" id="gallery_image_id">
      
      <div>
        <label style="display: block; font-size: 14px; font-weight: 600; margin-bottom: 8px;">Upload New Image (optional)</label>
        <input type="file" name="image_file" accept="image/*" style="width: 100%; padding: 12px; border: 1px solid #e5e7eb; border-radius: 8px;">
        <p style="font-size: 12px; color: #6b7280; margin-top: 4px;">Leave empty to keep current image</p>
      </div>
      
      <div>
        <label style="display: block; font-size: 14px; font-weight: 600; margin-bottom: 8px;">Alt Text / Description</label>
        <input type="text" name="alt_text" id="gallery_alt_text" style="width: 100%; padding: 12px; border: 1px solid #e5e7eb; border-radius: 8px;">
      </div>
      
      <div>
        <label style="display: block; font-size: 14px; font-weight: 600; margin-bottom: 8px;">Display Order</label>
        <input type="number" name="display_order" id="gallery_display_order" style="width: 100%; padding: 12px; border: 1px solid #e5e7eb; border-radius: 8px;">
      </div>
      
      <div style="display: flex; gap: 12px; margin-top: 16px;">
        <button type="button" onclick="closeGalleryImageModal()" style="flex: 1; padding: 12px 16px; border: 1px solid #d1d5db; background: white; color: #374151; border-radius: 8px; cursor: pointer; font-weight: 500;">Cancel</button>
        <button type="submit" name="update_image" style="flex: 1; padding: 12px 16px; background: #2563eb; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 500;">Save Changes</button>
      </div>
    </form>
  </div>
</div>

<script>
function editImageFromGallery(image) {
  document.getElementById('gallery_image_id').value = image.id;
  document.getElementById('gallery_alt_text').value = image.alt_text || '';
  document.getElementById('gallery_display_order').value = image.display_order || 0;
  document.getElementById('editGalleryImageModal').style.display = 'flex';
}

function closeGalleryImageModal() {
  document.getElementById('editGalleryImageModal').style.display = 'none';
}

document.getElementById('editGalleryImageModal').addEventListener('click', function(e) {
  if (e.target === this) {
    closeGalleryImageModal();
  }
});
</script>
<?php endif; ?>

<script src="js/main.clean.js"></script>
</body>
</html>

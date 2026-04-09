<?php
require_once __DIR__ . '/config/db.php';

header('Content-Type: application/xml; charset=utf-8');
header('X-Robots-Tag: noindex');

$base = rtrim(SITE_URL, '/') . '/ARS';
$now  = date('Y-m-d');

// Static pages
$static = [
    ['loc' => '',           'priority' => '1.0', 'freq' => 'daily'],
    ['loc' => '/shop.php',  'priority' => '0.9', 'freq' => 'daily'],
    ['loc' => '/about.php', 'priority' => '0.7', 'freq' => 'monthly'],
    ['loc' => '/contact.php','priority'=> '0.7', 'freq' => 'monthly'],
    ['loc' => '/faq.php',   'priority' => '0.6', 'freq' => 'monthly'],
    ['loc' => '/shipping.php','priority'=> '0.5', 'freq' => 'yearly'],
    ['loc' => '/terms.php', 'priority' => '0.4', 'freq' => 'yearly'],
    ['loc' => '/privacy.php','priority' => '0.4', 'freq' => 'yearly'],
];

// Dynamic: categories
try {
    $categories = $pdo->query("SELECT id, slug FROM categories")->fetchAll();
} catch (PDOException $e) { $categories = []; }

// Dynamic: products
try {
    $products = $pdo->query("SELECT slug, updated_at FROM products WHERE stock > 0 ORDER BY updated_at DESC")->fetchAll();
} catch (PDOException $e) { $products = []; }

echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">

<?php foreach ($static as $page): ?>
    <url>
        <loc><?= htmlspecialchars($base . $page['loc'] ?: $base . '/index.php') ?></loc>
        <lastmod><?= $now ?></lastmod>
        <changefreq><?= $page['freq'] ?></changefreq>
        <priority><?= $page['priority'] ?></priority>
    </url>
<?php endforeach; ?>

<?php foreach ($categories as $cat): ?>
    <url>
        <loc><?= htmlspecialchars($base . '/shop.php?category=' . $cat['id']) ?></loc>
        <lastmod><?= $now ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>
<?php endforeach; ?>

<?php foreach ($products as $prod): ?>
    <url>
        <loc><?= htmlspecialchars($base . '/product.php?slug=' . urlencode($prod['slug'])) ?></loc>
        <lastmod><?= date('Y-m-d', strtotime($prod['updated_at'] ?? $now)) ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.7</priority>
    </url>
<?php endforeach; ?>

</urlset>

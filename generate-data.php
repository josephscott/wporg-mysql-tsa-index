#!/opt/homebrew/opt/php@8.4/bin/php
<?php
ini_set('strict_types', 1);

require_once 'helpers.php';

class User {
    public function __construct(
        public int $id,
        public string $login,
        public string $displayName
    ) {}
}

$users = [
    new User(1, 'admin', 'Site Administrator'),
    new User(2, 'john_doe', 'John Doe'),
    new User(3, 'jane_smith', 'Jane Smith'),
    new User(4, 'blogger123', 'The Blogger'),
    new User(5, 'content_writer', 'Content Writer'),
    new User(6, 'editor', 'Chief Editor'),
    new User(7, 'guest_author', 'Guest Author')
];

$postTypes = ['post', 'page', 'attachment', 'revision', 'wp_navigation', 'shop_order_placehold', 'wp_font_family', 'wp_font_face'];
$postStatuses = ['publish', 'draft', 'inherit', 'private', 'trash', 'auto-draft'];
$commentStatuses = ['open', 'closed'];
$pingStatuses = ['open', 'closed'];
$mimeTypes = ['', 'image/jpeg', 'image/png', 'image/gif', 'application/pdf', 'text/plain', 'video/mp4'];

// Sample content pools
$sampleTitles = [
    'Hello World', 'Getting Started with WordPress', 'The Ultimate Guide to Success',
    'Tips and Tricks for Better Performance', '10 Ways to Improve Your Workflow',
    'Understanding the Basics', 'Advanced Techniques Explained', 'Best Practices to Follow',
    'Common Mistakes to Avoid', 'How to Optimize Your Site', 'The Future of Web Development',
    'Essential Tools for Developers', 'Building Better User Experiences', 'Mobile-First Design Principles',
    'SEO Strategies That Work', 'Content Marketing Fundamentals', 'Social Media Best Practices',
    'Privacy Policy', 'Terms of Service', 'About Us', 'Contact Information', 'Sample Page'
];

$sampleContent = [
    '<!-- wp:paragraph --><p>Welcome to WordPress. This is your first post. Edit or delete it, then start writing!</p><!-- /wp:paragraph -->',
    '<!-- wp:paragraph --><p>This is a sample post with some basic content. It demonstrates the standard WordPress block format.</p><!-- /wp:paragraph -->',
    '<!-- wp:heading --><h2>Introduction</h2><!-- /wp:heading --><!-- wp:paragraph --><p>This article covers the fundamentals of web development and best practices.</p><!-- /wp:paragraph -->',
    '<!-- wp:quote --><blockquote><p>Success is not final, failure is not fatal: it is the courage to continue that counts.</p></blockquote><!-- /wp:quote -->',
    '<!-- wp:list --><ul><li>First important point</li><li>Second key insight</li><li>Third essential element</li></ul><!-- /wp:list -->',
    '<!-- wp:image --><figure><img src="https://example.com/image.jpg" alt="Sample image"/><figcaption>This is a sample image caption</figcaption></figure><!-- /wp:image -->'
];

$db = new mysqli(
    '127.0.0.1',
    'test_user',
    'test_pass',
    'test_db',
    6330
);

if ($db->connect_error) {
    echo "Connection error: {$db->connect_error}\n";
    exit(1);
}

$db->query("SET SESSION sql_mode = REPLACE(@@sql_mode, 'NO_ZERO_DATE', '')");
$db->query("SET SESSION sql_mode = 'NO_ENGINE_SUBSTITUTION'");


drop_table($db);
run_sql_file( $db, 'create-table.sql' );
run_sql_file( $db, 'autoinc.sql' );

function randomDate(): string {
    $start = strtotime('2020-01-01');
    $end = strtotime('2025-09-03');
    $timestamp = rand($start, $end);
    return date('Y-m-d H:i:s', $timestamp);
}

function randomString(int $length = 10): string {
    return substr(str_shuffle('abcdefghijklmnopqrstuvwxyz0123456789'), 0, $length);
}

function generateSlug(string $title): string {
    $slug = strtolower($title);
    $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
    $slug = preg_replace('/\s+/', '-', $slug);
    $slug = trim($slug, '-');
    return substr($slug, 0, 200);
}

echo "Starting data generation for 400,000 records...\n";

$batchSize = 1000;
$totalRecords = 400000;
$batches = ceil($totalRecords / $batchSize);

for ($batch = 0; $batch < $batches; $batch++) {
    $values = [];
    $recordsInBatch = min($batchSize, $totalRecords - $batch * $batchSize);

    for ($i = 0; $i < $recordsInBatch; $i++) {
        $id = $batch * $batchSize + $i + 1000000; // Start from 1M to avoid conflicts
        $user = $users[array_rand($users)];
        $postType = $postTypes[array_rand($postTypes)];
        $postStatus = $postStatuses[array_rand($postStatuses)];

        // Adjust post status based on post type
        if (in_array($postType, ['revision', 'attachment'])) {
            $postStatus = 'inherit';
        } elseif ($postType === 'shop_order_placehold') {
            $postStatus = 'draft';
        }

        $title = $sampleTitles[array_rand($sampleTitles)] . ' ' . rand(1, 9999);
        $content = $sampleContent[array_rand($sampleContent)];
        $slug = generateSlug($title) . '-' . randomString(5);
        $date = randomDate();
        $commentStatus = $commentStatuses[array_rand($commentStatuses)];
        $pingStatus = $pingStatuses[array_rand($pingStatuses)];
        $mimeType = $mimeTypes[array_rand($mimeTypes)];
        $parentId = rand(0, 100) < 10 ? rand(1, 1000) : 0; // 10% chance of having a parent
        $menuOrder = rand(0, 100);
        $commentCount = rand(0, 50);

        $guid = "https://localhost:9999/wordpress/?";
        if ($postType === 'page') {
            $guid .= "page_id={$id}";
        } elseif (in_array($postType, ['post', 'wp_navigation'])) {
            $guid .= "p={$id}";
        } else {
            $guid .= "post_type={$postType}&p={$id}";
        }

        $values[] = sprintf(
            "(%d, %d, '%s', '%s', '%s', '%s', '', '%s', '%s', '%s', '', '%s', '', '', '%s', '%s', '', %d, '%s', %d, '%s', '%s', %d)",
            $id,
            $user->id,
            $db->real_escape_string($date),
            $db->real_escape_string($date),
            $db->real_escape_string($content),
            $db->real_escape_string($title),
            $postStatus,
            $commentStatus,
            $pingStatus,
            $db->real_escape_string($slug),
            $db->real_escape_string($date),
            $db->real_escape_string($date),
            $parentId,
            $db->real_escape_string($guid),
            $menuOrder,
            $postType,
            $mimeType,
            $commentCount
        );
    }

    $sql = "INSERT INTO `wp_posts` (`ID`, `post_author`, `post_date`, `post_date_gmt`, `post_content`, `post_title`, `post_excerpt`, `post_status`, `comment_status`, `ping_status`, `post_password`, `post_name`, `to_ping`, `pinged`, `post_modified`, `post_modified_gmt`, `post_content_filtered`, `post_parent`, `guid`, `menu_order`, `post_type`, `post_mime_type`, `comment_count`) VALUES " . implode(', ', $values);

    if (!$db->query($sql)) {
        echo "Error in batch {$batch}: " . $db->error . "\n";
        break;
    }

    $progress = ($batch + 1) * $batchSize;
    $percentage = min(100, round(($progress / $totalRecords) * 100, 1));
    echo "Batch " . ($batch + 1) . "/{$batches} completed. Progress: {$percentage}% ({$progress}/{$totalRecords} records)\n";
}

$db->close();
echo "Data generation completed!\n";

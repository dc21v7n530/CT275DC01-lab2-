<?php
/* Đoạn mã xử lý PHP. */

define('TITLE', 'Tìm kiếm Trích dẫn');

require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/footer.php';

$error_message = null;
$reason = null;
$sources = [];
$results = [];


$keyword = trim($_GET['keyword'] ?? '');
$selected_source = trim($_GET['source'] ?? '');

try {
    $pdo = get_database_connection();

    
    $stmt_sources = $pdo->query('SELECT DISTINCT source FROM quotes ORDER BY source ASC');
    $sources = $stmt_sources->fetchAll(PDO::FETCH_COLUMN);

    
    if (isset($_GET['submit'])) {
        $conditions = [];
        $params = [];

        if ($keyword !== '') {
            $conditions[] = 'quote ILIKE ?'; 
            $params[] = '%' . $keyword . '%';
        }

        if ($selected_source !== '') {
            $conditions[] = 'source = ?';
            $params[] = $selected_source;
        }

        $query = 'SELECT id, quote, source, favorite FROM quotes';
        if (!empty($conditions)) {
            $query .= ' WHERE ' . implode(' AND ', $conditions);
        }
        $query .= ' ORDER BY date_entered DESC';

        $stmt_search = $pdo->prepare($query);
        $stmt_search->execute($params);
        $results = $stmt_search->fetchAll();
    }
} catch (PDOException $e) {
    $error_message = 'Có lỗi xảy ra khi kết nối hoặc truy vấn dữ liệu.';
    $reason = $e->getMessage();
}
?>

<!-- Đoạn mã HTML trình bày nội dung trang web. -->
<?php render_page_header(); ?>

<h2>Tìm kiếm Trích dẫn</h2>

<?php if (!empty($error_message)): ?>
    <?php include __DIR__ . '/../partials/show_error.php'; ?>
<?php endif; ?>

<form action="search.php" method="get">
    <p>
        <label for="keyword">Từ khóa tìm kiếm:</label><br>
        <input type="text" name="keyword" id="keyword" value="<?= html_escape($keyword) ?>">
    </p>

    <p>
        <label for="source">Chọn nguồn/tác giả:</label><br>
        <select name="source" id="source">
            <option value="">-- Tất cả các nguồn --</option>
            <?php foreach ($sources as $src): ?>
                <option value="<?= html_escape($src) ?>" <?= $selected_source === $src ? 'selected' : '' ?>>
                    <?= html_escape($src) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </p>

    <p>
        <input type="submit" name="submit" value="Tìm kiếm">
    </p>
</form>

<hr>

<?php if (isset($_GET['submit'])): ?>
    <h3>Kết quả tìm kiếm</h3>

    <?php if (!empty($results)): ?>
        <p>Tìm thấy <strong><?= count($results) ?></strong> trích dẫn phù hợp:</p>
        <?php foreach ($results as $quote): ?>
            <div>
                <blockquote><?= html_escape($quote['quote']) ?></blockquote>
                <p>-- <?= html_escape($quote['source']) ?></p>
                <?php if (!empty($quote['favorite'])): ?>
                    <p><strong> | Yêu thích!</strong></p>
                <?php endif; ?>
            </div>
            <hr>
        <?php endforeach; ?>
    <?php else: ?>
        <p>Không tìm thấy trích dẫn nào phù hợp với điều kiện tìm kiếm.</p>
    <?php endif; ?>
<?php endif; ?>

<?php render_page_footer(); ?>
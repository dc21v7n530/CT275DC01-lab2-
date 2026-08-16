<?php
/* Đoạn mã xử lý PHP. */

define('TITLE', 'Xóa một Trích dẫn');

require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/footer.php';

$has_access = ensure_admin_access();
$error_message = null;
$reason = null;
$quote_details = null;
$delete_complete = false;

if ($has_access) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $quote_id = isset($_POST['id']) && is_numeric($_POST['id']) ? (int)$_POST['id'] : null;

        if (!empty($quote_id)) {
            $query = 'DELETE FROM quotes WHERE id = ?';

            try {
                $pdo = get_database_connection();
                $statement = $pdo->prepare($query);
                $statement->execute([$quote_id]);

                if ($statement->rowCount() === 1) {
                    $delete_complete = true;
                } else {
                    $error_message = 'Không thể xóa trích dẫn này';
                }
            } catch (PDOException $e) {
                $error_message = 'Không thể xóa trích dẫn này';
                $reason = $e->getMessage();
            }
        } else {
            $error_message = 'Không tìm thấy trích dẫn để xóa.';
        }
        } elseif (isset($_GET['id']) && is_numeric($_GET['id']) && (int)$_GET['id'] > 0) {
        $quote_id = (int)$_GET['id'];
        $query = 'SELECT id, quote, source FROM quotes WHERE id = ?';

        try {
            $pdo = get_database_connection();
            $statement = $pdo->prepare($query);
            $statement->execute([$quote_id]);
            $quote_details = $statement->fetch();

            if (!$quote_details) {
                $error_message = 'Không tìm thấy trích dẫn!';
            }
        } catch (PDOException $e) {
            $error_message = 'Không thể lấy thông tin trích dẫn';
            $reason = $e->getMessage();
        }
    } else {
        $error_message = 'Không tìm thấy trích dẫn để xóa.';
    }
} else {
    $error_message = 'Bạn không có quyền truy cập trang này';
}
    

?>

<!--
    Đoạn mã HTML trình bày nội dung trang web.
-->
<?php render_page_header(); ?>

<h2>Xóa một Trích dẫn</h2>

<?php if (!empty($error_message)): ?>
    <?php include __DIR__ . '/../partials/show_error.php'; ?>
<?php endif; ?>

<?php if ($has_access): ?>
    <?php if ($delete_complete): ?>
        <p>Trích dẫn đã bị xóa thành công!</p>
    <?php elseif (!empty($quote_details)): ?>
        <form action="delete_quote.php" method="post">
            <p>Bạn có chắc chắn muốn xóa trích dẫn này không?</p>
            <div>
                <blockquote><?= html_escape($quote_details['quote']) ?></blockquote>
                <p>-- <?= html_escape($quote_details['source']) ?></p>
            </div>
            <input type="hidden" name="id" value="<?= html_escape($quote_details['id']) ?>">
            <p><input type="submit" name="submit" value="Xóa trích dẫn này!"></p>
        </form>
    <?php else: ?>
        <?php include __DIR__ . '/../partials/show_error.php'; ?>
    <?php endif; ?>
<?php else: ?>
    <?php include __DIR__ . '/../partials/show_error.php'; ?>
<?php endif; ?>

<?php render_page_footer(); ?>
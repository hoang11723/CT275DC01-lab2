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
        $quote_id = isset($_POST['id']) && is_numeric($_POST['id']) ? (int) $_POST['id'] : null;

        if (!empty($quote_id)) {
            // Đã bỏ LIMIT 1 để tương thích với PostgreSQL
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
    } elseif (isset($_GET['id']) && is_numeric($_GET['id']) && (int) $_GET['id'] > 0) {
        $quote_id = (int) $_GET['id'];

        $query = 'SELECT id, quote, source, favorite FROM quotes WHERE id = ?';

        try {
            $pdo = get_database_connection();
            $statement = $pdo->prepare($query);
            $statement->execute([$quote_id]);
            $quote_details = $statement->fetch();

            if (!$quote_details) {
                $error_message = 'Không thể lấy trích dẫn này';
            }
        } catch (PDOException $e) {
            $error_message = 'Không thể lấy trích dẫn này';
            $reason = $e->getMessage();
        }
    } else {
        $error_message = 'Không tìm thấy trích dẫn để xóa.';
    }
} else {
    $error_message = 'Bạn không có quyền truy cập trang này';
}
?>

<!-- Đoạn mã HTML trình bày nội dung trang web. -->
<?php render_page_header(); ?>

<h2 style="color: #1e293b; margin-bottom: 20px;">Xóa một Trích dẫn</h2>

<?php if (!empty($error_message)): ?>
    <?php include __DIR__ . '/../partials/show_error.php'; ?>
<?php endif; ?>

<?php if ($delete_complete): ?>

    <div style="background-color: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; padding: 16px; border-radius: 8px; font-weight: 500; max-width: 600px;">
        ✅ Trích dẫn đã bị xóa.
    </div>

<?php elseif ($has_access && !$delete_complete && !empty($quote_details)): ?>

    <form action="delete_quote.php" method="post" style="background: #ffffff; padding: 24px; border-radius: 8px; border: 1px solid #fee2e2; box-shadow: 0 2px 4px rgba(0,0,0,0.05); max-width: 600px;">
        <p style="color: #dc2626; font-weight: bold; font-size: 1.1rem; margin-top: 0; margin-bottom: 16px;">
            ⚠️ Bạn có chắc là muốn xóa trích dẫn này?
        </p>

        <div style="border: 1px solid #e2e8f0; border-left: 5px solid #ef4444; border-radius: 6px; padding: 16px; background-color: #f8fafc; margin-bottom: 20px;">
            <blockquote style="margin: 0 0 10px 0; font-size: 1.05rem; color: #1e293b; font-style: italic; line-height: 1.5;">
                "<?= html_escape($quote_details['quote']) ?>"
            </blockquote>

            <p style="margin: 0; color: #64748b; font-size: 0.95rem;">
                — <strong><?= html_escape($quote_details['source']) ?></strong>
                <?php if (!empty($quote_details['favorite'])): ?>
                    <span style="background-color: #fef3c7; color: #d97706; padding: 2px 8px; border-radius: 12px; font-size: 0.85rem; font-weight: bold; margin-left: 8px;">
                        ⭐ Yêu thích
                    </span>
                <?php endif; ?>
            </p>
        </div>

        <input type="hidden" name="id" value="<?= html_escape((string) $quote_details['id']) ?>">

        <p style="margin: 0;">
            <input type="submit" name="submit" value="Xóa Trích dẫn này!" style="background-color: #dc2626; color: #ffffff; border: none; padding: 10px 20px; border-radius: 6px; font-size: 1rem; font-weight: bold; cursor: pointer; transition: background-color 0.2s;">
        </p>
    </form>

<?php endif; ?>

<?php render_page_footer(); ?>
<?php
/* Đoạn mã xử lý PHP. */

define('TITLE', 'Xem tất cả các Trích dẫn');

require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/footer.php';

$has_access = ensure_admin_access();
$error_message = null;
$reason = null;
$quotes = [];

if ($has_access) {
    $query = 'SELECT id, quote, source, favorite FROM quotes ORDER BY date_entered DESC';

    try {
        $pdo = get_database_connection();
        $statement = $pdo->prepare($query);
        $statement->execute();
        $quotes = $statement->fetchAll();
    } catch (PDOException $e) {
        $error_message = 'Không thể lấy dữ liệu';
        $reason = $e->getMessage();
    }
} else {
    $error_message = 'Bạn không có quyền truy cập trang này';
}
?>

<!-- Đoạn mã HTML trình bày nội dung trang web. -->
<?php render_page_header(); ?>

<h2>Tất cả các Trích dẫn</h2>

<?php if (!empty($error_message)): ?>
    <?php include __DIR__ . '/../partials/show_error.php'; ?>
<?php endif; ?>

<?php if ($has_access && empty($error_message)): ?>
    <?php if (!empty($quotes)): ?>
        <div style="display: flex; flex-direction: column; gap: 16px; margin-top: 20px;">
            <?php foreach ($quotes as $quote): ?>
                <div style="border: 1px solid #e2e8f0; border-left: 5px solid #3b82f6; border-radius: 8px; padding: 16px; background-color: #ffffff; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                    
                    <blockquote style="margin: 0 0 10px 0; font-size: 1.1rem; color: #1e293b; font-style: italic; line-height: 1.5;">
                        "<?= html_escape($quote['quote']) ?>"
                    </blockquote>
                    
                    <p style="margin: 0 0 12px 0; color: #64748b; font-size: 0.95rem;">
                        — <strong><?= html_escape($quote['source']) ?></strong>
                        <?php if (!empty($quote['favorite'])): ?>
                            <span style="background-color: #fef3c7; color: #d97706; padding: 2px 8px; border-radius: 12px; font-size: 0.85rem; font-weight: bold; margin-left: 8px;">
                                ⭐ Yêu thích
                            </span>
                        <?php endif; ?>
                    </p>
                    
                    <div style="border-top: 1px solid #f1f5f9; padding-top: 8px; font-size: 0.9rem; color: #475569;">
                        <strong>Quản trị Trích dẫn:</strong> 
                        <a href="edit_quote.php?id=<?= urlencode($quote['id']) ?>" style="color: #2563eb; text-decoration: none; margin-left: 5px; font-weight: 500;">✏️ Sửa</a>
                        <span style="color: #cbd5e1; margin: 0 6px;">|</span>
                        <a href="delete_quote.php?id=<?= urlencode($quote['id']) ?>" style="color: #dc2626; text-decoration: none; font-weight: 500;">🗑️ Xóa</a>
                    </div>
                    
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p style="color: #64748b; font-style: italic;">Chưa có trích dẫn nào trong hệ thống.</p>
    <?php endif; ?>
<?php endif; ?>

<?php render_page_footer(); ?>
<?php
/* Đoạn mã xử lý PHP. */

define('TITLE', 'Tìm kiếm Trích dẫn');

require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/footer.php';

$error_message = null;
$reason = null;
$sources = [];
$results = [];

$search_keyword = trim($_GET['keyword'] ?? '');
$selected_source = trim($_GET['source'] ?? '');

try {
    $pdo = get_database_connection();

    // Lấy danh sách nguồn/tác giả (duy nhất) để hiển thị trong combobox
    $source_query = 'SELECT DISTINCT source FROM quotes ORDER BY source ASC';
    $source_stmt = $pdo->query($source_query);
    $sources = $source_stmt->fetchAll(PDO::FETCH_COLUMN);

    // Xử lý tìm kiếm khi có từ khóa hoặc nguồn được chọn
    if ($search_keyword !== '' || $selected_source !== '') {
        $conditions = [];
        $params = [];

        if ($search_keyword !== '') {
            $conditions[] = 'quote ILIKE ?';
            $params[] = '%' . $search_keyword . '%';
        }

        if ($selected_source !== '') {
            $conditions[] = 'source = ?';
            $params[] = $selected_source;
        }

        $query = 'SELECT id, quote, source, favorite FROM quotes WHERE ' . implode(' AND ', $conditions) . ' ORDER BY id DESC';
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $results = $stmt->fetchAll();
    }
} catch (PDOException $e) {
    $error_message = 'Không thể tìm kiếm trích dẫn';
    $reason = $e->getMessage();
}
?>

<!-- Đoạn mã HTML trình bày nội dung trang web. -->
<?php render_page_header(); ?>

<h2 style="color: #1e293b; margin-bottom: 20px;">Tìm kiếm Trích dẫn</h2>

<?php if (!empty($error_message)): ?>
    <?php include __DIR__ . '/../partials/show_error.php'; ?>
<?php endif; ?>

<form action="search.php" method="get" style="background: #ffffff; padding: 24px; border-radius: 8px; border: 1px solid #e2e8f0; box-shadow: 0 2px 4px rgba(0,0,0,0.05); max-width: 600px; margin-bottom: 30px;">
    <p style="margin-bottom: 16px;">
        <label style="display: block; font-weight: bold; margin-bottom: 6px; color: #334155;">Từ khóa trích dẫn:
            <input type="text" name="keyword" value="<?= html_escape($search_keyword) ?>" placeholder="Nhập từ khóa..." style="width: 100%; margin-top: 6px; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 1rem; box-sizing: border-box;">
        </label>
    </p>

    <p style="margin-bottom: 20px;">
        <label style="display: block; font-weight: bold; margin-bottom: 6px; color: #334155;">Nguồn / Tác giả:
            <select name="source" style="width: 100%; margin-top: 6px; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 1rem; box-sizing: border-box; background-color: #ffffff;">
                <option value="">-- Tất cả nguồn --</option>
                <?php foreach ($sources as $source): ?>
                    <option value="<?= html_escape($source) ?>" <?= $selected_source === $source ? 'selected' : '' ?>>
                        <?= html_escape($source) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
    </p>

    <p style="margin: 0;">
        <input type="submit" value="Tìm kiếm" style="background-color: #2563eb; color: #ffffff; border: none; padding: 10px 20px; border-radius: 6px; font-size: 1rem; font-weight: bold; cursor: pointer; transition: background-color 0.2s;">
    </p>
</form>

<?php if ($search_keyword !== '' || $selected_source !== ''): ?>
    <h3 style="color: #334155; margin-bottom: 16px;">Kết quả tìm kiếm (<?= count($results) ?>)</h3>

    <?php if (empty($results)): ?>
        <p style="color: #64748b;">Không tìm thấy trích dẫn nào phù hợp.</p>
    <?php else: ?>
        <div style="display: flex; flex-direction: column; gap: 16px; max-width: 600px;">
            <?php foreach ($results as $row): ?>
                <div style="background: #ffffff; border-left: 4px solid #2563eb; border: 1px solid #e2e8f0; border-left-width: 4px; padding: 16px; border-radius: 6px;">
                    <blockquote style="margin: 0 0 10px 0; font-size: 1.05rem; color: #1e293b; font-style: italic;">
                        "<?= html_escape($row['quote']) ?>"
                    </blockquote>
                    <p style="margin: 0; color: #64748b; font-size: 0.95rem;">
                        — <strong><?= html_escape($row['source']) ?></strong>
                        <?php if (!empty($row['favorite'])): ?>
                            <span style="background-color: #fef3c7; color: #d97706; padding: 2px 8px; border-radius: 12px; font-size: 0.85rem; font-weight: bold; margin-left: 8px;">
                                ⭐ Yêu thích
                            </span>
                        <?php endif; ?>
                    </p>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
<?php endif; ?>

<?php render_page_footer(); ?>
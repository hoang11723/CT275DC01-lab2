<?php
/* Đoạn mã xử lý PHP. */

define('TITLE', 'Thêm một Trích dẫn');

require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/footer.php';

$has_access = ensure_admin_access();
$success_message = null;
$error_message = null;
$reason = null;

$form_data = [
    'quote' => trim($_POST['quote'] ?? ''),
    'source' => trim($_POST['source'] ?? ''),
    'favorite' => !empty($_POST['favorite'])
];

if ($has_access && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($form_data['quote'] !== '' && $form_data['source'] !== '') {
        $query = 'INSERT INTO quotes (quote, source, favorite) VALUES (?, ?, ?)';

        try {
            $pdo = get_database_connection();
            $statement = $pdo->prepare($query);
            $statement->bindValue(1, $form_data['quote'], PDO::PARAM_STR);
            $statement->bindValue(2, $form_data['source'], PDO::PARAM_STR);
            $statement->bindValue(3, $form_data['favorite'], PDO::PARAM_BOOL);
            $statement->execute();

            if ($statement->rowCount() === 1) {
                $success_message = 'Trích dẫn của bạn đã được lưu trữ.';
                $form_data = ['quote' => '', 'source' => '', 'favorite' => false];
            } else {
                $error_message = 'Không thể lưu trữ trích dẫn';
            }
        } catch (PDOException $e) {
            $error_message = 'Không thể lưu trữ trích dẫn';
            $reason = $e->getMessage();
        }
    } else {
        $error_message = 'Hãy gõ vào cả Trích dẫn và Nguồn của nó!';
    }
} elseif (!$has_access) {
    $error_message = 'Bạn không có quyền truy cập trang này';
}
?>

<!-- Đoạn mã HTML trình bày nội dung trang web. -->
<?php render_page_header(); ?>

<h2 style="color: #1e293b; margin-bottom: 20px;">Thêm một Trích dẫn</h2>

<?php if (!empty($error_message)): ?>
    <?php include __DIR__ . '/../partials/show_error.php'; ?>
<?php endif; ?>

<?php if ($has_access): ?>
    
    <?php if (!empty($success_message)): ?>
        <div style="background-color: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; padding: 12px 16px; border-radius: 6px; margin-bottom: 20px; font-weight: 500;">
            ✅ <?= html_escape($success_message) ?>
        </div>
    <?php endif; ?>

    <form action="add_quote.php" method="post" style="background: #ffffff; padding: 24px; border-radius: 8px; border: 1px solid #e2e8f0; box-shadow: 0 2px 4px rgba(0,0,0,0.05); max-width: 600px;">
        <p style="margin-bottom: 16px;">
            <label style="display: block; font-weight: bold; margin-bottom: 6px; color: #334155;">Trích dẫn:
                <textarea name="quote" rows="5" cols="30" style="width: 100%; margin-top: 6px; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-family: inherit; font-size: 1rem; box-sizing: border-box;"><?= html_escape($form_data['quote']) ?></textarea>
            </label>
        </p>

        <p style="margin-bottom: 16px;">
            <label style="display: block; font-weight: bold; margin-bottom: 6px; color: #334155;">Nguồn:
                <input type="text" name="source" value="<?= html_escape($form_data['source']) ?>" style="width: 100%; margin-top: 6px; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 1rem; box-sizing: border-box;">
            </label>
        </p>

        <p style="margin-bottom: 20px;">
            <label style="display: flex; align-items: center; gap: 8px; font-weight: 500; color: #334155; cursor: pointer;">
                <input type="checkbox" name="favorite" value="yes" <?= $form_data['favorite'] ? 'checked' : '' ?> style="width: 18px; height: 18px; cursor: pointer;">
                Đây là trích dẫn yêu thích?
            </label>
        </p>

        <p style="margin: 0;">
            <input type="submit" name="submit" value="Thêm Trích dẫn này!" style="background-color: #2563eb; color: #ffffff; border: none; padding: 10px 20px; border-radius: 6px; font-size: 1rem; font-weight: bold; cursor: pointer; transition: background-color 0.2s;">
        </p>
    </form>

<?php endif; ?>

<?php render_page_footer(); ?>
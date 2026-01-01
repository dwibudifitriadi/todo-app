<?php
/**
 * Breadcrumb Component
 * Usage: <?php require_once __DIR__ . '/includes/breadcrumb.php'; ?>
 * 
 * Parameters (pass via $breadcrumb variable):
 * - title: Page title (required)
 * - backUrl: URL to go back to (optional, defaults to index.php)
 * - backLabel: Label for back button (optional, defaults to "← Kembali")
 * - showBack: Whether to show back button (optional, defaults to true)
 * 
 * Example:
 * $breadcrumb = [
 *     'title' => 'Edit Todo',
 *     'backUrl' => 'index.php',
 *     'backLabel' => '← Kembali ke Todo List'
 * ];
 * require_once __DIR__ . '/includes/breadcrumb.php';
 */

$breadcrumb = $breadcrumb ?? [];
$title = $breadcrumb['title'] ?? 'Page';
$backUrl = $breadcrumb['backUrl'] ?? 'index.php';
$backLabel = $breadcrumb['backLabel'] ?? 'Kembali';
$showBack = $breadcrumb['showBack'] ?? true;
$useMarginBottom = $breadcrumb['useMarginBottom'] ?? true;
?>

<div class="breadcrumb-row<?= $useMarginBottom ? ' breadcrumb-spaced' : '' ?>">
    <h1 class="title-xl" style="color: var(--primary);"><?= htmlspecialchars($title) ?></h1>
    <?php if ($showBack): ?>
        <a href="<?= htmlspecialchars($backUrl) ?>" class="neo-btn neo-btn-secondary navbar-button">
            ← <?= htmlspecialchars($backLabel) ?>
        </a>
    <?php endif; ?>
</div>

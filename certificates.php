<?php

declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';

$pageTitle = 'Сертификаты';
$metaDescription = 'Сертификаты и документы о сдаче экзаменов — подтверждённые компетенции в веб-разработке и Bitrix.';
$canonicalPath = page_url('certificates');
$breadcrumbs = [
    ['name' => 'Главная', 'url' => page_url('home')],
    ['name' => 'Сертификаты'],
];

$certificates = db()->query(
    'SELECT * FROM certificates WHERE is_published = 1
     ORDER BY sort_order ASC, issued_at DESC, id DESC'
)->fetchAll();

$itemList = [];
foreach ($certificates as $i => $cert) {
    $entry = [
        '@type' => 'ListItem',
        'position' => $i + 1,
        'name' => $cert['title'],
    ];
    if (!empty($cert['image_path'])) {
        $entry['url'] = absolute_url(upload_url($cert['image_path']));
    }
    $itemList[] = $entry;
}

$schemaGraphs = [
    schema_person(),
    schema_website(),
    schema_breadcrumbs($breadcrumbs),
    [
        '@type' => 'CollectionPage',
        '@id' => absolute_url($canonicalPath) . '#collection',
        'url' => absolute_url($canonicalPath),
        'name' => $pageTitle,
        'description' => $metaDescription,
        'isPartOf' => ['@id' => absolute_url() . '#website'],
        'mainEntity' => [
            '@type' => 'ItemList',
            'itemListElement' => $itemList,
        ],
    ],
];

require __DIR__ . '/templates/header.php';
?>

<section class="page-hero">
  <div class="container">
    <?= render_breadcrumbs($breadcrumbs) ?>
    <p class="eyebrow reveal">Квалификация</p>
    <h1 class="reveal" style="--delay:.06s">Сертификаты</h1>
    <p class="lead reveal" style="--delay:.12s">Документы о сдаче экзаменов и подтверждённых компетенциях.</p>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="cert-grid">
      <?php foreach ($certificates as $i => $cert): ?>
        <article class="cert-item reveal" style="--delay:<?= e((string) ($i * 0.07)) ?>s">
          <div class="cert-preview">
            <?php if ($cert['image_path']): ?>
              <a href="<?= e(upload_url($cert['image_path'])) ?>" target="_blank" rel="noopener">
                <img src="<?= e(upload_url($cert['image_path'])) ?>" alt="<?= e($cert['title']) ?>" loading="lazy" width="480" height="360">
              </a>
            <?php else: ?>
              <div class="media-placeholder media-placeholder--cert" data-label="★"></div>
            <?php endif; ?>
          </div>
          <div class="cert-body">
            <div class="work-meta">
              <?php if ($cert['issuer']): ?><span><?= e($cert['issuer']) ?></span><?php endif; ?>
              <?php if ($cert['issued_at']): ?><span><?= e(format_date($cert['issued_at'])) ?></span><?php endif; ?>
            </div>
            <h3><?= e($cert['title']) ?></h3>
            <?php if ($cert['description']): ?>
              <p><?= e($cert['description']) ?></p>
            <?php endif; ?>
            <?php if ($cert['file_path']): ?>
              <a class="text-link" href="<?= e(upload_url($cert['file_path'])) ?>" target="_blank" rel="noopener">Скачать файл</a>
            <?php endif; ?>
          </div>
        </article>
      <?php endforeach; ?>
      <?php if (!$certificates): ?>
        <p class="empty-note">Добавьте грамоты через административное меню.</p>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php require __DIR__ . '/templates/footer.php'; ?>

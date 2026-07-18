<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <?php $pageTitle = trim($this->renderSection('title')); ?>
    <title><?= esc($pageTitle !== '' ? $pageTitle . ' - CoolHome' : 'CoolHome') ?></title>
    <link rel="stylesheet" href="/css/tailwind.css">
</head>
<body class="min-h-screen bg-gray-50 text-gray-800 antialiased">
    <header class="sticky top-0 z-10 border-b border-gray-200/80 bg-white/85 shadow-sm backdrop-blur">
        <div class="mx-auto flex max-w-[1100px] items-center justify-between px-4 py-3">
            <a href="/homes" class="text-lg font-extrabold tracking-tight text-teal-700">CoolHome</a>
            <a href="/devices" class="rounded-full border border-gray-200 bg-white px-3 py-1 text-sm font-semibold text-gray-600 hover:border-teal-200 hover:text-teal-700">Satellites</a>
        </div>
    </header>

    <main class="mx-auto w-full max-w-[1100px] px-4 py-8">
        <?php $breadcrumb = trim($this->renderSection('breadcrumb')); ?>
        <?php if ($breadcrumb !== ''): ?>
            <div class="mb-4 text-sm text-gray-600">
                <?= $breadcrumb ?>
            </div>
        <?php endif; ?>

        <?= $this->renderSection('content') ?>
    </main>

    <footer class="mt-10 border-t border-gray-200 bg-white/80">
        <div class="mx-auto flex max-w-[1100px] items-center justify-between px-4 py-4 text-sm text-gray-500">
            <span>CoolHome</span>
            <span><?= esc(date('Y')) ?></span>
        </div>
    </footer>

    <?= $this->renderSection('scripts') ?>
</body>
</html>

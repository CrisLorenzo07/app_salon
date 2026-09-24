<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>App Salón</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preload" href="/build/img/image-hero.avif" as="image" type="image/avif">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;700;900<?php echo !empty($useTangerine) ? '&amp;family=Tangerine:wght@700' : ''; ?>&amp;display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/build/css/app.css">
</head>

<body class="<?php echo htmlspecialchars($pageClass ?? '', ENT_QUOTES, 'UTF-8'); ?>">
    <div class="app-container">
        <div class="image"></div>
        <div class="app">
            <?php echo $content ?? ''; ?>
        </div>
    </div>

    <?php
    echo $script ?? '';
    ?>

</body>

</html>

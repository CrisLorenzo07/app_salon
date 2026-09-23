<?php

function debugDump($variable): string
{
    echo "<pre>";
    var_dump($variable);
    echo "</pre>";
    exit;
}

function s($html): string
{
    $s = htmlspecialchars($html);
    return $s;
}

function isAuth(): void
{
    if (!isset($_SESSION['login'])) {
        header('Location: /');
    }
}

function isAdmin(): void
{
    if (empty($_SESSION['login'])) {
        header('Location: /');
        exit;
    }

    if (($_SESSION['admin'] ?? 0) !== 1) {
        header('Location: /cita');
        exit;
    }
}
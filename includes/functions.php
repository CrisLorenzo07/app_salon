<?php

function s($html): string
{
    return htmlspecialchars($html, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function isAuth(): void
{
    if (empty($_SESSION['login'])) {
        header('Location: /');
        exit;
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

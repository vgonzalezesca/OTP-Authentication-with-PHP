<?php

function gesexInlineCss(): string
{
    static $css = null;
    if ($css === null) {
        $css = file_get_contents(__DIR__ . '/../assets/css/style.css');
    }
    return $css;
}

function gesexLogoDataUri(): string
{
    static $dataUri = null;
    if ($dataUri === null) {
        $dataUri = 'data:image/jpeg;base64,' . base64_encode(file_get_contents(__DIR__ . '/../assets/img/gesex-logo.jpg'));
    }
    return $dataUri;
}

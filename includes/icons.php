<?php

/**
 * Lokale Tabler-Icons als Inline-SVG.
 * Quelle und MIT-Lizenz: ../public/assets/licenses/tabler-icons.txt
 */
function motologIcon(string $name, string $class = 'icon'): string
{
    $paths = [
        'motorcycle' => '<path d="M2 16a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" />'
            . '<path d="M16 16a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" />'
            . '<path d="M7.5 14h5l4 -4h-10.5m1.5 4l4 -4" />'
            . '<path d="M13 6h2l1.5 3l2 4" />',
        'users' => '<path d="M5 7a4 4 0 1 0 8 0a4 4 0 1 0 -8 0" />'
            . '<path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" />'
            . '<path d="M16 3.13a4 4 0 0 1 0 7.75" />'
            . '<path d="M21 21v-2a4 4 0 0 0 -3 -3.85" />',
        'wrench' => '<path d="M7 10h3v-3l-3.5 -3.5a6 6 0 0 1 8 8l6 6a2 2 0 0 1 -3 3l-6 -6a6 6 0 0 1 -8 -8l3.5 3.5" />',
        'edit' => '<path d="M4 20h4l10.5 -10.5a2.828 2.828 0 1 0 -4 -4l-10.5 10.5v4" />'
            . '<path d="M13.5 6.5l4 4" />',
        'trash' => '<path d="M4 7l16 0" /><path d="M10 11l0 6" /><path d="M14 11l0 6" />'
            . '<path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" />'
            . '<path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" />',
        'search' => '<path d="M3 10a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" /><path d="M21 21l-6 -6" />',
        'refresh' => '<path d="M20 11a8.1 8.1 0 0 0 -15.5 -2m-.5 -4v4h4" />'
            . '<path d="M4 13a8.1 8.1 0 0 0 15.5 2m.5 4v-4h-4" />',
        'floppy' => '<path d="M6 4h10l4 4v10a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2" />'
            . '<path d="M10 14a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" /><path d="M14 4l0 4l-6 0l0 -4" />',
    ];

    if (!isset($paths[$name])) {
        return '';
    }

    // Nur den Leerraum des Motorrads verkleinern; die Originalpfade bleiben unverändert.
    $viewBox = $name === 'motorcycle' ? '0 3 24 18' : '0 0 24 24';

    return '<svg xmlns="http://www.w3.org/2000/svg" class="' . e($class) . '" viewBox="' . $viewBox . '" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">'
        . $paths[$name]
        . '</svg>';
}

<?php

// Werte sicher für die Ausgabe in HTML escapen.
function e($wert): string
{
    return htmlspecialchars((string) $wert, ENT_QUOTES, 'UTF-8');
}

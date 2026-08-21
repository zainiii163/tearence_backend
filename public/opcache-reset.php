<?php

/**
 * Temporary OPcache clear for shared hosting (no sudo / no php-fpm restart).
 * Visit once: https://api.worldwideadverts.info/opcache-reset.php
 * Then DELETE this file.
 */
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "OPcache cleared\n";
} else {
    echo "OPcache not enabled\n";
}

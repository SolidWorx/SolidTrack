<?php

use App\Kernel;

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

return static function (array $context): Kernel {
    $env = $context['APP_ENV'];
    if (! is_string($env)) {
        throw new LogicException('APP_ENV must be a string, got ' . get_debug_type($env) . '.');
    }

    return new Kernel($env, (bool) $context['APP_DEBUG']);
};

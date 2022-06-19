<?php
declare(strict_types=1);

namespace Mgrn\Scoperender;

abstract class ScopeAbstract implements ScopeInterface
{

    public function scope(string $target): void
    {
        require ($target);
    }
}
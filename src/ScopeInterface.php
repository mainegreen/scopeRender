<?php
declare(strict_types=1);

namespace Mgrn\Scoperender;

interface ScopeInterface
{

    public function setEscaper(EscaperInterface $escaper): void;

    public function hasEscaper(): bool;

    public function escape(): EscaperInterface;

    public function scope(string $target): void;

}
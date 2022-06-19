<?php
declare(strict_types=1);

namespace Mgrn\Scoperender\Test\Scopes;

use Mgrn\Scoperender\Scope;

class SimpleScope extends Scope
{

    protected readonly string $basicString;

    public function __construct(array $properties = [])
    {
        $this->basicString = isset($properties['basicString']) ? $properties['basicString'] : '';
        parent::__construct($properties);
    }

}
<?php
declare(strict_types=1);

namespace Mgrn\Scoperender;

class BasicEscapeJsPatterns extends EscapePatternsAbstract
{

    const BASIC_CHARACTERS_PATTERN = '/([\.]|[\[]|[\]]|[\^]|[\$]|[\|]|[\?]|[\*]|[\+]|[\(]|[\)]|[\\\']|[\"]|[\\\\])/';

    public function __construct()
    {
        $reflect = new \ReflectionClass(__CLASS__);
        $constants = $reflect->getConstants();
        foreach ($constants as $name=>$value) {
            if (is_string($value) && @preg_match($value,'')!==false) {
                $this->offsetSet($name,$value);
            }
        }
    }
}
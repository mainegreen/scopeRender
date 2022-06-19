<?php
declare(strict_types=1);

namespace Mgrn\Scoperender;

/**
 * When rendering in a partial, this class, or more likely a class inheriting from this class with properties set
 * will all that is visible or accessible, and will be available via $this. Aka, you could escape a string with
 * $this->escape()->escapeHtml(value: $someString, preserveFormatters: true). You can add more escaping methods
 * by extending BasicEscaper, and updating the signatures. By making very specific scopes for every partial, you
 * can know what properties and types of said properties are available in your partial being scoped/rendered.
 *
 * A basic constructor is provided that will initialize all public properties that are not initialized if it is
 * found in a passed properties array. Make sure the types match with this default or you will get an exception!
 */
class Scope extends ScopeAbstract
{

    protected ?BasicEscaper $escaper = null;

    public function __construct(array $properties=[])
    {
        $reflector = new \ReflectionClass($this);
        $reflectedProperties = $reflector->getProperties(\ReflectionProperty::IS_PUBLIC | \ReflectionProperty::IS_PROTECTED);
        foreach ($reflectedProperties as $property) {
            if (array_key_exists($property->name,$properties) && !$property->isInitialized($this)) {
                $this->{$property->name} = $properties[$property->name];
            }
        }
    }

    public function setEscaper(EscaperInterface $escaper): void
    {
        if (!is_a($escaper, BasicEscaper::class)) {
            throw new Exception('BasicEscaper not passed to setEscaper');
        }
        if (!is_null($this->escaper)) {
            throw new Exception('Escaper already set in scope');
        }
        $this->escaper = $escaper;
    }

    public function hasEscaper(): bool
    {
        return !is_null($this->escaper);
    }

    public function escape(): BasicEscaper
    {
        return $this->escaper;
    }

    final public function scope(string $target): void
    {
        require ($target);
    }

}
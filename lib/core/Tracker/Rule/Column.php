
<?php

namespace Tiki\Lib\core\Tracker\Rule;

abstract class Column
{
    /** @var string */
    protected string $label;
    /** @var string */
    protected string $argType;
    /** @var array */
    protected array $types;

    /**
     * Column constructor.
     *
     * @param string $label
     * @param string $argType argument type
     * @param array  $types
     */
    public function __construct(string $label, string $argType, array $types)
    {
        $this->label   = $label;
        $this->argType = $argType;
        $this->types   = $types;
    }

    /**
     * Returns the class name as the id
     *
     * @return string
     */
    public function getId(): string
    {
        try {
            $reflection = new \ReflectionClass($this);
            $name = $reflection->getName();
        } catch (\ReflectionException $e) {
            \Feedback::error(tr('Rules reflection error: %0', $e->getMessage()));
            $name = 'error';
        }
        return substr($name, strrpos($name, '\\') + 1);
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return self::class;
    }

    /**
     * @return array
     */
    public function getTypes(): array
    {
        return $this->types;
    }
    abstract public function get();
}
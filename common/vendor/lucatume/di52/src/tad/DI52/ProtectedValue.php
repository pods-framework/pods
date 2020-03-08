<?php

class tad_DI52_ProtectedValue
{

    /**
     * @var mixed
     */
    protected $value;

    /**
     * tad_DI52_ProtectedValue constructor.
     * @param mixed $value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }
}
<?php

/**
 * @license     http://opensource.org/licenses/mit-license.php MIT
 * @link        https://github.com/nicoSWD
 * @since       0.3.4
 * @author      Nicolas Oelgart <nico@oelgart.com>
 */
namespace nicoSWD\Rules;

use Iterator;

/**
 * Class AST
 * @package nicoSWD\Rules
 */
final class AST implements Iterator
{
    /**
     * @var Stack
     */
    protected $stack;

    protected $variables = [];

    /**
     * @param Stack   $stack
     * @param mixed[] $variables
     */
    public function __construct(Stack $stack, array $variables = [])
    {
        $this->stack = $stack;
        $this->stack->rewind();
        $this->variables = $variables;
    }

    /**
     * @return void
     */
    public function next()
    {
        $this->stack->next();
    }

    /**
     * @return bool
     */
    public function valid()
    {
        return $this->stack->valid();
    }

    /**
     * @return Tokens\BaseToken
     */
    public function current()
    {
        $current = $this->stack->current();

        switch (\true) {
            default:
                return $current;
            case $current instanceof Tokens\TokenString:
                return (new AST\NodeString($current))->getNode();
            case $current instanceof Tokens\TokenOpeningArray:
                return (new AST\NodeArray($current))->getNode();
            case $current instanceof Tokens\TokenFunction:
                return (new AST\NodeFunction($current))->getNode();
          //  case $current instanceof Tokens\TokenVariable:
          //      return (new AST\NodeVariable($current))->getNode();
        }
    }

    /**
     * @return mixed
     */
    public function key()
    {
        return $this->stack->key();
    }

    /**
     * @return void
     */
    public function rewind()
    {
        $this->stack->rewind();
    }

    public function getVariable($name)
    {
        return $this->variables[$name];
    }
}

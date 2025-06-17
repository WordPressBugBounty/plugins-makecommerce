<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 * (c) Armin Ronacher
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Modified by makecommerce on 03-March-2025 using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace MakeCommercePrefix\Twig\Node\Expression\Binary;

use MakeCommercePrefix\Twig\Compiler;
use MakeCommercePrefix\Twig\Node\Expression\AbstractExpression;
use MakeCommercePrefix\Twig\Node\Node;

abstract class AbstractBinary extends AbstractExpression implements BinaryInterface
{
    /**
     * @param AbstractExpression $left
     * @param AbstractExpression $right
     */
    public function __construct(Node $left, Node $right, int $lineno)
    {
        if (!$left instanceof AbstractExpression) {
            trigger_deprecation('twig/twig', '3.15', 'Not passing a "%s" instance to the "left" argument of "%s" is deprecated ("%s" given).', AbstractExpression::class, static::class, \get_class($left));
        }
        if (!$right instanceof AbstractExpression) {
            trigger_deprecation('twig/twig', '3.15', 'Not passing a "%s" instance to the "right" argument of "%s" is deprecated ("%s" given).', AbstractExpression::class, static::class, \get_class($right));
        }

        parent::__construct(['left' => $left, 'right' => $right], [], $lineno);
    }

    public function compile(Compiler $compiler): void
    {
        $compiler
            ->raw('(')
            ->subcompile($this->getNode('left'))
            ->raw(' ')
        ;
        $this->operator($compiler);
        $compiler
            ->raw(' ')
            ->subcompile($this->getNode('right'))
            ->raw(')')
        ;
    }

    abstract public function operator(Compiler $compiler): Compiler;
}

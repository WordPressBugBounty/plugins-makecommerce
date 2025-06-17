<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Modified by makecommerce on 03-March-2025 using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace MakeCommercePrefix\Twig\Node\Expression\Ternary;

use MakeCommercePrefix\Twig\Compiler;
use MakeCommercePrefix\Twig\Node\Expression\AbstractExpression;
use MakeCommercePrefix\Twig\Node\Expression\OperatorEscapeInterface;

final class ConditionalTernary extends AbstractExpression implements OperatorEscapeInterface
{
    public function __construct(AbstractExpression $test, AbstractExpression $left, AbstractExpression $right, int $lineno)
    {
        parent::__construct(['test' => $test, 'left' => $left, 'right' => $right], [], $lineno);
    }

    public function compile(Compiler $compiler): void
    {
        $compiler
            ->raw('((')
            ->subcompile($this->getNode('test'))
            ->raw(') ? (')
            ->subcompile($this->getNode('left'))
            ->raw(') : (')
            ->subcompile($this->getNode('right'))
            ->raw('))')
        ;
    }

    public function getOperandNamesToEscape(): array
    {
        return ['left', 'right'];
    }
}

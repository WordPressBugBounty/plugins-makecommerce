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

namespace MakeCommercePrefix\Twig\Node\Expression\Variable;

use MakeCommercePrefix\Twig\Compiler;
use MakeCommercePrefix\Twig\Node\Expression\TempNameExpression;

class TemplateVariable extends TempNameExpression
{
    public function getName(Compiler $compiler): string
    {
        if (null === $this->getAttribute('name')) {
            $this->setAttribute('name', $compiler->getVarName());
        }

        return $this->getAttribute('name');
    }

    public function compile(Compiler $compiler): void
    {
        $name = $this->getName($compiler);

        if ('_self' === $name) {
            $compiler->raw('$this');
        } else {
            $compiler
                ->raw('$macros[')
                ->string($name)
                ->raw(']')
            ;
        }
    }
}

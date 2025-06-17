<?php
/**
 * @license BSD-3-Clause
 *
 * Modified by makecommerce on 03-March-2025 using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace MakeCommercePrefix\Twig\Node\Expression\FunctionNode;

use MakeCommercePrefix\Twig\Compiler;
use MakeCommercePrefix\Twig\Error\SyntaxError;
use MakeCommercePrefix\Twig\Node\Expression\ConstantExpression;
use MakeCommercePrefix\Twig\Node\Expression\FunctionExpression;

class EnumFunction extends FunctionExpression
{
    public function compile(Compiler $compiler): void
    {
        $arguments = $this->getNode('arguments');
        if ($arguments->hasNode('enum')) {
            $firstArgument = $arguments->getNode('enum');
        } elseif ($arguments->hasNode('0')) {
            $firstArgument = $arguments->getNode('0');
        } else {
            $firstArgument = null;
        }

        if (!$firstArgument instanceof ConstantExpression || 1 !== \count($arguments)) {
            parent::compile($compiler);

            return;
        }

        $value = $firstArgument->getAttribute('value');

        if (!\is_string($value)) {
            throw new SyntaxError('The first argument of the "enum" function must be a string.', $this->getTemplateLine(), $this->getSourceContext());
        }

        if (!enum_exists($value)) {
            throw new SyntaxError(\sprintf('The first argument of the "enum" function must be the name of an enum, "%s" given.', $value), $this->getTemplateLine(), $this->getSourceContext());
        }

        if (!$cases = $value::cases()) {
            throw new SyntaxError(\sprintf('The first argument of the "enum" function must be a non-empty enum, "%s" given.', $value), $this->getTemplateLine(), $this->getSourceContext());
        }

        $compiler->raw(\sprintf('%s::%s', $value, $cases[0]->name));
    }
}

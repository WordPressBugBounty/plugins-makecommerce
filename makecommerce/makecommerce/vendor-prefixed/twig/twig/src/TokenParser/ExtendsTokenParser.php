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

namespace MakeCommercePrefix\Twig\TokenParser;

use MakeCommercePrefix\Twig\Error\SyntaxError;
use MakeCommercePrefix\Twig\Node\EmptyNode;
use MakeCommercePrefix\Twig\Node\Node;
use MakeCommercePrefix\Twig\Token;

/**
 * Extends a template by another one.
 *
 *  {% extends "base.html" %}
 *
 * @internal
 */
final class ExtendsTokenParser extends AbstractTokenParser
{
    public function parse(Token $token): Node
    {
        $stream = $this->parser->getStream();

        if ($this->parser->peekBlockStack()) {
            throw new SyntaxError('Cannot use "extend" in a block.', $token->getLine(), $stream->getSourceContext());
        } elseif (!$this->parser->isMainScope()) {
            throw new SyntaxError('Cannot use "extend" in a macro.', $token->getLine(), $stream->getSourceContext());
        }

        $this->parser->setParent($this->parser->getExpressionParser()->parseExpression());

        $stream->expect(Token::BLOCK_END_TYPE);

        return new EmptyNode($token->getLine());
    }

    public function getTag(): string
    {
        return 'extends';
    }
}

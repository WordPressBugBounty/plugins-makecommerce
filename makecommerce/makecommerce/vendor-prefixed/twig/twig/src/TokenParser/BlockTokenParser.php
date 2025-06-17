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
use MakeCommercePrefix\Twig\Node\BlockNode;
use MakeCommercePrefix\Twig\Node\BlockReferenceNode;
use MakeCommercePrefix\Twig\Node\EmptyNode;
use MakeCommercePrefix\Twig\Node\Node;
use MakeCommercePrefix\Twig\Node\Nodes;
use MakeCommercePrefix\Twig\Node\PrintNode;
use MakeCommercePrefix\Twig\Token;

/**
 * Marks a section of a template as being reusable.
 *
 *  {% block head %}
 *    <link rel="stylesheet" href="style.css" />
 *    <title>{% block title %}{% endblock %} - My Webpage</title>
 *  {% endblock %}
 *
 * @internal
 */
final class BlockTokenParser extends AbstractTokenParser
{
    public function parse(Token $token): Node
    {
        $lineno = $token->getLine();
        $stream = $this->parser->getStream();
        $name = $stream->expect(Token::NAME_TYPE)->getValue();
        $this->parser->setBlock($name, $block = new BlockNode($name, new EmptyNode(), $lineno));
        $this->parser->pushLocalScope();
        $this->parser->pushBlockStack($name);

        if ($stream->nextIf(Token::BLOCK_END_TYPE)) {
            $body = $this->parser->subparse([$this, 'decideBlockEnd'], true);
            if ($token = $stream->nextIf(Token::NAME_TYPE)) {
                $value = $token->getValue();

                if ($value != $name) {
                    throw new SyntaxError(\sprintf('Expected endblock for block "%s" (but "%s" given).', $name, $value), $stream->getCurrent()->getLine(), $stream->getSourceContext());
                }
            }
        } else {
            $body = new Nodes([
                new PrintNode($this->parser->getExpressionParser()->parseExpression(), $lineno),
            ]);
        }
        $stream->expect(Token::BLOCK_END_TYPE);

        $block->setNode('body', $body);
        $this->parser->popBlockStack();
        $this->parser->popLocalScope();

        return new BlockReferenceNode($name, $lineno);
    }

    public function decideBlockEnd(Token $token): bool
    {
        return $token->test('endblock');
    }

    public function getTag(): string
    {
        return 'block';
    }
}

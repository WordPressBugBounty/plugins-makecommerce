<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MakeCommercePrefix\Twig\ExpressionParser\Infix;

use MakeCommercePrefix\Twig\ExpressionParser\AbstractExpressionParser;
use MakeCommercePrefix\Twig\ExpressionParser\ExpressionParserDescriptionInterface;
use MakeCommercePrefix\Twig\ExpressionParser\InfixAssociativity;
use MakeCommercePrefix\Twig\ExpressionParser\InfixExpressionParserInterface;
use MakeCommercePrefix\Twig\Node\Expression\AbstractExpression;
use MakeCommercePrefix\Twig\Node\Expression\ArrowFunctionExpression;
use MakeCommercePrefix\Twig\Parser;
use MakeCommercePrefix\Twig\Token;

/**
 * @internal
 */
final class ArrowExpressionParser extends AbstractExpressionParser implements InfixExpressionParserInterface, ExpressionParserDescriptionInterface
{
    public function parse(Parser $parser, AbstractExpression $expr, Token $token): AbstractExpression
    {
        // As the expression of the arrow function is independent from the current precedence, we want a precedence of 0
        return new ArrowFunctionExpression($parser->parseExpression(), $expr, $token->getLine());
    }

    public function getName(): string
    {
        return '=>';
    }

    public function getDescription(): string
    {
        return 'Arrow function (x => expr)';
    }

    public function getPrecedence(): int
    {
        return 250;
    }

    public function getAssociativity(): InfixAssociativity
    {
        return InfixAssociativity::Left;
    }
}

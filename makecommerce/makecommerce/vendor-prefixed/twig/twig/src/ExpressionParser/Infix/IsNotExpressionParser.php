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

use MakeCommercePrefix\Twig\Node\Expression\AbstractExpression;
use MakeCommercePrefix\Twig\Node\Expression\Unary\NotUnary;
use MakeCommercePrefix\Twig\Parser;
use MakeCommercePrefix\Twig\Token;

/**
 * @internal
 */
final class IsNotExpressionParser extends IsExpressionParser
{
    public function parse(Parser $parser, AbstractExpression $expr, Token $token): AbstractExpression
    {
        return new NotUnary(parent::parse($parser, $expr, $token), $token->getLine());
    }

    public function getName(): string
    {
        return 'is not';
    }
}

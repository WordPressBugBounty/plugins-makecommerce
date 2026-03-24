<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MakeCommercePrefix\Twig\ExpressionParser;

use MakeCommercePrefix\Twig\Error\SyntaxError;
use MakeCommercePrefix\Twig\Node\Expression\AbstractExpression;
use MakeCommercePrefix\Twig\Parser;
use MakeCommercePrefix\Twig\Token;

interface PrefixExpressionParserInterface extends ExpressionParserInterface
{
    /**
     * @throws SyntaxError
     */
    public function parse(Parser $parser, Token $token): AbstractExpression;
}

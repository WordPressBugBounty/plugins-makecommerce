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

use MakeCommercePrefix\Twig\Node\Expression\AbstractExpression;
use MakeCommercePrefix\Twig\Parser;
use MakeCommercePrefix\Twig\Token;

interface InfixExpressionParserInterface extends ExpressionParserInterface
{
    public function parse(Parser $parser, AbstractExpression $left, Token $token): AbstractExpression;

    public function getAssociativity(): InfixAssociativity;
}

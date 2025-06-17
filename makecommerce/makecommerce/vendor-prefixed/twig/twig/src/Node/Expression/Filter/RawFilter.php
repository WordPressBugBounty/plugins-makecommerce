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

namespace MakeCommercePrefix\Twig\Node\Expression\Filter;

use MakeCommercePrefix\Twig\Attribute\FirstClassTwigCallableReady;
use MakeCommercePrefix\Twig\Compiler;
use MakeCommercePrefix\Twig\Node\EmptyNode;
use MakeCommercePrefix\Twig\Node\Expression\AbstractExpression;
use MakeCommercePrefix\Twig\Node\Expression\ConstantExpression;
use MakeCommercePrefix\Twig\Node\Expression\FilterExpression;
use MakeCommercePrefix\Twig\Node\Node;
use MakeCommercePrefix\Twig\TwigFilter;

/**
 * @author Fabien Potencier <fabien@symfony.com>
 */
class RawFilter extends FilterExpression
{
    /**
     * @param AbstractExpression $node
     */
    #[FirstClassTwigCallableReady]
    public function __construct(Node $node, TwigFilter|ConstantExpression|null $filter = null, ?Node $arguments = null, int $lineno = 0)
    {
        if (!$node instanceof AbstractExpression) {
            trigger_deprecation('twig/twig', '3.15', 'Not passing a "%s" instance to the "node" argument of "%s" is deprecated ("%s" given).', AbstractExpression::class, static::class, \get_class($node));
        }

        parent::__construct($node, $filter ?: new TwigFilter('raw', null, ['is_safe' => ['all']]), $arguments ?: new EmptyNode(), $lineno ?: $node->getTemplateLine());
    }

    public function compile(Compiler $compiler): void
    {
        $compiler->subcompile($this->getNode('node'));
    }
}

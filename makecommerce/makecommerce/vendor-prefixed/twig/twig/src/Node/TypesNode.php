<?php
/**
 * @license BSD-3-Clause
 *
 * Modified by makecommerce on 03-March-2025 using {@see https://github.com/BrianHenryIE/strauss}.
 */

namespace MakeCommercePrefix\Twig\Node;

use MakeCommercePrefix\Twig\Attribute\YieldReady;
use MakeCommercePrefix\Twig\Compiler;

/**
 * Represents a types node.
 *
 * @author Jeroen Versteeg <jeroen@alisqi.com>
 */
#[YieldReady]
class TypesNode extends Node
{
    /**
     * @param array<string, array{type: string, optional: bool}> $types
     */
    public function __construct(array $types, int $lineno)
    {
        parent::__construct([], ['mapping' => $types], $lineno);
    }

    /**
     * @return void
     */
    public function compile(Compiler $compiler)
    {
        // Don't compile anything.
    }
}

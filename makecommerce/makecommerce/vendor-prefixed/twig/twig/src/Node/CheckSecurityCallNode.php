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

namespace MakeCommercePrefix\Twig\Node;

use MakeCommercePrefix\Twig\Attribute\YieldReady;
use MakeCommercePrefix\Twig\Compiler;

/**
 * @author Fabien Potencier <fabien@symfony.com>
 */
#[YieldReady]
class CheckSecurityCallNode extends Node
{
    /**
     * @return void
     */
    public function compile(Compiler $compiler)
    {
        $compiler
            ->write("\$this->sandbox = \$this->extensions[SandboxExtension::class];\n")
            ->write("\$this->checkSecurity();\n")
        ;
    }
}

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

namespace MakeCommercePrefix\Twig\Sandbox;

use MakeCommercePrefix\Twig\Source;

/**
 * Interface for a class that can optionally enable the sandbox mode based on a template's Twig\Source.
 *
 * @author Yaakov Saxon
 */
interface SourcePolicyInterface
{
    public function enableSandbox(Source $source): bool;
}

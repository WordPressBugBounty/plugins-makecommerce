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

namespace MakeCommercePrefix\Twig\RuntimeLoader;

/**
 * Creates runtime implementations for Twig elements (filters/functions/tests).
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
interface RuntimeLoaderInterface
{
    /**
     * Creates the runtime implementation of a Twig element (filter/function/test).
     *
     * @return object|null The runtime instance or null if the loader does not know how to create the runtime for this class
     */
    public function load(string $class);
}

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

namespace MakeCommercePrefix\Twig\Attribute;

/**
 * Marks nodes that are ready to accept a TwigCallable instead of its name.
 */
#[\Attribute(\Attribute::TARGET_METHOD)]
final class FirstClassTwigCallableReady
{
}

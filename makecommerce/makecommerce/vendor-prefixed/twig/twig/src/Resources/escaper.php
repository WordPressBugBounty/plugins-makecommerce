<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use MakeCommercePrefix\Twig\Environment;
use MakeCommercePrefix\Twig\Extension\EscaperExtension;
use MakeCommercePrefix\Twig\Node\Node;
use MakeCommercePrefix\Twig\Runtime\EscaperRuntime;

/**
 * @internal
 *
 * @deprecated since Twig 3.9
 */
function makecommerceprefix_twig_raw_filter($string)
{
    makecommerceprefix_trigger_deprecation('twig/twig', '3.9', 'Using the internal "%s" function is deprecated.', __FUNCTION__);

    return $string;
}

/**
 * @internal
 *
 * @deprecated since Twig 3.9
 */
function makecommerceprefix_twig_escape_filter(Environment $env, $string, $strategy = 'html', $charset = null, $autoescape = false)
{
    makecommerceprefix_trigger_deprecation('twig/twig', '3.9', 'Using the internal "%s" function is deprecated.', __FUNCTION__);

    return $env->getRuntime(EscaperRuntime::class)->escape($string, $strategy, $charset, $autoescape);
}

/**
 * @internal
 *
 * @deprecated since Twig 3.9
 */
function makecommerceprefix_twig_escape_filter_is_safe(Node $filterArgs)
{
    makecommerceprefix_trigger_deprecation('twig/twig', '3.9', 'Using the internal "%s" function is deprecated.', __FUNCTION__);

    return EscaperExtension::escapeFilterIsSafe($filterArgs);
}

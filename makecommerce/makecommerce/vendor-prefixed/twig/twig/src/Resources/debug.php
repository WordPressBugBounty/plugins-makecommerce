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
use MakeCommercePrefix\Twig\Extension\DebugExtension;

/**
 * @internal
 *
 * @deprecated since Twig 3.9
 */
function makecommerceprefix_twig_var_dump(Environment $env, $context, ...$vars)
{
    makecommerceprefix_trigger_deprecation('twig/twig', '3.9', 'Using the internal "%s" function is deprecated.', __FUNCTION__);

    DebugExtension::dump($env, $context, ...$vars);
}

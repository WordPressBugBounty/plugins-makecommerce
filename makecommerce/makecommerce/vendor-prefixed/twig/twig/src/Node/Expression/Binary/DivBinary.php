<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 * (c) Armin Ronacher
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MakeCommercePrefix\Twig\Node\Expression\Binary;

use MakeCommercePrefix\Twig\Compiler;
use MakeCommercePrefix\Twig\Node\Expression\ReturnNumberInterface;

class DivBinary extends AbstractBinary implements ReturnNumberInterface
{
    public function operator(Compiler $compiler): Compiler
    {
        return $compiler->raw('/');
    }
}

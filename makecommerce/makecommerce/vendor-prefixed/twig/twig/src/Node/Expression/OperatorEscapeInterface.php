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

namespace MakeCommercePrefix\Twig\Node\Expression;

/**
 * Interface implemented by n-ary operators for n > 1.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
interface OperatorEscapeInterface
{
    /**
     * @return string[]
     */
    public function getOperandNamesToEscape(): array;
}

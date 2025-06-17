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

namespace MakeCommercePrefix\Twig\Util;

/**
 * @author Fabien Potencier <fabien@symfony.com>
 */
class TemplateDirIterator extends \IteratorIterator
{
    /**
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function current()
    {
        return file_get_contents(parent::current());
    }

    /**
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function key()
    {
        return (string) parent::key();
    }
}

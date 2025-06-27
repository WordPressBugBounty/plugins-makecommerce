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
use MakeCommercePrefix\Twig\Extension\CoreExtension;

/**
 * @internal
 *
 * @deprecated since Twig 3.9
 */
function makecommerceprefix_twig_cycle($values, $position)
{
    makecommerceprefix_trigger_deprecation('twig/twig', '3.9', 'Using the internal "%s" function is deprecated.', __FUNCTION__);

    return CoreExtension::cycle($values, $position);
}

/**
 * @internal
 *
 * @deprecated since Twig 3.9
 */
function makecommerceprefix_twig_random(Environment $env, $values = null, $max = null)
{
    makecommerceprefix_trigger_deprecation('twig/twig', '3.9', 'Using the internal "%s" function is deprecated.', __FUNCTION__);

    return CoreExtension::random($env->getCharset(), $values, $max);
}

/**
 * @internal
 *
 * @deprecated since Twig 3.9
 */
function makecommerceprefix_twig_date_format_filter(Environment $env, $date, $format = null, $timezone = null)
{
    makecommerceprefix_trigger_deprecation('twig/twig', '3.9', 'Using the internal "%s" function is deprecated.', __FUNCTION__);

    return $env->getExtension(CoreExtension::class)->formatDate($date, $format, $timezone);
}

/**
 * @internal
 *
 * @deprecated since Twig 3.9
 */
function makecommerceprefix_twig_date_modify_filter(Environment $env, $date, $modifier)
{
    makecommerceprefix_trigger_deprecation('twig/twig', '3.9', 'Using the internal "%s" function is deprecated.', __FUNCTION__);

    return $env->getExtension(CoreExtension::class)->modifyDate($date, $modifier);
}

/**
 * @internal
 *
 * @deprecated since Twig 3.9
 */
function makecommerceprefix_twig_sprintf($format, ...$values)
{
    makecommerceprefix_trigger_deprecation('twig/twig', '3.9', 'Using the internal "%s" function is deprecated.', __FUNCTION__);

    return CoreExtension::sprintf($format, ...$values);
}

/**
 * @internal
 *
 * @deprecated since Twig 3.9
 */
function makecommerceprefix_twig_date_converter(Environment $env, $date = null, $timezone = null)
{
    makecommerceprefix_trigger_deprecation('twig/twig', '3.9', 'Using the internal "%s" function is deprecated.', __FUNCTION__);

    return $env->getExtension(CoreExtension::class)->convertDate($date, $timezone);
}

/**
 * @internal
 *
 * @deprecated since Twig 3.9
 */
function makecommerceprefix_twig_replace_filter($str, $from)
{
    makecommerceprefix_trigger_deprecation('twig/twig', '3.9', 'Using the internal "%s" function is deprecated.', __FUNCTION__);

    return CoreExtension::replace($str, $from);
}

/**
 * @internal
 *
 * @deprecated since Twig 3.9
 */
function makecommerceprefix_twig_round($value, $precision = 0, $method = 'common')
{
    makecommerceprefix_trigger_deprecation('twig/twig', '3.9', 'Using the internal "%s" function is deprecated.', __FUNCTION__);

    return CoreExtension::round($value, $precision, $method);
}

/**
 * @internal
 *
 * @deprecated since Twig 3.9
 */
function makecommerceprefix_twig_number_format_filter(Environment $env, $number, $decimal = null, $decimalPoint = null, $thousandSep = null)
{
    makecommerceprefix_trigger_deprecation('twig/twig', '3.9', 'Using the internal "%s" function is deprecated.', __FUNCTION__);

    return $env->getExtension(CoreExtension::class)->formatNumber($number, $decimal, $decimalPoint, $thousandSep);
}

/**
 * @internal
 *
 * @deprecated since Twig 3.9
 */
function makecommerceprefix_twig_urlencode_filter($url)
{
    makecommerceprefix_trigger_deprecation('twig/twig', '3.9', 'Using the internal "%s" function is deprecated.', __FUNCTION__);

    return CoreExtension::urlencode($url);
}

/**
 * @internal
 *
 * @deprecated since Twig 3.9
 */
function makecommerceprefix_twig_array_merge(...$arrays)
{
    makecommerceprefix_trigger_deprecation('twig/twig', '3.9', 'Using the internal "%s" function is deprecated.', __FUNCTION__);

    return CoreExtension::merge(...$arrays);
}

/**
 * @internal
 *
 * @deprecated since Twig 3.9
 */
function makecommerceprefix_twig_slice(Environment $env, $item, $start, $length = null, $preserveKeys = false)
{
    makecommerceprefix_trigger_deprecation('twig/twig', '3.9', 'Using the internal "%s" function is deprecated.', __FUNCTION__);

    return CoreExtension::slice($env->getCharset(), $item, $start, $length, $preserveKeys);
}

/**
 * @internal
 *
 * @deprecated since Twig 3.9
 */
function makecommerceprefix_twig_first(Environment $env, $item)
{
    makecommerceprefix_trigger_deprecation('twig/twig', '3.9', 'Using the internal "%s" function is deprecated.', __FUNCTION__);

    return CoreExtension::first($env->getCharset(), $item);
}

/**
 * @internal
 *
 * @deprecated since Twig 3.9
 */
function makecommerceprefix_twig_last(Environment $env, $item)
{
    makecommerceprefix_trigger_deprecation('twig/twig', '3.9', 'Using the internal "%s" function is deprecated.', __FUNCTION__);

    return CoreExtension::last($env->getCharset(), $item);
}

/**
 * @internal
 *
 * @deprecated since Twig 3.9
 */
function makecommerceprefix_twig_join_filter($value, $glue = '', $and = null)
{
    makecommerceprefix_trigger_deprecation('twig/twig', '3.9', 'Using the internal "%s" function is deprecated.', __FUNCTION__);

    return CoreExtension::join($value, $glue, $and);
}

/**
 * @internal
 *
 * @deprecated since Twig 3.9
 */
function makecommerceprefix_twig_split_filter(Environment $env, $value, $delimiter, $limit = null)
{
    makecommerceprefix_trigger_deprecation('twig/twig', '3.9', 'Using the internal "%s" function is deprecated.', __FUNCTION__);

    return CoreExtension::split($env->getCharset(), $value, $delimiter, $limit);
}

/**
 * @internal
 *
 * @deprecated since Twig 3.9
 */
function makecommerceprefix_twig_get_array_keys_filter($array)
{
    makecommerceprefix_trigger_deprecation('twig/twig', '3.9', 'Using the internal "%s" function is deprecated.', __FUNCTION__);

    return CoreExtension::keys($array);
}

/**
 * @internal
 *
 * @deprecated since Twig 3.9
 */
function makecommerceprefix_twig_reverse_filter(Environment $env, $item, $preserveKeys = false)
{
    makecommerceprefix_trigger_deprecation('twig/twig', '3.9', 'Using the internal "%s" function is deprecated.', __FUNCTION__);

    return CoreExtension::reverse($env->getCharset(), $item, $preserveKeys);
}

/**
 * @internal
 *
 * @deprecated since Twig 3.9
 */
function makecommerceprefix_twig_sort_filter(Environment $env, $array, $arrow = null)
{
    makecommerceprefix_trigger_deprecation('twig/twig', '3.9', 'Using the internal "%s" function is deprecated.', __FUNCTION__);

    return CoreExtension::sort($env, $array, $arrow);
}

/**
 * @internal
 *
 * @deprecated since Twig 3.9
 */
function makecommerceprefix_twig_matches(string $regexp, ?string $str)
{
    makecommerceprefix_trigger_deprecation('twig/twig', '3.9', 'Using the internal "%s" function is deprecated.', __FUNCTION__);

    return CoreExtension::matches($regexp, $str);
}

/**
 * @internal
 *
 * @deprecated since Twig 3.9
 */
function makecommerceprefix_twig_trim_filter($string, $characterMask = null, $side = 'both')
{
    makecommerceprefix_trigger_deprecation('twig/twig', '3.9', 'Using the internal "%s" function is deprecated.', __FUNCTION__);

    return CoreExtension::trim($string, $characterMask, $side);
}

/**
 * @internal
 *
 * @deprecated since Twig 3.9
 */
function makecommerceprefix_twig_nl2br($string)
{
    makecommerceprefix_trigger_deprecation('twig/twig', '3.9', 'Using the internal "%s" function is deprecated.', __FUNCTION__);

    return CoreExtension::nl2br($string);
}

/**
 * @internal
 *
 * @deprecated since Twig 3.9
 */
function makecommerceprefix_twig_spaceless($content)
{
    makecommerceprefix_trigger_deprecation('twig/twig', '3.9', 'Using the internal "%s" function is deprecated.', __FUNCTION__);

    return CoreExtension::spaceless($content);
}

/**
 * @internal
 *
 * @deprecated since Twig 3.9
 */
function makecommerceprefix_twig_convert_encoding($string, $to, $from)
{
    makecommerceprefix_trigger_deprecation('twig/twig', '3.9', 'Using the internal "%s" function is deprecated.', __FUNCTION__);

    return CoreExtension::convertEncoding($string, $to, $from);
}

/**
 * @internal
 *
 * @deprecated since Twig 3.9
 */
function makecommerceprefix_twig_length_filter(Environment $env, $thing)
{
    makecommerceprefix_trigger_deprecation('twig/twig', '3.9', 'Using the internal "%s" function is deprecated.', __FUNCTION__);

    return CoreExtension::length($env->getCharset(), $thing);
}

/**
 * @internal
 *
 * @deprecated since Twig 3.9
 */
function makecommerceprefix_twig_upper_filter(Environment $env, $string)
{
    makecommerceprefix_trigger_deprecation('twig/twig', '3.9', 'Using the internal "%s" function is deprecated.', __FUNCTION__);

    return CoreExtension::upper($env->getCharset(), $string);
}

/**
 * @internal
 *
 * @deprecated since Twig 3.9
 */
function makecommerceprefix_twig_lower_filter(Environment $env, $string)
{
    makecommerceprefix_trigger_deprecation('twig/twig', '3.9', 'Using the internal "%s" function is deprecated.', __FUNCTION__);

    return CoreExtension::lower($env->getCharset(), $string);
}

/**
 * @internal
 *
 * @deprecated since Twig 3.9
 */
function makecommerceprefix_twig_striptags($string, $allowable_tags = null)
{
    makecommerceprefix_trigger_deprecation('twig/twig', '3.9', 'Using the internal "%s" function is deprecated.', __FUNCTION__);

    return CoreExtension::striptags($string, $allowable_tags);
}

/**
 * @internal
 *
 * @deprecated since Twig 3.9
 */
function makecommerceprefix_twig_title_string_filter(Environment $env, $string)
{
    makecommerceprefix_trigger_deprecation('twig/twig', '3.9', 'Using the internal "%s" function is deprecated.', __FUNCTION__);

    return CoreExtension::titleCase($env->getCharset(), $string);
}

/**
 * @internal
 *
 * @deprecated since Twig 3.9
 */
function makecommerceprefix_twig_capitalize_string_filter(Environment $env, $string)
{
    makecommerceprefix_trigger_deprecation('twig/twig', '3.9', 'Using the internal "%s" function is deprecated.', __FUNCTION__);

    return CoreExtension::capitalize($env->getCharset(), $string);
}

/**
 * @internal
 *
 * @deprecated since Twig 3.9
 */
function makecommerceprefix_twig_test_empty($value)
{
    makecommerceprefix_trigger_deprecation('twig/twig', '3.9', 'Using the internal "%s" function is deprecated.', __FUNCTION__);

    return CoreExtension::testEmpty($value);
}

/**
 * @internal
 *
 * @deprecated since Twig 3.9
 */
function makecommerceprefix_twig_test_iterable($value)
{
    makecommerceprefix_trigger_deprecation('twig/twig', '3.9', 'Using the internal "%s" function is deprecated.', __FUNCTION__);

    return is_iterable($value);
}

/**
 * @internal
 *
 * @deprecated since Twig 3.9
 */
function makecommerceprefix_twig_include(Environment $env, $context, $template, $variables = [], $withContext = true, $ignoreMissing = false, $sandboxed = false)
{
    makecommerceprefix_trigger_deprecation('twig/twig', '3.9', 'Using the internal "%s" function is deprecated.', __FUNCTION__);

    return CoreExtension::include($env, $context, $template, $variables, $withContext, $ignoreMissing, $sandboxed);
}

/**
 * @internal
 *
 * @deprecated since Twig 3.9
 */
function makecommerceprefix_twig_source(Environment $env, $name, $ignoreMissing = false)
{
    makecommerceprefix_trigger_deprecation('twig/twig', '3.9', 'Using the internal "%s" function is deprecated.', __FUNCTION__);

    return CoreExtension::source($env, $name, $ignoreMissing);
}

/**
 * @internal
 *
 * @deprecated since Twig 3.9
 */
function makecommerceprefix_twig_constant($constant, $object = null)
{
    makecommerceprefix_trigger_deprecation('twig/twig', '3.9', 'Using the internal "%s" function is deprecated.', __FUNCTION__);

    return CoreExtension::constant($constant, $object);
}

/**
 * @internal
 *
 * @deprecated since Twig 3.9
 */
function makecommerceprefix_twig_constant_is_defined($constant, $object = null)
{
    makecommerceprefix_trigger_deprecation('twig/twig', '3.9', 'Using the internal "%s" function is deprecated.', __FUNCTION__);

    return CoreExtension::constant($constant, $object, true);
}

/**
 * @internal
 *
 * @deprecated since Twig 3.9
 */
function makecommerceprefix_twig_array_batch($items, $size, $fill = null, $preserveKeys = true)
{
    makecommerceprefix_trigger_deprecation('twig/twig', '3.9', 'Using the internal "%s" function is deprecated.', __FUNCTION__);

    return CoreExtension::batch($items, $size, $fill, $preserveKeys);
}

/**
 * @internal
 *
 * @deprecated since Twig 3.9
 */
function makecommerceprefix_twig_array_column($array, $name, $index = null): array
{
    makecommerceprefix_trigger_deprecation('twig/twig', '3.9', 'Using the internal "%s" function is deprecated.', __FUNCTION__);

    return CoreExtension::column($array, $name, $index);
}

/**
 * @internal
 *
 * @deprecated since Twig 3.9
 */
function makecommerceprefix_twig_array_filter(Environment $env, $array, $arrow)
{
    makecommerceprefix_trigger_deprecation('twig/twig', '3.9', 'Using the internal "%s" function is deprecated.', __FUNCTION__);

    return CoreExtension::filter($env, $array, $arrow);
}

/**
 * @internal
 *
 * @deprecated since Twig 3.9
 */
function makecommerceprefix_twig_array_map(Environment $env, $array, $arrow)
{
    makecommerceprefix_trigger_deprecation('twig/twig', '3.9', 'Using the internal "%s" function is deprecated.', __FUNCTION__);

    return CoreExtension::map($env, $array, $arrow);
}

/**
 * @internal
 *
 * @deprecated since Twig 3.9
 */
function makecommerceprefix_twig_array_reduce(Environment $env, $array, $arrow, $initial = null)
{
    makecommerceprefix_trigger_deprecation('twig/twig', '3.9', 'Using the internal "%s" function is deprecated.', __FUNCTION__);

    return CoreExtension::reduce($env, $array, $arrow, $initial);
}

/**
 * @internal
 *
 * @deprecated since Twig 3.9
 */
function makecommerceprefix_twig_array_some(Environment $env, $array, $arrow)
{
    makecommerceprefix_trigger_deprecation('twig/twig', '3.9', 'Using the internal "%s" function is deprecated.', __FUNCTION__);

    return CoreExtension::arraySome($env, $array, $arrow);
}

/**
 * @internal
 *
 * @deprecated since Twig 3.9
 */
function makecommerceprefix_twig_array_every(Environment $env, $array, $arrow)
{
    makecommerceprefix_trigger_deprecation('twig/twig', '3.9', 'Using the internal "%s" function is deprecated.', __FUNCTION__);

    return CoreExtension::arrayEvery($env, $array, $arrow);
}

/**
 * @internal
 *
 * @deprecated since Twig 3.9
 */
function makecommerceprefix_twig_check_arrow_in_sandbox(Environment $env, $arrow, $thing, $type)
{
    makecommerceprefix_trigger_deprecation('twig/twig', '3.9', 'Using the internal "%s" function is deprecated.', __FUNCTION__);

    CoreExtension::checkArrow($env, $arrow, $thing, $type);
}

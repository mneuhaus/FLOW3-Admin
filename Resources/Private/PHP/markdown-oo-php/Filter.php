<?php
/**
 * Copyright (C) 2011, Maxim S. Tsepkov
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

/**
 * Superclass of all filters.
 *
 * Provides static methods to configure and use filtering system.
 *
 * @package markdown-oo-php
 * @subpackage Filter
 * @author Max Tsepkov <max@garygolden.me> http://garygolden.me
 * @version 0.9
 *
 */
abstract class Markdown_Filter
{
    /**
     * Flag indicates whether to append Markdown_Filter_Fallback to filter list.
     *
     * @var bool
     */
    protected static $_useFallbackFilter = true;

    /**
     *
     * @var array
     */
    protected static $_defaultFilters = array(
        'Blockquote',
        'Code',
        'Emphasis',
        'HeaderAtx',
        'HeaderSetext',
        'Hr',
        'Img',
        'Linebreak',
        'ListBulleted',
        'ListNumbered',
        'CustomBlock',
        'Paragraph'
    );

    /**
     * Lookup Markdown_Filter_{$filtername} class and return its instance.
     *
     * @throws InvalidArgumentException
     * @param string $filtername
     * @return Markdown_Filter
     */
    public static function factory($filtername)
    {
        if (is_string($filtername) && ctype_alnum($filtername)) {
            $file  = __DIR__ . '/Filter/' . $filtername . '.php';
            $class = 'Markdown_Filter_'   . $filtername;

            if (is_readable($file)) {
                require_once $file;

                if (class_exists($class)) {
                    return new $class;
                }
                else {
                    throw new InvalidArgumentException(
                        'Could not find class ' . $class
                    );
                }
            }
            else {
                throw new InvalidArgumentException($file . ' is not readable');
            }
        }
        else {
            throw new InvalidArgumentException(sprintf(
                '$filtername must be an alphanumeric string, <%s> given.',
                gettype($filtername)
            ));
        }
    }

    /**
     * @return array
     */
    public static function getDefaultFilters()
    {
        return self::$_defaultFilters;
    }

    /**
     * @param array $filters
     * @return Markdown_Filter
     */
    public static function setDefaultFilters(array $filters)
    {
        self::$_defaultFilters = $filters;
    }

    /**
     * Enable/disable original markdown perl script usage.
     * Returns current settings if called without parameter.
     *
     * @param bool $flag optional
     * @return boolean
     */
    public static function useFallbackFilter($flag = null)
    {
        if ($flag === null) {
            return self::$_useFallbackFilter;
        }
        else {
            self::$_useFallbackFilter = (bool) $flag;
            return self::$_useFallbackFilter;
        }
    }

    /**
     * Pass given $text through $filters chain and return result.
     * Use default filters in no $filters given.
     *
     * @param string $text
     * @param array $filters optional
     * @return string
     */
    public static function run($text, array $filters = null)
    {
        if ($filters === null) {
            $filters = self::getDefaultFilters();
            if (self::useFallbackFilter()) {
                $filters[] = self::factory('Fallback');
            }
        }

        foreach ($filters as $filter) {
            if ($filter instanceof Markdown_Filter) {
                // do nothing
            }
            elseif (is_string($filter)) {
                $filter = self::factory($filter);
            }
            else {
                throw new InvalidArgumentException(
                    '$filters must be an array which elements ' .
                    'is either a string or Markdown_Filter'
                );
            }

            $text = $filter->transform($text);
        }

        return $text;
    }

    abstract public function transform($text);
}

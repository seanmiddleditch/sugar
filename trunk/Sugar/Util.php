<?php
/**
 * PHP-Sugar Template Engine
 *
 * Copyright (c) 2007  AwesomePlay Productions, Inc. and
 * contributors.  All rights reserved.
 *
 * LICENSE:
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
 *
 * @package Sugar
 * @author Sean Middleditch <sean@awesomeplay.com>
 * @copyright 2007 AwesomePlay Productions, Inc. and contributors
 * @license http://opensource.org/licenses/mit-license.php MIT
 */

/**
 * Namespace for utility functions useful in Sugar functions.
 *
 * @package Sugar
 */
class SugarUtil {
    /**
     * Returns an argument from a function parameter list, supporting both
     * position and named parameters and default values.
     *
     * @param array $params Function parameter list.
     * @param string $name Parameter name.
     * @param int $index Parameter position.
     * @param mixed $default Default value if parameter is not specified.
     * @return mixed Value of parameter if given, or the default value otherwise.
     */
    public static function getArg ($params, $name, $index = -1, $default = null) {
        if (isset($params[$name]))
            return $params[$name];
        elseif ($index >= 0 && isset($params[$index]))
            return $params[$index];
        else
            return $default;
    }

    /**
     * Checks if an array is a "vector," or an array with only integral
     * indexes starting at zero and incrementally increasing.  Used only
     * for nice exporting to JavaScript.
     *
     * Only really used for {@link SugarUtil::jsValue}.
     *
     * @param array $array Array to check.
     * @return bool True if array is a vector.
     */
    public static function isVector ($array) {
        if (!is_array($array))
            return false;
        $next = 0;
        foreach ($array as $k=>$v) {
            if ($k !== $next)
                return false;
            ++$next;
        }
        return true;
    }

    /**
     * Formats a PHP value in JavaScript format.
     *
     * We can probably juse use json_encode() instead of this, except
     * json_encode() is PHP 5.2 only.
     *
     * @param mixed $value Value to format.
     * @return string Formatted result.
     */
    public static function jsValue ($value) {
        switch (gettype($value)) {
            case 'integer':
            case 'float':
                return $value;
            case 'array':
                if (SugarUtil::isVector($value))
                    return '['.implode(',', array_map(array('SugarUtil', 'jsValue'), $value)).']';

                $result = '{';
                $first = true;
                foreach($value as $k=>$v) {
                    if (!$first)
                        $result .= ',';
                    else
                        $first = false;
                    $result .= SugarUtil::jsValue($k).':'.SugarUtil::jsValue($v);
                }
                $result .= '}';
                return $result;
            case 'object':
                $result = '{\'phpType\':'.SugarUtil::jsValue(get_class($value));
                foreach(get_object_vars($value) as $k=>$v)
                    $result .= ',' . SugarUtil::jsValue($k).':'.SugarUtil::jsValue($v);
                $result .= '}';
                return $result;
            case 'null':
                return 'null';
            default:
                return "'".str_replace(array("\n", "\r", "\r\n"), '\\n', addslashes($value))."'";
        }
    }

    /**
     * Convert a value into a timestamp.  This is essentially strtotime(),
     * except that if an integer timestamp is passed in it is returned
     * verbatim, and if the value cannot be parsed, it returns the current
     * timestamp.
     *
     * @param mixed $value Time value to parse.
     * @return int Timestamp.
     */
    function valueToTime ($value) {
        // raw int?  it's a timestamp
        if (is_int($value))
            return $value;
        // otherwise, convert it with strtotime
        elseif (is_string($value))
            return strtotime($value);
        // something... use current time
        else
            return time();
    }
}
// vim: set expandtab shiftwidth=4 tabstop=4 : ?>
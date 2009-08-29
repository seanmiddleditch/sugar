<?php
/**
 * Cache driver interface.
 *
 * Defines the interface to be used by cache drivers.  Different cache
 * drivers can store and load cache files in different manners.  For example,
 * an application may wish to use a database storage engine for its cache
 * files instead of the filesystem.
 *
 * PHP version 5
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
 * @category Template
 * @package Sugar
 * @subpackage Drivers
 * @author Sean Middleditch <sean@mojodo.com>
 * @copyright 2008,2009 Mojodo, Inc. and contributors
 * @license http://opensource.org/licenses/mit-license.php MIT
 * @version 0.82
 * @link http://php-sugar.net
 */

/**
 * Interface for Sugar cache drivers.  These are used for storing and
 * retrieving bytecode and HTML caches.
 *
 * @category Template
 * @package Sugar
 * @subpackage Drivers
 * @author Sean Middleditch <sean@mojodo.com>
 * @copyright 2008,2009 Mojodo, Inc. and contributors
 * @license http://opensource.org/licenses/mit-license.php MIT
 * @version 0.82
 * @link http://php-sugar.net
 */
interface ISugarCache
{
    /**
     * Returns the timestamp for the given reference, or zero if the file
     * is not in the cache.
     *
     * @param SugarRef $ref File reference to lookup.
     * @param string $type Either 'ctpl' or 'chtml'.
     * @return int Timestamp, or 0 if the file does not exist.
     * @abstract
     */
    function stamp(SugarRef $ref, $type);

    /**
     * Returns the bytecode for the requested reference.
     *
     * @param SugarRef $ref File reference to lookup.
     * @param string $type Either 'ctpl' or 'chtml'.
     * @return array Bytecode, or false if not in the cache.
     * @abstract
     */
    function load(SugarRef $ref, $type);

    /**
     * Adds the bytecode to the cache.
     *
     * @param SugarRef $ref File reference to lookup.
     * @param string $type Either 'ctpl' or 'chtml'.
     * @param array $data Bytecode.
     * @abstract
     */
    function store(SugarRef $ref, $type, $data);

    /**
     * Erases the bytecode for the requested reference.
     *
     * @param SugarRef $ref File reference for the bytecode to erase.
     * @param string $type Either 'ctpl' or 'chtml'.
     * @abstract
     */
    function erase(SugarRef $ref, $type);

    /**
     * Clears all caches the driver is responsible for.
     *
     * @abstract
     */
    function clear();
}
// vim: set expandtab shiftwidth=4 tabstop=4 :
?>

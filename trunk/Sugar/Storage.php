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
 * @subpackage Drivers
 * @author Sean Middleditch <sean@awesomeplay.com>
 * @copyright 2007 AwesomePlay Productions, Inc. and contributors
 * @license http://opensource.org/licenses/mit-license.php MIT
 */

/**
 * Interface for storage drivers.  These are used to load template from
 * different resources, such as the file system or a database.
 *
 * @package Sugar
 * @subpackage Drivers
 */
interface ISugarStorage {
    /**
     * Returns the timestamp of the reference, or 0 if the reference does
     * not exist.
     *
     * @param SugarRef $ref Reference to lookup.
     * @return int Timestamp if it exists, or zero if it cannot be found.
     */
    function stamp (SugarRef $ref);

    /**
     * Returns the source for the requested reference.
     *
     * @param SugarRef $ref Reference to lookup.
     * @return string Source of reference.
     */
    function load (SugarRef $ref);

    /**
     * Returns a path name for the reference, mapped as appropriate for
     * the driver.  This is used for error messages.  The result should
     * make it easier for the template writer to identify which template
     * is being refrenced.  Returning {@link SugarRef::$full} may be
     * adequate for many drivers.
     *
     * @param SugarRef $ref Reference to lookup.
     * @return string User-friendly path to reference.
     */
    function path (SugarRef $ref);
}
// vim: set expandtab shiftwidth=4 tabstop=4 : ?>
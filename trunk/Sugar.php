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
 * Directory in which PHP-Sugar is installed.  Used for including
 * additional PHP-Sugar source files.
 */
define('SUGAR_ROOTDIR', dirname(__FILE__));

/**#@+
 * Core includes.
 */
require_once SUGAR_ROOTDIR.'/Sugar/Exception.php';
require_once SUGAR_ROOTDIR.'/Sugar/Ref.php';
require_once SUGAR_ROOTDIR.'/Sugar/Storage.php';
require_once SUGAR_ROOTDIR.'/Sugar/Cache.php';
/**#@-*/

/**#@+
 * Drivers.
 */
require_once SUGAR_ROOTDIR.'/Sugar/StorageFile.php';
require_once SUGAR_ROOTDIR.'/Sugar/CacheFile.php';
/**#@-*/

/**#@+
 * Utility includes.
 */
require_once SUGAR_ROOTDIR.'/Sugar/Escaped.php';
require_once SUGAR_ROOTDIR.'/Sugar/Util.php';
/**#@-*/

/**
 * PHP-Sugar Standard Library.
 */
require_once SUGAR_ROOTDIR.'/Sugar/Stdlib.php';

/**
 * Version of PHP-Sugar.
 */
define('SUGAR_VERSION', '0.72');

/**
 * Pass this flag to {@link Sugar::register} to indicate that the function
 * uses the native PHP function call syntax, instead of the PHP-Sugar
 * syntax.
 */
define('SUGAR_FUNC_NATIVE', 1);

/**
 * Pass this flag to {@link Sugar::register} to indicate that the return
 * value of the function should not be printed by default when called as a
 * top-level function.  This flag has no effect when the function is called
 * as part of an expression.
 */
define('SUGAR_FUNC_SUPPRESS_RETURN', 2);

/**
 * Passed to cache drivers to indicate that a compile cache is requested.
 */
define('SUGAR_CACHE_TPL', 'ctpl');

/**
 * Passed to cache drivers to indicate that an HTML cache is requested.
 */
define('SUGAR_CACHE_HTML', 'chtml');

/**
 * Causes all errors generated by Sugar templates to be printed to the user.
 * No indication of the error is returned to the calling script.  This is
 * the default behavior.
 */
define('SUGAR_ERROR_PRINT', 1);

/**
 * Errors will be thrown as {@link SugarException} objects.
 */
define('SUGAR_ERROR_THROW', 2);

/**
 * The error will be printed to the user, and then die() will be called to
 * terminate the script.
 */
define('SUGAR_ERROR_DIE', 3);

/**
 * The error will be silently ignored.
 */
define('SUGAR_ERROR_IGNORE', 4);

/**
 * All output will be escaped using htmlentities() with the
 * ENT_QUOTES flag set, using the {@link Sugar::$charset} setting.  This
 * is the default behavior.
 */
define('SUGAR_OUTPUT_HTML', 1);

/**
 * Identical to {@link SUGAR_OUTPUT_HTML}.
 */
define('SUGAR_OUTPUT_XHTML', 2);

/**
 * All output will be escaped using htmlspecialchars() with the
 * ENT_QUOTES flag set, using the {@link Sugar::$charset} setting.  This
 * differs from {@link SUGAR_OUTPUT_HTML} as only <, >, ", ', and & will
 * escaped.
 */
define('SUGAR_OUTPUT_XML', 3);

/**
 * Disables all output escaping.
 */
define('SUGAR_OUTPUT_TEXT', 4);
/**#@-*/

/**
 * PHP-Sugar core class.
 *
 * Instantiate this class to use PHP-Sugar.
 * @package Sugar
 */
class Sugar {
    /**
     * Stack of variable sets.  Each template invocation creates a new
     * entry on the stack, ensuring that templates cannot subvert the
     * environment of their caller.
     *
     * @var array
     */
    private $vars = array(array());

    /**
     * Map of all registered functions.  The key is the function name,
     * and the value is an array containing the callback and function
     * flags.
     */
    private $funcs = array();

    /**
     * Cache of files loaded into memory.
     */
    private $files = array();

    /**
     * A map of storage drivers.  The key is the storage driver name,
     * and the value is the storage driver object.
     */
    public $storage = array();

    /**
     * Cache management.  Used internally.
     *
     * @var SugarCacheHandler
     */
    public $cacheHandler = null;

    /**
     * This is the cache driver to use for storing bytecode and HTML caches.
     * This is initialized to the {@link SugarCacheFile} driver by default.
     *
     * @var ISugarCache
     */
    public $cache = null;

    /**
     * Setting this to true will disable all caching, forcing every template
     * to be recompiled and executed on every load.
     *
     * @var bool
     */
    public $debug = false;

    /**
     * Setting this to true will allow for methods to be called on object
     * variables within templates.  This is disabled by default for security
     * reasons.
     *
     * @var bool
     */
    public $methods = false;

    /**
     * This is the error handling method Sugar should use.  By default,
     * errors are echoed to the screen and no exceptions are thrown.  Set
     * this to one of the following:
     * - {@link SUGAR_ERROR_THROW}: throw SugarException objects
     * - {@link SUGAR_ERROR_PRINT}: print an error message (default)
     * - {@link SUGAR_ERROR_DIE}: terminate the script
     * - {@link SUGAR_ERROR_IGNORE}: do nothing
     *
     * @var int
     */
    public $errors = SUGAR_ERROR_PRINT;

    /**
     * This is the output escaping method to be used.  This is necessary
     * for many formats, such as XML and HTML, to ensure that special
     * are escaped properly.
     * - {@link SUGAR_OUTPUT_HTML}: escape HTML special characters (default)
     * - {@link SUGAR_OUTPUT_XHTML}: equivalent to SUGAR_OUTPUT_HTML
     * - {@link SUGAR_OUTPUT_XML}: escapes XML special characters
     * - {@link SUGAR_OUTPUT_TEXT}: no escaping is performed
     *
     * @var int
     */
    public $output = SUGAR_OUTPUT_HTML;

    /**
     * This is the default storage driver to use when no storage driver
     * is specified as part of a template name.
     *
     * @var string
     */
    public $defaultStorage = 'file';

    /**
     * Maximum age of HTML caches in seconds.
     *
     * @var int
     */
    public $cacheLimit = 3600; // one hour

    /**
     * Directory in which templates can be found when using the file storage
     * driver.
     *
     * @var string
     */
    public $templateDir = './templates';

    /**
     * Directory in which bytecode and HTML caches can be stored when using
     * the file cache driver.
     *
     * @var string
     */
    public $cacheDir = './templates/cache';

    /**
     * Character set that output should be in.
     *
     * @var string
     */
    public $charset = 'ISO-8859-1';

    /**
     * Constructor
     */
    public function __construct () {
        $this->storage ['file']= new SugarStorageFile($this);
        $this->cache = new SugarCacheFile($this);

        SugarStdlib::initialize($this);
    }

    /**
     * Set a new variable to be available within templates.
     *
     * @param string $name The variable's name.
     * @param mixed $value The variable's value.
     * @return bool true on success
     */
    public function set ($name, $value) {
        $name = strtolower($name);
        $this->vars[count($this->vars)-1] [$name]= $value;
        return true;
    }

    /**
     * Registers a new function to be available within templates.
     *
     * @param string $name The name to register the function under.
     * @param callback $invoke Optional PHP callback; if null, the $name parameter is used as the callback.
     * @param int $flags Bitset including {@link SUGAR_FUNC_SUPPRESS_RETURN} or {@link SUGAR_FUNC_NATIVE}.
     * @return bool true on success
     */
    public function register ($name, $invoke=null, $flags=0) {
        if (!$invoke)
            $invoke = $name;
        $this->funcs [strtolower($name)]= array($invoke, $flags);
        return true;
    }

    /**
     * Registers a list of functions to be available within templates.
     *
     * The input array is an associative array mapping the function name
     * to an array consisting of the callback in index 0 (zero) and the
     * function flags in index 1.  Functions with no flags must pass in
     * a 0 in index 1.
     *
     * @param array $funcs The list of functions to register.
     * @return bool true on success
     * @internal
     */
    public function registerList (array $funcs) {
        $this->funcs = array_merge($this->funcs, $funcs);
    }

    /**
     * Looks up the current value of a variable.
     *
     * @param string $name Name of the variable to lookup.
     * @return mixed
     */
    public function getVariable ($name) {
        $name = strtolower($name);
        for ($i = count($this->vars)-1; $i >= 0; --$i)
            if (array_key_exists($name, $this->vars[$i]))
                return $this->vars[$i][$name];
        return null;
    }

    /**
     * Returns an array containing the data for a registered function.  The
     * first field of the array is the callback, and the second field are
     * the function flags.
     *
     * @param string $name Function name to lookup.
     * @return array
     */
    public function getFunction ($name) {
        return $this->funcs[strtolower($name)];
    }

    /**
     * Register a new storage driver.
     *
     * @param string $name Name to register driver under, used in template references.
     * @param ISugarStorage $driver Driver object to register.
     * @return bool true on success
     */
    public function addStorage ($name, ISugarStorage $driver) {
        $this->storage [$name]= $driver;
        return true;
    }

    /**
     * Escape the input string according to the current value of {@link Sugar::$charset}.
     *
     * @param string $output String to escape.
     * @return string Escaped output.
     */
    public function escape ($output) {
        // do not escape for raw values - just return text
        if (is_a($output, 'SugarEscaped'))
            return $output->getText();

        // perform proper escaping for current mode
        switch ($this->output) {
            case SUGAR_OUTPUT_HTML:
            case SUGAR_OUTPUT_XHTML:
                return htmlentities($output, ENT_QUOTES, $this->charset);
            case SUGAR_OUTPUT_XML:
                return htmlspecialchars($output, ENT_QUOTES, $this->charset);
            case SUGAR_OUTPUT_TEXT:
            default:
                return $output;
        }
    }

    /**
     * Process a {@link SugarException} according to the current value of {@link Sugar::$errors}.
     *
     * @param SugarException $e Exception to process.
     */
    public function handleError (SugarException $e) {
        // if in throw mode, re-throw the exception
        if ($this->errors == SUGAR_ERROR_THROW)
            throw $e;

        // if in ignore mode, just return
        if ($this->errors == SUGAR_ERROR_IGNORE)
            return;

        // print the error
        echo "\n[[ ".$this->escape(get_class($e)).': '.$this->escape($e->getMessage())." ]]\n";

        // die if in die mode
        if ($this->errors == SUGAR_ERROR_DIE)
            die();
    }

    /**
     * Execute Sugar bytecode.
     *
     * @param array $data Bytecode.
     * @return mixed Return value of bytecode.
     */
    private function execute (array $data) {
        // create new domain
        $this->vars []= array();

        try {
            /**
             * Runtime.
             */
            require_once SUGAR_ROOTDIR.'/Sugar/Runtime.php';

            // execute bytecode
            $rs = SugarRuntime::execute($this, $data['bytecode']);

            // cleanup
            array_pop($this->vars);
            return $rs;
        } catch (Exception $e) {
            // cleanup
            array_pop($this->vars);
            throw $e;
        }
    }

    /**
     * Load the requested template, compile it if necessary, and then
     * execute the bytecode.
     *
     * @param SugarRed $ref The template to load.
     */
    private function loadExecute (SugarRef $ref) {
        // check template exists, and remember stamp
        $sstamp = $ref->storage->stamp($ref);
        if ($sstamp === false)
            throw new SugarException('template not found: '.$ref->full);

        // cache file ref
        if ($this->cacheHandler)
            $this->cacheHandler->addRef($ref);

        // if debug is off and the stamp is good, load compiled version
        $cstamp = $this->cache->stamp($ref, SUGAR_CACHE_TPL);
        if (!$this->debug && $cstamp !== false && $cstamp > $sstamp) {
            $data = $this->cache->load($ref, SUGAR_CACHE_TPL);
            // if version checks out, run it
            if ($data !== false && $data['version'] == SUGAR_VERSION) {
                $this->execute($data);
                return;
            }
        }

        /**
         * Compiler.
         */
        require_once SUGAR_ROOTDIR.'/Sugar/Parser.php';

        // compile
        $source = $ref->storage->load($ref);
        if ($source === false)
            throw new SugarException('template not found: '.$ref->full);
        $parser = new SugarParser($this);
        $data = $parser->compile($source, $ref->storage->path($ref));
        $parser = null;

        // store
        $this->cache->store($ref, SUGAR_CACHE_TPL, $data);

        // execute
        $this->execute($data);
    }
    
    /**
     * Attempt to load an HTML cached file.  Will return false if
     * the cached file does not exist or if the cached file is out
     * of date.
     *
     * @param SugarRef $ref Cache reference.
     * @return false|array Cache data on success, false on error.
     */
    public function loadCache (SugarRef $ref) {
        // if the file is already loaded, use that
        if (isset($this->files[$ref->uid]))
            return $this->files[$ref->uid];

        // get the cache's stamp, and fail if it can't be found
        $cstamp = $this->cache->stamp($ref, SUGAR_CACHE_HTML);
        if ($cstamp === false)
            return false;

        // fail if the cache is too old
        if ($cstamp < time() - $this->cacheLimit)
            return false;

        // load the cache data, fail if loading fails or the
        // version doesn't match
        $data = $this->cache->load($ref, SUGAR_CACHE_HTML);
        if ($data === false || $data['version'] != SUGAR_VERSION)
            return false;

        // compare stamps with the included references
        foreach ($data['refs'] as $file) {
            // try to reference the file; ignore failures
            $iref = SugarRef::create($file, $this);
            if (!$iref)
                continue;

            // get the stamp of the reference; ignore failures
            $stamp = $iref->storage->stamp($iref);
            if ($stamp === false)
                continue;

            // if the stamp is newer than the cache stamp, fail
            if ($cstamp < $stamp)
                return false;
        }

        // cache this file
        $this->files[$ref->uid] = $data;

        // everything has checked out, the cache is valid
        return $data;
    }

    /**
     * Load, compile, and display the requested template.
     *
     * @param string $file Template to display.
     * @return bool true on success.
     */
    public function display ($file) {
        // validate name
        $ref = SugarRef::create($file, $this);
        if ($ref === false)
            throw new SugarException('illegal template name: '.$file);

        // ensure template exists
        if ($ref->storage->stamp($ref) === false)
            throw new SugarException('template not found: '.$ref->full);

        // load and run
        try {
            $this->loadExecute($ref);
            return true;
        } catch (SugarException $e) {
            $this->handleError($e);
            return false;
        }

        return true;
    }

    /**
     * Displays a template using {@link Sugar::display}, but returns
     * the result as a string instead of displaying it to the user.
     *
     * @param string $file Template to process.
     * @return string Template output.
     */
    public function fetch ($file) {
        ob_start();
        $this->display($file);
        $result = ob_get_content();
        ob_end_clean();
        return $result;
    }

    /**
     * Check if a given template has a valid HTML cache.  If an HTML cache
     * already exists, applications can avoid expensive database queries
     * and other operations necessary to fill in template data.
     *
     * @param string $file File to check.
     * @param string $cacheId Optional cache identifier.
     * @return bool True if a valid HTML cache exists for the file.
     */
    function isCached ($file, $cacheId=null) {
        // validate name
        $ref = SugarRef::create($file, $this, $cacheId);
        if ($ref === false)
            throw new SugarException('illegal template name: '.$file);

        // if the cache can be loaded, it is valid
        return $this->loadCache($ref) !== false;
    }

    /**
     * Load, compile, and display a template, caching the result.
     *
     * @param string $file Template to display.
     * @param string $cacheId Optinal cache identifier.
     * @return bool true on success.
     */
    function displayCache ($file, $cacheId = null) {
        // validate name
        $ref = SugarRef::create($file, $this, $cacheId);
        if ($ref === false)
            throw new SugarException('illegal template name: '.$file);

        try {
            // if cache exists and is up-to-date and debug is off, load cache
            $data = $this->loadCache($ref);
            if (!$this->debug && $data !== false) {
                $this->execute($data);
                return true;
            }

            // create cache handler if necessary
            if (!$this->cacheHandler) {
                /**
                 * Cache handler.
                 */
                require_once SUGAR_ROOTDIR.'/Sugar/CacheHandler.php';

                // create cache
                $this->cacheHandler = new SugarCacheHandler($this);
                $this->loadExecute($ref);
                $cache = $this->cacheHandler->getOutput();
                $this->cacheHandler = null;

                // attempt to save cache
                $this->cache->store($ref, SUGAR_CACHE_HTML, $cache);

                // display cache
                $this->execute($cache);

            // cache handler already running - just display normally
            } else {
                $this->loadExecute($ref);
            }

            return true;
        } catch (SugarException $e) {
            $this->handleError($e);
            return false;
        }
    }

    /**
     * Displays a cached template using {@link Sugar::displayCache}, but
     * returns the result as a string instead of displaying it to the user.
     *
     * @param string $file Template to process.
     * @param string $cacheId Optional cache identifier.
     * @return string Template output.
     */
    public function fetchCache ($file, $cacheId = null) {
        ob_start();
        $this->displayCache($file, $cacheId);
        $result = ob_get_content();
        ob_end_clean();
        return $result;
    }

    /**
     * Compile and display the template source code given as a string.
     *
     * @param string $source Template code to display.
     * @return bool true on success.
     */
    function displayString ($source) {
        try {
            /**
             * Compiler.
             */
            require_once SUGAR_ROOTDIR.'/Sugar/Parser.php';

            // compile
            $parser = new SugarParser($this);
            $data = $parser->compile($source);
            $parser = null;

            // run
            $this->execute($data);
            
            return true;
        } catch (SugarException $e) {
            $this->handleError($e);
            return false;
        }
    }

    /**
     * Processes template source using {@link Sugar::displayString}, but
     * returns the result as a string instead of displaying it to the user.
     *
     * @param string $Source Template code to process.
     * @return string Template output.
     */
    public function fetchString ($source) {
        ob_start();
        $this->displayString($source);
        $result = ob_get_content();
        ob_end_clean();
        return $result;
    }

    /**
     * Fetch the source code for a template from the storage driver.
     *
     * @param string $file Template to lookup.
     * @return string Template's source code.
     */
    function getSource ($file) {
        // validate name
        $ref = SugarRef::create($file, $this);
        if ($ref === false)
            throw new SugarException('illegal template name: '.$file);

        // fetch source
        return $ref->storage->load($ref);
    }
}
// vim: set expandtab shiftwidth=4 tabstop=4 : ?>
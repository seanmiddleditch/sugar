<?php
/**
 * Template instance class.
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
 * @category   Template
 * @package    Sugar
 * @subpackage Template
 * @author     Sean Middleditch <sean@mojodo.com>
 * @copyright  2008-2009 Mojodo, Inc. and contributors
 * @license    http://opensource.org/licenses/mit-license.php MIT
 * @version    SVN: $Id$
 * @link       http://php-sugar.net
 */

/**
 * Template instance object.
 *
 * Encapsulates all operations to be performed for a particular template.
 *
 * @category   Template
 * @package    Sugar
 * @subpackage Template
 * @author     Sean Middleditch <sean@mojodo.com>
 * @copyright  2008-2010 Mojodo, Inc. and contributors
 * @license    http://opensource.org/licenses/mit-license.php MIT
 * @version    Release: 0.84
 * @link       http://php-sugar.net
 */
class Sugar_Template
{
    /**
     * Our Sugar instance
     *
     * @var Sugar $sugar
     */
    public $sugar;

    /**
     * Name of the template as given by the user.
     *
     * @var string $name
     */
    public $name;

    /**
     * Cache identifier.
     *
     * @var string $cacheId
     */
    public $cacheId;

    /**
     * Storage driver for this reference.
     *
     * @var Sugar_StorageDriver $storage
     */
    private $_storage;

    /**
     * Storage driver handle.
     *
     * @var mixed $_handle 
     */
    private $_handle;

    /**
     * Local variable data
     *
     * @var Sugar_Data $_data
     */
    private $_data;

    /**
     * HTML cache data.
     *
     * If an array, it's a valid cache.  If null, we haven't checked.
     * If false, it's known to be out of date.
     *
     * @var mixed $_htmlCache
     */
    private $_htmlCache = null;

    /**
     * Compiled template cache.
     *
     * @var mixed $_compiled
     */
    private $_compiled = null;

    /**
     * Optional inherited template, overrides template specified inherited
     * template.
     *
     * @var string $_inherit
     */
    private $_inherit = null;

    /**
     * Constructor.
     *
     * @param Sugar               $sugar       Sugar object.
     * @param Sugar_StorageDriver $storage     Storage driver.
     * @param mixed               $handle      Storage driver handle.
     * @param string              $name        Name of template requested by user.
     * @param string              $cacheId     The cache ID for the reference.
     */
    public function __construct(Sugar $sugar, Sugar_StorageDriver $storage,
    $handle, $name, $cacheId) {
        $this->sugar = $sugar;
        $this->_storage = $storage;
        $this->_handle = $handle;
        $this->name = $name;
        $this->cacheId = $cacheId;

        $this->_data = new Sugar_Data($sugar->getGlobals(), array());
    }

    /**
     * Get the last-modified timestamp of the template.
     *
     * @return int Last-modified timestamp.
     */
    public function getLastModified()
    {
        return $this->_storage->getLastModified($this->_handle);
    }

    /**
     * Get the source code of the template.
     *
     * @return string Source code of the template.
     */
    public function getSource()
    {
        return $this->_storage->getSource($this->_handle);
    }

    /**
     * Get a user-friendly name for the template
     *
     * @return string User-friendly template name.
     */
    public function getName()
    {
        return $this->_storage->getName($this->_handle, $this->name);
    }

    /**
     * Get the template's local variable data
     *
     * @return Sugar_Data
     */
    public function getData()
    {
        return $this->_data;
    }

    /**
     * Set the inherited template, which overrides any inherited
     * template specified in the template source.
     *
     * @param string $file Template to inherit from
     */
    public function setInherit($file)
    {
        $this->_inherit = $file;
    }
    
    /**
     * Attempt to load an HTML cached file.  Will return false if
     * the cached file does not exist or if the cached file is out
     * of date.
     *
     * @return false|array Cache data on success, false on error.
     */
    private function _loadCache()
    {
        // if the cache is already loaded, just return it
        if (!is_null($this->_htmlCache)) {
            return $this->_htmlCache;
        }

        // get the cache's stamp, and fail if it can't be found
        $cstamp = $this->sugar->cache->getLastModified($this, Sugar::CACHE_HTML);
        if ($cstamp === false) {
            return false;
        }

        // fail if the cache is too old
        if ($cstamp < time() - $this->sugar->cacheLimit) {
            return false;
        }

        // load the cache data, fail if loading fails or the
        // version doesn't match
        $data = $this->sugar->cache->load($this, Sugar::CACHE_HTML);
        if ($data === false) {
            return false;
        }

        // compare stamps with the included references; if any fail,
        // unmark our _cached flag so we can report back to the user
        // on a call to isCached()
        foreach ($data->getReferences() as $file) {
            // try to reference the file; ignore failures
            $inc = $this->sugar->getTemplate($file, $this->cacheId);
            if ($inc === false) {
                continue;
            }

            // get the stamp of the reference; ignore failures
            $stamp = $inc->getLastModified();
            if ($stamp === false) {
                continue;
            }

            // if the stamp is newer than the cache stamp, fail
            if ($cstamp < $stamp) {
                return false;
            }
        }

        // store the bytecode so we don't need to reload it
        $this->_htmlCache = $data;
        return $data;
    }

    /**
     * Check if the template has a valid and completely up-to-date ache.
     *
     * This will check the cache status of included templates as well.
     *
     * @return bool True for a valid cache, false if missing or outdated.
     */
    public function isCached()
    {
        return $this->_loadCache() !== false;
    }

    /**
     * Helper to set a variable in the template's local data
     *
     * @param string $name  Name of variable to set
     * @param mixed  $value Value of variable
     */
    public function set($name, $value)
    {
        $this->_data->set($name, $value);
    }

    /**
     * Load and compile (if necessary) the template code.
     *
     * @return mixed
     */
    private function _loadCompile()
    {
        // if we already have a compiled version, don't reload
        if (!is_null($this->_compiled)) {
            return $this->_compiled;
        }

        // if debug is off and the stamp is good, load compiled version
        if (!$this->sugar->debug) {
            $sstamp = $this->getLastModified();
            $cstamp = $this->sugar->cache->getLastModified($this, Sugar::CACHE_TPL);
            if ($cstamp !== false && $cstamp > $sstamp) {
                $data = $this->sugar->cache->load($this, Sugar::CACHE_TPL);
                // if version checks out, run it
                if ($data !== false && $data) {
                    $this->_compiled = $data;
                    return $data;
                }
            }
        }

        /**
         * Compiler.
         */
        include_once $GLOBALS['__sugar_rootdir'].'/Sugar/Grammar.php';

        // compile
        $source = $this->getSource();
        if ($source === false) {
            throw new Sugar_Exception_Usage('template not found: '.$this->getName());
        }
        $parser = new Sugar_Grammar($this->sugar);
        $data = $parser->compile($this, $source);
        unset($parser);

        // store compiled bytecode into cache
        $this->sugar->cache->store($this, Sugar::CACHE_TPL, $data);

        $this->_compiled = $data;
        return $data;
    }

    /**
     * Display the template
     *
     * @param Sugar_Data $data Optional data to use instead
     *                           of the default local data
     */
    public function display($data = null)
    {
        try {
            // use a default data if none provided
            if (is_null($data)) {
                $data = new Sugar_Data($this->getData(), array());
            }

            // if we are to be cached, check for an existing cache and use that if
            // it exists and is up to date
            if (!$this->sugar->debug && !is_null($this->cacheId)) {
                $code = $this->_loadCache();
                if ($code !== false) {
                    $context = new Sugar_Context($this->sugar, $this, $data, $code, null);
                    Sugar_Runtime::execute($context);
                    return true;
                }
            }

            // if we are to be cached and aren't alrady running inside an existing
            // cache handler instance, create a new one
            if (!is_null($this->cacheId)) {
                /**
                 * Cache handler.
                 */
                include_once $GLOBALS['__sugar_rootdir'].'/Sugar/Cache.php';

                // create cache
                $cache = new Sugar_Cache($this->sugar);
            } else {
                $cache = null;
            }

            // load compiled template
            $code = $this->_loadCompile();

            // if we have an inherited template, load it and merge it with our data
            $inherit = $this->_inherit ? $this->_inherit : $code->getInherit();
            if ($inherit) {
                // load compiled parent (inherited template)
                $parent = $this->sugar->getTemplate($inherit, $this->cacheId);
                if ($parent === false) {
                    throw new Sugar_Exception_Usage('inherited template not found: '.$inherit);
                }
                $pcode = $parent->_loadCompile();

                // merge code
                $pcode->mergeChild($code);
                $code = $pcode;
                unset($pcode);
            }

            // execute our compiled template
            $context = new Sugar_Context($this->sugar, $this, $data, $code, $cache);
            Sugar_Runtime::execute($context);
            unset($context);

            // clean up the cache handler and display the uncachable data if
            // and only if we created the cache handler
            if ($cache) {
                $code = $cache->getOutput();
                unset($cache);

                // attempt to save cache
                $this->sugar->cache->store($this, Sugar::CACHE_HTML, $code);

                // display cache
                $context = new Sugar_Context($this->sugar, $this, $data, $code, null);
                Sugar_Runtime::execute($context);
            }

            return true;
        } catch (Sugar_Exception $e) {
            $this->sugar->handleError($e);
            return false;
        }
    }

    /**
     * Fetch template output as a string
     *
     * @param Sugar_Data $data Optional data to use instead
     *                           of the default local data
     *
     * @return string
     */
    public function fetch($data = null)
    {
        ob_start();
        try {
            $this->display($data);
            $output = ob_get_contents();
        } catch (Exception $e) {
            ob_end_clean();
            throw $e;
        }
        ob_end_clean();
        return $output;
    }
}
// vim: set expandtab shiftwidth=4 tabstop=4 :
?>

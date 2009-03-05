<?php
/**
 * The Horde_Cache_Base:: class provides the abstract class definition for
 * Horde_Cache drivers.
 *
 * Copyright 1999-2009 The Horde Project (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (LGPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/lgpl.html.
 *
 * @author  Anil Madhavapeddy <anil@recoil.org>
 * @author  Chuck Hagenbuch <chuck@horde.org>
 * @author  Michael Slusarz <slusarz@horde.org>
 * @package Horde_Cache
 */
abstract class Horde_Cache_Base
{
    /**
     * Cache parameters.
     *
     * @var array
     */
    protected $_params = array();

    /**
     * Construct a new Horde_Cache object.
     *
     * @param array $params  Parameter array.
     */
    public function __construct($params = array())
    {
        if (!isset($params['lifetime'])) {
            $params['lifetime'] = isset($GLOBALS['conf']['cache']['default_lifetime'])
                ? $GLOBALS['conf']['cache']['default_lifetime']
                : 86400;
        }

        $this->_params = $params;
    }

    /**
     * Attempts to retrieve a cached object and return it to the
     * caller.
     *
     * @param string $key        Object ID to query.
     * @param integer $lifetime  Lifetime of the object in seconds.
     *
     * @return mixed  Cached data, or false if none was found.
     */
    abstract public function get($key, $lifetime = 1);

    /**
     * Attempts to store an object in the cache.
     *
     * @param string $key        Object ID used as the caching key.
     * @param mixed $data        Data to store in the cache.
     * @param integer $lifetime  Object lifetime - i.e. the time before the
     *                           data becomes available for garbage
     *                           collection.  If null use the default Horde GC
     *                           time.  If 0 will not be GC'd.
     *
     * @return boolean  True on success, false on failure.
     */
    abstract public function set($key, $data, $lifetime = null);

    /**
     * Checks if a given key exists in the cache, valid for the given
     * lifetime.
     *
     * @param string $key        Cache key to check.
     * @param integer $lifetime  Lifetime of the key in seconds.
     *
     * @return boolean  Existence.
     */
    abstract public function exists($key, $lifetime = 1);

    /**
     * Expire any existing data for the given key.
     *
     * @param string $key  Cache key to expire.
     *
     * @return boolean  Success or failure.
     */
    abstract public function expire($key);

    /**
     * Attempts to directly output a cached object.
     *
     * @param string $key        Object ID to query.
     * @param integer $lifetime  Lifetime of the object in seconds.
     *
     * @return boolean  True if output or false if no object was found.
     */
    public function output($key, $lifetime = 1)
    {
        $data = $this->get($key, $lifetime);
        if ($data === false) {
            return false;
        }

        echo $data;
        return true;
    }

    /**
     * Determine the default lifetime for data.
     *
     * @param mixed $lifetime  The lifetime to use or null for default.
     *
     * @return integer  The lifetime, in seconds.
     */
    protected function _getLifetime($lifetime)
    {
        return is_null($lifetime) ? $this->_params['lifetime'] : $lifetime;
    }

}
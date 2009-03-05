<?php
/**
 * The Horde_Cache_Eaccelerator:: class provides a eAccelerator content cache
 * (version 0.9.5+) implementation of the Horde caching system.
 *
 * Copyright 2006-2007 Duck <duck@obala.net>
 *
 * See the enclosed file COPYING for license information (LGPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/lgpl.html.
 *
 * @author  Duck <duck@obala.net>
 * @package Horde_Cache
 */
class Horde_Cache_Eaccelerator extends Horde_Cache_Base
{
    /**
     * Attempts to retrieve a piece of cached data and return it to the caller.
     *
     * @param string $key        Cache key to fetch.
     * @param integer $lifetime  Lifetime of the key in seconds.
     *
     * @return mixed  Cached data, or false if none was found.
     */
    public function get($key, $lifetime = 1)
    {
        $this->_setExpire($key, $lifetime);
        return eaccelerator_get($key);
    }

    /**
     * Attempts to store an object to the cache.
     *
     * @param string $key        Cache key (identifier).
     * @param mixed $data        Data to store in the cache.
     * @param integer $lifetime  Data lifetime.
     *
     * @return boolean  True on success, false on failure.
     */
    public function set($key, $data, $lifetime = null)
    {
        $lifetime = $this->_getLifetime($lifetime);
        eaccelerator_put($key . '_expire', time(), $lifetime);
        return eaccelerator_put($key, $data, $lifetime);
    }

    /**
     * Checks if a given key exists in the cache, valid for the given
     * lifetime.
     *
     * @param string $key        Cache key to check.
     * @param integer $lifetime  Lifetime of the key in seconds.
     *
     * @return boolean  Existence.
     */
    public function exists($key, $lifetime = 1)
    {
        $this->_setExpire($key, $lifetime);
        return (eaccelerator_get($key) === false) ? false : true;
    }

    /**
     * Expire any existing data for the given key.
     *
     * @param string $key  Cache key to expire.
     *
     * @return boolean  Success or failure.
     */
    public function expire($key)
    {
        eaccelerator_rm($key . '_expire');
        return eaccelerator_rm($key);
    }

    /**
     * Set expire time on each call since eAccelerator sets it on
     * cache creation.
     *
     * @param string $key        Cache key to expire.
     * @param integer $lifetime  Lifetime of the data in seconds.
     */
    protected function _setExpire($key, $lifetime)
    {
        if ($lifetime == 0) {
            // Don't expire.
            return;
        }

        $expire = eaccelerator_get($key . '_expire');

        // Set prune period.
        if ($expire + $lifetime < time()) {
            // Expired
            eaccelerator_rm($key);
            eaccelerator_rm($key . '_expire');
        }
    }

}
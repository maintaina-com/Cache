<?php
/**
 * Horde_Cache_Stack:: is a Cache implementation that will loop through a
 * given list of Cache drivers to search for a cached value.  This driver
 * allows for use of caching backends on top of persistent backends.
 *
 * Copyright 2010 The Horde Project (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (LGPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/lgpl.html.
 *
 * @author   Michael Slusarz <slusarz@horde.org>
 * @category Horde
 * @package  Cache
 */
class Horde_Cache_Stack extends Horde_Cache_Base
{
    /**
     * Stack of cache drivers.
     *
     * @var string
     */
    protected $_stack = array();

    /**
     * Constructor.
     *
     * @param array $params  Parameters:
     * <pre>
     * 'stack' - (array) [REQUIRED] A list of cache drivers to loop
     *           through, in order of priority. The last entry is considered
     *           the 'master' driver, for purposes of writes.
     *           Each value should contain an array with two keys: 'driver', a
     *           string value with the Cache driver to use, and 'params',
     *           containing any parameters needed by this driver.
     * </pre>
     *
     * @throws InvalidArgumentException
     */
    public function __construct(array $params = array())
    {
        if (!isset($params['stack'])) {
            throw new InvalidArgumentException('Missing stack parameter.');
        }

        foreach ($params['stack'] as $val) {
            $this->_stack[] = Horde_Cache::factory($val['driver'], $val['params']);
        }

        unset($params['stack']);

        parent::__construct($params);
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
    public function get($key, $lifetime = 1)
    {
        foreach ($this->_stack as $val) {
            $result = $val->get($key, $lifetime);
            if ($result !== false) {
                break;
            }
        }

        return $result;
    }

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
    public function set($key, $data, $lifetime = null)
    {
        /* Do writes in *reverse* order - it is OK if a write to one of the
         * non-master backends fails. */
        $master = true;

        foreach (array_reverse($this->_stack) as $val) {
            $result = $val->set($key, $data, $lifetime);
            if ($result === false) {
                if ($master) {
                    return false;
                }

                /* Attempt to invalidate cache if write failed. */
                $val->expire($id);
            }
            $master = false;
        }

        return true;
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
        foreach ($this->_stack as $val) {
            $result = $val->exists($key, $lifetime);
            if ($result === true) {
                break;
            }
        }

        return $result;
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
        /* Only report success from master. */
        $master = $success = true;

        foreach (array_reverse($this->_stack) as $val) {
            $result = $val->expire($id);
            if ($master && ($result === false)) {
                $success = false;
            }
            $master = false;
        }

        return $success;
    }

}
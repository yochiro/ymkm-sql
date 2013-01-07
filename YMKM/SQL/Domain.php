<?php
/**
 * @package ymkm-sql
 * @author Yoann Mikami <yoann@ymkm.org>
 */

/**
 * Defines a global context to be passed on when building an SQL query.
 *
 * @package ymkm-sql
 */
final class YMKM_SQL_Domain
{
    private $_values = array();


    public function add($key, $val)
    {
        $this->_values[$key][] = $val;
    }

    public function set($key, $val)
    {
        $this->_values[$key] = $val;
    }

    public function get($key=null)
    {
        if (is_null($key)) {
            return $this->_values;
        }
        return $this->_values[$key];
    }

    public function has($key)
    {
        return array_key_exists($key, $this->_values);
    }


    public function __get($key)
    {
        return $this->get($key);
    }

    public function __isset($key)
    {
        return $this->has($key);
    }
}

<?php
/**
 *
 * @package ymkm-sql
 * @author Yoann Mikami <yoann.mikami@gmail.com>
 */

/**
 * Interface to SQL expressions useable as SELECT s-exprs in SQL queries
 *
 * @package ymkm-sql
 */
interface YMKM_SQL_Iface_SelectAware extends YMKM_SQL_Iface_Parseable
{
    /**
     * Returns an alias for this select definition
     *
     * @return string the alias if any
     */
    function alias();
}

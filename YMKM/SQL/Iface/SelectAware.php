<?php
/**
 *
 * @package ymkm-sql
 * @author Yoann Mikami <yoann@ymkm.org>
 */


require_once(__DIR__.'/Parseable.php');


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

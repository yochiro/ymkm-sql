<?php
/**
 *
 * @package ymkm-sql
 * @author Yoann Mikami <yoann@ymkm.org>
 */


require_once(__DIR__.'/Parseable.php');


/**
 * Interface to SQL expressions useable as FROM s-exprs in SQL queries
 *
 * @package ymkm-sql
 */
interface YMKM_SQL_Iface_FromAware extends YMKM_SQL_Iface_Parseable
{
    /**
     * Returns the table definition name
     *
     * @return string the table name
     */
    function name();

    /**
     * Returns the table alias if any
     *
     * @return string the table alias
     */
    function alias();
}

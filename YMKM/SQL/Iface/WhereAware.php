<?php
/**
 *
 * @package ymkm-sql
 * @author Yoann Mikami <yoann@ymkm.org>
 */


require_once(__DIR__.'/Parseable.php');


/**
 * Interface to SQL expressions useable as WHERE s-exprs in SQL queries
 *
 * @package ymkm-sql
 */
interface YMKM_SQL_Iface_WhereAware extends YMKM_SQL_Iface_Parseable
{
}

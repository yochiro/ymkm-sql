<?php
/**
 *
 * @package ymkm-sql
 * @author Yoann Mikami <yoann@ymkm.org>
 */

require_once(__DIR__.'/Parseable.php');


/**
 * Interface to SQL expressions useable as GROUP BY s-exprs in SQL queries
 *
 * @package ymkm-sql
 */
interface YMKM_SQL_Iface_GroupAware extends YMKM_SQL_Iface_Parseable
{
}

<?php
/**
 *
 * @package ymkm-sql
 * @author Yoann Mikami <yoann@ymkm.org>
 */

/**
 * Stateful class which defines entities valid inside a HAVING part of an SQL query
 *
 * @package ymkm-sql
 */
final class YMKM_SQL_Entity_Having extends YMKM_SQL_Entity_AbstractHaving
{
    /**
     * @see YMKM_SQL_Entity_AbstractHaving
     */
    protected function doParse(YMKM_SQL_Domain $domain)
    {
        throw new YMKM_SQL_ParseException('Not implemented');
    }
}

<?php
/**
 *
 * @package ymkm-sql
 * @author Yoann Mikami <yoann@ymkm.org>
 */

/**
 * Abstract class which defines entities that can be defined in an SQL query
 *
 * The SQL specifications currently define the following for SELECT type queries :
 * - SELECT
 * - FROM
 * - JOIN
 * - WHERE
 * - GROUP BY
 * - HAVING
 * - ORDER BY
 * - LIMIT
 *
 * And entity represents a piece of any of such part.
 *
 * The basic method for entities is the parse method, which returns the
 * SQL valid parsed string based on its previously set definitions.
 *
 * @package ymkm-sql
 */
abstract class YMKM_SQL_Entity_Abstract implements YMKM_SQL_Iface_Entity
{
    /**
     * Parses that entity and returns the piece of SQL generated
     *
     * The $domain parameter contains the definition domain of its parent query.
     *
     * @param YMKM_SQL_Domain $domain the column and table definitions set on the query
     * @throw YMKM_SQL_ParseException if parsing fails
     */
    final public function parse(YMKM_SQL_Domain $domain)
    {
        return $this->doParse($domain);
    }


    /// Abstract method to be implemented by subclasses. ///

    abstract protected function doParse(YMKM_SQL_Domain $domain);
}

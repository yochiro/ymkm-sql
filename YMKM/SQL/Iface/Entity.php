<?php
/**
 *
 * @package ymkm-sql
 * @author Yoann Mikami <yoann.mikami@gmail.com>
 */

/**
 * Interface for SQL query entities
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
interface YMKM_SQL_Iface_Entity extends YMKM_SQL_Iface_Parseable
{
}

<?php
/**
 *
 * @package ymkm-sql
 * @author Yoann Mikami <yoann@ymkm.org>
 */


require_once(__DIR__.'/../Domain.php');
require_once(__DIR__.'/../Iface/Expression.php');
require_once(__DIR__.'/../Iface/Parseable.php');


/**
 * Abstract class which defines SQL expression, units that form an SQL entity
 *
 * @package ymkm-sql
 */
abstract class YMKM_SQL_Expression_Abstract implements YMKM_SQL_Iface_Expression,
                                                       YMKM_SQL_Iface_Parseable
{
    /**
     * Parses expressions and returns generated content
     *
     * @param YMKM_SQL_Domain $domain the column and table definitions set on this query
     * @return string the parsed SQL expression
     * @throw YMKM_SQL_ParseException if parsing fails
     */
    final public function parse(YMKM_SQL_Domain $domain)
    {
        return $this->doParse($domain);
    }


    /// Abstract method to be implemented by subclasses. ///

    abstract protected function doParse(YMKM_SQL_Domain $domain);
}

<?php
/**
 *
 * @package ymkm-sql
 * @author Yoann Mikami <yoann.mikami@gmail.com>
 */

/**
 * Abstract class which defines a null-expression.
 *
 * The parser basically returns nothing.
 *
 * It is mostly used in where statements for unary predicates.
 *
 * @package ymkm-sql
 */
final class YMKM_SQL_Expression_Null extends YMKM_SQL_Expression_Abstract
                                   implements YMKM_SQL_Iface_WhereAware
{
    /**
     * Constructor
     *
     * @param string $expr anything. Ignored
     */
    public function __construct($expr)
    {
    }

    /**
     * @see YMKM_SQL_Expression_Abstract
     */
    final protected function doParse(YMKM_SQL_Domain $domain)
    {
        return '';
    }
}

<?php
/**
 *
 * @package ymkm-sql
 * @author Yoann Mikami <yoann@ymkm.org>
 */

/**
 * Defines statement for LIMIT part of an SQL query
 *
 * parsing uses lambda functions, and thus this class requires PHP5.3 or more
 * to work.
 *
 * @require PHP 5.3+ (Lambda-fn)
 * @package ymkm-sql
 */
final class YMKM_SQL_Statement_Limit extends YMKM_SQL_Statement_Base
{
    /**
     * @see YMKM_SQL_Statement_Base
     */
    protected function _doParse(Closure $entitiesFn, YMKM_SQL_Domain $domain)
    {
        // Returns LIMIT number OFFSET offset
        return 'LIMIT ' . $entitiesFn(
                            function($e) use ($domain) {
                              return $e->number().' OFFSET '.$e->offset(); },
                            function($e1, $e2) { // nothing to join, just return e2
                              return $e2; });
    }
}

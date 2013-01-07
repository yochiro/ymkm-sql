<?php
/**
 *
 * @package ymkm-sql
 * @author Yoann Mikami <yoann@ymkm.org>
 */

/**
 * Defines statement for ORDER BY part of an SQL query
 *
 * parsing uses lambda functions, and thus this class requires PHP5.3 or more
 * to work.
 *
 * @require PHP 5.3+ (Lambda-fn)
 * @package ymkm-sql
 */
final class YMKM_SQL_Statement_Order extends YMKM_SQL_Statement_Base
{
    /**
     * @see YMKM_SQL_Statement_Base
     */
    protected function _doParse(Closure $entitiesFn, YMKM_SQL_Domain $domain)
    {
        // Returns ORDER BY (entity->parse), ...
        return 'ORDER BY ' . $entitiesFn(
                               function($e) use ($domain) {
                                 return $e->parse($domain); },
                               function($e1, $e2) {
                                 return (!is_null($e1)?$e1.',':'').$e2; });
    }
}

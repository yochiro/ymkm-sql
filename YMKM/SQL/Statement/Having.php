<?php
/**
 *
 * @package ymkm-sql
 * @author Yoann Mikami <yoann.mikami@gmail.com>
 */

/**
 * Defines statement for HAVING part of an SQL query
 *
 * parsing uses lambda functions, and thus this class requires PHP5.3 or more
 * to work.
 *
 * @require PHP 5.3+ (Lambda-fn)
 * @package ymkm-sql
 */
final class YMKM_SQL_Statement_Having extends YMKM_SQL_Statement_Base
{
    /**
     * @see YMKM_SQL_Statement_Base
     */
    protected function _doParse(Closure $entitiesFn, YMKM_SQL_Domain $domain)
    {
        // Returns HAVING (entity->parse), ...
        return 'HAVING ' . $entitiesFn(
                             function($e) use ($domain) {
                               return $e->parse($domain); },
                             function($e1, $e2) {
                              return (!is_null($e1)?$e1.' AND ':'').$e2; });
    }
}

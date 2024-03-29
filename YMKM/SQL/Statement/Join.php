<?php
/**
 *
 * @package ymkm-sql
 * @author Yoann Mikami <yoann@ymkm.org>
 */


require_once(__DIR__.'/Base.php');
require_once(__DIR__.'/../Domain.php');


/**
 * Defines statement for JOIN part of an SQL query
 *
 * parsing uses lambda functions, and thus this class requires PHP5.3 or more
 * to work.
 *
 * @require PHP 5.3+ (Lambda-fn)
 * @package ymkm-sql
 */
final class YMKM_SQL_Statement_Join extends YMKM_SQL_Statement_Base
{
    /**
     * @see YMKM_SQL_Statement_Base
     */
    protected function _doParse(Closure $entitiesFn, YMKM_SQL_Domain $domain)
    {
        // Returns <JoinType> JOIN (entity->parse), ...
        return $entitiesFn(
                 function($e) use ($domain) {
                   return (!is_null($e->joinType())?($e->joinType().' '):'') . 'JOIN ' . $e->parse($domain); },
                 function($e1, $e2) {
                   return (!is_null($e1)?$e1.' ':'').$e2; });
    }
}

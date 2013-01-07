<?php
/**
 *
 * @package ymkm-sql
 * @author Yoann Mikami <yoann@ymkm.org>
 */

/**
 * Defines statement for WHERE part of an SQL query
 *
 * parsing uses lambda functions, and thus this class requires PHP5.3 or more
 * to work.
 *
 * @require PHP 5.3+ (Lambda-fn)
 * @package ymkm-sql
 */
final class YMKM_SQL_Statement_Where extends YMKM_SQL_Statement_Base
{
    /**
     * @see YMKM_SQL_Statement_Base
     */
    protected function _doParse(Closure $entitiesFn, YMKM_SQL_Domain $domain)
    {
        // Returns WHERE (entity->parse) AND ...
        // YMKM_SQL_Entity_Where can be nest itself, which
        // allows a single where entity to generates more complex
        // conditions than cond1 AND cond2.
        // Default here is to AND all where entities defined in the statement.
        return 'WHERE ' . $entitiesFn(
                            function($e) use ($domain) {
                              return $e->parse($domain); },
                            function($e1, $e2) {
                              return (!is_null($e1)?$e1.' AND ':'').$e2; });
    }
}

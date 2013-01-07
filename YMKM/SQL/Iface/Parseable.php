<?php
/**
 *
 * @package ymkm-sql
 * @author Yoann Mikami <yoann@ymkm.org>
 */

/**
 * Interface to classes which should respond to the parse($domain) method
 *
 * @package ymkm-sql
 */
interface YMKM_SQL_Iface_Parseable
{
    /**
     * Parses and returns generated content as string, using given $domain
     *
     * @param YMKM_SQL_Domain $domain the global domain applying to current parsing
     * @return string the generated string
     */
    function parse(YMKM_SQL_Domain $domain);
}

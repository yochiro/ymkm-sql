<?php
/**
 *
 * @package ymkm-sql
 * @author Yoann Mikami <yoann@ymkm.org>
 */


require_once(__DIR__.'/AbstractReference.php');
require_once(__DIR__.'/../Domain.php');
require_once(__DIR__.'/../ParseException.php');
require_once(__DIR__.'/../Iface/TableRef.php');


/**
 * Abstract class which defines an SQL expression for a table reference
 *
 * A table reference is valid anywhere outside FROM/JOIN statements :
 * It assumes a table definition was created in the FROM/JOIN section
 * with specified reference name and enforces that check during parsing.
 *
 * @package ymkm-sql
 */
abstract class YMKM_SQL_Expression_AbstractTableReference
                                    extends YMKM_SQL_Expression_AbstractReference
                                 implements YMKM_SQL_Iface_TableRef
{
    /**
     * @see YMKM_SQL_Expression_Abstract
     *
     * Parsed content is :
     * - Table alias if defined
     * - Table name if no alias defined, AND IF no other table
     *   with that same name/alias is defined in the domain.
     */
    final protected function doParse(YMKM_SQL_Domain $domain)
    {
        $out = $this->refName();
        $dupDefs = array_reduce(
                       $domain->get('tables'),
                       function($cnt, $t) use ($out) {
                         if (($out === $t->name()) ||
                             (!is_null($t->alias()) && $out === $t->name())) {
                             return $cnt + 1;
                         }
                         return $cnt;
                       }, 0);

        if ($dupDefs > 1) {
            throw new YMKM_SQL_ParseException('Table `' . $out .
                                              '\' reference is ambiguous: domain has ' .
                                              $dupDefs .
                                              ' table definitions with that name');
        }

        return $this->_doParse($domain);
    }


    /// Abstract method to be implemented by subclasses. ///

    abstract protected function _doParse(YMKM_SQL_Domain $domain);
}

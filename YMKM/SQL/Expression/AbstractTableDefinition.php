<?php
/**
 *
 * @package ymkm-sql
 * @author Yoann Mikami <yoann@ymkm.org>
 */

/**
 * Abstract class which defines an SQL expression for a table definition
 *
 * A table definition is valid within FROM/JOIN statements :
 * It creates a new table reference given a name and an optional alias.
 * Once defined, that table can be referenced in other parts of the
 * statement using either its alias (if provided), or its name.
 * However, the name can lead to ambiguous references if same table
 * is defined several times (different aliases is then needed).
 *
 * @package ymkm-sql
 */
abstract class YMKM_SQL_Expression_AbstractTableDefinition
                                    extends YMKM_SQL_Expression_AbstractDefinition
                                 implements YMKM_SQL_Iface_FromAware,
                                            YMKM_SQL_Iface_TableDef
{
    /**
     * Constructor
     *
     * @param string $name the table name
     * @param string $alias the table alias
     */
    public function __construct($name, $alias=null)
    {
        $this->setName($name);
        $this->setAlias($alias);
    }

    /**
     * Returns a table reference from this definition
     *
     * @return YMKM_SQL_Iface_TableRef table reference matching this definition
     */
    final public function toTableReference()
    {
        return $this->doToTableReference();
    }


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
        $out = (!is_null($this->alias())?$this->alias():$this->name());
        $dupDefs = array_reduce(
                       $domain->get('tables'),
                       function($cnt, $t) use ($out) {
                         if (($out === $t->alias()) ||
                             ($out === $t->name())) {
                             return $cnt + 1;
                         }
                         return $cnt;
                       }, 0);
        if ($dupDefs > 1) {
            throw new YMKM_SQL_ParseException('Table `' . $out .
                                              '\' definition is ambiguous: domain has ' .
                                              $dupDefs .
                                              ' table definitions with that name');
        }
        return $this->_doParse($domain);
    }


    /// Abstract method to be implemented by subclasses. ///

    abstract protected function _doParse(YMKM_SQL_Domain $domain);

    abstract protected function doToTableReference();
}

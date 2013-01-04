<?php
/**
 *
 * @package ymkm-sql
 * @author Yoann Mikami <yoann.mikami@gmail.com>
 */

/**
 * Abstract class which defines an SQL expression for a column definition
 *
 * A column definition is valid within SELECT statements :
 * It creates a new column reference given a name and an optional alias.
 * It also needs its table reference.
 * Once defined, that column can be referenced in other parts of the
 * statement using either its alias (if provided), or its name.
 * However, the name can lead to ambiguous references if same column
 * is defined several times (different aliases is then needed).
 *
 * @package ymkm-sql
 */
abstract class YMKM_SQL_Expression_AbstractColumnDefinition
                                    extends YMKM_SQL_Expression_AbstractDefinition
                                 implements YMKM_SQL_Iface_SelectAware, YMKM_SQL_Iface_ColumnDef
{
    /**
     * Constructor
     *
     * @param string $name the column name
     * @param YMKM_SQL_Iface_TableRef $tableRef the table reference
     * @param string $alias the column alias
     */
    public function __construct($name, YMKM_SQL_Iface_TableRef $tableRef=null,
                                $alias=null)
    {
        $this->setName($name);
        if (!is_null($tableRef)) {
            $this->setTableReference($tableRef);
        }
        if (!is_null($alias)) {
            $this->setAlias($alias);
        }
    }

    /**
     * Sets the table reference
     *
     * @param YMKM_SQL_Iface_TableRef $tableRef the table reference
     * @return $this for chaining
     */
    final public function setTableReference(YMKM_SQL_Iface_TableRef $tableRef)
    {
        $this->doSetTableReference($tableRef);
        return $this;
    }

    /**
     * Returns the table reference
     *
     * @return YMKM_SQL_Iface_TableRef the table reference
     */
    final public function tableReference()
    {
        return $this->doTableReference();
    }


    /**
     * @see YMKM_SQL_Expression_Abstract
     *
     * Parsed content is :
     * - Column alias if defined
     * - Column name if no alias defined, AND IF no other column
     *   with that same name/alias and table reference is defined in the domain.
     */
    final protected function doParse(YMKM_SQL_Domain $domain)
    {
        $out = (!is_null($this->alias())?$this->alias():$this->name());
        $tblRef = (!is_null($this->tableReference())?
                   $this->tableReference()->parse($domain):null);

        $dupDefs = array_reduce(
                     $domain->get('columns'),
                     function($cnt, $c) use ($out, $tblRef) {
                       // Count as potential duplicate if :
                       // Another column with same alias is defined (no matter the table)
                       // Another column with same name is defined and no table reference
                       // was assigned with the column.
                       if (($out === $c->alias()) ||
                           (($out === $c->name()) && is_null($tblRef))) {
                           return $cnt + 1;
                       }
                       return $cnt;
                     }, 0);
        if ($dupDefs > 1) {
            throw new YMKM_SQL_ParseException('Column `' . $out .
                                              '\' definition is ambiguous: domain has ' .
                                              $dupDefs .
                                              ' column definitions with that name' .
                                              ' for table reference `' . $tblRef . '\'');
        }

        return $this->_doParse($domain);
    }


    /// Abstract method to be implemented by subclasses. ///

    abstract protected function doSetTableReference(YMKM_SQL_Iface_TableRef $tableRef);
    abstract protected function doTableReference();
    abstract protected function _doParse(YMKM_SQL_Domain $domain);
}

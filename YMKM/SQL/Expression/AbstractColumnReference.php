<?php
/**
 *
 * @package ymkm-sql
 * @author Yoann Mikami <yoann@ymkm.org>
 */

/**
 * Abstract class which defines an SQL expression for a column reference
 *
 * A column definition is valid anywhere outside SELECT statements :
 * It assumes a column definition was created in the SELECT section
 * with specified reference name and enforces that check during parsing.
 *
 * @package ymkm-sql
 */
abstract class YMKM_SQL_Expression_AbstractColumnReference
                                    extends YMKM_SQL_Expression_AbstractReference
                                 implements YMKM_SQL_Iface_WhereAware,
                                            YMKM_SQL_Iface_GroupAware,
                                            YMKM_SQL_Iface_HavingAware,
                                            YMKM_SQL_Iface_OrderAware,
                                            YMKM_SQL_Iface_ColumnRef
{
    /**
     * Constructor
     *
     * A column reference only needs a reference name and an optional table reference,
     * as it supposes the column is already defined somewhere else.
     * The reference name can either be a column name or a column alias depending on how
     * it was previously defined.
     *
     * @param string $refName the column name reference
     * @param YMKM_SQL_Iface_TableRef $tableRef the table reference
     */
    public function __construct($refName, YMKM_SQL_Iface_TableRef $tableRef=null)
    {
        $this->setRefName($refName);
        if (!is_null($tableRef)) {
            $this->setTableReference($tableRef);
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
    protected function doParse(YMKM_SQL_Domain $domain)
    {
        $out = $this->refName();
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
                                              '\' reference is ambiguous: domain has ' .
                                              $dupDefs .
                                              ' column definitions with that name or alias.');
        }

        return $this->_doParse($domain);
    }


    /// Abstract method to be implemented by subclasses. ///

    abstract protected function doSetTableReference(YMKM_SQL_Iface_TableRef $tableRef);
    abstract protected function doTableReference();
    abstract protected function _doParse(YMKM_SQL_Domain $domain);
}

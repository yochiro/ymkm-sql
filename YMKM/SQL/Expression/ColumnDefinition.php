<?php
/**
 *
 * @package ymkm-sql
 * @author Yoann Mikami <yoann.mikami@gmail.com>
 */

/**
 * Stateful class which defines an SQL expression for a column definition
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
final class YMKM_SQL_Expression_ColumnDefinition
                     extends YMKM_SQL_Expression_AbstractColumnDefinition
{
    /**
     * Column alias
     * @var string
     */
    private $_alias = null;

    /**
     * Column name
     * @var string
     */
    private $_name = null;

    /**
     * Table reference
     * @var YMKM_SQL_Expression_Iface_TableRef
     */
    private $_tableRef = null;


    /**
     * @see YMKM_SQL_Expression_AbstractColumnDefinition
     */
    protected function doSetName($s)
    {
        $this->_name = $s;
    }

    /**
     * @see YMKM_SQL_Expression_AbstractColumnDefinition
     *
     * If table reference responds to getTableInfo, then
     * it must be handling tables from object names.
     * In that case, the column name could be an alias
     * defined in the YMKM_TableInfo associated with the
     * object name. Check if it has such column or alias,
     * and return the column name in any case.
     */
    protected function doName()
    {
        return $this->_name;
    }

    /**
     * @see YMKM_SQL_Expression_AbstractColumnDefinition
     */
    protected function doSetAlias($alias)
    {
        $this->_alias = $alias;
    }

    /**
     * @see YMKM_SQL_Expression_AbstractColumnDefinition
     */
    protected function doAlias()
    {
        return $this->_alias;
}

    /**
     * @see YMKM_SQL_Expression_AbstractColumnDefinition
     */
    protected function doSetTableReference(YMKM_SQL_Iface_TableRef $tableRef)
    {
        $this->_tableRef = $tableRef;
    }

    /**
     * @see YMKM_SQL_Expression_AbstractColumnDefinition
     */
    protected function doTableReference()
    {
        return $this->_tableRef;
    }


    protected function _doParse(YMKM_SQL_Domain $domain)
    {
        $tRef = (!is_null($this->tableReference())?
                $this->tableReference()->refName():null);

        $tDef = null;
        if (!is_null($tRef)) {
            foreach ($domain->get('tables') as $t) {
                if (($t->alias() === $tRef) ||
                    ($t->name() === $tRef)) {
                    $tDef = $t;
                    break;
                }
            }
        }

        $name = $this->name();
        if (is_null($tDef)) {
/* We allow unreferenced columns in the column definition list: this can lead to an ambiguous column definition error...
            throw new YMKM_SQL_ParseException('ColumnDefinition('. $name .'):: ' .
                                              'Table reference `' . $tRef .
                                              '\' is not defined in the domain');
*/
        }
        else {
            $tRef = (!is_null($tDef->alias())?$tDef->alias():$tDef->name());
        }

        return (!is_null($tRef)?$tRef.'.':'') .  $name;
    }
}

<?php
/**
 *
 * @package ymkm-sql
 * @author Yoann Mikami <yoann.mikami@gmail.com>
 */

/**
 * Stateful class which defines an SQL expression for a column reference
 *
 * A column definition is valid anywhere outside SELECT statements :
 * It assumes a column definition was created in the SELECT section
 * with specified reference name and enforces that check during parsing.
 *
 * @package ymkm-sql
 */
final class YMKM_SQL_Expression_ColumnReference
                        extends YMKM_SQL_Expression_AbstractColumnReference
{
    /**
     * Column refName/name
     * @var string
     */
    private $_refName = null;

    /**
     * Table reference, if any
     * @var YMKM_SQL_Iface_TableRef
     */
    private $_tableRef = null;

    /**
     * @see YMKM_SQL_Expression_AbstractColumnReference
     */
    protected function doTableReference()
    {
        return $this->_tableRef;
    }

    /**
     * @see YMKM_SQL_Expression_AbstractColumnReference
     */
    protected function doSetTableReference(YMKM_SQL_Iface_TableRef $tableRef)
    {
        $this->_tableRef = $tableRef;
    }

    /**
     * @see YMKM_SQL_Expression_AbstractColumnReference
     */
    protected function doRefName()
    {
        return $this->_refName;
    }

    /**
     * @see YMKM_SQL_Expression_AbstractColumnReference
     */
    protected function doSetRefName($refName)
    {
        $this->_refName = $refName;
    }

    protected function doParse(YMKM_SQL_Domain $domain)
    {
        $name = $this->refName();
        if (is_null($this->tableReference())) {
            $defCnt = count(
                        array_unique(
                          array_filter(
                            array_map(
                              function($col) use ($name) {
                                return ($col->name() === $name ||
                                  $col->alias() === $name)?
                                  $col->tableReference()->refName():
                                  null;
                              }, $domain->get('columns')),
                            function($tbl) { return !is_null($tbl); }
                          )
                        )
                      );
            if ($defCnt > 1) {
                $from = $domain->get('from');
                if (!empty($from) && 1 === count($from)) {
                    $tblDef = $from[0];
                    $this->setTableReference($tblDef->toTableReference());
                }
                else {
                    throw new YMKM_SQL_ParseException('Column `' . $name .
                                                      '\' reference is ambiguous: ' .  $defCnt .
                                                      ' tables in domain have a column definition with that name or alias.');
                }
            }
/* Don't enforce this
            elseif (0 === $defCnt) {
                throw new YMKM_SQL_ParseException('Column `' . $name .
                                                  '\' reference is unknown: ' .
                                                  ' no tables in domain have a column definition with that name or alias.');
            }
*/
        }
        return parent::doParse($domain);
    }

    protected function _doParse(YMKM_SQL_Domain $domain)
    {
        $tRef = (!is_null($this->tableReference())?
                $this->tableReference()->refName():null);
        $name = $this->refName();
        $tDef = null;

        // If no table reference specified, first look at the column domain
        // and try to see there is an alias named identically to the current col ref.
        // return the name as is if that is the case.
        if (is_null($tRef)) {
            foreach ($domain->get('columns') as $c) {
                if ($c->alias() === $name ||
                    $c->name() === $name) {
                    $tRef = $c->tableReference()->refName();
                    break;
                }
            }
        }

        if (!is_null($tRef)) {
            foreach ($domain->get('tables') as $t) {
                if (($t->alias() === $tRef) ||
                    ($t->name() === $tRef)) {
                    $tDef = $t;
                    break;
                }
            }

            if (is_null($tDef)) {
                throw new YMKM_SQL_ParseException('ColumnReference('. $name . '):: ' .
                                                  'Table reference `' . $tRef .
                                                  '\' is not defined in the domain');
            }
            else {
                $tRef = (!is_null($tDef->alias())?$tDef->alias():$tDef->name());
            }
        }

        return (!is_null($tRef)?$tRef.'.':'') .  $name;
    }
}

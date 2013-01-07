<?php
/**
 *
 * @package ymkm-sql
 * @author Yoann Mikami <yoann@ymkm.org>
 */

/**
 * Stateful class which defines entities valid inside a JOIN part of an SQL query
 *
 * @package ymkm-sql
 */
final class YMKM_SQL_Entity_Join extends YMKM_SQL_Entity_AbstractJoin
{
    /**
     * The join type
     * @var string
     */
    private $_joinType = '';

    /**
     * The target (table) definition
     * @var YMKM_SQL_Iface_FromAware
     */
    private $_target = null;

    /**
     * Join conditions
     * @var YMKM_SQL_Entity_Where
     */
    private $_joinCols = null;


    /**
     * Augment current join conditions with specified conds
     *
     * @param YMKM_SQL_Entity_AbstractWhere $jConds conditions to add
     * @return $this
     */
    public function augment(YMKM_SQL_Entity_AbstractWhere $jConds)
    {
        $newExpr = new YMKM_SQL_Expression_Where(
                        array($this->_joinCols, $jConds),
                        null,
                        function($e, $f) { return $e.' AND '.$f; },
                        null);
        $this->_joinCols = $newExpr;
        return $this;
    }

    /**
     * Constructor
     *
     * @param YMKM_SQL_Iface_FromAware $t the target (table) to handle
     * @param string $jType the join type
     * @param YMKM_SQL_Entity_AbstractWhere $jConds the join conditions
     */
    public function __construct(YMKM_SQL_Iface_FromAware $t, $jType = '',
                                YMKM_SQL_Entity_AbstractWhere $jConds = null)
    {
        $this->setTarget($t);
        $this->setJoinType($jType);
        $this->_joinCols = $jConds;
    }


    /**
     * @see YMKM_SQL_Entity_AbstractJoin
     */
    protected function doSetJoinType($jType)
    {
        $this->_joinType = $jType;
    }

    /**
     * @see YMKM_SQL_Entity_AbstractJoin
     */
    protected function doJoinType()
    {
        return $this->_joinType;
    }

    /**
     * @see YMKM_SQL_Entity_AbstractJoin
     */
    protected function doSetTarget(YMKM_SQL_Iface_FromAware $t)
    {
        $this->_target = $t;
    }

    /**
     * @see YMKM_SQL_Entity_AbstractJoin
     */
    protected function doTarget()
    {
        return $this->_target;
    }

    /**
     * @see YMKM_SQL_Entity_AbstractJoin
     */
    protected function doParse(YMKM_SQL_Domain $domain)
    {
        // Parse join conditions first if set
        $parsed = (!is_null($this->_joinCols))?
                  $this->_joinCols->parse($domain):'';

        // Returns target->parse [AS target->alias] [ON (jConds->parse)]
        return $this->_target->parse($domain) .
                (!is_null($this->_target->alias())?
                 ' AS ' . $this->_target->alias():
                 '') . (''!==$parsed?
                 ' ON (' .  $parsed . ')':'');
    }
}

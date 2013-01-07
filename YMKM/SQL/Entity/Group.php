<?php
/**
 *
 * @package ymkm-sql
 * @author Yoann Mikami <yoann@ymkm.org>
 */

/**
 * Stateful class which defines entities valid inside a GROUP BY part of an SQL query
 *
 * @package ymkm-sql
 */
final class YMKM_SQL_Entity_Group extends YMKM_SQL_Entity_AbstractGroup
{
    /**
     * The target column to group by
     * @var YMKM_SQL_Iface_GroupAware
     */
    private $_target = null;

    /**
     * Constructor
     *
     * @oaram YMKM_SQL_Iface_GroupAware $t the target column to group by
     */
    public function __construct(YMKM_SQL_Iface_GroupAware $t)
    {
        $this->setTarget($t);
    }

    /**
     * @see YMKM_SQL_Entity_AbstractGroup
     */
    protected function doSetTarget(YMKM_SQL_Iface_GroupAware $t)
    {
        $this->_target = $t;
    }

    /**
     * @see YMKM_SQL_Entity_AbstractGroup
     */
    protected function doTarget()
    {
        return $this->_target;
    }

    /**
     * @see YMKM_SQL_Entity_AbstractGroup
     */
    protected function doParse(YMKM_SQL_Domain $domain)
    {
        // Returns target->parse
        return $this->_target->parse($domain);
    }
}

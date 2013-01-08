<?php
/**
 *
 * @package ymkm-sql
 * @author Yoann Mikami <yoann@ymkm.org>
 */


require_once(__DIR__.'/AbstractOrder.php');
require_once(__DIR__.'/../Domain.php');
require_once(__DIR__.'/../Iface/OrderAware.php');


/**
 * Stateful class which defines entities valid inside a ORDER BY part of an SQL query
 *
 * @package ymkm-sql
 */
final class YMKM_SQL_Entity_Order extends YMKM_SQL_Entity_AbstractOrder
{
    /**
     * The order direction
     * @rvar string
     */
    private $_dir = 'ASC';

    /**
     * The target column to order by
     * @var YMKM_SQL_Iface_OrderAware
     */
    private $_target = null;


    /**
     * Constructor
     *
     * @oaram YMKM_SQL_Iface_OrderAware $t the target column to order by
     * @param string $dir the order direction
     */
    public function __construct(YMKM_SQL_Iface_OrderAware $t, $dir = 'ASC')
    {
        $this->setTarget($t);
        $this->setDir($dir);
    }


    /**
     * @see YMKM_SQL_Entity_AbstractOrder
     */
    protected function doSetDir($dir)
    {
        $this->_dir = $dir;
    }

    /**
     * @see YMKM_SQL_Entity_AbstractOrder
     */
    protected function doDir()
    {
        return $this->_dir;
    }

    /**
     * @see YMKM_SQL_Entity_AbstractOrder
     */
    protected function doSetTarget(YMKM_SQL_Iface_OrderAware $t)
    {
        $this->_target = $t;
    }

    /**
     * @see YMKM_SQL_Entity_AbstractOrder
     */
    protected function doTarget()
    {
        return $this->_target;
    }

    /**
     * @see YMKM_SQL_Entity_AbstractOrder
     */
    protected function doParse(YMKM_SQL_Domain $domain)
    {
        // Returns target->parse ASC|DESC
        return $this->_target->parse($domain).' '.$this->_dir;
    }
}

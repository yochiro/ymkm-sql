<?php
/**
 *
 * @package ymkm-sql
 * @author Yoann Mikami <yoann.mikami@gmail.com>
 */

/**
 * Abstract class which defines a decorator for any expression
 *
 * A decorator can be used to add evaluated expressions around other expressions,
 * e.g. MAX, MIN, AVG, COALESCE...
 * Any subclass implementing a decorator MUST call this class's constructor,
 * with any number of arguments provided the first one is always the
 * expression being decorated.
 * the doParse method is left to the subclass for implementation.
 *
 * @package ymkm-sql
 */
abstract class YMKM_SQL_Expression_Decorator extends YMKM_SQL_Expression_Abstract
                                          implements YMKM_SQL_Iface_SelectAware,
                                                     YMKM_SQL_Iface_WhereAware,
                                                     YMKM_SQL_Iface_GroupAware,
                                                     YMKM_SQL_Iface_HavingAware,
                                                     YMKM_SQL_Iface_OrderAware
{
    /**
     * The decorated expression
     * @var YMKM_SQL_Expression_Abstract
     */
    private $_expr = null;


    /**
     * Constructor.
     *
     * Subclasses can override this to add any arguments as they need.
     * The first one should always be -though not enforced- the expression being
     * decorated to keep consistency in the way different decorators might
     * be instantiated.
     * All but the first argument are passed to the init function.
     */
    public function __construct(YMKM_SQL_Expression_Abstract $expr)
    {
        $this->_expr = $expr;
        $args = func_get_args();
        array_shift($args);
        $this->init($args);
    }

    /**
     * Initializes the decorator with specified arguments
     *
     * @param array $args the arguments used for initialization
     */
    final public function init(array $args)
    {
        $this->doInit($args);
    }

    /**
     * Sets the decorated expression
     *
     * @param YMKM_SQL_Expression_Abstract $expr the expression to decorate
     * @return $this for chaining
     */
    final public function setExpr(YMKM_SQL_Expression_Abstract $expr)
    {
        $this->_expr = $expr;
        return $this;
    }

    /**
     * Returns the decorated expression
     *
     * @return YMKM_SQL_Expression_Abstract the decorated expression
     */
    final public function expr()
    {
        return $this->_expr;
    }


    /**
     * Delegates any unhandled method call to the decorated expression
     */
    final public function __call($method, $args)
    {
        return call_user_func_array(array($this->_expr, $method), $args);
    }


    /// Abstract method to be implemented by subclasses. ///

    abstract protected function doInit($args);
}

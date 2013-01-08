<?php
/**
 *
 * @package ymkm-sql
 * @author Yoann Mikami <yoann@ymkm.org>
 */


require_once(__DIR__.'/Abstract.php');
require_once(__DIR__.'/../Domain.php');
require_once(__DIR__.'/../Iface/HavingAware.php');
require_once(__DIR__.'/../Iface/SelectAware.php');
require_once(__DIR__.'/../Iface/WhereAware.php');


/**
 * Abstract class which defines an arithmetic SQL expression
 *
 * An arithmetic expression is self-evaluated; it does not depend on the
 * query domain.
 *
 * It is the most versatile, as it can be used within SELECTs, WHEREs and HAVING
 * statements.
 *
 * @package ymkm-sql
 */
abstract class YMKM_SQL_Expression_AbstractArithmetic
                                    extends YMKM_SQL_Expression_Abstract
                                 implements YMKM_SQL_Iface_SelectAware,
                                            YMKM_SQL_Iface_WhereAware,
                                            YMKM_SQL_Iface_HavingAware
{
    /**
     * Constructor
     *
     * @param string $expr a valid SQL arithmetic expression
     */
    public function __construct($expr)
    {
        $this->setExpression($expr);
    }

    /**
     * Sets the alias if used as a SELECT expression
     *
     * @param string $alias the alias to set
     * @return $this for chaining
     */
    final public function setAlias($alias)
    {
        $this->doSetAlias($alias);
        return $this;
    }

    /**
     * Returns the alias when used as a SELECT expression
     *
     * @return string the alias
     */
    final public function alias()
    {
        return $this->doAlias();
    }

    /**
     * Sets the expression to evaluate
     *
     * @param string $expr the expression
     * @return $this for chaining
     */
    final public function setExpression($expr)
    {
        $this->doSetExpression($expr);
        return $this;
    }

    /**
     * Returns the expression it renders
     *
     * @return string the expression
     */
    final public function expression()
    {
        return $this->doExpression();
    }


    /**
     * @see YMKM_SQL_Expression_Abstract
     */
    final protected function doParse(YMKM_SQL_Domain $domain)
    {
        return $this->expression();
    }


    /// Abstract method to be implemented by subclasses. ///

    abstract protected function doSetAlias($alias);
    abstract protected function doAlias();
    abstract protected function doSetExpression($expr);
    abstract protected function doExpression();
}

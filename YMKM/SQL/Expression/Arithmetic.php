<?php
/**
 *
 * @package ymkm-sql
 * @author Yoann Mikami <yoann.mikami@gmail.com>
 */

/**
 * Stateful class which defines and arithmetic SQL expression
 *
 * An arithmetic expression is self-evaluated; it does not depend on the
 * query domain.
 *
 * @package ymkm-sql
 */
final class YMKM_SQL_Expression_Arithmetic extends YMKM_SQL_Expression_AbstractArithmetic
{
    /**
     * The evaluated expression
     * @var string
     */
    private $_expr = null;

    /**
     * Expresison alias if used as a SELECT expression
     * @var string
     */
    private $_alias = null;


    /**
     * @see YMKM_SQL_Expression_AbstractArithmetic
     */
    protected function doSetAlias($alias)
    {
        $this->_alias = $alias;
    }

    /**
     * @see YMKM_SQL_Expression_AbstractArithmetic
     */
    protected function doAlias()
    {
        return $this->_alias;
    }

    /**
     * @see YMKM_SQL_Expression_AbstractArithmetic
     */
    protected function doSetExpression($expr)
    {
        $this->_expr = $expr;
    }

    /**
     * @see YMKM_SQL_Expression_AbstractArithmetic
     */
    protected function doExpression()
    {
        return $this->_expr;
    }
}

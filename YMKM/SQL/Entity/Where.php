<?php
/**
 *
 * @package ymkm-sql
 * @author Yoann Mikami <yoann.mikami@gmail.com>
 */

/**
 * Stateful class which defines entities valid inside a WHERE part of an SQL query
 *
 * @package ymkm-sql
 */
final class YMKM_SQL_Entity_Where extends YMKM_SQL_Entity_AbstractWhere
{
    /**
     * Expression
     * @var YMKM_SQL_Iface_WhereAware
     */
    private $_expr = null;


    /**
     * Constructor
     *
     * @param YMKM_SQL_Iface_WhereAware the expression to handle
     */
    public function __construct(YMKM_SQL_Iface_WhereAware $expr=null)
    {
        $this->_expr = $expr;
    }


    /**
     * @see YMKM_SQL_Entity_AbstractWhere
     */
    protected function doSetExpr(YMKM_SQL_Iface_WhereAware $expr)
    {
        $this->_expr = $expr;
    }

    /**
     * @see YMKM_SQL_Entity_AbstractWhere
     */
    protected function doExpr()
    {
        return $this->_expr;
    }

    /**
     * @see YMKM_SQL_Entity_AbstractWhere
     */
    protected function doParse(YMKM_SQL_Domain $domain)
    {
        // Returns :
        // - Handle single columns conditions :
        // * WHERE xx (NOT) IN (yy) | yy = value | RAW SQL
        // * WHERE xx [<,>,<=,>=,<>,=] yy | yy = Entity | value | RAW SQL
        // * WHERE xx LIKE 'yy' | yy = value
        // * WHERE MATCH(xx) AGAINST (yy opts) | yy = value

        // - Handle multi columns conditions :
        // * WHERE x1,x2,x3,...,xn (NOT) IN (yy) | yy = value | RAW SQL
        // * WHERE x1,x2,x3,...,xn [<,>,<=,>=,<>,=] yy | yy = Entity | value | RAW SQL
        // * WHERE x1,x2,x3,...,xn LIKE 'yy' | yy = value
        // * WHERE MATCH(x1,x2,x3,...,xn) AGAINST (yy opts) | yy = value
        return (!is_null($this->_expr)?$this->_expr->parse($domain):'');
    }
}

<?php
/**
 *
 * @package ymkm-sql
 * @author Yoann Mikami <yoann@ymkm.org>
 */


require_once(__DIR__.'/Abstract.php');
require_once(__DIR__.'/Decorator.php');
require_once(__DIR__.'/../Domain.php');


/**
 * Decorator class for column expressions providing decoration through lambda fn
 *
 * Lambdas functions takes two parameters: the $domain and the column to decorate.
 * They can be used to do things such as MAX(col), AVG(col)...
 *
 * @package ymkm-sql
 */
final class YMKM_SQL_Expression_ColumnFn extends YMKM_SQL_Expression_Decorator
{
    /**
     * Function to call which has the decoration features
     * @var Closure
     */
    private $_fn = null;


    /**
     * Constructor
     *
     * Takes an additional argument being the function which we call to decorate
     * the column specified as the first argument.
     *
     * @param YMKM_SQL_Expression_AbstractColumn $column the column to decorate
     * @param Closure $fn the function to call
     */
    public function __construct(YMKM_SQL_Expression_Abstract $column, $fn)
    {
        parent::__construct($column, $fn);
    }

    /**
     * Sets the column alias
     *
     * @param string $alias the alias to set
     * @return $this for chaining
     */
    final public function setAlias($alias)
    {
        $this->expr()->setAlias($alias);
        return $this;
    }

    /**
     * Returns the column alias
     *
     * @return string the column alias
     */
    final public function alias()
    {
        return $this->expr()->alias();
    }

    /**
     * Initialization routine
     *
     * One argument : function
     */
    protected function doInit($args)
    {
        $fn = $args[0];
        $this->_fn = $fn;
    }

    /**
     * @see YMKM_SQL_Expression_Abstract
     *
     * Renders decorated column content applied to closure function.
     * function examples :
     * MAX(col) : function($domain, $c) { return 'MAX('.$c.')'; }
     */
    protected function doParse(YMKM_SQL_Domain $domain)
    {
        $fn = $this->_fn;
        return $fn($domain, $this->expr()->parse($domain));
    }
}

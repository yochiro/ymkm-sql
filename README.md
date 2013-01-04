# ymkm-sql, an SQL query builder

## About

This library provides an SQL builder with a syntax inspired from the S-Expr style found in LISP or Scheme.
That is, Where conditions have the following form :
	$builder->addWhere(<Operand>, <expr...>)
where `<Operand>` can be any valid PHP closure (the library supplies commonly used ones in `YMKM_Query`, e.g. `YMKM_Query::and_` or `YMKM_Query::or_`.

This syntax makes the parsing and composition easy, as `<expr>` can either be a primitive value (String, numeric, array of primitives...), or an array of the type
	array(<Operand>, <expr...>)
ie. the same input recognized by `addWhere`.

For instance, the following example
	$builder->addWhere(YMKM_Query::and_(),
				array(YMKM_Query::eq(), 't1.col', '=foo'),
				array(YMKM_Query::ge(), 't2.col', '=10))
will produce the following SQL part :
	WHERE t1.col = 'foo' AND t2.col >= 10

## Licence

MIT

## Requirements

This library requires PHP 5.3+ , as it heavily uses Closures.

## Dependency :

- Zend, for `Zend_Loader`

## Usage

The SQL query can be built using the main class `Query_Builder`; it has a fluent interface.

### Create a new Builder

	$builder = YMKM_Query::create();

### Generating the SQL

	$builder->parse();

### Create a basic query

	$builder = YMKM_Query::create()
				->addCol('t1.foo')->addCol('t1.bar')
				->addFrom('table1', 't1')
				->addWhere(YMKM_Query::eq(), 'bar', 'foo')
				->setLimit(10, 10)
				->addOrder('foo', 'DESC')
				->addOrder(array('t1.bar'=>'DESC'));

The code above will produce the following SQL :

	SELECT t1.foo,t1.bar FROM table1 AS t1 WHERE (t1.bar = t1.foo) ORDER BY t1.foo DESC,t1.bar DESC LIMIT 10 OFFSET 10

The basic version of `addCol` adds a column to the SELECT statement, while `addFrom` takes a table name and an optional alias
and adds it to the FROM statement.
`addWhere` adds a WHERE condition, `addOrder` takes a column to ORDER BY, with an optional direction (`'ASC'` or `'DESC'`).
`setLimit` defines resp. the LIMIT and the OFFSET of the query.
Except for `setLimit`, these methods add up when called multiple times. `setLimit` will override the previously set values if called again.

`addOrder` can be used to add multiple columns to sort by using an array as the first argument (the second argument is then ignored) :

	$builer->addOrder(array('foo'=>'ASC', 'bar'=>'DESC'));

Produces :

	ORDER BY t1.foo ASC,t1.bar DESC;

> Note that no check is performed on the existence of tables and/or columns in the DB.
> However, columns references (ie. column names/aliases used outside the `addCol`)
> may omit the table name/alias. The parser will try to infer the table the column name/alias belongs to
> by looking up the table domain created through `addFrom`/`addJoin`.
> The example above produces an output where the table aliases were prepended to the column references
> with unspecified table references.
> An exception will be raised if the column name/alias without a table reference is ambiguous.
> The table reference can be either the actual table name or the alias defined through the query.

### Column aliases, SQL functions on Columns

The query above can be complexified a bit using the following code :

	$builder = YMKM_Query::create()
				->addCol('t1.foo', 'foo_alias')->addCol('t1.bar', 'bar_alias', YMKM_Query::max())
				->addFrom('table1', 't1')
				->addWhere(YMKM_Query::eq(), 'bar', 'foo')
				->setLimit(10, 10)
				->addOrder('foo', 'DESC')
				->addOrder(array('t1.bar'=>'DESC'));

Which will produce the following :

	SELECT t1.foo AS foo_alias,MAX(t1.bar) AS bar_alias FROM table1 AS t1 WHERE (t1.bar = t1.foo) ORDER BY t1.foo DESC,t1.bar DESC LIMIT 10 OFFSET 10

`addCol` accepts optional parameters which are the column alias, and an SQL function to apply, in this order.

### Define values to WHERE conditions

The previous examples were defining WHERE conditions between two table columns. Let's see how to filter columns on primitive values :

	$builder = YMKM_Query::create()
				->addCol('t1.foo', 'foo_alias')->addCol('t1.bar', 'bar_alias', YMKM_Query::max())
				->addFrom('table1', 't1')
				->addWhere(YMKM_Query::and_(),
					array(YMKM_Query::le(), 'bar', '=10'),
					array(YMKM_Query::like(), 'foo', '?text%'))
				->setLimit(10, 10)
				->addOrder('foo', 'DESC')
				->addOrder(array('t1.bar'=>'DESC'));

Again, the above code will yield the following :

	SELECT t1.foo AS foo_alias,MAX(t1.bar) AS bar_alias FROM table1 AS t1 WHERE ((t1.bar <= 10) AND (t1.foo LIKE ?)) ORDER BY t1.foo DESC,t1.bar DESC LIMIT 10 OFFSET 10

Here we used composition to `AND` two conditions on `t1.bar` and `t1.foo` : The `eq()` operator is used against `t1.bar` while `like()` is used against `t1.foo`.
Both are filtered using values, resp. an int and a string.

There are two ways to inject values to the query :
- Direct injection : '=<value>', which will output the value directly into the generated query, and thus can be very unsafe if the source of the value is unknown and is not sanitized.
- As a query parameter (prepared statement) : '?<value>', which will replace the value with a positional query parameter '?'.    
The ordered list of values to be replaced with can be obtained using

	$builder->getBindParams();

Some operators such as `and_` or `or_` can take any number of parameters, which allows to `and`|`or` any sequence of expressions.

> The `in()` operator takes an array of primitives as its input, each value of which has to be made into a query parameter.
> It is also possible to use the helper `$builder->addWhereIn(array(values))` to automatically transform each value inside the input array into positional arguments.

### Positional arguments

In `ORDER BY` and `GROUP BY` statements, it is possible to reference columns using their position in the `SELECT` clause,
using the special notation `#<position>`, e.g.

	$builder->addOrder('#1', 'ASC');
	$builder->addGroupBy('#1');

### Joining between multiple tables

It is possible to `INNER JOIN`, `LEFT JOIN` or `RIGHT JOIN` using resp. `addJoin`, `addLeftJoin` or `addRightJoin`.

All three methods can be used the same way :

	$builder->addJoin('table_to_join', 'join_alias', array(<operator>, <expr...>))

Where the third argument is of the same format as the conditions passed in as an array
to `addWhere`.

For example, the following :

	$builder = YMKM_Query::create()
				->addCol('t1.foo', 'foo_alias')->addCol('t1.bar', 'bar_alias', YMKM_Query::max())
				->addFrom('table1', 't1')
				->addJoin('table2', 't2', array(YMKM_Query::eq(), 't2.foo', 't1.foo'))
				->addWhere(YMKM_Query::and_(),
					array(YMKM_Query::le(), 'bar', '=10'),
					array(YMKM_Query::like(), 'foo', '?text%'))
				->setLimit(10, 10)
				->addOrder('foo', 'DESC')
				->addOrder(array('t1.bar'=>'DESC'));

will produce

	SELECT t1.foo AS foo_alias,MAX(t1.bar) AS bar_alias FROM table1 AS t1 JOIN table2 AS t2 ON ((t2.foo = t1.foo)) WHERE ((t1.bar <= 10) AND (t1.foo LIKE ?)) ORDER BY t1.foo DESC,t1.bar DESC LIMIT 10 OFFSET 10

### Subqueries

It is possible to nest queries inside `WHERE` conditions :

	$builder = YMKM_Query::create()
				->addCol('t1.foo', 'foo_alias')->addCol('t1.bar', 'bar_alias', YMKM_Query::max())
				->addFrom('table1', 't1')
				->addJoin('table2', 't2', array(YMKM_Query::eq(), 't2.foo', 't1.foo'))
				->addWhere(YMKM_Query::and_(),
					array(YMKM_Query::le(), 'bar', YMKM_Query::create()->addCol('t3.bar', 't3_bar', YMKM_Query::max())
																	   ->addFrom('table3', 't3')
																	   ->addWhere(YMKM_Query::eq(), 't3.bar', 'bar_alias')),
					array(YMKM_Query::like(), 'foo', '?text%'))
				->setLimit(10, 10)
				->addOrder('foo', 'DESC')
				->addOrder(array('t1.bar'=>'DESC'));

Will produce

	SELECT t1.foo AS foo_alias,MAX(t1.bar) AS bar_alias FROM table1 AS t1 JOIN table2 AS t2 ON ((t2.foo = t1.foo)) WHERE ((t1.bar <= (SELECT MAX(t3.bar) AS t3_bar FROM table3 AS t3 WHERE (t3.bar = t1.bar_alias))) AND (t1.foo LIKE ?)) ORDER BY t1.foo DESC,t1.bar DESC LIMIT 10 OFFSET 10

It is possible to reference columns defined in the main query inside subqueries, like the example above with `bar_alias`.
The table reference is not needed unless ambiguity occurs. In this case, the column alias was used instead of the table
name to disambiguate.

### Adding SELECT columns in bulk

`addCol` accepts an alternative syntax which allows to add columns in bulk from the same table :

	addCol($tblNameOrAlias, array($colDef|$colDef=>array([alias=>$alias], [fn=>Closure])))

The second argument is an array of column definitions, where $colDef can be :

- `[table_ref.]column_name`
- `=expression` (e.g. `SELECT 10 FROM ...`)

Column definitions can be array values or array keys. In the latter case, their value is yet another
array with additional properties : `alias` and|or `fn`.

### Using Groups

`GROUP BY` statement can be added using `addGroupBy`. Its parameter can either be a column reference (string)
or an array of column references.

> HAVING syntax is not yet implemented.

## Reference

#### `YMKM_Query`

Proxy to `YMKM_SQL_QueryBuilder`.

- `static create() : YMKM_SQL_QueryBuilder`
- `parse() : string`
- `addWhere() : YMKM_Query`
- `addWhereIn($col, array $listOfVals) : YMKM_Query`
- public interface from `YMKM_SQL_QueryBuilder`

#### `YMKM_SQL_QueryBuilder`

Main object that builds SQL queries

- `addCol($tcref, $tcrefsOrAlias=null, $fn=null)`
- `replaceCol(YMKM_SQL_Entity_Select $o, $tcref, $alias=null, $fn=null)`
- `removeCols($cols)`
- `addFrom($tref, $alias)`
- `addLeftJoin($tref, $trefAlias=null, $jConds=array())`
- `addRightJoin($tref, $trefAlias=null, $jConds=array())`
- `addJoin($tref, $trefAlias=null, $jConds=array())`
- `addWhereJoin($trefOrAlias, $jConds)`
- `addWhere()`
- `addOrder($tcref, $dir='ASC')`
- `addGroupBy($tcref)`
- `setLimit($limit, $offset)`
- `getBindParams()`
- `cols()`
- `tables()`
- `parse()`

Helpers :

- `tableDefinition($tref, $alias=null)`
- `tableReference($tref)`
- `colDefinition($cref, $tref=null, $alias=null, $fn=null)`
- `colReference($cref, $tref=null, $fn=null)`
- `orderColRef($tcref)`

### Predefined column operators

- `YMKM_Query::distinct()` : `DISTINCT` keyword
- `YMKM_Query::cnt()` : `COUNT`
- `YMKM_Query::max()` : `MAX`
- `YMKM_Query::sum()` : `SUM`

### Predefined where operators

- `YMKM_Query::and_()` : `AND` two expressions
- `YMKM_Query::or_()` : `OR` two expressions
- `YMKM_Query::in()` : `IN` a list of values
- `YMKM_Query::eq()` : `=` operator
- `YMKM_Query::le()` : `<=` operator
- `YMKM_Query::lt()` : `<` operator
- `YMKM_Query::gt()` : `\>` operator
- `YMKM_Query::ge()` : `>=` operator
- `YMKM_Query::like()` : `LIKE` operator
- `YMKM_Query::null()` : `IS NULL` condition
- `YMKM_Query::nnul()` : `IS NOT NULL` condition
- `YMKM_Query::pair()` : Joins two expressions with a comma `,`
- `YMKM_Query::match()` : For Full-text search operations , `MATCH <col> AGAINST(<value> IN BOOLEAN MODE)`

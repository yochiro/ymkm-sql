<?php
    ini_set('html_errors', true);
    require_once('Zend/Loader/Autoloader.php');
    $autoloader = Zend_Loader_Autoloader::getInstance();
    $autoloader->registerNamespace('YMKM_');
    $autoloader->setFallbackAutoloader(true);

    $builder = YMKM_Query::create()
            ->addCol('t1.foo')->addCol('t1.bar')
            ->addFrom('table1', 't1')
            ->addWhere(YMKM_Query::eq(), 'bar', 'foo')
            ->setLimit(10, 10)
            ->addOrder('foo', 'DESC')
            ->addOrder(array('t1.bar'=>'DESC'));
    echo $builder->parse() . PHP_EOL;

    $builder = YMKM_Query::create()
                ->addCol('t1.foo', 'foo_alias')->addCol('t1.bar', 'bar_alias', YMKM_Query::max())
                ->addFrom('table1', 't1')
                ->addWhere(YMKM_Query::eq(), 'bar', 'foo')
                ->setLimit(10, 10)
                ->addOrder('foo', 'DESC')
                ->addOrder(array('t1.bar'=>'DESC'));
    echo $builder->parse() . PHP_EOL;

    $builder = YMKM_Query::create()
                ->addCol('t1.foo', 'foo_alias')->addCol('t1.bar', 'bar_alias', YMKM_Query::max())
                ->addFrom('table1', 't1')
                ->addWhere(YMKM_Query::and_(),
                    array(YMKM_Query::le(), 'bar', '=10'),
                    array(YMKM_Query::like(), 'foo', '?text%'))
                ->setLimit(10, 10)
                ->addOrder('foo', 'DESC')
                ->addOrder(array('t1.bar'=>'DESC'));
    echo $builder->parse() . PHP_EOL;

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
    echo $builder->parse() . PHP_EOL;

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
    echo $builder->parse() . PHP_EOL;

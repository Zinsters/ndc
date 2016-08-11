<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application;

use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\Mvc\MvcEvent;
use Zend\Session\Container;

class Module implements ConfigProviderInterface
{
    const VERSION = '3.0.0dev';

    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }
    
    public function getServiceConfig()
    {
        return [
            'factories' => [
                Model\UserTable::class => function($container) {
                    $tableGateway = $container->get(Model\UserTableGateway::class);
                    return new Model\UserTable($tableGateway);
                },
                Model\UserTableGateway::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Model\User());
                    return new TableGateway('users_data', $dbAdapter, null, $resultSetPrototype);
                },
                Model\InvoiceTable::class => function($container) {
                    $tableGateway = $container->get(Model\InvoiceTableGateway::class);
                    return new Model\InvoiceTable($tableGateway);
                },
                Model\InvoiceTableGateway::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Model\Invoice());
                    return new TableGateway('ndc_invoices', $dbAdapter, null, $resultSetPrototype);
                },
            ],
        ];
    }

    public function getControllerConfig()
    {
        return [
            'factories' => [
                Controller\CustomerController::class => function($container) {
                    return new Controller\CustomerController(
                        $container->get(Model\UserTable::class),
                        $container->get(Model\InvoiceTable::class)
                    );
                },
            ],
        ];
    }
    
    public function onBootstrap(MvcEvent $e)
    {
        $sm = $e->getApplication()->getServiceManager();
        $view = $e->getViewModel();

        $router = $sm->get('router');
        $request = $sm->get('request');
        $matchedRoute = $router->match($request);

        $params = $matchedRoute->getParams();

        $controller = $params['controller'];
        $action = $params['action'];

        $module_array = explode('\\', $controller);
        $module = array_pop($module_array);

        $route = $matchedRoute->getMatchedRouteName();

        $view->setVariables(
            array(
                'currentModuleName' => $module,
                'currentControllerName' => $controller,
                'currentActionName' => $action,
                'currentRouteName' => $route,
            )
        );

        $container = new Container( 'default' );        
        if ( isset ( $container->currentCustomerUserid ) ) {
        	$userTable = $sm->get(Model\UserTable::class);
        	$currentCustomer = $userTable->getByUserid( $container->currentCustomerUserid );

        	$view->setVariables(
            	array(
                	'currentCustomer' => $currentCustomer,
            	)
        	);
        }
    }
}

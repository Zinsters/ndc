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
use Locale;

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
                Model\Db\UserTable::class => function($container) {
                    $tableGateway = $container->get(Model\Db\UserTableGateway::class);
                    return new Model\Db\UserTable($tableGateway);
                },
                Model\Db\UserTableGateway::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Model\Db\Row\User());
                    return new TableGateway('users_data', $dbAdapter, null, $resultSetPrototype);
                },
                Model\Db\InvoiceTable::class => function($container) {
                    $tableGateway = $container->get(Model\Db\InvoiceTableGateway::class);
                    return new Model\Db\InvoiceTable($tableGateway);
                },
                Model\Db\InvoiceTableGateway::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Model\Db\Row\Invoice());
                    return new TableGateway('ndc_invoices', $dbAdapter, null, $resultSetPrototype);
                },
                Model\Db\ConsultTable::class => function($container) {
                    $tableGateway = $container->get(Model\Db\ConsultTableGateway::class);
                    return new Model\Db\ConsultTable($tableGateway);
                },
                Model\Db\ConsultTableGateway::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Model\Db\Row\Consult());
                    return new TableGateway('ndc_consults', $dbAdapter, null, $resultSetPrototype);
                },
                Model\Db\EmployeeTable::class => function($container) {
                    $tableGateway = $container->get(Model\Db\EmployeeTableGateway::class);
                    return new Model\Db\EmployeeTable($tableGateway);
                },
                Model\Db\EmployeeTableGateway::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Model\Db\Row\Employee());
                    return new TableGateway('ndc_employees', $dbAdapter, null, $resultSetPrototype);
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
                        $container->get(Model\Db\UserTable::class),
                        $container->get(Model\Db\InvoiceTable::class),
                        $container->get(Model\Db\ConsultTable::class),
                        $container->get(Model\Db\EmployeeTable::class)
                    );
                },
            ],
        ];
    }
    
    public function onBootstrap(MvcEvent $e)
    {
		date_default_timezone_set('Europe/Amsterdam');
		setlocale(LC_TIME, 'nl_NL.utf8');
		
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
        	$userTable = $sm->get(Model\Db\UserTable::class);
        	$currentCustomer = $userTable->getCustomer( $container->currentCustomerUserid );

        	$view->setVariables(
            	array(
                	'currentCustomer' => $currentCustomer,
            	)
        	);
        }
    }
}

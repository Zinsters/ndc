<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application;

use Zend\Router\Http\Literal;
use Zend\Router\Http\Segment;
use Zend\ServiceManager\Factory\InvokableFactory;
use Zend\Navigation\Service\NavigationAbstractServiceFactory;

return [
    'router' => [
        'routes' => [
            'home' => [
                'type' => Literal::class,
                'options' => [
                    'route'    => '/',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
            'index' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/index[/:action]',
                    'defaults' => [
                        'controller'    => Controller\IndexController::class,
                        'action'        => 'index',
                    ],
                ],
            ],
            'customer' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/customer[/:action]',
                    'defaults' => [
                        'controller'    => Controller\CustomerController::class,
                        'action'        => 'index',
                    ],
                ],
                'may_terminate' => true,
				'child_routes' => array(
					'view' => array(
						'type' => 'segment',
						'options' => array(
							'route'    => '/:userid',
							'defaults' => array(
								'action'     => 'view',
							),
							'constraints' => array(
								'userid' => '\d+'
							)
						),
					),
				),
            ],
        ],
    ],
    'controllers' => [
        'factories' => [
            Controller\IndexController::class => InvokableFactory::class,
        ],
    ],
    'view_manager' => [
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map' => [
            'layout/layout'           => __DIR__ . '/../view/layout/layout.phtml',
            'application/index/index' => __DIR__ . '/../view/application/index/index.phtml',
            'error/404'               => __DIR__ . '/../view/error/404.phtml',
            'error/index'             => __DIR__ . '/../view/error/index.phtml',
        ],
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
		'strategies' => [
            'ViewJsonStrategy',
        ],
    ],
    'navigation' => [
        'default' => [
            [
                'label'  => 'Klant',
                'route'  => 'customer',
                'pages' => [
                    [
                        'label'  => 'Nieuwe klant',
                        'route'  => 'customer',
                        'action' => 'new',
                    ],
                    [
                        'label'  => 'Kies een klant',
                        'route'  => 'customer',
                        'action' => 'index',
                    ],
                    [
                        'label'  => 'Profiel',
                        'route'  => 'customer',
                        'action' => 'view',
                    ],
                    [
                        'label'  => 'Afrekenen',
                        'route'  => 'customer',
                        'action' => 'invoice',
                    ],
                    [
                        'label'  => 'Rekeningen',
                        'route'  => 'customer',
                        'action' => 'invoices',
                    ],
                ],
            ],
        ],
        'top' => [
            [
                'label'  => 'Profiel',
                'route'  => 'customer',
                'action' => 'view',
            ],
            [
                'label'  => 'Contact',
                'route'  => 'customer',
                'action' => 'contact',
            ],
            [
                'label'  => 'Klant',
                'route'  => 'customer',
                'action' => 'customer',
            ],
            [
                'label'  => 'Intake',
                'route'  => 'customer',
                'action' => 'intake',
            ],
            [
                'label'  => 'Dieet',
                'route'  => 'customer',
                'action' => 'diet',
            ],
            [
                'label'  => 'Medisch',
                'route'  => 'customer',
                'action' => 'medical',
            ],
            [
                'label'  => 'Metingen',
                'route'  => 'customer',
                'action' => 'measurements',
            ],
            [
                'label'  => 'Consult',
                'route'  => 'customer',
                'action' => 'newconsult',
            ],
        ],
    ],
    'service_manager' => [
        'abstract_factories' => [
            NavigationAbstractServiceFactory::class,
        ],
    ],
];

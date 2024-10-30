<?php

/**
 * This file has been auto-generated
 * by the Symfony Routing Component.
 */

return [
    false, // $matchHost
    [ // $staticRoutes
        '/api/languages' => [[['_route' => 'api_get_languages', '_controller' => 'Module\\Languages\\Controller\\Api\\LanguagesController::getLanguages'], null, ['GET' => 0], null, false, false, null]],
        '/api/employees' => [[['_route' => 'api_get_employees', '_controller' => 'Module\\Employees\\Controller\\Api\\EmployeesController::getEmployees'], null, ['GET' => 0], null, false, false, null]],
    ],
    [ // $regexpList
        0 => '{^(?'
                .'|/_error/(\\d+)(?:\\.([^/]++))?(*:35)'
                .'|/api/(?'
                    .'|languages/(?'
                        .'|([^/]++)(*:71)'
                        .'|add(*:81)'
                        .'|update/([^/]++)(*:103)'
                        .'|delete/([^/]++)(*:126)'
                    .')'
                    .'|employees/(?'
                        .'|([^/]++)(*:156)'
                        .'|add(*:167)'
                        .'|update(?'
                            .'|/([^/]++)(*:193)'
                            .'|\\-field/([^/]++)(*:217)'
                        .')'
                        .'|delete/([^/]++)(*:241)'
                    .')'
                .')'
            .')/?$}sDu',
    ],
    [ // $dynamicRoutes
        35 => [[['_route' => '_preview_error', '_controller' => 'error_controller::preview', '_format' => 'html'], ['code', '_format'], null, null, false, true, null]],
        71 => [[['_route' => 'api_get_language', '_controller' => 'Module\\Languages\\Controller\\Api\\LanguagesController::getLanguage'], ['id'], ['GET' => 0], null, false, true, null]],
        81 => [[['_route' => 'api_add_language', '_controller' => 'Module\\Languages\\Controller\\Api\\LanguagesController::addLanguage'], [], ['POST' => 0], null, false, false, null]],
        103 => [[['_route' => 'api_update_language', '_controller' => 'Module\\Languages\\Controller\\Api\\LanguagesController::updateLanguage'], ['id'], ['PUT' => 0], null, false, true, null]],
        126 => [[['_route' => 'api_delete_language', '_controller' => 'Module\\Languages\\Controller\\Api\\LanguagesController::deleteLanguage'], ['id'], ['DELETE' => 0], null, false, true, null]],
        156 => [[['_route' => 'api_get_employee', '_controller' => 'Module\\Employees\\Controller\\Api\\EmployeesController::getEmployee'], ['id'], ['GET' => 0], null, false, true, null]],
        167 => [[['_route' => 'api_add_employee', '_controller' => 'Module\\Employees\\Controller\\Api\\EmployeesController::addEmployee'], [], ['POST' => 0], null, false, false, null]],
        193 => [[['_route' => 'api_update_employee', '_controller' => 'Module\\Employees\\Controller\\Api\\EmployeesController::updateEmployee'], ['id'], ['PUT' => 0], null, false, true, null]],
        217 => [[['_route' => 'api_update_employee_field', '_controller' => 'Module\\Employees\\Controller\\Api\\EmployeesController::updateEmployeeField'], ['id'], ['PATCH' => 0], null, false, true, null]],
        241 => [
            [['_route' => 'api_delete_employee', '_controller' => 'Module\\Employees\\Controller\\Api\\EmployeesController::deleteEmployee'], ['id'], ['DELETE' => 0], null, false, true, null],
            [null, null, null, null, false, false, 0],
        ],
    ],
    null, // $checkCondition
];

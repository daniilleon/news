<?php

/**
 * This file has been auto-generated
 * by the Symfony Routing Component.
 */

return [
    false, // $matchHost
    [ // $staticRoutes
        '/api/languages' => [[['_route' => 'api_get_languages', '_controller' => 'Module\\Languages\\Controller\\Api\\LanguageController::getLanguages'], null, ['GET' => 0], null, false, false, null]],
        '/api/employees' => [[['_route' => 'api_get_employees', '_controller' => 'Module\\Employees\\Controller\\Api\\EmployeeController::getEmployees'], null, ['GET' => 0], null, false, false, null]],
    ],
    [ // $regexpList
        0 => '{^(?'
                .'|/api/(?'
                    .'|languages/(?'
                        .'|([^/]++)(*:36)'
                        .'|add(*:46)'
                        .'|delete/([^/]++)(*:68)'
                        .'|update/([^/]++)(*:90)'
                    .')'
                    .'|employees/(?'
                        .'|([^/]++)(*:119)'
                        .'|add(*:130)'
                        .'|update(?'
                            .'|/([^/]++)(*:156)'
                            .'|\\-field/([^/]++)(*:180)'
                        .')'
                        .'|delete/([^/]++)(*:204)'
                    .')'
                .')'
            .')/?$}sDu',
    ],
    [ // $dynamicRoutes
        36 => [[['_route' => 'api_get_id_language', '_controller' => 'Module\\Languages\\Controller\\Api\\LanguageController::getLanguage'], ['id'], ['GET' => 0], null, false, true, null]],
        46 => [[['_route' => 'api_add_language', '_controller' => 'Module\\Languages\\Controller\\Api\\LanguageController::addLanguage'], [], ['POST' => 0], null, false, false, null]],
        68 => [[['_route' => 'api_delete_language', '_controller' => 'Module\\Languages\\Controller\\Api\\LanguageController::deleteLanguage'], ['id'], ['DELETE' => 0], null, false, true, null]],
        90 => [[['_route' => 'api_update_language', '_controller' => 'Module\\Languages\\Controller\\Api\\LanguageController::updateLanguage'], ['id'], ['PUT' => 0], null, false, true, null]],
        119 => [[['_route' => 'api_get_employee', '_controller' => 'Module\\Employees\\Controller\\Api\\EmployeeController::getEmployee'], ['id'], ['GET' => 0], null, false, true, null]],
        130 => [[['_route' => 'api_add_employee', '_controller' => 'Module\\Employees\\Controller\\Api\\EmployeeController::addEmployee'], [], ['POST' => 0], null, false, false, null]],
        156 => [[['_route' => 'api_update_employee', '_controller' => 'Module\\Employees\\Controller\\Api\\EmployeeController::updateEmployee'], ['id'], ['PUT' => 0], null, false, true, null]],
        180 => [[['_route' => 'api_update_employee_field', '_controller' => 'Module\\Employees\\Controller\\Api\\EmployeeController::updateEmployeeField'], ['id'], ['PATCH' => 0], null, false, true, null]],
        204 => [
            [['_route' => 'api_delete_employee', '_controller' => 'Module\\Employees\\Controller\\Api\\EmployeeController::deleteEmployee'], ['id'], ['DELETE' => 0], null, false, true, null],
            [null, null, null, null, false, false, 0],
        ],
    ],
    null, // $checkCondition
];

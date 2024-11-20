<?php

/**
 * This file has been auto-generated
 * by the Symfony Routing Component.
 */

return [
    false, // $matchHost
    [ // $staticRoutes
        '/api/core/languages' => [[['_route' => 'api_get_languages', '_controller' => 'Module\\Core\\Languages\\Controller\\Api\\LanguagesController::getLanguages'], null, ['GET' => 0], null, true, false, null]],
        '/api/core/categories' => [[['_route' => 'api_categories_get_all', '_controller' => 'Module\\Core\\Categories\\Controller\\Api\\CategoriesController::getCategories'], null, ['GET' => 0], null, true, false, null]],
        '/api/core/countries' => [[['_route' => 'api_get_countries', '_controller' => 'Module\\Core\\Countries\\Controller\\Api\\CountriesController::getCountries'], null, ['GET' => 0], null, true, false, null]],
        '/api/shared/industries' => [[['_route' => 'api_industries_get_all', '_controller' => 'Module\\Shared\\Industries\\Controller\\Api\\IndustriesController::getIndustries'], null, ['GET' => 0], null, true, false, null]],
        '/api/shared/rolestatus' => [[['_route' => 'api_rolestatus_get_all', '_controller' => 'Module\\Shared\\RoleStatus\\Controller\\Api\\RoleStatusController::getRoleStatus'], null, ['GET' => 0], null, true, false, null]],
        '/api/employees/staff' => [[['_route' => 'api_employees_staff_get_all', '_controller' => 'Module\\Employees\\Employees\\Controller\\Api\\EmployeesController::getEmployees'], null, ['GET' => 0], null, true, false, null]],
        '/api/employees/job_title' => [[['_route' => 'api_employees_jobtitle_get_all', '_controller' => 'Module\\Employees\\EmployeesJobTitle\\Controller\\Api\\EmployeesJobTitleController::getEmployeesJobTitle'], null, ['GET' => 0], null, true, false, null]],
        '/api/persons/maritalstatus' => [[['_route' => 'api_persons_maritalstatus_get_all', '_controller' => 'Module\\Persons\\MaritalStatus\\Controller\\Api\\MaritalStatusController::getMaritalStatus'], null, ['GET' => 0], null, true, false, null]],
        '/api/persons/educationlevels' => [[['_route' => 'api_persons_educationlevels_get_all', '_controller' => 'Module\\Persons\\EducationLevels\\Controller\\Api\\EducationLevelsController::getEducationLevels'], null, ['GET' => 0], null, true, false, null]],
    ],
    [ // $regexpList
        0 => '{^(?'
                .'|/_error/(\\d+)(?:\\.([^/]++))?(*:35)'
                .'|/api/(?'
                    .'|core/(?'
                        .'|languages/(?'
                            .'|([^/]++)(*:79)'
                            .'|add(*:89)'
                            .'|([^/]++)/(?'
                                .'|update(*:114)'
                                .'|delete(*:128)'
                            .')'
                            .'|seed(*:141)'
                        .')'
                        .'|c(?'
                            .'|ategories/(?'
                                .'|([^/]++)(*:175)'
                                .'|add(*:186)'
                                .'|([^/]++)/(?'
                                    .'|up(?'
                                        .'|load\\-image(*:222)'
                                        .'|date(?'
                                            .'|(*:237)'
                                            .'|\\-translation/([^/]++)(*:267)'
                                        .')'
                                    .')'
                                    .'|add\\-translation(*:293)'
                                    .'|delete(?'
                                        .'|\\-translation/([^/]++)(*:332)'
                                        .'|(*:340)'
                                    .')'
                                .')'
                                .'|seed(*:354)'
                            .')'
                            .'|ountries/(?'
                                .'|([^/]++)(*:383)'
                                .'|add(*:394)'
                                .'|([^/]++)/(?'
                                    .'|up(?'
                                        .'|load\\-image(*:430)'
                                        .'|date(?'
                                            .'|(*:445)'
                                            .'|\\-translation/([^/]++)(*:475)'
                                        .')'
                                    .')'
                                    .'|add\\-translation(*:501)'
                                    .'|delete(?'
                                        .'|\\-translation/([^/]++)(*:540)'
                                        .'|(*:548)'
                                    .')'
                                .')'
                                .'|seed(*:562)'
                            .')'
                        .')'
                    .')'
                    .'|shared/(?'
                        .'|industries/(?'
                            .'|([^/]++)(*:605)'
                            .'|add(*:616)'
                            .'|([^/]++)/(?'
                                .'|up(?'
                                    .'|load\\-image(*:652)'
                                    .'|date(?'
                                        .'|(*:667)'
                                        .'|\\-translation/([^/]++)(*:697)'
                                    .')'
                                .')'
                                .'|add\\-translation(*:723)'
                                .'|delete(?'
                                    .'|\\-translation/([^/]++)(*:762)'
                                    .'|(*:770)'
                                .')'
                            .')'
                            .'|seed(*:784)'
                        .')'
                        .'|rolestatus/(?'
                            .'|([^/]++)(*:815)'
                            .'|add(*:826)'
                            .'|([^/]++)/(?'
                                .'|add\\-translation(*:862)'
                                .'|update(?'
                                    .'|(*:879)'
                                    .'|\\-translation/([^/]++)(*:909)'
                                .')'
                                .'|delete(?'
                                    .'|\\-translation/([^/]++)(*:949)'
                                    .'|(*:957)'
                                .')'
                            .')'
                            .'|seed(*:971)'
                        .')'
                    .')'
                    .'|employees/(?'
                        .'|staff/(?'
                            .'|([^/]++)(*:1011)'
                            .'|add(*:1023)'
                            .'|([^/]++)/(?'
                                .'|update(*:1050)'
                                .'|toggle\\-status(*:1073)'
                                .'|delete(*:1088)'
                            .')'
                            .'|seed(*:1102)'
                        .')'
                        .'|job_title/(?'
                            .'|([^/]++)(*:1133)'
                            .'|add(*:1145)'
                            .'|([^/]++)/(?'
                                .'|add\\-translation(*:1182)'
                                .'|update(?'
                                    .'|(*:1200)'
                                    .'|\\-translation/([^/]++)(*:1231)'
                                .')'
                                .'|delete(?'
                                    .'|\\-translation/([^/]++)(*:1272)'
                                    .'|(*:1281)'
                                .')'
                            .')'
                            .'|seed(*:1296)'
                        .')'
                    .')'
                    .'|persons/(?'
                        .'|maritalstatus/(?'
                            .'|([^/]++)(*:1343)'
                            .'|add(*:1355)'
                            .'|([^/]++)/(?'
                                .'|add\\-translation(*:1392)'
                                .'|update(?'
                                    .'|(*:1410)'
                                    .'|\\-translation/([^/]++)(*:1441)'
                                .')'
                                .'|delete(?'
                                    .'|\\-translation/([^/]++)(*:1482)'
                                    .'|(*:1491)'
                                .')'
                            .')'
                            .'|seed(*:1506)'
                        .')'
                        .'|educationlevels/(?'
                            .'|([^/]++)(*:1543)'
                            .'|add(*:1555)'
                            .'|([^/]++)/(?'
                                .'|add\\-translation(*:1592)'
                                .'|update(?'
                                    .'|(*:1610)'
                                    .'|\\-translation/([^/]++)(*:1641)'
                                .')'
                                .'|delete(?'
                                    .'|\\-translation/([^/]++)(*:1682)'
                                    .'|(*:1691)'
                                .')'
                            .')'
                            .'|seed(*:1706)'
                        .')'
                    .')'
                .')'
            .')/?$}sDu',
    ],
    [ // $dynamicRoutes
        35 => [[['_route' => '_preview_error', '_controller' => 'error_controller::preview', '_format' => 'html'], ['code', '_format'], null, null, false, true, null]],
        79 => [[['_route' => 'api_get_language', '_controller' => 'Module\\Core\\Languages\\Controller\\Api\\LanguagesController::getLanguage'], ['id'], ['GET' => 0], null, false, true, null]],
        89 => [[['_route' => 'api_add_language', '_controller' => 'Module\\Core\\Languages\\Controller\\Api\\LanguagesController::addLanguage'], [], ['POST' => 0], null, false, false, null]],
        114 => [[['_route' => 'api_update_language', '_controller' => 'Module\\Core\\Languages\\Controller\\Api\\LanguagesController::updateLanguage'], ['id'], ['PUT' => 0], null, false, false, null]],
        128 => [[['_route' => 'api_delete_language', '_controller' => 'Module\\Core\\Languages\\Controller\\Api\\LanguagesController::deleteLanguage'], ['id'], ['DELETE' => 0], null, false, false, null]],
        141 => [[['_route' => 'api_seed_languages', '_controller' => 'Module\\Core\\Languages\\Controller\\Api\\LanguagesController::seedLanguages'], [], ['POST' => 0], null, false, false, null]],
        175 => [[['_route' => 'api_categories_get_id', '_controller' => 'Module\\Core\\Categories\\Controller\\Api\\CategoriesController::getCategory'], ['categoryId'], ['GET' => 0], null, false, true, null]],
        186 => [[['_route' => 'api_categories_add', '_controller' => 'Module\\Core\\Categories\\Controller\\Api\\CategoriesController::addCategory'], [], ['POST' => 0], null, false, false, null]],
        222 => [[['_route' => 'api_categories_upload_image', '_controller' => 'Module\\Core\\Categories\\Controller\\Api\\CategoriesController::updateCategoryImage'], ['categoryId'], ['POST' => 0], null, false, false, null]],
        237 => [[['_route' => 'api_categories_update_id', '_controller' => 'Module\\Core\\Categories\\Controller\\Api\\CategoriesController::updateCategoryLink'], ['categoryId'], ['PUT' => 0], null, false, false, null]],
        267 => [[['_route' => 'api_categories_update_translation', '_controller' => 'Module\\Core\\Categories\\Controller\\Api\\CategoriesController::updateCategoryTranslation'], ['categoryId', 'translationId'], ['PUT' => 0], null, false, true, null]],
        293 => [[['_route' => 'api_categories_add_translation', '_controller' => 'Module\\Core\\Categories\\Controller\\Api\\CategoriesController::addCategoryTranslation'], ['categoryId'], ['POST' => 0], null, false, false, null]],
        332 => [[['_route' => 'api_categories_delete_translation', '_controller' => 'Module\\Core\\Categories\\Controller\\Api\\CategoriesController::deleteCategoryTranslation'], ['categoryId', 'translationId'], ['DELETE' => 0], null, false, true, null]],
        340 => [[['_route' => 'api_categories_delete_id', '_controller' => 'Module\\Core\\Categories\\Controller\\Api\\CategoriesController::deleteCategory'], ['id'], ['DELETE' => 0], null, false, false, null]],
        354 => [[['_route' => 'api_categories_seed', '_controller' => 'Module\\Core\\Categories\\Controller\\Api\\CategoriesController::seedCategoriesAndTranslations'], [], ['POST' => 0], null, false, false, null]],
        383 => [[['_route' => 'api_get_country', '_controller' => 'Module\\Core\\Countries\\Controller\\Api\\CountriesController::getCountry'], ['countryId'], ['GET' => 0], null, false, true, null]],
        394 => [[['_route' => 'api_add_country', '_controller' => 'Module\\Core\\Countries\\Controller\\Api\\CountriesController::addCountry'], [], ['POST' => 0], null, false, false, null]],
        430 => [[['_route' => 'api_update_country_image', '_controller' => 'Module\\Core\\Countries\\Controller\\Api\\CountriesController::updateCountryImage'], ['countryId'], ['POST' => 0], null, false, false, null]],
        445 => [[['_route' => 'api_update_country', '_controller' => 'Module\\Core\\Countries\\Controller\\Api\\CountriesController::updateCountryLink'], ['countryId'], ['PUT' => 0], null, false, false, null]],
        475 => [[['_route' => 'api_update_country_translation', '_controller' => 'Module\\Core\\Countries\\Controller\\Api\\CountriesController::updateCountryTranslation'], ['countryId', 'translationId'], ['PUT' => 0], null, false, true, null]],
        501 => [[['_route' => 'api_add_country_translation', '_controller' => 'Module\\Core\\Countries\\Controller\\Api\\CountriesController::addCountryTranslation'], ['countryId'], ['POST' => 0], null, false, false, null]],
        540 => [[['_route' => 'api_delete_country_translation', '_controller' => 'Module\\Core\\Countries\\Controller\\Api\\CountriesController::deleteCountryTranslation'], ['countryId', 'translationId'], ['DELETE' => 0], null, false, true, null]],
        548 => [[['_route' => 'api_delete_country', '_controller' => 'Module\\Core\\Countries\\Controller\\Api\\CountriesController::deleteCountry'], ['countryId'], ['DELETE' => 0], null, false, false, null]],
        562 => [[['_route' => 'api_seed_countries_and_translations', '_controller' => 'Module\\Core\\Countries\\Controller\\Api\\CountriesController::seedCountriesAndTranslations'], [], ['POST' => 0], null, false, false, null]],
        605 => [[['_route' => 'api_industries_get_id', '_controller' => 'Module\\Shared\\Industries\\Controller\\Api\\IndustriesController::getIndustry'], ['industryId'], ['GET' => 0], null, false, true, null]],
        616 => [[['_route' => 'api_industries_add', '_controller' => 'Module\\Shared\\Industries\\Controller\\Api\\IndustriesController::addIndustry'], [], ['POST' => 0], null, false, false, null]],
        652 => [[['_route' => 'api_industries_upload_image', '_controller' => 'Module\\Shared\\Industries\\Controller\\Api\\IndustriesController::updateIndustryImage'], ['industryId'], ['POST' => 0], null, false, false, null]],
        667 => [[['_route' => 'api_industries_update_id', '_controller' => 'Module\\Shared\\Industries\\Controller\\Api\\IndustriesController::updateIndustryLink'], ['industryId'], ['PUT' => 0], null, false, false, null]],
        697 => [[['_route' => 'api_industries_update_translation', '_controller' => 'Module\\Shared\\Industries\\Controller\\Api\\IndustriesController::updateIndustryTranslation'], ['industryId', 'translationId'], ['PUT' => 0], null, false, true, null]],
        723 => [[['_route' => 'api_industries_add_translation', '_controller' => 'Module\\Shared\\Industries\\Controller\\Api\\IndustriesController::addIndustryTranslation'], ['industryId'], ['POST' => 0], null, false, false, null]],
        762 => [[['_route' => 'api_industries_delete_translation', '_controller' => 'Module\\Shared\\Industries\\Controller\\Api\\IndustriesController::deleteIndustryTranslation'], ['industryId', 'translationId'], ['DELETE' => 0], null, false, true, null]],
        770 => [[['_route' => 'api_industries_delete_id', '_controller' => 'Module\\Shared\\Industries\\Controller\\Api\\IndustriesController::deleteIndustry'], ['id'], ['DELETE' => 0], null, false, false, null]],
        784 => [[['_route' => 'api_industries_seed', '_controller' => 'Module\\Shared\\Industries\\Controller\\Api\\IndustriesController::seedIndustriesAndTranslations'], [], ['POST' => 0], null, false, false, null]],
        815 => [[['_route' => 'api_rolestatus_get_id', '_controller' => 'Module\\Shared\\RoleStatus\\Controller\\Api\\RoleStatusController::getRoleStatusId'], ['roleStatusId'], ['GET' => 0], null, false, true, null]],
        826 => [[['_route' => 'api_rolestatus_add', '_controller' => 'Module\\Shared\\RoleStatus\\Controller\\Api\\RoleStatusController::addRoleStatus'], [], ['POST' => 0], null, false, false, null]],
        862 => [[['_route' => 'api_rolestatus_add_translation', '_controller' => 'Module\\Shared\\RoleStatus\\Controller\\Api\\RoleStatusController::addRoleStatusTranslation'], ['roleStatusId'], ['POST' => 0], null, false, false, null]],
        879 => [[['_route' => 'api_rolestatus_update_id', '_controller' => 'Module\\Shared\\RoleStatus\\Controller\\Api\\RoleStatusController::updateRoleStatusCode'], ['roleStatusId'], ['PUT' => 0], null, false, false, null]],
        909 => [[['_route' => 'api_rolestatus_update_translation', '_controller' => 'Module\\Shared\\RoleStatus\\Controller\\Api\\RoleStatusController::updateRoleStatusTranslation'], ['roleStatusId', 'translationId'], ['PUT' => 0], null, false, true, null]],
        949 => [[['_route' => 'api_rolestatus_delete_translation', '_controller' => 'Module\\Shared\\RoleStatus\\Controller\\Api\\RoleStatusController::deleteRoleStatusTranslation'], ['roleStatusId', 'translationId'], ['DELETE' => 0], null, false, true, null]],
        957 => [[['_route' => 'api_rolestatus_delete_id', '_controller' => 'Module\\Shared\\RoleStatus\\Controller\\Api\\RoleStatusController::deleteRoleStatus'], ['roleStatusId'], ['DELETE' => 0], null, true, false, null]],
        971 => [[['_route' => 'api_rolestatus_seed', '_controller' => 'Module\\Shared\\RoleStatus\\Controller\\Api\\RoleStatusController::seedRoleStatusAndTranslations'], [], ['POST' => 0], null, false, false, null]],
        1011 => [[['_route' => 'api_employees_staff_get_id', '_controller' => 'Module\\Employees\\Employees\\Controller\\Api\\EmployeesController::getEmployee'], ['id'], ['GET' => 0], null, false, true, null]],
        1023 => [[['_route' => 'api_employees_staff_add', '_controller' => 'Module\\Employees\\Employees\\Controller\\Api\\EmployeesController::addEmployee'], [], ['POST' => 0], null, false, false, null]],
        1050 => [[['_route' => 'api_employees_staff_update_id', '_controller' => 'Module\\Employees\\Employees\\Controller\\Api\\EmployeesController::updateEmployee'], ['id'], ['PUT' => 0], null, false, false, null]],
        1073 => [[['_route' => 'api_employees_staff_toggle_status', '_controller' => 'Module\\Employees\\Employees\\Controller\\Api\\EmployeesController::toggleEmployeeStatus'], ['id'], ['PUT' => 0], null, false, false, null]],
        1088 => [[['_route' => 'api_employees_staff_delete_id', '_controller' => 'Module\\Employees\\Employees\\Controller\\Api\\EmployeesController::deleteEmployee'], ['id'], ['DELETE' => 0], null, false, false, null]],
        1102 => [[['_route' => 'api_employees_staff_seed', '_controller' => 'Module\\Employees\\Employees\\Controller\\Api\\EmployeesController::seedEmployeeAndTranslations'], [], ['POST' => 0], null, false, false, null]],
        1133 => [[['_route' => 'api_employees_jobtitle_get_id', '_controller' => 'Module\\Employees\\EmployeesJobTitle\\Controller\\Api\\EmployeesJobTitleController::getEmployeeJobTitle'], ['employeeJobTitleId'], ['GET' => 0], null, false, true, null]],
        1145 => [[['_route' => 'api_employees_jobtitle_add', '_controller' => 'Module\\Employees\\EmployeesJobTitle\\Controller\\Api\\EmployeesJobTitleController::addEmployeeJobTitle'], [], ['POST' => 0], null, false, false, null]],
        1182 => [[['_route' => 'api_employees_jobtitle_add_translation', '_controller' => 'Module\\Employees\\EmployeesJobTitle\\Controller\\Api\\EmployeesJobTitleController::addEmployeeJobTitleTranslation'], ['employeeJobTitleId'], ['POST' => 0], null, false, false, null]],
        1200 => [[['_route' => 'api_employees_jobtitle_update_id', '_controller' => 'Module\\Employees\\EmployeesJobTitle\\Controller\\Api\\EmployeesJobTitleController::updateEmployeeJobTitleCode'], ['employeeJobTitleId'], ['PUT' => 0], null, false, false, null]],
        1231 => [[['_route' => 'api_employees_jobtitle_update_translation', '_controller' => 'Module\\Employees\\EmployeesJobTitle\\Controller\\Api\\EmployeesJobTitleController::updateEmployeeJobTitleTranslation'], ['employeeJobTitleId', 'translationId'], ['PUT' => 0], null, false, true, null]],
        1272 => [[['_route' => 'api_employees_jobtitle_delete_translation', '_controller' => 'Module\\Employees\\EmployeesJobTitle\\Controller\\Api\\EmployeesJobTitleController::deleteEmployeeJobTitleTranslation'], ['employeeJobTitleId', 'translationId'], ['DELETE' => 0], null, false, true, null]],
        1281 => [[['_route' => 'api_employees_jobtitle_delete_id', '_controller' => 'Module\\Employees\\EmployeesJobTitle\\Controller\\Api\\EmployeesJobTitleController::deleteEmployeeJobTitle'], ['employeeJobTitleId'], ['DELETE' => 0], null, true, false, null]],
        1296 => [[['_route' => 'api_employees_jobtitle_seed', '_controller' => 'Module\\Employees\\EmployeesJobTitle\\Controller\\Api\\EmployeesJobTitleController::seedJobTitlesAndTranslations'], [], ['POST' => 0], null, false, false, null]],
        1343 => [[['_route' => 'api_persons_maritalstatus_get_id', '_controller' => 'Module\\Persons\\MaritalStatus\\Controller\\Api\\MaritalStatusController::getMaritalStatusId'], ['maritalStatusId'], ['GET' => 0], null, false, true, null]],
        1355 => [[['_route' => 'api_persons_maritalstatus_add', '_controller' => 'Module\\Persons\\MaritalStatus\\Controller\\Api\\MaritalStatusController::addMaritalStatus'], [], ['POST' => 0], null, false, false, null]],
        1392 => [[['_route' => 'api_persons_maritalstatus_add_translation', '_controller' => 'Module\\Persons\\MaritalStatus\\Controller\\Api\\MaritalStatusController::addMaritalStatusTranslation'], ['maritalStatusId'], ['POST' => 0], null, false, false, null]],
        1410 => [[['_route' => 'api_persons_maritalstatus_update_id', '_controller' => 'Module\\Persons\\MaritalStatus\\Controller\\Api\\MaritalStatusController::updateMaritalStatusCode'], ['maritalStatusId'], ['PUT' => 0], null, false, false, null]],
        1441 => [[['_route' => 'api_persons_maritalstatus_update_translation', '_controller' => 'Module\\Persons\\MaritalStatus\\Controller\\Api\\MaritalStatusController::updateMaritalStatusTranslation'], ['maritalStatusId', 'translationId'], ['PUT' => 0], null, false, true, null]],
        1482 => [[['_route' => 'api_persons_maritalstatus_delete_translation', '_controller' => 'Module\\Persons\\MaritalStatus\\Controller\\Api\\MaritalStatusController::deleteMaritalStatusTranslation'], ['maritalStatusId', 'translationId'], ['DELETE' => 0], null, false, true, null]],
        1491 => [[['_route' => 'api_persons_maritalstatus_delete_id', '_controller' => 'Module\\Persons\\MaritalStatus\\Controller\\Api\\MaritalStatusController::deleteMaritalStatus'], ['maritalStatusId'], ['DELETE' => 0], null, true, false, null]],
        1506 => [[['_route' => 'api_persons_maritalstatus_seed', '_controller' => 'Module\\Persons\\MaritalStatus\\Controller\\Api\\MaritalStatusController::seedMaritalStatusAndTranslations'], [], ['POST' => 0], null, false, false, null]],
        1543 => [[['_route' => 'api_persons_educationlevels_get_id', '_controller' => 'Module\\Persons\\EducationLevels\\Controller\\Api\\EducationLevelsController::getEducationLevelId'], ['educationLevelId'], ['GET' => 0], null, false, true, null]],
        1555 => [[['_route' => 'api_persons_educationlevels_add', '_controller' => 'Module\\Persons\\EducationLevels\\Controller\\Api\\EducationLevelsController::addEducationLevel'], [], ['POST' => 0], null, false, false, null]],
        1592 => [[['_route' => 'api_persons_educationlevels_add_translation', '_controller' => 'Module\\Persons\\EducationLevels\\Controller\\Api\\EducationLevelsController::addEducationLevelTranslation'], ['educationLevelId'], ['POST' => 0], null, false, false, null]],
        1610 => [[['_route' => 'api_persons_educationlevels_update_id', '_controller' => 'Module\\Persons\\EducationLevels\\Controller\\Api\\EducationLevelsController::updateEducationLevelCode'], ['educationLevelId'], ['PUT' => 0], null, false, false, null]],
        1641 => [[['_route' => 'api_persons_educationlevels_update_translation', '_controller' => 'Module\\Persons\\EducationLevels\\Controller\\Api\\EducationLevelsController::updateEducationLevelTranslation'], ['educationLevelId', 'translationId'], ['PUT' => 0], null, false, true, null]],
        1682 => [[['_route' => 'api_persons_educationlevels_delete_translation', '_controller' => 'Module\\Persons\\EducationLevels\\Controller\\Api\\EducationLevelsController::deleteEducationLevelTranslation'], ['educationLevelId', 'translationId'], ['DELETE' => 0], null, false, true, null]],
        1691 => [[['_route' => 'api_persons_educationlevels_delete_id', '_controller' => 'Module\\Persons\\EducationLevels\\Controller\\Api\\EducationLevelsController::deleteEducationLevel'], ['educationLevelId'], ['DELETE' => 0], null, false, false, null]],
        1706 => [
            [['_route' => 'api_persons_educationlevels_seed', '_controller' => 'Module\\Persons\\EducationLevels\\Controller\\Api\\EducationLevelsController::seedEducationLevelAndTranslations'], [], ['POST' => 0], null, false, false, null],
            [null, null, null, null, false, false, 0],
        ],
    ],
    null, // $checkCondition
];

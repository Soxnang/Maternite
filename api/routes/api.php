<?php
// Routes API
$routes = [
    ['POST',   '/auth/login',           'AuthController@login'],
    ['POST',   '/auth/logout',          'AuthController@logout'],
    ['GET',    '/patients',             'PatientController@index'],
    ['GET',    '/patients/{id}',        'PatientController@show'],
    ['POST',   '/patients',             'PatientController@store'],
    ['PUT',    '/patients/{id}',        'PatientController@update'],
    ['DELETE', '/patients/{id}',        'PatientController@destroy'],
    ['GET',    '/dossiers',             'DossierController@index'],
    ['GET',    '/dossiers/{id}',        'DossierController@show'],
    ['POST',   '/dossiers',             'DossierController@store'],
    ['PUT',    '/dossiers/{id}',        'DossierController@update'],
    ['DELETE', '/dossiers/{id}',        'DossierController@destroy'],
    ['GET',    '/statistiques/dashboard','StatistiqueController@dashboard'],
    ['GET',    '/export/pdf/{id}',      'ExportController@exportPdf'],
    ['GET',    '/export/csv/{type}',    'ExportController@exportCsv'],
];
return $routes;

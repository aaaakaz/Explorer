<?php
namespace Config;
use CodeIgniter\Router\RouteCollection;

$routes = Services::routes();
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();

// ── Home ─────────────────────────────────────────────────────
$routes->get('/',                     'Home::index');
$routes->get('/search',               'Home::search');        // AJAX: local DB suggestions
$routes->get('/google-autocomplete',  'Home::googleAutocomplete'); // AJAX: Google Places autocomplete
$routes->post('/google-import',       'Home::googleImport');  // AJAX: import place from Google
$routes->get('/filter',               'Home::filter');        // AJAX: category filter / load more
$routes->get('/weather',              'Home::weather');       // AJAX: weather proxy

// ── Places ───────────────────────────────────────────────────
$routes->get('/places',               'Places::index');
$routes->get('/places/(:num)',        'Places::detail/$1');
$routes->post('/places/review',       'Places::review');      // AJAX: submit review
$routes->get('/places/photos/(:num)', 'Places::photos/$1');   // AJAX: Unsplash photos
$routes->get('/places/weather/(:num)','Places::weather/$1');  // AJAX: weather for place

// ── Auth ─────────────────────────────────────────────────────
$routes->get('/login',                'Auth::login');
$routes->post('/login',               'Auth::login');
$routes->get('/register',             'Auth::register');
$routes->post('/register',            'Auth::register');
$routes->get('/logout',               'Auth::logout');

// ── Profile ───────────────────────────────────────────────────
$routes->get('/profile',         'Profile::index');
$routes->post('/profile/update', 'Profile::update');
$routes->post('/profile/avatar', 'Profile::avatar');
$routes->post('/profile/upload-avatar', 'Profile::uploadAvatar');
$routes->post('/profile/remove-avatar', 'Profile::removeAvatar');
$routes->get('/profile/avatar-img/(:segment)', 'Profile::avatarImg/$1');

$routes->post('/places/favourite', 'Places::favourite');
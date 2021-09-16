<?php

namespace Config;

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

// Load the system's routing file first, so that the app and ENVIRONMENT
// can override as needed.
if (file_exists(SYSTEMPATH . 'Config/Routes.php')) {
    require SYSTEMPATH . 'Config/Routes.php';
}

/**
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
$routes->setAutoRoute(true);

/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// We get a performance increase by specifying the default
// route since we don't have to scan directories.
$routes->get('/home', 'Home::index');
// Test route
$routes->get('test', 'Home::test');

// Render group exams
$routes->get('render-ungraded-exam/(:num)', 'Admin\GroupController::renderUngradedExam/$1');
$routes->get('render-graded-exam/(:num)', 'Admin\GroupController::renderGradedExam/$1');

// Cron jobs every day
// 0 0 * * * php /home/username/index.php GroupController clearGroups
$routes->get('clear-groups', 'Utils\CronJob::clearGroups');
$routes->get('dismiss-groups', 'Utils\CronJob::dismissGroups');
$routes->get('update-groups-status', 'Utils\CronJob::updateGroupsStatus');

// Cron jobs every 5 mins
$routes->get('delete-stale_mock-exam-supplies', 'Utils\CronJob::deleteStaleMockExamSupplies');
$routes->get('dismiss-single-mode-stale-exams', 'Utils\CronJob::dismissSingleModeStaleExams');

$routes->group('api', function ($routes) {
    // General API
    $routes->get('countries', 'CountryController::index');
    $routes->get('schools', 'SchoolController::index');

    $routes->get('verify-email/(:any)', 'UserController::verifyEmail/$1');
    // $routes->post('upload-images', 'ExamController::uploadImages');
    $routes->get('students', 'UserController::getStudents');

    $routes->group('auth', function ($routes) {
        $routes->post('signup', 'AuthController::register');
        $routes->post('login', 'AuthController::login');
        $routes->post('forgot-password', 'AuthController::forgotPassword');
        $routes->post('check-reset-token', 'AuthController::checkResetPasswordToken');
        $routes->post('reset-password', 'AuthController::resetPassword');
        $routes->get('me', 'AuthController::me');
    });

    $routes->group('exams', function ($routes) {
        $routes->get('/', 'ExamController::index');
        $routes->get('(:num)', 'ExamController::getExamById/$1');
        $routes->get('get-disciplines', 'ExamController::getAllDesciplines');
        $routes->get('get-levels', 'ExamController::getAllLevels');
        $routes->get('get-subjects', 'ExamController::getAllSubjects');
        $routes->get('get-by-user', 'ExamController::getExamsByUser');
        $routes->post('update/(:num)', 'ExamController::update/$1');
        $routes->post('create', 'ExamController::create');
        $routes->get('get-professor/(:any)', 'ExamController::getProfessorByEmail/$1');
        $routes->get('get-group/(:any)', 'ExamController::getGroupByName/$1');
        $routes->get('ratings/(:num)', 'ExamRatingController::getExamRatingsByExamId/$1');
    });

    $routes->group('ratings', function ($routes) {
        $routes->post('create', 'ExamRatingController::createExamRating');
        $routes->get('get-by-user-and-exam/(:num)&(:num)', 'ExamRatingController::getExamRatingByExamIdAndUserId/$1/$2');
    });

    $routes->group('requests', function ($routes) {
        $routes->get('/', 'RequestController::index');
        $routes->get('(:num)', 'RequestController::getById/$1');
        $routes->post('(:num)/purchase', 'RequestController::purchase/$1');
        $routes->get('get-by-user', 'RequestController::getByUser');
        $routes->post('create', 'RequestController::create');
        $routes->get('dismiss/(:num)', 'RequestController::dismiss/$1');
        $routes->get('delete/(:num)', 'RequestController::delete/$1');
    });

    $routes->group('users', function ($routes) {
        $routes->get('(:num)', 'UserController::getById/$1');
        $routes->post('suspend', 'UserController::suspendUser');
        $routes->post('restore', 'UserController::restoreUser');
        $routes->post('update-me', 'UserController::updateMe');
        $routes->get('get-payment-methods', 'UserController::getPaymentMethods');
        $routes->post('save-payment-method', 'UserController::savePaymentMethod');
        $routes->get('remove-payment-method/(:num)', 'UserController::removePaymentMethod/$1');
    });

    $routes->group('settings', function ($routes) {
        $routes->get('available-payment-methods', 'SettingController::getAvailablePaymentMethods');
    });

    $routes->group('groups', function ($routes) {
        $routes->get('/', 'GroupController::index');
        $routes->get('(:num)', 'GroupController::getById/$1');
        $routes->delete('(:num)', 'GroupController::delete/$1');
        $routes->get('(:num)/preview-compose', 'GroupController::previewComposedExam/$1');
        $routes->post('get-group-users/(:num)', 'GroupController::getGroupUsersByGroupId/$1');
        $routes->get('group-qas/(:num)', 'GroupController::getGroupQA/$1');
    });

    $routes->group('reviewers', function ($routes) {
        $routes->get('get-exams', 'ReviewerController::getAvailableExams');
        $routes->post('review-exam', 'ReviewerController::reviewExam');
    });

    // Admin API
    $routes->group('admin', function ($routes) {
        $routes->get('requests/(:num)', 'Admin\RequestController::getById/$1');
        $routes->post('requests/assign', 'Admin\RequestController::assignExam');
        $routes->get('requests/find-match/(:num)', 'Admin\RequestController::findMatch/$1');
        $routes->get('exams/(:num)', 'Admin\ExamController::getById/$1');
        $routes->post('exams/ask', 'ExamController::askToUpload');
        $routes->post('exams/mark-verified', 'ExamController::markAsVerfied');
        $routes->get('exams/activate/(:num)', 'Admin\ExamController::activateExam/$1');
        $routes->get('groups', 'Admin\GroupController::index');
        $routes->post('groups/set-quality', 'Admin\GroupController::setQuality');
        // $routes->post('groups/suspend-member', 'Admin\GroupController::suspendMember');
        $routes->get('generate/(:num)', 'Admin\GroupController::generatePDF/$1');
        $routes->get('settings', 'SettingController::index');
        $routes->post('settings', 'SettingController::update');
        $routes->get('get-payable-users', 'PayoutController::index');
        $routes->post('payout', 'PayoutController::payout');
        $routes->post('reviewers', 'Admin\ReviewerController::create');
        $routes->post('reviewers/get-by-competency', 'Admin\ReviewerController::getByCompetency');
        $routes->post('reviewers/request-review', 'Admin\ReviewerController::requestReview');
        $routes->get('reviewers', 'Admin\ReviewerController::getReviewers');
        $routes->get('reviewers/(:num)', 'Admin\ReviewerController::getById/$1');
        $routes->post('reviewers/update/(:num)', 'Admin\ReviewerController::updateReviewer/$1');
        $routes->post('school/add-abbreviation/(:num)', 'SchoolController::addAbbreviation/$1');
        $routes->post('school/remove-abbreviation/(:num)', 'SchoolController::removeAbbreviation/$1');
    });

    // Process webhooks
    $routes->post('hook/metascan', 'ExamController::processMetascanResult');
    $routes->post('hook/stripe', 'RequestController::processStripeResult');
    $routes->post('hook/paypal', 'PayoutController::processPaypalResult');
    $routes->post('hook/wise', 'PayoutController::processWiseResult');
});

/*
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 *
 * There will often be times that you need additional routing and you
 * need it to be able to override any defaults in this file. Environment
 * based routes is one such time. require() additional route files here
 * to make that happen.
 *
 * You will have access to the $routes object within that file without
 * needing to reload it.
 */
if (file_exists(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}

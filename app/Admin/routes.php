<?php

use Illuminate\Routing\Router;

Admin::routes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
    'as'            => config('admin.route.prefix') . '.',
], function (Router $router) {

    $router->resource('events', EventModelController::class);

    $router->resource('trainings', TrainingController::class);
    $router->get('/', 'HomeController@index')->name('home');
    $router->resource('gens', GenController::class);
    $router->resource('saccos', SaccoController::class);
    $router->resource('loan-scheems', LoanScheemController::class);
    $router->resource('loans', LoanController::class);
    $router->resource('meetings', MeetingController::class);
    $router->resource('loan-transactions', LoanTransactionController::class);
    $router->resource('reports', ReportController::class);
    $router->resource('contributions', ContributionProgramController::class);
    $router->resource('contribution-program-records', ContributionProgramRecordController::class);

    /* ========================START OF NEW THINGS===========================*/

    $router->resource('crops', CropController::class);
    $router->resource('crop-protocols', CropProtocolController::class);
    $router->resource('gardens', GardenController::class);
    $router->resource('garden-activities', GardenActivityController::class);
    $router->resource('cycles', CycleController::class);
    $router->resource('share-records', ShareRecordController::class);

    /* ========================END OF NEW THINGS=============================*/

    $router->resource('service-providers', ServiceProviderController::class);
    $router->resource('groups', GroupController::class);
    $router->resource('associations', AssociationController::class);
    $router->resource('people', PersonController::class);
    $router->resource('disabilities', DisabilityController::class);
    $router->resource('institutions', InstitutionController::class);
    $router->resource('counselling-centres', CounsellingCentreController::class);
    $router->resource('jobs', JobController::class);
    $router->resource('job-applications', JobApplicationController::class);

    $router->resource('course-categories', CourseCategoryController::class);
    $router->resource('courses', CourseController::class);
    $router->resource('settings', UserController::class);
    $router->resource('participants', ParticipantController::class);
    $router->resource('members', MembersController::class);
    $router->resource('post-categories', PostCategoryController::class);
    $router->resource('news-posts', NewsPostController::class);
    //$router->resource('events', EventController::class);
    $router->resource('event-bookings', EventBookingController::class);
    $router->resource('products', ProductController::class);
    $router->resource('product-orders', ProductOrderController::class);
    $router->resource('transactions', TransactionController::class);
    $router->resource('transactions-all', TransactionAllController::class);

    // API routes for AJAX select fields
    $router->get('api/users', function (\Illuminate\Http\Request $request) {
        $q = $request->get('q');
        $sacco_id = $request->get('sacco_id');
        
        return \App\Models\User::where('sacco_id', $sacco_id)
            ->where('sacco_join_status', 'Approved')
            ->where(function($query) use ($q) {
                $query->where('name', 'like', "%$q%")
                      ->orWhere('email', 'like', "%$q%");
            })
            ->orderBy('name')
            ->paginate(null, ['id', 'name as text']);
    });

    $router->get('api/contribution-programs', function (\Illuminate\Http\Request $request) {
        $q = $request->get('q');
        $sacco_id = $request->get('sacco_id');
        
        return \App\Models\ContributionProgram::where('sacco_id', $sacco_id)
            ->where('status', 'Active')
            ->where('name', 'like', "%$q%")
            ->orderBy('name')
            ->paginate(null, ['id', 'name as text']);
    });
});

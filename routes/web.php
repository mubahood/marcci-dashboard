<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\MainController;
use App\Http\Controllers\MemberReportController;
use App\Http\Controllers\ProgramReportController;
use App\Http\Controllers\SaccoReportController;
use App\Http\Controllers\FamilyTreeReportController;
use App\Http\Middleware\Authenticate;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Models\ContributionProgram;
use App\Models\Gen;
use Encore\Admin\Facades\Admin;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;


Route::get('do-prepare', function () {
    $program = ContributionProgram::orderBy('id', 'desc')->first();
    $program->prepared = 'No';
    ContributionProgram::prepare($program);
    die("here.");
    return $content;
    dd($program);
});
Route::get('app', function () {
    $url  = url('mobisave-v1.apk');
    return redirect($url);
});
Route::get('report-print', function () {
    $report = \App\Models\Report::find($_GET['id']);
    $pdf = App::make('dompdf.wrapper');
    $pdf->loadHTML(view('reports.finance', [
        'r' => $report
    ]));
    return $pdf->stream();
});

Route::get('policy', function () {
    return view('policy');
});

// Member Contribution Report
Route::get('member-report/{user_id}', [MemberReportController::class, 'show'])->name('member.report');

// Program Contribution Report
Route::get('program-report/{program_id}', [ProgramReportController::class, 'show'])->name('program.report');

// SACCO/Family Comprehensive Report
Route::get('sacco-report', [SaccoReportController::class, 'show'])->name('sacco.report');

// Family Tree Visualization Report
Route::get('family-tree', [FamilyTreeReportController::class, 'show'])->name('family.tree');

Route::get('/gen-form', function () {
    die(Gen::find($_GET['id'])->make_forms());
})->name("gen-form");


Route::get('generate-class', [MainController::class, 'generate_class']);
Route::get('/gen', function () {
    die(Gen::find($_GET['id'])->do_get());
})->name("register");
 

/* 

Route::get('generate-variables', [MainController::class, 'generate_variables']); 
Route::get('/', [MainController::class, 'index'])->name('home');
Route::get('/about-us', [MainController::class, 'about_us']);
Route::get('/our-team', [MainController::class, 'our_team']);
Route::get('/news-category/{id}', [MainController::class, 'news_category']);
Route::get('/news-category', [MainController::class, 'news_category']);
Route::get('/news', [MainController::class, 'news_category']);
Route::get('/news/{id}', [MainController::class, 'news']);
Route::get('/members', [MainController::class, 'members']);
Route::get('/dinner', [MainController::class, 'dinner']);
Route::get('/ucc', function(){ return view('chair-person-message'); });
Route::get('/vision-mission', function(){ return view('vision-mission'); }); 
Route::get('/constitution', function(){ return view('constitution'); }); 
Route::get('/register', [AccountController::class, 'register'])->name('register');

Route::get('/login', [AccountController::class, 'login'])->name('login')
    ->middleware(RedirectIfAuthenticated::class);

Route::post('/register', [AccountController::class, 'register_post'])
    ->middleware(RedirectIfAuthenticated::class);

Route::post('/login', [AccountController::class, 'login_post'])
    ->middleware(RedirectIfAuthenticated::class);


Route::get('/dashboard', [AccountController::class, 'dashboard'])
    ->middleware(Authenticate::class);


Route::get('/account-details', [AccountController::class, 'account_details'])
    ->middleware(Authenticate::class);

Route::post('/account-details', [AccountController::class, 'account_details_post'])
    ->middleware(Authenticate::class);

Route::get('/logout', [AccountController::class, 'logout']);
 */
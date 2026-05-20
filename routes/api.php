<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserModelController;
use App\Http\Controllers\CategoryModelController;
use App\Http\Controllers\PostModelController;
use App\Http\Controllers\NoteModelController;
use App\Http\Controllers\TagModelController;
use App\Http\Controllers\PostTagModelController;
use App\Http\Controllers\MasterCountryModelController;
use App\Http\Controllers\MasterRoleModelController;

// Public routes (no authentication required)
Route::post('/post/fetch-public', [PostModelController::class, 'fetchPublicPosts']);

// Authentication required routes - wrap these with middleware('auth:sanctum') in production
Route::post('/user/get-dropdowns', [UserModelController::class, 'getRegistrationDropdowns']);
Route::post('/user/create',        [UserModelController::class, 'registerUser']);
Route::post('/user/login',         [UserModelController::class, 'loginUser']);
Route::post('/user/logout',        [UserModelController::class, 'logoutUser']);
Route::post('/user/fetch-all',     [UserModelController::class, 'fetchAllUsers']);
Route::post('/user/fetch-single',  [UserModelController::class, 'fetchSingleUser']);
Route::post('/user/update',        [UserModelController::class, 'updateUser']);
Route::post('/user/delete',        [UserModelController::class, 'deleteUser']);

Route::post('/category/create',       [CategoryModelController::class, 'createCategory']);
Route::post('/category/fetch-all',    [CategoryModelController::class, 'fetchAllCategories']);
Route::post('/category/fetch-single', [CategoryModelController::class, 'fetchSingleCategory']);
Route::post('/category/update',       [CategoryModelController::class, 'updateCategory']);
Route::post('/category/delete',       [CategoryModelController::class, 'deleteCategory']);

Route::post('/post/create',       [PostModelController::class, 'createPost']);
Route::post('/post/fetch-all',    [PostModelController::class, 'fetchAllPosts']);
Route::post('/post/fetch-single', [PostModelController::class, 'fetchSinglePost']);
Route::post('/post/update',       [PostModelController::class, 'updatePost']);
Route::post('/post/delete',       [PostModelController::class, 'deletePost']);

Route::post('/note/create',       [NoteModelController::class, 'createNote']);
Route::post('/note/fetch-all',    [NoteModelController::class, 'fetchAllNotes']);
Route::post('/note/fetch-single', [NoteModelController::class, 'fetchSingleNote']);
Route::post('/note/update',       [NoteModelController::class, 'updateNote']);
Route::post('/note/delete',       [NoteModelController::class, 'deleteNote']);

Route::post('/tag/create',       [TagModelController::class, 'createTag']);
Route::post('/tag/fetch-all',    [TagModelController::class, 'fetchAllTags']);
Route::post('/tag/fetch-single', [TagModelController::class, 'fetchSingleTag']);
Route::post('/tag/update',       [TagModelController::class, 'updateTag']);
Route::post('/tag/delete',       [TagModelController::class, 'deleteTag']);

Route::post('/post-tag/attach', [PostTagModelController::class, 'attachTag']);
Route::post('/post-tag/detach', [PostTagModelController::class, 'detachTag']);

Route::post('/country/create',       [MasterCountryModelController::class, 'createCountry']);
Route::post('/country/fetch-all',    [MasterCountryModelController::class, 'fetchAllCountries']);
Route::post('/country/fetch-single', [MasterCountryModelController::class, 'fetchSingleCountry']);
Route::post('/country/update',       [MasterCountryModelController::class, 'updateCountry']);
Route::post('/country/delete',       [MasterCountryModelController::class, 'deleteCountry']);
Route::post('/role/create',       [MasterRoleModelController::class, 'createRole']);
Route::post('/role/fetch-all',    [MasterRoleModelController::class, 'fetchAllRoles']);
Route::post('/role/fetch-single', [MasterRoleModelController::class, 'fetchSingleRole']);
Route::post('/role/update',       [MasterRoleModelController::class, 'updateRole']);
Route::post('/role/delete',       [MasterRoleModelController::class, 'deleteRole']);
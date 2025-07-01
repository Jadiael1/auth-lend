<?php

use App\Http\Controllers\Api\V1\AnnouncementController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CardFlagController;
use App\Http\Controllers\Api\V1\CardFlagInstallmentLimitController;
use App\Http\Controllers\Api\V1\ClientController;
use App\Http\Controllers\Api\V1\InterestConfigurationController;
use App\Http\Controllers\Api\V1\SimulationController;
use App\Http\Controllers\Api\V1\SolutionController;
use App\Http\Controllers\Api\V1\StoreController;
use App\Http\Controllers\Api\V1\TestimonyController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\ValueTypeController;
use App\Http\Middleware\Api\V1\AdminMiddleware;
use App\Http\Resources\Api\V1\ResponseResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::middleware('throttle:5,1')->post('/signup', [AuthController::class, 'signup']);
        Route::middleware('throttle:5,1')->post('/signin', [AuthController::class, 'signin']);
        Route::middleware(['auth:sanctum'])->post('/signout', [AuthController::class, 'signout']);
        Route::middleware(['auth:sanctum'])->get('/user', [AuthController::class, 'user']);
    });

    Route::prefix('users')->group(function () {
        Route::middleware(['auth:sanctum', AdminMiddleware::class])->get('/', [UserController::class, 'index']);
        Route::middleware(['auth:sanctum', AdminMiddleware::class])->put('/{user}/permissions', [UserController::class, 'updatePermissions']);
        Route::middleware(['auth:sanctum', AdminMiddleware::class])->put('/{user}/admin', [UserController::class, 'updateIsAdmin']);
        Route::middleware(['auth:sanctum', AdminMiddleware::class])->put('/{user}/status', [UserController::class, 'updateStatus']);
    });

    Route::prefix('stores')->group(function () {
        Route::get('/', [StoreController::class, 'index']);
        Route::middleware(['auth:sanctum', AdminMiddleware::class])->post('/', [StoreController::class, 'store']);
        Route::middleware(['auth:sanctum', AdminMiddleware::class])->put('/{store}/photos/{photo}', [StoreController::class, 'updatePhoto']);
        Route::middleware(['auth:sanctum', AdminMiddleware::class])->delete('/{store}/photos/{photo}', [StoreController::class, 'deletePhoto']);
        Route::middleware(['auth:sanctum', AdminMiddleware::class])->post('/{store}/photos', [StoreController::class, 'addPhoto']);
        Route::middleware(['auth:sanctum', AdminMiddleware::class])->put('/{store}', [StoreController::class, 'update']);
        Route::middleware(['auth:sanctum', AdminMiddleware::class])->delete('/{store}', [StoreController::class, 'destroy']);
    });

    Route::prefix('solutions')->group(function () {
        Route::get('/', [SolutionController::class, 'index']);
        Route::middleware(['auth:sanctum', AdminMiddleware::class])->post('/', [SolutionController::class, 'store']);
        Route::middleware(['auth:sanctum', AdminMiddleware::class])->put('/{id}', [SolutionController::class, 'update']);
        Route::middleware(['auth:sanctum', AdminMiddleware::class])->post('/{id}/photo', [SolutionController::class, 'updatePhoto']);
        Route::middleware(['auth:sanctum', AdminMiddleware::class])->delete('/{id}/photo', [SolutionController::class, 'deletePhoto']);
        Route::middleware(['auth:sanctum', AdminMiddleware::class])->delete('/{id}', [SolutionController::class, 'destroy']);
    });

    Route::prefix('card-flags')->group(function () {
        Route::get('/', [CardFlagController::class, 'index']);
        Route::middleware(['auth:sanctum', AdminMiddleware::class])->post('/', [CardFlagController::class, 'store']);
        Route::middleware(['auth:sanctum', AdminMiddleware::class])->put('/{id}', [CardFlagController::class, 'update']);
        Route::middleware(['auth:sanctum', AdminMiddleware::class])->post('/{id}/image', [CardFlagController::class, 'updateImage']);
        Route::middleware(['auth:sanctum', AdminMiddleware::class])->delete('/{id}/image', [CardFlagController::class, 'deleteImage']);
        Route::middleware(['auth:sanctum', AdminMiddleware::class])->delete('/{id}', [CardFlagController::class, 'destroy']);
    });

    Route::prefix('card-flag-installment-limits')->group(function () {
        Route::get('/', [CardFlagInstallmentLimitController::class, 'index']);
        Route::middleware(['auth:sanctum', AdminMiddleware::class])->post('/', [CardFlagInstallmentLimitController::class, 'store']);
        Route::middleware(['auth:sanctum', AdminMiddleware::class])->put('/{id}', [CardFlagInstallmentLimitController::class, 'update']);
        Route::middleware(['auth:sanctum', AdminMiddleware::class])->delete('/{id}', [CardFlagInstallmentLimitController::class, 'destroy']);
    });

    Route::prefix('interest-configurations')->group(function () {
        Route::get('/', [InterestConfigurationController::class, 'index']);
        Route::middleware(['auth:sanctum', AdminMiddleware::class])->post('/', [InterestConfigurationController::class, 'store']);
        Route::middleware(['auth:sanctum', AdminMiddleware::class])->put('/{id}', [InterestConfigurationController::class, 'update']);
        Route::middleware(['auth:sanctum', AdminMiddleware::class])->delete('/{id}', [InterestConfigurationController::class, 'destroy']);
    });

    Route::prefix('value-types')->group(function () {
        Route::get('/', [ValueTypeController::class, 'index']);
        Route::middleware(['auth:sanctum', AdminMiddleware::class])->post('/', [ValueTypeController::class, 'store']);
        Route::middleware(['auth:sanctum', AdminMiddleware::class])->put('/{id}', [ValueTypeController::class, 'update']);
        Route::middleware(['auth:sanctum', AdminMiddleware::class])->delete('/{id}', [ValueTypeController::class, 'destroy']);
    });

    Route::prefix('testimonies')->group(function () {
        Route::get('/', [TestimonyController::class, 'index']);
        Route::middleware(['throttle:2,1440'])->post('/', [TestimonyController::class, 'store']);
        Route::middleware(['auth:sanctum', AdminMiddleware::class])->put('/{id}', [TestimonyController::class, 'update']);
        Route::middleware(['auth:sanctum', AdminMiddleware::class])->post('/{id}/photo', [TestimonyController::class, 'updatePhoto']);
        Route::middleware(['auth:sanctum', AdminMiddleware::class])->delete('/{id}/photo', [TestimonyController::class, 'deletePhoto']);
        Route::middleware(['auth:sanctum', AdminMiddleware::class])->delete('/{id}', [TestimonyController::class, 'destroy']);
        Route::middleware(['auth:sanctum', AdminMiddleware::class])->patch('/{id}/approve', [TestimonyController::class, 'approve']);
        Route::middleware(['auth:sanctum', AdminMiddleware::class])->patch('/{id}/reject', [TestimonyController::class, 'reject']);
    });

    Route::prefix('announcements')->group(function () {
        Route::get('/', [AnnouncementController::class, 'index']);
        Route::middleware(['auth:sanctum', AdminMiddleware::class])->post('/', [AnnouncementController::class, 'store']);
        Route::middleware(['auth:sanctum', AdminMiddleware::class])->put('/{id}', [AnnouncementController::class, 'update']);
        Route::middleware(['auth:sanctum', AdminMiddleware::class])->post('/{id}/photo', [AnnouncementController::class, 'updatePhoto']);
        Route::middleware(['auth:sanctum', AdminMiddleware::class])->delete('/{id}/photo', [AnnouncementController::class, 'deletePhoto']);
        Route::middleware(['auth:sanctum', AdminMiddleware::class])->delete('/{id}', [AnnouncementController::class, 'remove']);
        Route::middleware(['auth:sanctum', AdminMiddleware::class])->patch('/{id}/activate', [AnnouncementController::class, 'activeAd']);
        Route::middleware(['auth:sanctum', AdminMiddleware::class])->patch('/{id}/disable', [AnnouncementController::class, 'disableAd']);
    });

    Route::prefix('clients')->group(function () {
        Route::middleware(['auth:sanctum', AdminMiddleware::class])->get('/', [ClientController::class, 'index']);
        Route::middleware(['auth:sanctum', AdminMiddleware::class])->post('/', [ClientController::class, 'store']);
        Route::middleware(['auth:sanctum', AdminMiddleware::class])->put('/{id}', [ClientController::class, 'update']);
        Route::middleware(['auth:sanctum', AdminMiddleware::class])->delete('/{id}', [ClientController::class, 'remove']);

        Route::middleware(['auth:sanctum', AdminMiddleware::class])->post('/{id}/cpf-front-photo', [ClientController::class, 'updateCpfFrontPhoto']);
        Route::middleware(['auth:sanctum', AdminMiddleware::class])->delete('/{id}/cpf-front-photo', [ClientController::class, 'deleteCpfFrontPhoto']);
        Route::middleware(['auth:sanctum', AdminMiddleware::class])->post('/{id}/cpf-back-photo', [ClientController::class, 'updateCpfBackPhoto']);
        Route::middleware(['auth:sanctum', AdminMiddleware::class])->delete('/{id}/cpf-back-photo', [ClientController::class, 'deleteCpfBackPhoto']);

        Route::middleware(['auth:sanctum', AdminMiddleware::class])->post('/{id}/rg-front-photo', [ClientController::class, 'updateRgFrontPhoto']);
        Route::middleware(['auth:sanctum', AdminMiddleware::class])->delete('/{id}/rg-front-photo', [ClientController::class, 'deleteRgFrontPhoto']);
        Route::middleware(['auth:sanctum', AdminMiddleware::class])->post('/{id}/rg-back-photo', [ClientController::class, 'updateRgBackPhoto']);
        Route::middleware(['auth:sanctum', AdminMiddleware::class])->delete('/{id}/rg-back-photo', [ClientController::class, 'deleteRgBackPhoto']);
    });

    Route::prefix('simulations')->group(function () {
        Route::middleware(['throttle:7,1'])->post('/', [SimulationController::class, 'simulate']);
        Route::get('/{uuid}', [SimulationController::class, 'show']);
        Route::middleware(['auth:sanctum', AdminMiddleware::class])->get('/', [SimulationController::class, 'index']);
        Route::middleware(['auth:sanctum', AdminMiddleware::class])->patch('/{id}', [SimulationController::class, 'update']);
        Route::middleware(['auth:sanctum', AdminMiddleware::class])->delete('/{id}', [SimulationController::class, 'destroy']);
    });

    Route::prefix('routes')->group(function () {
        Route::middleware(['auth:sanctum', AdminMiddleware::class])->get('/prefix', function (Request $request) {
            $routes = Route::getRoutes();
            $prefixes = collect($routes)
                ->filter(function ($route) {
                    $middlewares = $route->gatherMiddleware();
                    return in_array(AdminMiddleware::class, $middlewares, true);
                })
                ->map(function ($route) {
                    return $route->getPrefix();
                })
                ->filter()
                ->unique()
                ->values();
            return new ResponseResource([
                'status' => 'success',
                'status_code' => 200,
                'message' => 'Route prefixes successfully obtained',
                'data' => $prefixes,
                'errors' => null
            ]);
        });
    });
});

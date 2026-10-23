<?php

use App\Http\Controllers\HistoryController;
use App\Http\Controllers\WorkspaceController;
use App\Http\Middleware\EnsureFinanceEnabled;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Sessões e proteção CSRF herdadas do grupo web; todas as respostas são JSON.
Route::prefix('api')->group(function () {
    Route::get('csrf', fn (Request $request) => response()->json(['token' => $request->session()->token()]));
    Route::post('register', [HistoryController::class, 'register'])->middleware('throttle:5,1');
    Route::post('login', [HistoryController::class, 'login'])->middleware('throttle:5,1');
    Route::middleware(['auth', 'auth.session'])->group(function () {
        Route::get('features', fn () => response()->json(['financeiro' => (bool) config('features.financeiro')]));
        Route::put('user', [WorkspaceController::class, 'profile'])->middleware('throttle:10,1,account-profile');
        Route::put('password', [WorkspaceController::class, 'password'])->middleware('throttle:10,1,account-password');
        Route::post('push-test', [WorkspaceController::class, 'testPush'])->middleware('throttle:3,1');
        Route::get('push-config', [WorkspaceController::class, 'pushConfig']);
        Route::put('push-subscription', [WorkspaceController::class, 'subscribe']);
        Route::delete('push-subscription', [WorkspaceController::class, 'unsubscribe']);
        Route::get('relatorios', [WorkspaceController::class, 'reports'])->middleware(EnsureFinanceEnabled::class);
        Route::put('motocicletas/{moto}', [WorkspaceController::class, 'updateMotorcycle']);
        Route::delete('motocicletas/{moto}', [WorkspaceController::class, 'deleteMotorcycle']);
        Route::get('motocicletas/{moto}/despesas', [WorkspaceController::class, 'expenses'])->middleware(EnsureFinanceEnabled::class);
        Route::post('motocicletas/{moto}/despesas', [WorkspaceController::class, 'storeExpense'])->middleware(EnsureFinanceEnabled::class);
        Route::put('motocicletas/{moto}/despesas/{despesa}', [WorkspaceController::class, 'updateExpense'])->middleware(EnsureFinanceEnabled::class);
        Route::delete('motocicletas/{moto}/despesas/{despesa}', [WorkspaceController::class, 'deleteExpense'])->middleware(EnsureFinanceEnabled::class);
        Route::get('motocicletas/{moto}/alertas', [WorkspaceController::class, 'alerts']);
        Route::post('motocicletas/{moto}/alertas', [WorkspaceController::class, 'storeAlert']);
        Route::put('motocicletas/{moto}/alertas/{alerta}', [WorkspaceController::class, 'updateAlert']);
        Route::delete('motocicletas/{moto}/alertas/{alerta}', [WorkspaceController::class, 'deleteAlert']);
        Route::get('user', fn (Request $request) => response()->json($request->user()));
        Route::post('logout', [HistoryController::class, 'logout']);
        Route::get('motocicletas', [HistoryController::class, 'motorcycles']);
        Route::post('motocicletas', [HistoryController::class, 'storeMotorcycle']);
        Route::get('motocicletas/{moto}/manutencoes', [HistoryController::class, 'index']);
        Route::post('motocicletas/{moto}/manutencoes', [HistoryController::class, 'store']);
        Route::put('motocicletas/{moto}/manutencoes/{manutencao}', [HistoryController::class, 'update']);
        Route::delete('motocicletas/{moto}/manutencoes/{manutencao}', [HistoryController::class, 'destroy']);
    });
});

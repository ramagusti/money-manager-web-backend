<?php

use App\Http\Controllers\GroupController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\TransactionCategoryController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\User;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/email/verify/{id}/{hash}', function ($id, $hash, Request $request) {
    // Find the user
    $user = User::find($id);

    if (!$user) {
        return response()->json(['message' => 'User not found.'], 404);
    }

    // Verify the hash
    if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
        return response()->json(['message' => 'Invalid verification link.'], 403);
    }

    // If already verified
    if ($user->hasVerifiedEmail()) {
        return redirect(env('FRONTEND_URL', 'http://localhost:5173') . '/already-verified');
    }

    // Mark email as verified
    $user->markEmailAsVerified();

    return redirect(env('FRONTEND_URL', 'http://localhost:5173') . '/verified-success');
})->middleware(['signed'])->name('verification.verify');

Route::post('/email/resend', function (Request $request) {
    $request->validate([
        'email' => 'required|email|exists:users,email',
    ]);

    $user = User::where('email', $request->email)->first();

    if ($user->hasVerifiedEmail()) {
        return response()->json(['message' => 'Email already verified.'], 400);
    }

    $user->sendEmailVerificationNotification();

    return response()->json(['message' => 'Verification email sent!']);
});

// For simplicity, using the "auth" middleware here (adjust based on your auth setup)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Groups
    Route::get('/groups', [GroupController::class, 'index']);
    Route::post('/groups', [GroupController::class, 'store']);
    Route::get('/groups/{group}', [GroupController::class, 'show']);
    Route::put('/groups/{group}', [GroupController::class, 'update']);
    Route::delete('/groups/{group}', [GroupController::class, 'destroy']);
    Route::get('/groups/{group}/members', [GroupController::class, 'getMembers']);
    Route::get('/groups/{group}/balance', [GroupController::class, 'getBalance']);
    Route::get('/groups/{group}/goal', [GroupController::class, 'getGoal']); 
    Route::post('/groups/{group}/goal', [GroupController::class, 'storeGoal']); 
    Route::get('/groups/{group}/incomeexpense', [GroupController::class, 'getIncomeExpense']);

    // Transactions
    Route::get('/transactions', [TransactionController::class, 'index']);
    Route::post('/transactions', [TransactionController::class, 'store']);
    Route::get('/transactions/export', [TransactionController::class, 'export']);
    Route::get('/transactions/template', [TransactionController::class, 'downloadTemplate']);
    Route::post('/transactions/import', [TransactionController::class, 'import']);
    Route::put('/transactions/{transaction}', [TransactionController::class, 'update']);
    Route::delete('/transactions/{transaction}', [TransactionController::class, 'destroy']);

    // Transaction Categories
    Route::get('/categories', [TransactionCategoryController::class, 'index']);
    Route::post('/categories', [TransactionCategoryController::class, 'store']);
    Route::delete('/categories/{category}', [TransactionCategoryController::class, 'destroy']);
});

<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;

use Illuminate\Foundation\Auth\EmailVerificationRequest;
use App\Mail\VerifyEmail;

use App\Models\User;
use App\Models\Group;
use App\Models\GroupInvitation;

use App\Http\Controllers\EntryIngestionController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\TransactionCategoryController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\AuthController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink']);
Route::post('/reset-password', [PasswordResetController::class, 'resetPassword']);

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
        return redirect(env('FRONTEND_URL', 'https://ragst.vip') . '/already-verified');
    }

    // Mark email as verified
    $user->markEmailAsVerified();

    // Check if user was invited to a group
    if ($user->invite_token) {
        $invitation = GroupInvitation::where('token', $user->invite_token)->first();

        if ($invitation) {
            $group = Group::find($invitation->group_id);
            if ($group) {
                $group->users()->attach($user->id, ['role' => 'member']);
                $invitation->delete(); // Remove the used invitation
            }
        }
    }

    return redirect(env('FRONTEND_URL', 'https://ragst.vip') . '/verified-success');
})->middleware(['signed'])->name('verification.verify');

Route::post('/email/resend', function (Request $request) {
    $request->validate([
        'email' => 'required|email|exists:users,email',
    ]);

    $user = User::where('email', $request->email)->first();

    if ($user->hasVerifiedEmail()) {
        return response()->json(['message' => 'Email already verified.'], 400);
    }

    // $user->sendEmailVerificationNotification();
    Mail::to($user->email)->send(new VerifyEmail($user));

    return response()->json(['message' => 'Verification email sent!']);
});

Route::post('/entries/ingest', [EntryIngestionController::class, 'store']);

// For simplicity, using the "auth" middleware here (adjust based on your auth setup)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Groups
    Route::get('/groups', [GroupController::class, 'index']);
    Route::post('/groups', [GroupController::class, 'store']);
    Route::post('/groups/join', [GroupController::class, 'acceptInvitation']);
    Route::get('/groups/{group}', [GroupController::class, 'show']);
    Route::put('/groups/{group}', [GroupController::class, 'update'])->middleware('groupRole:owner|admin');
    Route::delete('/groups/{group}', [GroupController::class, 'destroy'])->middleware('groupRole:owner');
    Route::get('/groups/{group}/balance', [GroupController::class, 'getBalance']);
    Route::get('/groups/{group}/goal', [GroupController::class, 'getGoal']);
    Route::post('/groups/{group}/goal', [GroupController::class, 'storeGoal'])->middleware('groupRole:owner|admin');
    Route::get('/groups/{group}/incomeexpense', [GroupController::class, 'getIncomeExpense']);
    Route::get('/groups/{group}/members', [GroupController::class, 'getMembers']);
    Route::post('/groups/{group}/members', [GroupController::class, 'addMember'])->middleware('groupRole:owner|admin');
    Route::post('/groups/{group}/members/invite', [GroupController::class, 'inviteMember'])->middleware('groupRole:owner|admin');
    Route::put('/groups/{group}/members/{user}', [GroupController::class, 'updateMemberRole'])->middleware('groupRole:owner|admin');
    Route::delete('/groups/{group}/members/{user}', [GroupController::class, 'removeMember'])->middleware('groupRole:owner|admin');

    // Transactions
    Route::get('/transactions', [TransactionController::class, 'index']);
    Route::post('/transactions', [TransactionController::class, 'store']);
    Route::get('/dashboard-data', [TransactionController::class, 'dashboardData']);
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

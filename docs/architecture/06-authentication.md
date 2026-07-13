# 06 - Authentication System

## Gambaran Umum

CatatUang menggunakan dual authentication system:
- **Web**: Laravel Breeze (session-based)
- **API**: Laravel Sanctum (token-based)

## Laravel Breeze (Web)

### Install

```bash
composer require laravel/breeze --dev
php artisan breeze:install
```

### Structure

```
app/Http/Controllers/Auth/
├── AuthenticatedSessionController.php
├── ConfirmablePasswordController.php
├── EmailVerificationNotificationController.php
├── EmailVerificationPromptController.php
├── NewPasswordController.php
├── PasswordController.php
├── PasswordResetLinkController.php
├── RegisteredUserController.php
└── VerifyEmailController.php
```

### Routes (routes/auth.php)

```php
// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create']);
    Route::post('register', [RegisteredUserController::class, 'store']);
    Route::get('login', [AuthenticatedSessionController::class, 'create']);
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
    Route::get('forgot-password', [PasswordResetLinkController::class, 'create']);
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store']);
    Route::get('reset-password/{token}', [NewPasswordController::class, 'create']);
    Route::post('reset-password', [NewPasswordController::class, 'store']);
});

// Auth routes
Route::middleware('auth')->group(function () {
    Route::get('verify-email', EmailVerificationPromptController::class);
    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class);
    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store']);
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy']);
    Route::put('password', [PasswordController::class, 'update']);
    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show']);
    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);
});
```

### Views

```
resources/views/auth/
├── confirm-password.blade.php
├── forgot-password.blade.php
├── login.blade.php
├── register.blade.php
├── reset-password.blade.php
└── verify-email.blade.php
```

## Laravel Sanctum (API)

### Install

```bash
php artisan install:api
```

### User Model

```php
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    // HasApiTokens trait adds:
    // - createToken()
    // - currentAccessToken()
    // - tokens()
    // - deleteToken()
    // - deleteTokens()
}
```

### API Auth Controller

```php
class AuthApiController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        if (!Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out']);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json($request->user());
    }
}
```

### API Routes

```php
// routes/api.php
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthApiController::class, 'login']);
    Route::post('/register', [AuthApiController::class, 'register']);
    Route::post('/logout', [AuthApiController::class, 'logout'])->middleware('auth:sanctum');
    Route::get('/me', [AuthApiController::class, 'me'])->middleware('auth:sanctum');
});
```

## Middleware Protection

| Route Type | Middleware | Description |
|------------|-----------|-------------|
| Web (Blade) | `auth` | Session-based auth |
| API | `auth:sanctum` | Token-based auth |
| API (Optional) | `auth:sanctum:optional` | Token optional |

## SPA Token Handling

Vue SPA menyimpan token di localStorage:

```javascript
// stores/auth.js
export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    token: localStorage.getItem('token'),
  }),

  actions: {
    async login(email, password) {
      const { user, token } = await fetch('/api/auth/login', {
        method: 'POST',
        body: JSON.stringify({ email, password })
      }).then(r => r.json())

      this.user = user
      this.token = token
      localStorage.setItem('token', token)
    },

    logout() {
      this.user = null
      this.token = null
      localStorage.removeItem('token')
    }
  }
})
```

## Login Request Validation

```php
class LoginRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ];
    }
}
```

## Related Files

- `app/Models/User.php`
- `app/Http/Controllers/Auth/*.php`
- `app/Http/Controllers/Api/AuthApiController.php`
- `routes/auth.php`
- `routes/api.php`
- `resources/views/auth/*.blade.php`
- `resources/js/spa/stores/auth.js`

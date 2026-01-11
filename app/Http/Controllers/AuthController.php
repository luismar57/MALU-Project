<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    // Web login method (unchanged)
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $request->session()->regenerate();
            return redirect()->intended('/dashboard')->with('success', 'Inicio de sesión exitoso');
        }

        return back()->withErrors([
            'email' => 'Credenciales inválidas. Por favor intente nuevamente.',
        ]);
    }

    // Web logout method (unchanged)
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login')->with('success', 'Sesión cerrada exitosamente');
    }

    // Web register method (unchanged)
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        Auth::login($user);

        return redirect('/dashboard')->with('success', 'Registro exitoso');
    }

    // API login method
    public function apiLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
    
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();

            if (!$user instanceof User) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Autenticación fallida',
                ], 401);
            }
            $token = $user->createToken('auth_token')->plainTextToken;
    
            return response()->json([
                'status' => 'success',
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'token' => $token,
                ],
            ], 200);
        }
    
        return response()->json([
            'status' => 'error',
            'message' => 'Credenciales inválidas',
        ], 401);
    }
    
    


    // API logout method
    public function apiLogout(Request $request)
    {
        $request->user()->tokens->each(function ($token) {
            $token->delete(); // Revoke all tokens
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Sesión cerrada exitosamente',
        ], 200);
    }

    // API register method
    public function apiRegister(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validación fallida',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Create a token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Registro exitoso',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'token' => $token,
            ],
        ], 201);
    }

    

    public function getAllUsers()
{
    // Select only the columns you want
    $users = User::select('id', 'name', 'email', 'email_verified_at', 'password', 'remember_token', 'created_at', 'updated_at')->get();

    return response()->json($users);
}

public function addUser(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users',
        'password' => 'required|min:6',
    ]);

    $user = User::create([
        'name' => $validated['name'],
        'email' => $validated['email'],
        'password' => Hash::make($validated['password']),
    ]);

    return response()->json([
        'message' => 'Usuario agregado exitosamente',
        'user' => $user,
    ], 201);
}



public function deleteUser($id)
{
    $user = User::find($id);

    if (!$user) {
        return response()->json([
            'message' => 'Usuario no encontrado'
        ], 404);
    }

    $user->delete();

    return response()->json([
        'message' => 'Usuario eliminado exitosamente'
    ]);
}


public function updateUser(Request $request, $id)
{
    $user = User::find($id);

    if (!$user) {
        return response()->json([
            'message' => 'Usuario no encontrado'
        ], 404);
    }

    $request->validate([
        'name' => 'sometimes|required|string|max:255',
        'email' => 'sometimes|required|email|unique:users,email,' . $id,
        'password' => 'sometimes|required|min:6'
    ]);

    $user->name = $request->name ?? $user->name;
    $user->email = $request->email ?? $user->email;

    if ($request->filled('password')) {
        $user->password = Hash::make($request->password);
    }

    $user->save();

    return response()->json([
        'message' => 'Usuario actualizado exitosamente',
        'user' => $user
    ]);
}

public function forgotPassword(Request $request)
{
    $validator = Validator::make($request->all(), [
        'email' => 'required|email|exists:users,email',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => 'error',
            'message' => $validator->errors()->first()
        ], 400);
    }

    $status = Password::sendResetLink($request->only('email'));

    if ($status === Password::RESET_LINK_SENT) {
        return response()->json([
            'status' => 'success',
            'message' => 'Enlace de recuperación enviado a tu correo'
        ], 200);
    }

    return response()->json([
        'status' => 'error',
        'message' => 'Error al enviar el correo de recuperación'
    ], 500);
}

public function resetPassword(Request $request)
{
    $validator = Validator::make($request->all(), [
        'token' => 'required',
        'email' => 'required|email',
        'password' => 'required|min:6|confirmed',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => 'error',
            'message' => $validator->errors()->first()
        ], 400);
    }

    $status = Password::reset(
        $request->only('email', 'password', 'password_confirmation', 'token'),
        function (User $user, string $password) {
            $user->forceFill([
                'password' => Hash::make($password)
            ])->setRememberToken(Str::random(60));
            $user->save();
            event(new PasswordReset($user));
        }
    );

    if ($status === Password::PASSWORD_RESET) {
        return response()->json([
            'status' => 'success',
            'message' => 'Contraseña actualizada exitosamente'
        ], 200);
    }

    return response()->json([
        'status' => 'error',
        'message' => 'Token inválido o expirado'
    ], 400);
}

    

}

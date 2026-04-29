<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        return response()->json([
            'data' => User::latest()->get()
        ]);
    }

    public function store(Request $request)
    {
        // Normalize input
        $request->merge([
            'email' => strtolower(trim($request->email)),
            'name' => trim($request->name),
        ]);

        $messages = [
            'name.required' => 'Tên hiển thị không được để trống.',
            'email.required' => 'Tên đăng nhập (Email) không được để trống.',
            'email.unique' => 'Tên đăng nhập này đã có người sử dụng.',
            'password.required' => 'Mật khẩu không được để trống.',
            'password.min' => 'Mật khẩu phải dài ít nhất 4 ký tự.',
        ];

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|max:255|unique:users',
            'password' => 'required|string|min:4',
            'role' => ['required', Rule::in(['admin', 'staff'])],
        ], $messages);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
        ]);

        return response()->json([
            'message' => 'Tài khoản đã được tạo thành công',
            'data' => $user
        ], 201);
    }

    public function update(Request $request, User $user)
    {
        // Normalize input
        if ($request->has('email')) {
            $request->merge(['email' => strtolower(trim($request->email))]);
        }
        if ($request->has('name')) {
            $request->merge(['name' => trim($request->name)]);
        }

        // Prevent user from downgrading their own admin role
        if (auth()->id() === $user->id && $request->has('role') && $request->role !== $user->role) {
            return response()->json(['message' => 'Không thể tự thay đổi quyền của chính mình'], 403);
        }

        $messages = [
            'name.required' => 'Tên hiển thị không được để trống.',
            'email.required' => 'Tên đăng nhập (Email) không được để trống.',
            'email.unique' => 'Tên đăng nhập này đã có người sử dụng.',
            'password.min' => 'Mật khẩu phải dài ít nhất 6 ký tự.',
        ];

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => ['sometimes', 'string', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:4',
            'role' => ['sometimes', Rule::in(['admin', 'staff'])],
        ], $messages);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return response()->json([
            'message' => 'Cập nhật tài khoản thành công',
            'data' => $user
        ]);
    }

    public function destroy(User $user)
    {
        if (auth()->id() === $user->id) {
            return response()->json(['message' => 'Không thể xóa tài khoản đang đăng nhập'], 403);
        }

        $user->delete();

        return response()->json([
            'message' => 'Tài khoản đã được xóa'
        ]);
    }
}

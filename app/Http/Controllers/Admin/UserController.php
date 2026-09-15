<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\NomorWaNormalizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $role = $request->string('role')->toString();
        $q = trim($request->string('q')->toString());

        $allowedRoles = ['admin', 'admin_cabang', 'fasilitator', 'executive'];
        $query = User::whereIn('role', $allowedRoles)
            ->orderByRaw("CASE role WHEN 'admin' THEN 1 WHEN 'admin_cabang' THEN 2 WHEN 'fasilitator' THEN 3 WHEN 'executive' THEN 4 ELSE 5 END")
            ->orderBy('name');

        if (in_array($role, $allowedRoles, true)) {
            $query->where('role', $role);
        }

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%")
                    ->orWhere('pcm', 'like', "%{$q}%")
                    ->orWhere('no_wa', 'like', "%{$q}%");
            });
        }

        return view('admin.users.index', [
            'users' => $query->paginate(25)->withQueryString(),
            'currentRole' => $role,
            'q' => $q,
        ]);
    }

    public function create(): View
    {
        return view('admin.users.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', Rule::in(['admin', 'admin_cabang', 'fasilitator', 'executive'])],
            'pcm' => ['nullable', 'required_if:role,admin_cabang', 'string', 'max:100'],
            'no_wa' => ['nullable', 'string'],
        ]);

        $noWa = ! empty($data['no_wa']) ? NomorWaNormalizer::normalize($data['no_wa']) : null;

        User::create([
            'name' => $data['name'],
            'email' => strtolower(trim($data['email'])),
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
            'pcm' => $data['role'] === 'admin_cabang' ? ($data['pcm'] ?? null) : null,
            'status' => 'aktif',
            'no_wa' => $noWa,
            'verified_at' => now(),
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', "Akun {$data['role']} berhasil dibuat dan aktif.");
    }

    public function toggleStatus(User $user): RedirectResponse
    {
        abort_if((int) $user->id === (int) auth()->id(), 403, 'Anda tidak dapat mengubah status akun Anda sendiri.');

        $newStatus = $user->status === 'aktif' ? 'nonaktif' : 'aktif';
        $user->update(['status' => $newStatus]);

        return back()->with('success', "Status akun {$user->name} diubah menjadi {$newStatus}.");
    }
}

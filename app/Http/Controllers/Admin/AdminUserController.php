<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

use function in_array;

class AdminUserController extends Controller
{
    public function index(Request $request): Response
    {
        $filter = $request->string('filter', 'all')->toString();
        $validFilters = ['all', 'active', 'inactive', 'verified', 'unverified', 'admin', 'client'];
        if (! in_array($filter, $validFilters, true)) {
            $filter = 'all';
        }

        $search = $request->string('search')->trim()->toString();

        $stats = [
            'total' => User::count(),
            'active' => User::where('is_active', true)->count(),
            'inactive' => User::where('is_active', false)->count(),
            'unverified' => User::whereNull('email_verified_at')->count(),
        ];

        $query = User::query()
            ->withCount('events')
            ->latest();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($filter === 'active') {
            $query->where('is_active', true);
        } elseif ($filter === 'inactive') {
            $query->where('is_active', false);
        } elseif ($filter === 'verified') {
            $query->whereNotNull('email_verified_at');
        } elseif ($filter === 'unverified') {
            $query->whereNull('email_verified_at');
        } elseif ($filter === 'admin') {
            $query->where('is_admin', true);
        } elseif ($filter === 'client') {
            $query->where('is_admin', false);
        }

        $users = $query->paginate(15)
            ->withQueryString()
            ->through(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'is_admin' => $user->is_admin,
                'is_active' => $user->is_active,
                'email_verified_at' => $user->email_verified_at?->toIso8601String(),
                'created_at' => $user->created_at?->translatedFormat('d M Y, H:i'),
                'events_count' => (int) ($user->events_count ?? 0),
            ]);

        return Inertia::render('admin/users/Index', [
            'users' => $users,
            'filters' => [
                'filter' => $filter,
                'search' => $search,
            ],
            'stats' => $stats,
        ]);
    }

    public function toggleStatus(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'Anda tidak dapat menonaktifkan akun Anda sendiri.',
            ]);

            return back();
        }

        $user->is_active = ! $user->is_active;
        $user->save();

        $action = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';
        Inertia::flash('toast', [
            'type' => 'success',
            'message' => "Akun {$user->name} berhasil {$action}.",
        ]);

        return back();
    }

    public function toggleVerification(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'Anda tidak dapat mengubah status verifikasi akun Anda sendiri.',
            ]);

            return back();
        }

        if ($user->hasVerifiedEmail()) {
            $user->email_verified_at = null;
            $user->save();
            $message = "Verifikasi email akun {$user->name} dibatalkan.";
        } else {
            $user->is_active = true;
            $user->markEmailAsVerified();
            $message = "Email akun {$user->name} berhasil diverifikasi manual.";
        }

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => $message,
        ]);

        return back();
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'Anda tidak dapat menghapus akun Anda sendiri.',
            ]);

            return back();
        }

        $name = $user->name;
        $user->delete();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => "User {$name} berhasil dihapus.",
        ]);

        return back();
    }
}

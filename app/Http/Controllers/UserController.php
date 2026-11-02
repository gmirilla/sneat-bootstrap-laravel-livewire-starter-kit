<?php

namespace App\Http\Controllers;

use App\Mail\ClaimAccountRejectedMail;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\agentsdetailsModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of all users.
     */
    public function index(Request $request)
    {
        $searchParams = $request->only(['search', 'role']);

        $query = User::query();

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('firstname', 'like', "%{$term}%")
                  ->orWhere('lastname',  'like', "%{$term}%")
                  ->orWhere('email',     'like', "%{$term}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(30)->withQueryString();

        return view('usermgmgt.listusers', compact('users', 'searchParams'));
    }

    public const ROLES = ['superadmin', 'admin', 'agent', 'subagent', 'user', 'broker'];

    /**
     * Change a user's role. Route is admin/superadmin only; only a superadmin may
     * grant or alter the admin/superadmin roles.
     */
    public function updateuser(Request $request)
    {
        $data = $request->validate([
            'uid'  => 'required|integer|exists:users,id',
            'role' => ['required', Rule::in(self::ROLES)],
        ]);

        $actor  = Auth::user();
        $user   = User::findOrFail($data['uid']);
        $newRole = $data['role'];

        $privileged = ['admin', 'superadmin'];
        if ($actor->role !== 'superadmin' && (in_array($newRole, $privileged, true) || in_array($user->role, $privileged, true))) {
            abort(403, 'Only a superadmin can grant or change admin roles.');
        }

        // Never let the last superadmin (or yourself) be demoted by accident.
        if ($user->id === $actor->id && $newRole !== $user->role) {
            return back()->with('error', 'You cannot change your own role.');
        }

        $oldRole = $user->role;
        $adetail = agentsdetailsModel::where('uid', $user->id)->first();

        $user->role = $newRole;
        $user->save();

        if ($newRole === 'agent') {
            if ($adetail) {
                $adetail->status = 'activated';
                $adetail->save();
            } else {
                $agentdetail         = new agentsdetailsModel();
                $agentdetail->uid    = $user->id;
                $agentdetail->status = 'activated';
                $agentdetail->save();
            }
        } elseif ($adetail) {
            $adetail->status = 'deactivated';
            $adetail->save();
        }

        Log::info('User role changed', [
            'actor_id' => $actor->id,
            'user_id'  => $user->id,
            'from'     => $oldRole,
            'to'       => $newRole,
        ]);

        return redirect()->route('list_users');
    }


    /**
     * Set the user's API Token (superadmin only, enforced by route middleware).
     */
    public function generateeapitoken(Request $request)
    {
        $request->validate(['auid' => 'required|integer|exists:users,id']);

        $user  = User::findOrFail($request->auid);
        $token = $user->setApiToken();

        Log::info('API token generated', ['actor_id' => Auth::id(), 'user_id' => $user->id]);

        return response()->json(['token' => $token]);
    }

    /**
     * Reset a sub-agent's password. Allowed for the sub-agent's parent agent or an admin.
     */
    public function resetPassword(User $id, Request $request)
    {
        $actor   = Auth::user();
        $isAdmin = in_array($actor->role, ['admin', 'superadmin'], true);

        $isParent = $id->role === 'subagent' && (int) $id->parentid === (int) $actor->id;
        abort_unless($isParent || ($isAdmin && ! in_array($id->role, ['admin', 'superadmin'], true)) || $actor->role === 'superadmin', 403);

        $request->validate([
            'password'              => ['required', 'string', 'min:8', 'max:64'],
            'password_confirmation' => 'required|same:password',
        ]);

        $id->password = Hash::make($request->password);
        $id->save();

        Log::info('Password reset', ['actor_id' => $actor->id, 'user_id' => $id->id]);

        return back()->with('Success', 'Password updated');
    }



    public function pendingAccounts()
    {
        $this->requireAdmin();

        $users = User::where('account_status', 'pending')
            ->with(['claimNotifications' => fn($q) => $q->latest()->limit(1)])
            ->orderBy('created_at', 'desc')
            ->paginate(30);

        return view('usermgmgt.pending_accounts', compact('users'));
    }

    public function approveAccount(User $user)
    {
        $this->requireAdmin();

        $user->update(['account_status' => 'active']);

        // Fix 6: check whether the reset link was actually sent
        $status = Password::broker()->sendResetLink(['email' => $user->email]);

        $msg = $status === Password::RESET_LINK_SENT
            ? "Account for {$user->name} approved. A password setup email has been sent."
            : "Account for {$user->name} approved, but the password email could not be sent (reason: {$status}). Please contact the claimant directly at {$user->email}.";

        return back()->with('success', $msg);
    }

    public function rejectAccount(User $user)
    {
        $this->requireAdmin();

        $user->update(['account_status' => 'rejected']);

        // Fix 4: notify claimant so they are not left waiting indefinitely
        Mail::to($user->email)->send(new ClaimAccountRejectedMail($user));

        return back()->with('success', "Account for {$user->name} has been rejected. The claimant has been notified.");
    }

    private function requireAdmin(): void
    {
        if (!in_array(Auth::user()->role, ['admin', 'superadmin'])) {
            abort(403);
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Mail\ClaimAccountRejectedMail;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\agentsdetailsModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;

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

        public function updateuser(Request $request)
    {
        //
        $user=User::where('id',$request->uid)->first();

        $adetail=agentsdetailsModel::where('uid',$user->id)->first();

        if ($request->role!=='agent' && !empty($adetail) ){
            
            $adetail->status='deactivated';
            $adetail->save();

        }

        

        $user->role=$request->role;
        $user->save();
        if ($user->role=='agent') {
            # check if a record exists in the agents table and create
            if (empty($adetail)) {
                # no agent record exist create...
                $agentdetail=new agentsdetailsModel();
                $agentdetail->uid=$user->id;
                $agentdetail->status='activated';
                 $agentdetail->save();

            } else {
                # code...
                $adetail->status='deactivated';
                $adetail->save();
            }
           
        }


       

        return redirect()->route('list_users');
    }


    /**
     * Set the user's API Token.
     */
    public function generateeapitoken(Request $request)
    {
        //
        $user=User::where('id', $request->auid)->first();
        $token=$user->setApiToken();
        return response()->json(['token' => $token]);
    }

        /**
     * Reset the User password     */
    public function resetPassword(User $id, Request $request)
    {
        //
        $request->validate([
    'password' => 'required|string|min:6|max:16',
    'password_confirmation' => 'required|same:password',
]);


        $id->password=Hash::make($request->password);


        $id->save();

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

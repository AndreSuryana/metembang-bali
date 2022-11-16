<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\TembangSubmission;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class AdminController extends Controller
{
    /**
     * Return admin login form view.
     * 
     * @return view
     */
    public function login()
    {
        $data = [
            'title' => 'Login',
            'site_name' => 'Metembang Bali',
            'menu' => false
        ];

        return view('admin.login', $data);
    }

    /**
     * Authenticate admin.
     * 
     * @return \Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse
     */
    public function authenticate(Request $request)
    {
        try {
            // Validate request data with Validator
            $request->validate([
                'email' => 'required|email',
                'password' => 'required|min:6'
            ]);

            // Find admin
            $admin = Admin::where('email', $request->email)->get()->first();

            // Check admin is exists
            if ($admin) {
                if (Hash::check($request->password, $admin->password)) {
                    // Store admin info in session
                    Session::put('admin', $admin);

                    return redirect()->route('admin.dashboard');
                } else {
                    return redirect()->back()->with([
                        'error' => 'Password admin tidak cocok'
                    ]);
                }
            } else {
                return redirect()->back()->with([
                    'error' => 'Email admin salah atau tidak ditemukan'
                ]);
            }
        } catch (Exception $e) {
            return redirect()->back()->with([
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Admin logout.
     * 
     * @return \Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        try {
            // Clear session
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            $request->session()->flush();

            return redirect()->route('admin.login');
        } catch (Exception $e) {
            return redirect()->back()->with([
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Return admin dashboard view with data.
     * 
     * @return view
     */
    public function dashboard()
    {
        // Get tembang total
        $result = $this->sparql->query("
            SELECT DISTINCT (count(?tembang) as ?total) WHERE {
                ?tembang rdf:type tb:TembangBali .
            }
        ");

        $tembang_count = 0;

        if ($result->numRows() > 0) {
            $tembang_count = $this->parseData($result[0]->total, true);
        }

        $data = [
            'title' => 'Dashboard',
            'site_name' => 'Metembang Bali',
            'menu' => true,
            'admin_count' => Admin::count(),
            'tembang_count' => $tembang_count,
            'submission_count' => TembangSubmission::count(),
            'user_count' => User::count(),
            'latest_submissions' => TembangSubmission::orderByDesc('created_at')->limit(8)->get(),
            'latest_users' => User::orderByDesc('created_at')->limit(5)->get(),
        ];
        
        return view('admin.dashboard', $data);
    }

    /**
     * Return tembang submission view with data.
     * 
     * @return view
     */
    public function submission()
    {
        $data = [
            'title' => 'Submission',
            'site_name' => 'Metembang Bali',
            'menu' => true,
            'submissions' => DB::table('tembang_submissions')
                ->orderByDesc('created_at')
                ->paginate(10)
        ];

        return view('admin.submission', $data);
    }

    /**
     * Return tembang submission detail view.
     * 
     * @return view
     */
    public function showSubmission($id)
    {
        $data = [
            'title' => 'Submission Detail',
            'site_name' => 'Metembang Bali',
            'menu' => true,
            'submission' => TembangSubmission::find($id)
        ];
        
        return view('admin.submission-detail', $data);
    }

    /**
     * Destroy tembang submission record.
     * 
     * @return \Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse
     */
    public function destroySubmission($id)
    {
        try {
            // Find tembang submission by id
            $tembang = TembangSubmission::find($id);

            // Store title
            $title = $tembang->title;

            // Delete tembang submission
            $tembang->delete();

            return redirect()->route('admin.submission')->with([
                'success' => 'Submission tembang ' . $title . ' (#' . $id . ') berhasil dihapus'
            ]);
        } catch (Exception $e) {
            return redirect()->back()->with([
                'error' => $e->getMessage()
            ]);
        }
    }
}

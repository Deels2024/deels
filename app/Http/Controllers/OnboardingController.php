<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Faq;
use App\Models\Mailing;
use App\Models\News;
use App\Models\NewsletterMail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class OnboardingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return View
     */
    public function index()
    {
        return view('onboarding');
    }

    public function finish()
    {
        if (Auth::user()) {
            Auth::user()->update(['is_onboarding' => true]);
        }
        return redirect('/');
    }


}

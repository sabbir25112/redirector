<?php namespace App\Http\Controllers;

use App\Models\Analytics;
use App\Models\Redirect;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class RedirectController extends Controller
{
    public function redirectToDestination()
    {
        $match = [];
        $host = request()->header('host');
        preg_match('/^(?:http:\/\/|www\.|https:\/\/)([^\/]+)/', $host, $match);
        $domain = $match[1];

        $redirect_url = $this->getRedirectURL($domain);
        $this->storeAnalytics($domain, $redirect_url);

        return \redirect()->away($redirect_url);
    }

    public function index()
    {
        $redirects = Redirect::paginate(20);
        return view('redirect.index', compact('redirects'));
    }

    public function create()
    {
        return view('redirect.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'incoming'  => [
                'required',
                Rule::unique('redirects')->where(function ($query) {
                    $query->whereNull('deleted_at');
                })
            ],
            'outgoing'  => 'required',
        ]);

        Redirect::create([
            'incoming'  => $request->incoming,
            'outgoing'  => $request->outgoing,
        ]);

        session()->flash('status', 'Redirection Information Stored Successfully');
        return \redirect()->route('redirect.index');
    }

    public function edit(Redirect $redirect)
    {
        return view('redirect.edit', compact('redirect'));
    }

    public function update(Redirect $redirect, Request $request)
    {
        $request->validate([
            'incoming'  => [
                'required',
                Rule::unique('redirects')->where(function ($query) use ($redirect) {
                    $query->where('id' , '!=', $redirect->id)
                        ->whereNull('deleted_at');
                })
            ],
            'outgoing'  => 'required',
        ]);

        $redirect->update([
            'incoming'  => $request->incoming,
            'outgoing'  => $request->outgoing,
        ]);

        session()->flash('status', 'Redirection Information Updated Successfully');
        return \redirect()->route('redirect.index');
    }

    public function delete(Redirect $redirect)
    {
        $redirect->delete();

        session()->flash('status', 'Redirection Information Deleted Successfully');
        return \redirect()->route('redirect.index');
    }

    private function getRedirectURL($domain)
    {
        $redirect = Redirect::where('incoming', 'like', "%" . $domain)->first();
        if (!$redirect) return config('app.default_redirect_url');
        return $redirect;
    }

    private function storeAnalytics($domain, $redirect_url)
    {
        try {
            $subIds = [
                'subId',
                'subId',
                'SubId',
                'subid',
                'SUBID',
                'sub_id',
                'Sub_Id',
                'Sub_ID',
                'SUB_ID',
                'suBId',
            ];

            foreach ($subIds as $subId) {
                $user_identity = \request()->has($subId) ? \request()->get($subId) : null;

                if ($user_identity) break;
            }
            Analytics::create([
                'subId'         => $user_identity,
                'time'          => Carbon::now(),
                'ip'            => \request()->ip(),
                'origin'        => $domain,
                'destination'   => $redirect_url,
                'request'       => json_encode(\request()->all()),
            ]);
            throw new \Exception("OKK");
        } catch (\Exception $exception) {
            Log::error($exception);

            Analytics::create([
                'time'          => Carbon::now(),
                'ip'            => \request()->ip(),
                'origin'        => $domain,
                'destination'   => $redirect_url,
                'request'       => json_encode(\request()->all()),
            ]);
        }
    }
}

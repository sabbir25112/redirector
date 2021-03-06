<?php namespace App\Http\Controllers;

use App\Models\Analytics;
use App\Models\Redirect;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Jenssegers\Agent\Agent;

class RedirectController extends Controller
{
    public function redirectToDestination()
    {
        try {
            $match = [];
            $host = request()->header('host');
            preg_match('/^(?:http:\/\/|www\.|https:\/\/)([^\/]+)/', $host, $match);
            $domain = $match[1];

            $redirect_url = $this->getRedirectURL($domain);
            $redirect_url = $this->storeAnalytics($domain, $redirect_url);
        } catch (\Exception $exception) {
            $host = request()->header('host');
            $redirect_url = $this->getRedirectURL($host);
            $redirect_url = $this->storeAnalytics($host, $redirect_url);
        }

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
        return $redirect->outgoing;
    }

    private function storeAnalytics($domain, $redirect_url)
    {
        $Agent = new Agent();
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
                'a',
                'b',
                'c',
                'd',
                'e',
                'f',
                'g',
                'h',
                'i',
                'j',
                'k',
                'l',
                'm',
                'n',
                'o',
                'p',
                'q',
                'r',
                's',
                't',
                'u',
                'v',
                'w',
                'x',
                'y',
                'z',
            ];

            foreach ($subIds as $subId) {
                $user_identity = \request()->has($subId) ? \request()->get($subId) : null;

                if ($user_identity) break;
            }

            if ($user_identity == null) {
                $redirect_url = config('app.default_redirect_url');
            }

            Analytics::create([
                'subId'         => $user_identity,
                'time'          => Carbon::now(),
                'ip'            => \request()->ip(),
                'origin'        => $domain,
                'destination'   => $redirect_url,
                'request'       => json_encode(\request()->all()),
                'browser'       => $Agent->browser(),
                'os_name'       => $Agent->platform(),
            ]);
        } catch (\Exception $exception) {
            Log::error($exception);

            Analytics::create([
                'time'          => Carbon::now(),
                'ip'            => \request()->ip(),
                'origin'        => $domain,
                'destination'   => $redirect_url,
                'request'       => json_encode(\request()->all()),
                'browser'       => $Agent->browser(),
                'os_name'       => $Agent->platform(),
            ]);
        }
        return $redirect_url;
    }
}

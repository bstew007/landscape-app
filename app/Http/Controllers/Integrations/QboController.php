<?php

namespace App\Http\Controllers\Integrations;

use App\Http\Controllers\Controller;
use App\Models\QboToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class QboController extends Controller
{
    public function launch()
    {
        // Entry point from Intuit AppCenter. Route user to settings or kick off connect.
        if (!QboToken::latest('updated_at')->first()) {
            return redirect()->route('integrations.qbo.connect');
        }
        return redirect()->route('integrations.qbo.settings');
    }

    public function connect()
    {
        $conf = config('qbo');
        $state = Str::random(32);
        session(['qbo_oauth_state' => $state]);
        $scopes = implode('%20', $conf['scopes']);
        $authUrl = sprintf(
            'https://appcenter.intuit.com/connect/oauth2?client_id=%s&scope=%s&redirect_uri=%s&response_type=code&state=%s',
            urlencode($conf['client_id']),
            $scopes,
            urlencode($conf['redirect_uri']),
            urlencode($state)
        );
        return redirect()->away($authUrl);
    }

    public function callback(Request $request)
    {
        $state = $request->get('state');
        if (!$state || $state !== session('qbo_oauth_state')) {
            abort(403, 'Invalid state');
        }
        $code = $request->get('code');
        $realmId = $request->get('realmId');
        if (!$code || !$realmId) {
            return redirect()->route('integrations.qbo.settings')->with('error', 'Missing code or realm ID');
        }
        $conf = config('qbo');
        $tokenRes = Http::asForm()
            ->withBasicAuth($conf['client_id'], $conf['client_secret'])
            ->post('https://oauth.platform.intuit.com/oauth2/v1/tokens/bearer', [
                'grant_type' => 'authorization_code',
                'code' => $code,
                'redirect_uri' => $conf['redirect_uri'],
            ]);
        if (!$tokenRes->ok()) {
            return redirect()->route('integrations.qbo.settings')->with('error', 'Token exchange failed');
        }
        $data = $tokenRes->json();
        QboToken::updateOrCreate(
            ['realm_id' => $realmId],
            [
                'access_token' => $data['access_token'] ?? '',
                'refresh_token' => $data['refresh_token'] ?? '',
                'expires_at' => now()->addSeconds($data['expires_in'] ?? 0),
                'id_token' => $data['id_token'] ?? null,
            ]
        );
        return redirect()->route('integrations.qbo.settings')->with('success', 'QuickBooks connected');
    }

    public function settings()
    {
        $token = QboToken::latest('updated_at')->first();
        return view('integrations.qbo', ['token' => $token]);
    }

    public function disconnect(Request $request)
    {
        $realmId = $request->get('realmId');
        $token = $realmId ? QboToken::where('realm_id', $realmId)->first() : QboToken::latest('updated_at')->first();
        if ($token) {
            // Revoke refresh token per Intuit docs
            $conf = config('qbo');
            try {
                Http::asForm()
                    ->withBasicAuth($conf['client_id'], $conf['client_secret'])
                    ->post('https://developer.api.intuit.com/v2/oauth2/tokens/revoke', [
                        'token' => $token->refresh_token,
                    ]);
            } catch (\Throwable $e) {
                // swallow errors; we're disconnecting regardless
            }
            $token->delete();
        }
        return redirect()->route('integrations.qbo.settings')->with('success', 'QuickBooks disconnected');
    }
}

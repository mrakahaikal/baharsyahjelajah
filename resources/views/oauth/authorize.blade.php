<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Otorisasi Aplikasi — Baharsyah Jelajah OAuth</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen items-center justify-center bg-slate-950 p-4 text-slate-100 antialiased selection:bg-blue-600 selection:text-white">
    <div class="w-full max-w-lg rounded-2xl border border-slate-800/80 bg-slate-900/90 p-6 shadow-2xl backdrop-blur-xl sm:p-8">
        <div class="flex items-center gap-3 border-b border-slate-800 pb-6">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl border border-blue-500/30 bg-blue-500/10 font-mono text-lg font-bold text-blue-400">
                BJ
            </div>
            <div>
                <h1 class="text-base font-semibold tracking-tight text-white">Baharsyah Jelajah OAuth Server</h1>
                <p class="text-xs text-slate-400">Permintaan Otorisasi Akses AI Agent / Aplikasi</p>
            </div>
        </div>

        <div class="space-y-4 py-6">
            <p class="text-sm leading-relaxed text-slate-300">
                Aplikasi <strong class="font-semibold text-blue-400">{{ $client->name }}</strong> meminta izin untuk
                mengakses akun Anda atas nama:
            </p>

            <div class="flex items-center gap-3 rounded-xl border border-slate-800 bg-slate-950/60 p-3.5">
                <div class="flex h-9 w-9 items-center justify-center rounded-full bg-slate-800 text-sm font-bold text-slate-300">
                    {{ substr($user->name, 0, 1) }}
                </div>
                <div class="truncate">
                    <p class="truncate text-sm font-medium text-white">{{ $user->name }}</p>
                    <p class="truncate text-xs text-slate-400">{{ $user->email }}</p>
                </div>
            </div>

            @if (count($scopes) > 0)
                <div class="space-y-2 pt-2">
                    <p class="text-xs font-semibold tracking-wider text-slate-400 uppercase">
                        Izin yang Diminta (Scopes):
                    </p>
                    <ul class="space-y-1.5 text-xs text-slate-300">
                        @foreach ($scopes as $scope)
                            <li class="flex items-start gap-2 rounded-lg border border-slate-800/60 bg-slate-950/40 p-2.5">
                                <span class="mt-0.5 shrink-0 text-blue-400">✓</span>
                                <div>
                                    <span class="font-mono font-medium text-blue-300">{{ $scope->id }}</span>
                                    <p class="mt-0.5 text-[11px] text-slate-400">{{ $scope->description }}</p>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        <div class="flex flex-col gap-3 border-t border-slate-800 pt-4 sm:flex-row">
            {{-- Approve Form --}}
            <form method="post" action="{{ route('passport.authorizations.approve') }}" class="flex-1">
                @csrf
                <input type="hidden" name="state" value="{{ $request->input('state') }}" />
                <input type="hidden" name="client_id" value="{{ $client->id }}" />
                <input type="hidden" name="auth_token" value="{{ $authToken }}" />
                <button
                    type="submit"
                    class="flex w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-500"
                >
                    Izinkan Akses
                </button>
            </form>

            {{-- Deny Form --}}
            <form method="post" action="{{ route('passport.authorizations.deny') }}" class="flex-1">
                @csrf
                @method('DELETE')
                <input type="hidden" name="state" value="{{ $request->input('state') }}" />
                <input type="hidden" name="client_id" value="{{ $client->id }}" />
                <input type="hidden" name="auth_token" value="{{ $authToken }}" />
                <button
                    type="submit"
                    class="w-full rounded-xl bg-slate-800 px-4 py-2.5 text-sm font-medium text-slate-300 transition hover:bg-slate-700"
                >
                    Tolak
                </button>
            </form>
        </div>
    </div>
</body>
</html>


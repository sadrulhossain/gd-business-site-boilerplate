<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign in</title>
    <style>
        :root { color-scheme: light dark; --primary: #0f766e; --primary-hover: #115e59; --canvas: #f4f8f7; --card: #fff; --ink: #0f1f1d; --muted: #4a5f5b; --line: #7a908c; --danger: #b91c1c; }
        @media (prefers-color-scheme: dark) { :root { --primary-hover: #14877c; --canvas: #0a1514; --card: #10201e; --ink: #e6f2f0; --muted: #9db5b1; --line: #5f7f7a; --danger: #f87171; } }
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; color: var(--ink);
               background: radial-gradient(60rem 30rem at 10% -10%, rgb(15 118 110 / .28), transparent), radial-gradient(50rem 28rem at 100% 110%, rgb(45 212 191 / .2), transparent), var(--canvas);
               font: 14px/1.5 ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif; }
        main { width: min(24rem, calc(100vw - 2rem)); background: var(--card); border: 1px solid rgb(15 118 110 / .25); border-radius: .875rem; padding: 2rem 1.75rem; box-shadow: 0 12px 28px -6px rgb(15 31 29 / .25); }
        .mark { display: grid; place-items: center; width: 2.75rem; height: 2.75rem; margin-bottom: 1rem; border-radius: .625rem; background: var(--primary); color: #fff; box-shadow: 0 4px 12px -2px rgb(15 118 110 / .5); }
        h1 { margin: 0 0 1.25rem; font-size: 1.4rem; }
        label { display: block; margin-bottom: .85rem; }
        label span { display: block; margin-bottom: .25rem; color: var(--muted); font-weight: 600; }
        input { width: 100%; padding: .55rem .7rem; border: 1px solid var(--line); border-radius: .625rem; background: var(--card); color: var(--ink); font: inherit; }
        input:focus { outline: none; border-color: #14877c; box-shadow: 0 0 0 3px rgb(20 135 124 / .3); }
        button { width: 100%; margin-top: .25rem; padding: .6rem; border: 0; border-radius: .625rem; background: var(--primary); color: #fff; font: inherit; font-weight: 600; cursor: pointer; box-shadow: 0 1px 2px rgb(0 0 0 / .15); }
        button:hover { background: var(--primary-hover); }
        .error { margin: 0 0 .75rem; color: var(--danger); }
    </style>
</head>
<body>
<main>
    <div class="mark" aria-hidden="true"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 2 10 5-10 5L2 7z"/><path d="m2 12 10 5 10-5"/><path d="m2 17 10 5 10-5"/></svg></div>
    <h1>Sign in</h1>
    <form method="POST" action="{{ url('login') }}">
        @csrf
        @error('email')<p class="error" role="alert">{{ $message }}</p>@enderror
        <label><span>Email</span><input type="email" name="email" value="{{ old('email') }}" autocomplete="username" required autofocus></label>
        <label><span>Password</span><input type="password" name="password" autocomplete="current-password" required></label>
        <button type="submit">Sign in</button>
    </form>
</main>
</body>
</html>

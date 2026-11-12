<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign in</title>
    <style>
        :root { color-scheme: light; --primary: #4f46e5; --ink: #1b1f2a; --muted: #5b6275; --line: #d9dce3; --danger: #b91c1c; }
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; background: #f4f5f7; color: var(--ink);
               font: 14px/1.5 ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif; }
        main { width: min(22rem, calc(100vw - 2rem)); background: #fff; border: 1px solid var(--line); border-radius: .5rem; padding: 1.5rem; }
        h1 { margin: 0 0 1rem; font-size: 1.25rem; }
        label { display: block; margin-bottom: .75rem; }
        label span { display: block; margin-bottom: .25rem; color: var(--muted); }
        input { width: 100%; padding: .5rem .625rem; border: 1px solid var(--line); border-radius: .375rem; font: inherit; }
        input:focus { outline: 2px solid var(--primary); outline-offset: -1px; }
        button { width: 100%; padding: .55rem; border: 0; border-radius: .375rem; background: var(--primary); color: #fff; font: inherit; cursor: pointer; }
        button:hover { background: #4338ca; }
        .error { margin: 0 0 .75rem; color: var(--danger); }
    </style>
</head>
<body>
<main>
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
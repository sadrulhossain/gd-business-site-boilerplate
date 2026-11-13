#!/bin/sh
# Secrets live in the environment only. Fails when a tracked file holds a private key or a hard-coded credential,
# when a .env file is tracked, or (APP_ENV=production) when .env still has a "change-me" placeholder.
cd "$(dirname "$0")/.." || exit 1
bad=0

tracked_env=$(git ls-files | grep -E '(^|/)\.env($|\.[^e])' | grep -v '\.example$')
[ -z "$tracked_env" ] || { echo "TRACKED env file: $tracked_env"; bad=1; }

keys=$(git ls-files -z | xargs -0 grep -IlE 'BEGIN ([A-Z]+ )?PRIVATE KEY|AKIA[0-9A-Z]{16}|ghp_[A-Za-z0-9]{30,}|sk-[A-Za-z0-9]{32,}' 2>/dev/null)
[ -z "$keys" ] || { echo "SECRET-LIKE content in: $keys"; bad=1; }

# Credentials must come from env(); a literal fallback in config/ is a hard-coded default.
lit=$(grep -rnE "env\('[A-Z_]*(PASSWORD|SECRET|TOKEN|KEY)', *'[^']+'\)" api/config | grep -v "env('APP_KEY'" )
[ -z "$lit" ] || { echo "Literal credential default in config:"; echo "$lit"; bad=1; }

# An example file (as staged or committed) must not carry a real-looking password (anything but empty, null or change-me).
ex=$(git grep --cached -nE '^[A-Z_]*(PASSWORD|SECRET)[A-Z_]*=.+' -- '*.env.example' '*.env.prod.example' | grep -vE '=(change-me|null|"?\$\{[A-Z_]+\}"?)$')
[ -z "$ex" ] || { echo "Real-looking secret in an example file:"; echo "$ex"; bad=1; }

if [ -f .env ] && grep -qE '^APP_ENV=production' .env && grep -q 'change-me' .env; then
  echo "APP_ENV=production but .env still has change-me placeholders"; bad=1
fi

[ "$bad" = 0 ] && echo "secrets: ok"
exit $bad

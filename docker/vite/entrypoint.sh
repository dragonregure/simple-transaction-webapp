#!/usr/bin/env sh
set -eu

if [ ! -d node_modules/vite ]; then
  npm ci
fi

rm -f public/hot

if [ "$#" -eq 0 ]; then
  set -- npm run dev -- --host 0.0.0.0 --port "${VITE_PORT:-5173}"
fi

exec "$@"

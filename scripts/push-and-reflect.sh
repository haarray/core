#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

TARGET="${1:-log}"
if [[ $# -gt 0 ]]; then
  shift
fi

php artisan haarray:reflect:sync "$TARGET" --commit-targets --push-targets --push-core "$@"

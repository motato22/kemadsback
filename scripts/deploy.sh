#!/usr/bin/env bash

set -Eeuo pipefail

DEPLOY_BRANCH="${DEPLOY_BRANCH:-main}"
DEPLOY_PATH="${DEPLOY_PATH:-/var/www/kemads.bridgestudio.host/current}"
SHARED_ENV_PATH="${SHARED_ENV_PATH:-/var/www/kemads.bridgestudio.host/shared/.env}"
SSH_HOST="${SSH_HOST:-${STAGING_HOST:-}}"
SSH_USER="${SSH_USER:-${STAGING_USER:-root}}"
SSH_PORT="${SSH_PORT:-22}"
SSH_KEY_PATH="${SSH_KEY_PATH:-}"
COMPOSE_FILE="${COMPOSE_FILE:-docker-compose.prod.yml}"

if [[ -z "$SSH_HOST" ]]; then
    echo "Missing SSH_HOST or STAGING_HOST." >&2
    exit 1
fi

ssh_args=(
    -p "$SSH_PORT"
    -o StrictHostKeyChecking=accept-new
    -o IdentitiesOnly=yes
)

if [[ -n "$SSH_KEY_PATH" ]]; then
    ssh_args+=(-i "$SSH_KEY_PATH")
fi

remote_vars=(
    "DEPLOY_BRANCH=$(printf '%q' "$DEPLOY_BRANCH")"
    "DEPLOY_PATH=$(printf '%q' "$DEPLOY_PATH")"
    "SHARED_ENV_PATH=$(printf '%q' "$SHARED_ENV_PATH")"
    "COMPOSE_FILE=$(printf '%q' "$COMPOSE_FILE")"
)

ssh "${ssh_args[@]}" "${SSH_USER}@${SSH_HOST}" "${remote_vars[*]} bash -s" <<'REMOTE_SCRIPT'
set -Eeuo pipefail

cd "$DEPLOY_PATH"

if [[ ! -d .git ]]; then
    echo "$DEPLOY_PATH is not a Git checkout." >&2
    exit 1
fi

if [[ ! -f "$SHARED_ENV_PATH" ]]; then
    echo "Missing environment file: $SHARED_ENV_PATH" >&2
    exit 1
fi

if ! command -v docker >/dev/null 2>&1; then
    echo "Docker is not installed on the server." >&2
    exit 1
fi

if ! docker compose version >/dev/null 2>&1; then
    echo "Docker Compose plugin is not available on the server." >&2
    exit 1
fi

git fetch origin "$DEPLOY_BRANCH"
git checkout "$DEPLOY_BRANCH"
git reset --hard "origin/$DEPLOY_BRANCH"

docker compose -f "$COMPOSE_FILE" build --no-cache --pull app
docker compose -f "$COMPOSE_FILE" up -d --remove-orphans app

docker compose -f "$COMPOSE_FILE" exec -T app php artisan package:discover --ansi
docker compose -f "$COMPOSE_FILE" exec -T app php artisan storage:link
docker compose -f "$COMPOSE_FILE" exec -T app php artisan optimize:clear
docker compose -f "$COMPOSE_FILE" exec -T app php artisan migrate --force
docker compose -f "$COMPOSE_FILE" exec -T app php artisan config:cache
docker compose -f "$COMPOSE_FILE" exec -T app php artisan route:cache
docker compose -f "$COMPOSE_FILE" exec -T app php artisan view:cache

docker compose -f "$COMPOSE_FILE" ps
REMOTE_SCRIPT

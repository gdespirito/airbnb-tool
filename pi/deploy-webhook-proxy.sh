#!/bin/bash
# Deploy webhook proxy to Pi (managed by systemd user service)
set -e

PI="pi@10.10.11.76"
REMOTE_DIR="~/.openclaw/webhook-proxy"
SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"

echo "Syncing webhook-proxy to Pi..."
rsync -avz --exclude node_modules "$SCRIPT_DIR/webhook-proxy/" "$PI:$REMOTE_DIR/"

echo "Installing dependencies..."
ssh "$PI" "cd $REMOTE_DIR && npm install --production"

echo "Installing systemd service..."
ssh "$PI" "cp $REMOTE_DIR/openclaw-webhook-proxy.service ~/.config/systemd/user/ && systemctl --user daemon-reload && systemctl --user enable openclaw-webhook-proxy && systemctl --user restart openclaw-webhook-proxy"

echo "Checking status..."
ssh "$PI" "systemctl --user status openclaw-webhook-proxy --no-pager"

echo "Done."

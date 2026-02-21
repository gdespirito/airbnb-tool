---
name: openclaw
description: Manage OpenClaw AI agents running on Raspberry Pi (pi@10.10.11.76). Use when working with OpenClaw configuration, agents (Alma/Atlas), WhatsApp bot, sessions, skills, knowledge files, heartbeat, or gateway. Activate when the user mentions openclaw, alma, atlas, agents, whatsapp bot, pi, or raspberry.
allowed-tools: Bash(ssh *), Bash(scp *), Read, Grep, Glob
---

# OpenClaw Management

OpenClaw is an AI agent platform running on a Raspberry Pi that manages WhatsApp bots.

## Connection

```bash
ssh pi@10.10.11.76
```

Binary: `/home/pi/.npm-global/bin/openclaw`

## Architecture

| Agent | ID | Workspace | Model | Purpose |
|-------|----|-----------|-------|---------|
| Atlas | `main` | `~/.openclaw/workspace/` | claude-haiku-4-5 | Gonza's personal assistant |
| Alma | `airbnb` | `~/.openclaw/workspace-airbnb/` | claude-opus-4-6 | Airbnb guest hospitality |

### Routing (WhatsApp bindings)

- Gonza (+56988153776) → agent `main` (Atlas)
- Everyone else → agent `airbnb` (Alma)
- Commands restricted: only Gonza can run `/new`, `/usage`, `/reset` etc.

## Key Files

### Main config
- `~/.openclaw/openclaw.json` — agents, bindings, channels, commands, tools, hooks, cron

### Alma workspace (`~/.openclaw/workspace-airbnb/`)
| File | Purpose |
|------|---------|
| `IDENTITY.md` | Name, language, emoji |
| `SOUL.md` | Personality, limits, guardrails (only hospitality, no code gen) |
| `AGENTS.md` | Workflows: guest identification, welcome material, pre-checkin, checkout, cleaning, FAQs, property isolation |
| `USER.md` | Properties & team info |
| `HEARTBEAT.md` | Scheduled checks (2x daily) |
| `knowledge/PUPUYA.md` | Casa Pupuya guest guide (WiFi, amenities, restaurants, activities) |
| `knowledge/PULLINQUE.md` | Cabaña Pullinque guest guide (WiFi, hot tub instructions, firewood) |
| `skills/laravel-api/SKILL.md` | API documentation for guest-facing operations |
| `config/api-token.txt` | Bearer token for airbnb.freshwork.dev API |

### Media files (allowed directory for WhatsApp sending)
- `~/.openclaw/media/pullinque/guia-casa-pullinque.pdf`
- `~/.openclaw/media/pupuya/guia-casa-pupuya.pdf`
- `~/.openclaw/media/pupuya/como-llegar.mp4` + 5 photos (`como-llegar-1.jpeg` to `como-llegar-5.jpeg`)

NOTE: Media MUST be in `~/.openclaw/media/` (not workspace) for WhatsApp to send it.

### Atlas workspace (`~/.openclaw/workspace/`)
| File | Purpose |
|------|---------|
| `skills/airbnb-api/SKILL.md` | API admin skill for Gonza |
| `skills/airbnb-hosting/SKILL.md` | Browser-based Airbnb hosting operations |

## Common Operations

### Restart gateway
```bash
ssh pi@10.10.11.76 "/home/pi/.npm-global/bin/openclaw gateway restart"
```

### View/edit agent config
```bash
ssh pi@10.10.11.76 "cat ~/.openclaw/openclaw.json"
```

Edit agents.list for agent config (model, tools.deny, etc.). Edit at root for commands, bindings, channels.

### Reset a guest session
```python
# On Pi, use python3 to edit sessions.json
ssh pi@10.10.11.76 "python3 -c \"
import json
path = '/home/pi/.openclaw/agents/airbnb/sessions/sessions.json'
with open(path) as f:
    data = json.load(f)
key = 'agent:airbnb:whatsapp:direct:+56XXXXXXXXX'
if key in data:
    sid = data[key].get('sessionId', '')
    del data[key]
    with open(path, 'w') as f:
        json.dump(data, f, indent=2)
    import os, glob
    for f in glob.glob(f'/home/pi/.openclaw/agents/airbnb/sessions/{sid}*'):
        os.remove(f)
\""
```
Then restart gateway.

### Read session messages
```python
ssh pi@10.10.11.76 "python3 -c \"
import json
with open('/home/pi/.openclaw/agents/airbnb/sessions/SESSION_ID.jsonl') as f:
    for line in f:
        d = json.loads(line)
        if d.get('type') == 'message':
            role = d['message'].get('role','?')
            content = d['message'].get('content','')
            if isinstance(content, list):
                content = ' '.join(c.get('text','') for c in content if c.get('type')=='text')
            print(f'[{role}] {content[:300]}')
\""
```

### Update API token
1. Generate on production: `kubectl exec -n airbnb-tool deploy/airbnb-tool -- php artisan tinker --execute='...'`
2. Update on Pi: `ssh pi@10.10.11.76 "echo -n 'TOKEN' > ~/.openclaw/workspace-airbnb/config/api-token.txt"`

### Add/update knowledge
Edit files in `~/.openclaw/workspace-airbnb/knowledge/` (PUPUYA.md, PULLINQUE.md).

### Add/update media
Copy to `~/.openclaw/media/{property}/` (NOT workspace/media).
```bash
scp localfile.pdf pi@10.10.11.76:~/.openclaw/media/pullinque/
```

## Valid Per-Agent Config Keys (in openclaw.json → agents.list[])

`id`, `name`, `workspace`, `model`, `identity`, `tools` (allow/deny), `sandbox`, `heartbeat`, `compaction`, `humanDelay`, `typingMode`, `agentDir`, `subagents`, `default`

NOTE: `commands` is NOT per-agent — it's root-level only. Use `commands.allowFrom` to restrict who can run commands.

## Properties

| Property | Hostex ID | Aseo | Fee |
|----------|-----------|------|-----|
| Casa Pupuya (ID 1) | — | Eliene +56 9 9983 4369 | $25.000 |
| Cabaña Pullinque (ID 2) | 12559596 | Viviana +56 9 7397 8287 | $30.000 |

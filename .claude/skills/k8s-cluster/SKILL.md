---
name: k8s
description: Manage the Kubernetes cluster (homelab) and the airbnb-tool deployment. Use when working with kubectl, pods, deployments, sealed secrets, ArgoCD, migrations, tinker, queue workers, or production troubleshooting. Activate when the user mentions k8s, kubernetes, cluster, production, deploy, pods, or infrastructure.
allowed-tools: Bash(kubectl *), Bash(kubeseal *), Bash(argocd *), Read, Grep, Glob
---

# Kubernetes Cluster Management

## Cluster Info

- **Context**: `admin@freshwork`
- **OS**: Talos Linux v1.11.5
- **K8s**: v1.34.1
- **Nodes**: 4 (3 control-plane, 1 worker)
- **GitOps**: ArgoCD
- **Secrets**: Bitnami Sealed Secrets
- **Ingress**: nginx
- **Certs**: cert-manager (Let's Encrypt)

## Airbnb Tool Deployment

- **Namespace**: `airbnb-tool`
- **Domain**: `airbnb.freshwork.dev`
- **DB**: MariaDB (`mariadb-cluster.mariadb.svc.cluster.local`, database: `airbnb_tool`)
- **Image**: Built by GitHub Actions, pushed to private registry
- **Infra repo**: `/Users/gonza/code/k8s-infra/clusters/lab/apps/airbnb-tool.yaml`

### Components

| Resource | Purpose |
|----------|---------|
| `deploy/airbnb-tool` | Web app (Laravel) |
| `deploy/airbnb-tool-worker` | Queue worker (`php artisan queue:work`) |
| `cronjob/airbnb-tool-scheduler` | Scheduler (`php artisan schedule:run` every min) |
| `svc/airbnb-tool` | ClusterIP service (port 80) |
| `ingress/airbnb-tool` | nginx ingress → airbnb.freshwork.dev |

### Sealed Secrets
| Name | Contents |
|------|----------|
| `airbnb-tool-db-password` | DB credentials |
| `airbnb-tool-secrets` | APP_KEY, HOSTEX_API_KEY |
| `airbnb-tool-resend-key` | RESEND_KEY (email via Resend) |
| `registry-pull-secret` | Docker registry auth |

## Common Operations

### Check status
```bash
kubectl get pods -n airbnb-tool
kubectl logs -n airbnb-tool deploy/airbnb-tool --tail=50
kubectl logs -n airbnb-tool deploy/airbnb-tool-worker --tail=50
```

### Run migrations
```bash
kubectl exec -n airbnb-tool deploy/airbnb-tool -- php artisan migrate --force --no-interaction
```

### Run tinker (single-quoted PHP to avoid shell escaping)
```bash
kubectl exec -n airbnb-tool deploy/airbnb-tool -- php artisan tinker --execute='
$user = App\Models\User::first();
echo $user->email;
'
```

IMPORTANT: Use single quotes for tinker --execute to avoid bash escaping issues. Avoid apostrophes/single-quotes inside PHP code (use string concatenation or double quotes).

### Hostex sync (backfill reservations)
```bash
kubectl exec -n airbnb-tool deploy/airbnb-tool -- php artisan hostex:sync --all
```

### Create API token
```bash
kubectl exec -n airbnb-tool deploy/airbnb-tool -- php artisan tinker --execute='
$user = App\Models\User::first();
$user->tokens()->delete();
$token = $user->createToken("openclaw-airbnb");
echo $token->plainTextToken;
'
```
Then update on Pi: `ssh pi@10.10.11.76 "echo -n 'TOKEN' > ~/.openclaw/workspace-airbnb/config/api-token.txt"`

### Force new deployment (when image tag doesn't change)
```bash
kubectl set env deploy/airbnb-tool -n airbnb-tool DEPLOY_TIMESTAMP=$(date +%s)
```

### Restart pods
```bash
kubectl rollout restart deploy/airbnb-tool -n airbnb-tool
kubectl rollout restart deploy/airbnb-tool-worker -n airbnb-tool
```

### View worker queue
```bash
kubectl exec -n airbnb-tool deploy/airbnb-tool -- php artisan tinker --execute='
echo App\Models\User::count() . " users\n";
echo DB::table("jobs")->count() . " pending jobs\n";
echo DB::table("failed_jobs")->count() . " failed jobs\n";
'
```

### Check webhook logs
```bash
kubectl logs -n airbnb-tool deploy/airbnb-tool --tail=100 | grep -i hostex
```

## Sealed Secrets

Create or update sealed secrets using `kubeseal`:

```bash
# Create a sealed secret (namespace-wide scope)
echo -n "secret-value" | kubectl create secret generic secret-name \
  --namespace airbnb-tool \
  --from-file=KEY=/dev/stdin \
  --dry-run=client -o yaml | \
  kubeseal --controller-namespace sealed-secrets \
  --scope namespace-wide \
  -o yaml > sealed-secret.yaml
```

IMPORTANT: Always use `--scope namespace-wide` for this namespace. Previous attempts without this flag failed to decrypt.

## ArgoCD

```bash
# Sync app
argocd app sync airbnb-tool

# Check app status
argocd app get airbnb-tool
```

## GitHub Actions

The CI/CD pipeline:
1. Push to `main` branch
2. GitHub Actions builds Docker image
3. Pushes to private registry
4. ArgoCD detects new image and deploys

To trigger a deploy: push a commit to main. The image tag may not change (uses `latest` or branch-based), so use `kubectl set env` trick to force pod recreation.

## Database Setup (from scratch)

If the DB is wiped, recreate everything:

```bash
# 1. Run migrations
kubectl exec -n airbnb-tool deploy/airbnb-tool -- php artisan migrate --force --no-interaction

# 2. Create user (get creds from 1Password)
op item get "evwespar242xhd2bbyzyku2omq" --vault "xtqcr6slvug2ihnmkgl6tew2mu" --account my.1password.com --format json

# 3. Seed via tinker: user, contacts (Eliene, Viviana), properties (Pupuya, Pullinque)

# 4. Create API token and update on Pi

# 5. Backfill reservations
kubectl exec -n airbnb-tool deploy/airbnb-tool -- php artisan hostex:sync --all
```

## Infra Repo

Manifests: `/Users/gonza/code/k8s-infra/clusters/lab/apps/airbnb-tool.yaml`

Contains: web Deployment, worker Deployment, scheduler CronJob, Service, Ingress, SealedSecrets, env vars.

## Troubleshooting

- **Pod CrashLoopBackOff**: Check logs, likely missing env var or DB connection issue
- **Image not updating**: Use `kubectl set env` trick to force new pod
- **migrate:fresh blocked**: Production prevents destructive commands. Truncate migrations table manually via tinker, then run migrate
- **Sealed secret won't decrypt**: Re-seal with `--scope namespace-wide`
- **Queue not processing**: Check worker pod logs, restart if needed

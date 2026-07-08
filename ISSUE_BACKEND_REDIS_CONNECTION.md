# Issue: Backend Container Returns 500 Errors - Redis Connection Failure

**Status:** 🔴 **UNRESOLVED**  
**Severity:** Critical  
**Component:** Backend / Docker Compose  
**Date Created:** 2026-07-08  
**Session:** Initial troubleshooting complete, needs resolution in fresh session  

---

## Summary

Backend container (PHP-FPM) health check fails with continuous HTTP 500 errors on all requests. Root cause identified as **Redis connection failure**, but the underlying reason remains unclear despite all connection parameters being correct.

---

## Current Symptoms

- ✘ Backend container marked as "unhealthy" after ~150 seconds
- 🔴 All HTTP requests return 500 Internal Server Error
- ✘ Frontend cannot start (depends on backend health check)
- 📋 Error logs: `REDIS SERVER IS DOWN` repeated every 10 seconds

### Container Status
```
mythicaldash_v3_backend   unhealthy   (Up 2 minutes)
mythicaldash_v3_mysql     healthy     (Up 3 minutes)
mythicaldash_v3_redis     healthy     (Up 3 minutes)
```

---

## Root Cause Analysis

### Problem Identified
Backend PHP application cannot connect to Redis, causing firewall initialization to fail, which triggers 500 errors on all requests.

### Evidence
```
Error Log: /var/www/html/storage/logs/mythicaldash.log
| (2026-07-08 22:51:17) [ERROR]  [MythicalDash\App] REDIS SERVER IS DOWN
| (2026-07-08 22:51:17) [ERROR]  [MythicalDash\App] RATE LIMITING IS DISABLED
| (2026-07-08 22:51:17) [ERROR]  [MythicalDash\App] YOU SHOULD FIX THIS ASAP
```

Location: [backend/app/App.php:112-129](backend/app/App.php#L112-L129)

### What We Know ✓
- MySQL: **Healthy** (responds to healthcheck)
- Redis: **Healthy** (responds to healthcheck)
- Redis: **Responds to direct connection** (`redis-cli -a mythicaldash_v3_redis ping` → PONG)
- PHP Redis Extension: **Installed** (`php -m | grep redis` → redis)
- Backend Environment Variables: **Correct**
  ```
  REDIS_HOST=redis ✓
  REDIS_PASSWORD=mythicaldash_v3_redis ✓
  ```
- Docker Network: **Exists** (mythicaldash_v3_network bridge)
- Credentials Match docker-compose: **Yes** (defaults used)

### What We Don't Know ❓
- Why PHP in backend container cannot connect to Redis
- If there's a PHP Redis extension configuration issue
- If the Redis connection object is being instantiated incorrectly in the code
- If there's a timeout or firewall rule blocking the connection

---

## Approaches Already Tried

### 1. Fixed Health Check Command
**What:** Changed docker-compose health check from invalid `php cli help` to proper HTTP check  
**File:** docker-compose-dev.yml:67  
**Before:**
```yaml
healthcheck:
  test: ["CMD", "php", "cli", "help"]
  timeout: 20s
  retries: 10
```
**After:**
```yaml
healthcheck:
  test: ["CMD", "curl", "-f", "http://localhost:80/"]
  timeout: 20s
  retries: 10
  start_period: 30s
  interval: 10s
```
**Result:** ❌ Still failing (500 errors prevent health check from passing)

### 2. .env File Configuration
**What:** Created proper `.env` file with correct credentials  
**File:** .env  
**Actions:**
- Set `MARIADB_PASSWORD=mythicaldash_v3_password` (matches docker-compose default)
- Set `REDIS_PASSWORD=mythicaldash_v3_redis` (matches docker-compose default)
- Added firewall config keys upfront to prevent duplicate appending
- Cleaned duplicate entries multiple times

**Issue Found:** `.env` file was being corrupted by `App.php::updateEnvValue()` appending duplicate entries on each restart. This was a secondary issue (not the Redis cause).

**Result:** ❌ Redis still unavailable despite correct credentials in .env

### 3. Attempted Read-Only .env Mount
**What:** Made `.env` read-only to prevent corruption from `updateEnvValue()`  
**File:** docker-compose-dev.yml:74  
**Change:** `./.env:/var/www/html/storage/.env:ro`  
**Result:** ❌ Reverted - need to investigate if write access is required

### 4. Database Credential Verification
**What:** Ensured all credentials match across .env and docker-compose  
**Result:** ✓ MySQL connects successfully (logs show migrations attempted)

### 5. Environment Variable Verification
**What:** Checked what backend container actually received  
```bash
docker compose -f docker-compose-dev.yml exec backend env | grep -i redis
# Output:
# REDIS_HOST=redis
# REDIS_PASSWORD=mythicaldash_v3_redis
```
**Result:** ✓ Variables are passed correctly

### 6. Redis Connectivity Test
**What:** Direct Redis connection from host  
```bash
docker compose -f docker-compose-dev.yml exec redis redis-cli -a mythicaldash_v3_redis ping
# Output: PONG (with warning about password in CLI)
```
**Result:** ✓ Redis is listening and accepting connections

---

## Secondary Issue: .env File Corruption

**Cause:** [backend/app/App.php:100-110](backend/app/App.php#L100-L110)

The `updateEnvValue()` method is called during initialization to set firewall defaults:
```php
if (!isset($_ENV['firewall_enabled'])) {
    $_ENV['firewall_enabled'] = 'true';
    $this->updateEnvValue(ConfigInterface::FIREWALL_ENABLED, 'true', false);
}
```

**Problem:** If the `.env` file doesn't have these keys initially, `updateEnvValue()` appends them as new lines instead of updating existing ones. On container restart, it tries again, creating duplicates.

**Example of corruption:**
```
firewall_enabled=true
firewall_rate_limit=100
firewall_block_vpn=false
REDIS_HOST=redis
REDIS_PASSWORD=mythicaldash_v3_redis
DATABASE_HOST=mysql
... (entire DOCKER COMPOSE ENVIRONMENT section duplicated)
```

**Fix Applied:** Add all firewall keys to `.env` upfront so `updateEnvValue()` finds them and updates in-place rather than appending.

**Status:** ✓ Temporarily mitigated (but duplicates still accumulate with each restart)

---

## Current .env File State

Last verified clean state (before duplicates reappear):
```
# ============================================
# FIREWALL CONFIGURATION
# ============================================

firewall_enabled=true
firewall_rate_limit=100
firewall_block_vpn=false
```

**Note:** Duplicates of DOCKER COMPOSE ENVIRONMENT section appear at EOF after restart

---

## Files Modified This Session

1. **docker-compose-dev.yml**
   - Line 67: Updated health check command
   - Line 70-71: Added start_period and interval
   - Line 74: Attempted read-only mount (reverted)

2. **.env**
   - Created with correct credentials
   - Cleaned duplicate entries multiple times
   - Added firewall config keys

3. **Memory**
   - Created `mythicaldash-backend-500-issue.md` documenting the issue

---

## Next Steps for Resolution

### Required Investigation
1. **Check PHP Redis Extension Configuration**
   ```bash
   docker compose -f docker-compose-dev.yml exec backend php -i | grep -i redis -A 5
   ```
   Look for:
   - Redis version
   - Session handler (should not interfere)
   - Any disabled functions

2. **Check Redis Connection Code**
   - Find where `\Redis()` is instantiated in backend code
   - Check for connection timeout settings
   - Verify authentication is attempted correctly

3. **Network Diagnostics**
   ```bash
   docker compose -f docker-compose-dev.yml exec backend ping redis
   docker compose -f docker-compose-dev.yml exec backend telnet redis 6379
   ```

4. **Redis Logs from Container**
   ```bash
   docker compose -f docker-compose-dev.yml logs redis --tail 100
   ```
   Look for authentication failures or connection rejections

5. **Backend Startup Logs (Earlier)**
   ```bash
   docker compose -f docker-compose-dev.yml logs backend --tail 200 | head -100
   ```
   Capture the full startup sequence including any Redis initialization errors

### Potential Causes to Investigate
- Redis authentication timeout
- PHP Redis extension not properly configured
- Connection pool exhaustion
- Redis module configuration incompatibility with PHP version
- Network policy or firewall rule in Docker
- Database initialization blocking Redis connection attempt

### Potential Fixes
1. Add Redis connection retry logic with exponential backoff
2. Check if `session.save_handler` should be set to `redis` explicitly
3. Verify Redis port 6379 is accessible from backend container
4. Check if Redis requires AUTH before PING
5. Look for any Redis module loading issues

---

## Commands for Quick Reference

### View current state
```bash
docker compose -f docker-compose-dev.yml ps
docker compose -f docker-compose-dev.yml logs backend --tail 50
```

### View error details
```bash
docker compose -f docker-compose-dev.yml exec backend cat /var/www/html/storage/logs/mythicaldash.log | tail -100
```

### Check Redis from backend
```bash
docker compose -f docker-compose-dev.yml exec backend php -r "
\$redis = new \Redis();
try {
    \$redis->connect('redis', 6379);
    echo 'Connected without auth\n';
} catch (Exception \$e) {
    echo 'Failed without auth: ' . \$e->getMessage() . \"\n\";
}
try {
    \$redis->auth('mythicaldash_v3_redis');
    echo 'Auth successful\n';
} catch (Exception \$e) {
    echo 'Auth failed: ' . \$e->getMessage() . \"\n\";
}
"
```

### Check environment inside container
```bash
docker compose -f docker-compose-dev.yml exec backend env | sort
```

---

## Related Code Locations

- [backend/app/App.php:100-129](backend/app/App.php#L100-L129) — Firewall initialization with Redis dependency
- [backend/app/App.php:283-369](backend/app/App.php#L283-L369) — updateEnvValue() method causing .env corruption
- [docker-compose-dev.yml:46-77](docker-compose-dev.yml#L46-L77) — Backend service configuration
- [docker-compose-dev.yml:32-44](docker-compose-dev.yml#L32-L44) — Redis service configuration

---

## Session Summary

**Started:** Working backend image (`ghcr.io/mrfreakcmd/mythicaldash_v3-backend:dev`)  
**Ended:** Backend returns 500 on all requests, Redis connection fails  
**Duration:** Full troubleshooting session  
**Context Used:** ~150k tokens

**What Worked:**
- Fixed health check command in docker-compose
- Identified .env corruption issue
- Created proper .env with correct credentials
- Narrowed root cause to Redis connection failure

**What Didn't Work:**
- Making .env read-only (reverted)
- Various .env cleaning attempts (duplicates return after restart)
- Waiting longer for Redis to initialize

**Remaining Unknown:**
- Why Redis connection fails despite all parameters being correct
- Whether this is a recent regression or a setup issue

---

## How to Continue

When reopening this issue in a fresh session:

1. Start with the PHP Redis connection test (see "Check Redis from backend" command above)
2. Review Redis container logs for authentication or connection rejections
3. Check if the issue is specific to the development Docker image or a general configuration problem
4. Consider whether this worked in a previous version and what changed

The issue is reproducible, well-scoped, and not related to `.env` corruption (that was a secondary symptom).

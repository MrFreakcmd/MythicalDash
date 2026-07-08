# Docker Troubleshooting & Diagnostics Guide

In-depth guide for diagnosing and fixing Docker deployment issues.

## Table of Contents

- [Diagnostic Workflow](#diagnostic-workflow)
- [Service-Specific Issues](#service-specific-issues)
- [Performance Issues](#performance-issues)
- [Data & Volume Issues](#data--volume-issues)
- [Network Issues](#network-issues)
- [Security Issues](#security-issues)
- [Advanced Diagnostics](#advanced-diagnostics)

---

## Diagnostic Workflow

Follow this systematic approach when something goes wrong:

### Step 1: Assess Current State

```bash
# Check overall status
docker compose ps

# Expected output:
# NAME                  STATUS
# mythicaldash_v3_mysql         Up X seconds (healthy)
# mythicaldash_v3_redis         Up X seconds (healthy)
# mythicaldash_v3_backend       Up X seconds (healthy)
# mythicaldash_v3_frontend      Up X seconds (healthy)
```

**Status meanings:**
- `Up X seconds (healthy)` - Service working normally
- `Up X seconds (unhealthy)` - Service running but health check failed
- `Exited (code 1)` - Service crashed
- `Created` - Container exists but never started
- `Removing` - Being deleted

### Step 2: Check Recent Logs

```bash
# All services
docker compose logs --tail=50

# Specific service
docker compose logs --tail=50 backend

# Filter for errors
docker compose logs | grep -i error
```

**Key log patterns:**
- `Connection refused` - Service not listening or port issue
- `No such file or directory` - Missing volume or file
- `Permission denied` - File permissions issue
- `Out of memory` - Resource exhaustion
- `Health check failure` - Service unhealthy

### Step 3: Isolate the Problem

```bash
# Test each service individually
docker compose exec mysql mariadb-admin ping -h localhost -u root
docker compose exec redis redis-cli ping
docker compose exec backend php cli help
docker compose exec frontend curl -f http://localhost
```

### Step 4: Consult Service-Specific Section

Jump to the service that's failing (MySQL, Redis, Backend, Frontend).

---

## Service-Specific Issues

### MySQL/MariaDB Issues

#### Issue: Database won't start

**Symptoms:**
- Container exits immediately
- `docker compose ps` shows `Exited`

**Diagnosis:**
```bash
# View startup logs
docker compose logs mysql

# Check for common errors:
# - "InnoDB: Unable to lock" - existing lock file
# - "Can't create directory" - volume permission issue
# - "Got error -1 from storage engine" - corruption
```

**Solutions:**

```bash
# 1. Delete corrupted database and restart
docker compose down
docker volume rm mythicaldash_v3_mysql_data  # WARNING: Deletes database!
docker compose up -d mysql

# 2. Fix permissions (if permission denied)
docker compose down
sudo chown -R 999:999 /var/lib/docker/volumes/mythicaldash_v3_mysql_data/_data
docker compose up -d mysql

# 3. Restore from backup (if available)
docker compose down
docker compose up -d mysql
# Wait for MySQL to be healthy
sleep 30
docker compose exec -T mysql mariadb -u mythicaldash_v3 \
  -pmythicaldash_v3_password mythicaldash_v3 < backup.sql
docker compose up -d
```

#### Issue: Slow database queries

**Symptoms:**
- Application is slow
- Database CPU usage high

**Diagnosis:**
```bash
# Connect to database
docker compose exec mysql mariadb -u mythicaldash_v3 -p mythicaldash_v3

# Inside MySQL:
SHOW PROCESSLIST;  -- Running queries
SHOW SLOW_LOGS;    -- Slow query log
```

**Solutions:**

```bash
# Optimize tables
docker compose exec mysql mariadb -u mythicaldash_v3 -p mythicaldash_v3 \
  -e "OPTIMIZE TABLE table_name;"

# Add indexes (if missing)
docker compose exec mysql mariadb -u mythicaldash_v3 -p mythicaldash_v3 \
  -e "CREATE INDEX idx_name ON table_name(column_name);"

# Check table size
docker compose exec mysql mariadb -u mythicaldash_v3 -p mythicaldash_v3 \
  -e "SELECT table_name, ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size_mb \
      FROM information_schema.tables \
      WHERE table_schema = 'mythicaldash_v3' \
      ORDER BY size_mb DESC;"

# Archive old data
# Create archival strategy for old records
```

#### Issue: Connection refused from backend

**Symptoms:**
- Backend logs show "Connection refused"
- Frontend won't load

**Diagnosis:**
```bash
# Check MySQL is running and healthy
docker compose ps mysql

# Test connection from backend
docker compose exec backend mariadb -h mysql -u mythicaldash_v3 \
  -pmythicaldash_v3_password -e "SELECT 1;"
```

**Solutions:**

```bash
# 1. Restart MySQL
docker compose restart mysql

# 2. Check credentials in .env
grep MARIADB .env

# 3. Verify network connectivity
docker compose exec backend ping mysql

# 4. Full restart
docker compose down
docker compose up -d
```

---

### Redis Issues

#### Issue: Redis won't start

**Symptoms:**
- Redis container exits immediately
- Health check fails

**Diagnosis:**
```bash
docker compose logs redis

# Common errors:
# - "Can't chdir to ..." - permission issue
# - "Error in accept..." - port binding issue
```

**Solutions:**

```bash
# 1. Check permissions
docker compose down
sudo chown -R 999:999 /var/lib/docker/volumes/redis_data/_data

# 2. Check if port 6379 is available
docker compose up -d redis

# 3. Full reset (clears cache)
docker compose down
docker volume rm redis_data
docker compose up -d redis
```

#### Issue: Backend can't connect to Redis

**Symptoms:**
- Cache not working
- Backend logs show "Redis connection failed"

**Diagnosis:**
```bash
# Check Redis is running
docker compose ps redis

# Test Redis connection
docker compose exec redis redis-cli ping

# Test with password
docker compose exec redis redis-cli -a "$REDIS_PASSWORD" ping

# Check from backend
docker compose exec backend redis-cli -h redis ping
```

**Solutions:**

```bash
# 1. Restart Redis
docker compose restart redis

# 2. Check password in .env
grep REDIS_PASSWORD .env

# 3. Verify network
docker compose exec backend ping redis

# 4. Clear Redis data if corrupted
docker compose exec redis redis-cli FLUSHALL
```

#### Issue: Redis memory issues

**Symptoms:**
- Redis consuming too much memory
- Eviction messages in logs

**Diagnosis:**
```bash
# Check memory usage
docker compose exec redis redis-cli INFO memory

# View memory breakdown
docker compose exec redis redis-cli INFO stats
```

**Solutions:**

```bash
# 1. Increase allocated memory (edit docker-compose.yml)
redis:
  deploy:
    resources:
      limits:
        memory: 1G

# 2. Clear old cache data
docker compose exec redis redis-cli FLUSHDB

# 3. Configure maxmemory policy
docker compose exec redis redis-cli CONFIG SET maxmemory-policy allkeys-lru

# 4. Monitor memory usage
watch "docker compose exec redis redis-cli INFO memory"
```

---

### Backend (PHP/Laravel) Issues

#### Issue: Backend container won't stay healthy

**Symptoms:**
- Container shows "unhealthy"
- `docker compose ps` shows status as unhealthy

**Diagnosis:**
```bash
# View health check details
docker inspect mythicaldash_v3_backend --format='{{json .State.Health}}' | jq

# Test health manually
docker compose exec backend php cli help

# Check logs
docker compose logs backend | tail -50
```

**Solutions:**

```bash
# 1. Wait for startup (services take 60-120 seconds)
sleep 120
docker compose ps

# 2. Check PHP configuration
docker compose exec backend php -i | grep memory_limit

# 3. Restart backend
docker compose restart backend

# 4. Full restart if persistent
docker compose down
docker compose up -d
```

#### Issue: Backend crashes with "Out of memory"

**Symptoms:**
- Backend container keeps restarting
- Logs show "Allowed memory size exhausted"

**Solutions:**

```bash
# 1. Increase PHP memory limit (edit backend/Dockerfile)
# Find: memory_limit = -1
# Verify it's already unlimited, or change to larger value

# 2. Increase Docker memory allocation
# Edit docker-compose.yml:
backend:
  deploy:
    resources:
      limits:
        memory: 2G

# 3. Check for memory leaks in application code
docker stats

# 4. Optimize application
# - Cache frequently accessed data
# - Limit query results
# - Stream large files instead of loading into memory
```

#### Issue: Application features not working

**Symptoms:**
- Login fails
- File uploads don't work
- API returns errors

**Solutions:**

```bash
# 1. Check backend logs for errors
docker compose logs backend

# 2. Verify database is initialized
docker compose exec mysql mariadb -u mythicaldash_v3 \
  -pmythicaldash_v3_password mythicaldash_v3 \
  -e "SHOW TABLES;"

# 3. Run migrations if needed
docker compose exec backend php artisan migrate

# 4. Clear cache
docker compose exec redis redis-cli FLUSHALL

# 5. Check permissions on attachments directory
docker compose exec backend ls -la /var/www/html/public/attachments

# 6. Fix permissions if needed
docker compose exec backend chown -R www-data:www-data /var/www/html/public/attachments
```

---

### Frontend (Nginx) Issues

#### Issue: Frontend won't load

**Symptoms:**
- `curl http://localhost:4832` returns connection refused
- Browser shows "Can't reach server"

**Diagnosis:**
```bash
# Check container status
docker compose ps frontend

# Test from inside container
docker compose exec frontend curl http://localhost

# Check logs
docker compose logs frontend
```

**Solutions:**

```bash
# 1. Restart frontend
docker compose restart frontend

# 2. Check port binding
docker compose ps frontend

# 3. Verify port availability
lsof -i :4832              # macOS/Linux
netstat -ano | findstr :4832  # Windows PowerShell

# 4. Change port if needed (edit docker-compose.yml)
frontend:
  ports:
    - "8080:80"

docker compose down
docker compose up -d
```

#### Issue: Frontend loads but API calls fail

**Symptoms:**
- Frontend loads but shows errors
- Console shows CORS errors
- API calls return 503 or connection refused

**Diagnosis:**
```bash
# Check backend is running
docker compose ps backend

# Test backend from frontend container
docker compose exec frontend curl http://backend:80

# Check Nginx configuration
docker compose exec frontend cat /etc/nginx/conf.d/default.conf
```

**Solutions:**

```bash
# 1. Restart backend
docker compose restart backend

# 2. Check backend is healthy
docker compose ps backend

# 3. Check API endpoint
curl http://localhost:4832/api/health

# 4. Review Nginx error logs
docker compose exec frontend cat /var/log/nginx/error.log

# 5. Rebuild frontend if configuration changed
docker compose build frontend
docker compose up -d frontend
```

#### Issue: Static files (CSS, JS) not loading

**Symptoms:**
- Page loads but styling is broken
- Console shows 404 errors for CSS/JS files

**Diagnosis:**
```bash
# Check if files exist in container
docker compose exec frontend ls -la /usr/share/nginx/html

# Check Nginx access log
docker compose exec frontend tail /var/log/nginx/access.log
```

**Solutions:**

```bash
# 1. Rebuild frontend
docker compose build frontend

# 2. Verify build output
docker compose exec frontend ls -la /usr/share/nginx/html/dist

# 3. Restart frontend
docker compose restart frontend

# 4. Check file permissions
docker compose exec frontend chmod -R 755 /usr/share/nginx/html
```

---

## Performance Issues

### High CPU Usage

**Diagnosis:**
```bash
# Identify resource usage
docker stats

# Check which process is consuming CPU
docker top mythicaldash_v3_backend
```

**Common causes & solutions:**

1. **Slow database queries**
   ```bash
   docker compose exec mysql mariadb -u mythicaldash_v3 -p mythicaldash_v3 \
     -e "SHOW PROCESSLIST;"
   ```
   - Add indexes
   - Optimize queries
   - Archive old data

2. **Memory pressure (causing garbage collection overhead)**
   ```bash
   docker stats
   # If memory is near limit, increase allocation
   ```

3. **PHP processing**
   ```bash
   # Increase timeouts if needed
   # Edit backend/Dockerfile - increase max_execution_time
   ```

### High Memory Usage

**Diagnosis:**
```bash
docker stats

# Check process memory breakdown
docker compose exec backend ps aux --sort=-%mem
```

**Solutions:**

1. **Identify memory leaks**
   ```bash
   # Monitor memory over time
   watch docker stats
   ```

2. **Reduce PHP memory limit if safe**
   ```bash
   docker compose exec backend php -i | grep memory_limit
   ```

3. **Increase system memory allocated to Docker**
   - Docker Desktop: Preferences > Resources > Memory
   - Docker on Linux: Already using system memory

4. **Clear old logs and cache**
   ```bash
   docker compose exec redis redis-cli FLUSHALL
   docker compose exec backend rm -rf /var/www/html/storage/logs/*
   ```

### Slow Response Times

**Diagnosis:**
```bash
# Measure response time
time curl http://localhost:4832

# Check database performance
docker compose exec mysql mariadb -u mythicaldash_v3 -p mythicaldash_v3 \
  -e "SHOW PROCESSLIST;"

# Check backend processes
docker top mythicaldash_v3_backend
```

**Solutions:**

1. **Database optimization**
   - Add indexes
   - Optimize queries
   - Archive old data

2. **Cache optimization**
   - Increase Redis memory
   - Implement caching for frequent queries

3. **Network optimization**
   - Minimize data transfer
   - Use CDN for static assets
   - Compress responses

---

## Data & Volume Issues

### Disk Space Issues

**Diagnosis:**
```bash
# Check overall usage
docker system df

# Check specific volume
docker volume inspect mythicaldash_v3_attachments --format='{{.Mountpoint}}'
du -sh /var/lib/docker/volumes/mythicaldash_v3_*/_data
```

**Solutions:**

```bash
# 1. Clean Docker system
docker system prune -a

# 2. Archive old database records
# Connect to MySQL and archive/delete old data

# 3. Remove old logs
docker compose exec backend rm -rf /var/www/html/storage/logs/*
docker compose exec backend rm -rf /var/www/html/storage/cache/*

# 4. Clean Redis
docker compose exec redis redis-cli FLUSHALL

# 5. Expand disk if needed (infrastructure change)
```

### Volume Permission Issues

**Symptoms:**
- "Permission denied" errors
- Files can't be written to attachments directory

**Diagnosis:**
```bash
# Check volume ownership
ls -l /var/lib/docker/volumes/mythicaldash_v3_attachments/_data

# Check permissions in container
docker compose exec backend ls -la /var/www/html/public/attachments
```

**Solutions:**

```bash
# 1. Fix permissions on host
docker compose down
sudo chown -R 999:999 /var/lib/docker/volumes/mythicaldash_v3_attachments/_data
sudo chmod -R 755 /var/lib/docker/volumes/mythicaldash_v3_attachments/_data
docker compose up -d

# 2. Fix permissions in container
docker compose exec backend chown -R www-data:www-data /var/www/html/public/attachments
docker compose exec backend chmod -R 755 /var/www/html/public/attachments
```

### Volume Data Corruption

**Symptoms:**
- Database won't start
- Attachment files are corrupted

**Recovery:**

```bash
# 1. Restore from backup (best option)
docker compose down
docker compose up -d mysql
sleep 30
docker compose exec -T mysql mariadb -u mythicaldash_v3 \
  -pmythicaldash_v3_password mythicaldash_v3 < backup.sql
docker compose up -d

# 2. Rebuild volume if data is lost
docker compose down
docker volume rm mythicaldash_v3_attachments
docker volume create mythicaldash_v3_attachments
docker compose up -d
```

---

## Network Issues

### Services Can't Communicate

**Symptoms:**
- Backend can't reach MySQL
- Frontend can't reach backend

**Diagnosis:**
```bash
# Check network
docker network inspect mythicaldash_v3_network

# Test connectivity
docker compose exec backend ping mysql
docker compose exec backend ping redis
docker compose exec frontend ping backend

# Check DNS resolution
docker compose exec backend nslookup mysql
docker compose exec backend getent hosts mysql
```

**Solutions:**

```bash
# 1. Verify services are on same network
docker network inspect mythicaldash_v3_network

# 2. Recreate network if corrupted
docker compose down
docker network rm mythicaldash_v3_network
docker compose up -d

# 3. Check service names in code match docker-compose.yml
# backend should use: mysql, redis (service names)
# not: localhost, 127.0.0.1
```

### External Network Access Issues

**Symptoms:**
- Can't access frontend from another machine
- Port forwarding doesn't work

**Diagnosis:**
```bash
# Check port binding
docker compose ps

# Test locally
curl http://localhost:4832

# Test from another machine
curl http://YOUR_IP:4832

# Check firewall
sudo ufw status  # Linux
netsh advfirewall show allprofiles  # Windows
```

**Solutions:**

```bash
# 1. Open firewall port (Linux)
sudo ufw allow 4832

# 2. Open firewall port (Windows)
# Windows Defender Firewall > Advanced Settings > Inbound Rules > New Rule

# 3. Configure port forwarding (if behind NAT)
# Router settings: Forward port 4832 to your machine's IP

# 4. Use reverse proxy for external access (recommended)
# See docs/Docker-Deployment.md for Nginx setup
```

---

## Security Issues

### Exposed Credentials

**Check:**
```bash
# Verify .env is not committed
git status
grep ".env" .gitignore

# Ensure database/Redis not exposed
docker compose ps | grep ports

# Should only show frontend port (4832)
```

**Fix:**

```bash
# 1. If accidentally committed
git rm --cached .env
git commit -m "Remove .env from tracking"

# 2. Regenerate passwords
# Edit .env with new credentials
docker compose down
docker compose up -d

# 3. Check for exposed services
# Remove any port bindings for mysql/redis in docker-compose.yml
```

### Unsecured External Access

**Current security:**
- Database: Internal only ✓
- Redis: Internal only ✓
- Frontend: Exposed on port 4832
- Backend: Internal only ✓

**To expose securely:**

```bash
# Use reverse proxy with SSL/TLS
# See docs/Docker-Deployment.md for setup

# Never expose database or Redis directly
# Always use reverse proxy with authentication
```

---

## Advanced Diagnostics

### Container Inspect

```bash
# Full container details
docker inspect mythicaldash_v3_backend

# Specific information
docker inspect mythicaldash_v3_backend --format='{{.Config.Env}}'
docker inspect mythicaldash_v3_backend --format='{{.NetworkSettings.Networks}}'
docker inspect mythicaldash_v3_backend --format='{{.Mounts}}'
```

### Log Analysis

```bash
# Export full logs
docker compose logs > full_logs.txt

# Analyze logs
grep -i error full_logs.txt
grep -i warning full_logs.txt
grep connection full_logs.txt

# Follow specific service logs
docker compose logs -f --tail=100 backend 2>&1 | tee logs.txt
```

### Performance Profiling

```bash
# Continuous resource monitoring
watch -n 1 'docker stats --no-stream'

# Export for analysis
docker stats --no-stream > stats.log
```

### Network Debugging

```bash
# Trace network calls
docker compose exec backend traceroute mysql
docker compose exec backend traceroute -n redis

# Check open ports
docker compose exec backend netstat -tuln

# DNS debugging
docker compose exec backend nslookup mysql
docker compose exec backend dig mysql
```

### Application Debugging

```bash
# Access application shell
docker compose exec backend bash

# Inside container:
# - Check environment: env
# - View logs: tail -f /var/www/html/storage/logs/*
# - Run commands: php, composer, etc.

# Exit shell
exit
```

---

## Emergency Procedures

### Complete System Reset

```bash
# WARNING: This deletes all data!
docker compose down -v

# Remove all volumes
docker volume prune -a

# Remove all images
docker image prune -a

# Start fresh
docker compose pull
docker compose up -d
```

### Data Recovery

```bash
# If backup available:
docker compose down
docker compose up -d mysql

# Wait for MySQL to be healthy
sleep 30

# Restore database
docker compose exec -T mysql mariadb -u mythicaldash_v3 \
  -pmythicaldash_v3_password mythicaldash_v3 < backup.sql

# Restore volumes
docker run --rm -v mythicaldash_v3_attachments:/dest \
  -v /path/to/backup:/source alpine \
  tar xzf /source/attachments_backup.tar.gz -C /dest

# Restart all services
docker compose up -d
```

### Rollback Deployment

```bash
# 1. Stop current deployment
docker compose down

# 2. Restore database
docker compose up -d mysql
sleep 30
docker compose exec -T mysql mariadb -u mythicaldash_v3 \
  -pmythicaldash_v3_password mythicaldash_v3 < backup_previous.sql

# 3. Use previous image versions
# Edit docker-compose.yml - change image tags to previous version

# 4. Start services
docker compose up -d

# 5. Verify
docker compose ps
curl http://localhost:4832
```

---

## Getting Help

### Information to Collect

When reporting issues, include:

```bash
# System information
docker --version
docker compose version
uname -a

# Current state
docker compose ps
docker compose logs --tail=100

# Resource usage
docker stats --no-stream

# Specific service logs
docker compose logs SERVICE_NAME --tail=50

# Container inspection
docker inspect mythicaldash_v3_CONTAINER --format='{{json .State}}'
```

### Resources

- **Docker Documentation**: https://docs.docker.com/
- **MythicalDash GitHub**: https://github.com/mrfreakcmd/MythicalDash
- **Docker Community**: https://forums.docker.com/
- **Stack Overflow**: Tag `docker` and `docker-compose`

---

**Last Updated:** 2026-07-09

**Remember:** Always backup before making changes!

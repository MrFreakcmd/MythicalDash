# Docker Deployment Quick Reference

Fast reference for common Docker and Docker Compose commands for MythicalDash.

## Essential Commands

### Starting & Stopping Services

```bash
# Start services (development)
docker compose -f docker-compose-dev.yml up -d

# Start services (production)
docker compose up -d

# Stop all services gracefully
docker compose down

# Stop and remove volumes (careful - deletes data!)
docker compose down -v

# Restart all services
docker compose restart

# Restart specific service
docker compose restart backend
docker compose restart frontend
docker compose restart mysql
docker compose restart redis

# Pause services (without stopping)
docker compose pause

# Unpause services
docker compose unpause
```

### Viewing Status & Logs

```bash
# List all containers and status
docker compose ps

# List all containers (including stopped)
docker compose ps -a

# Follow logs from all services
docker compose logs -f

# Follow logs from specific service
docker compose logs -f backend
docker compose logs -f frontend
docker compose logs -f mysql
docker compose logs -f redis

# View last 50 lines
docker compose logs --tail=50

# View logs with timestamps
docker compose logs --timestamps

# Export logs to file
docker compose logs > logs.txt

# Filter logs for errors
docker compose logs | grep -i error
```

### Executing Commands in Containers

```bash
# Run command in backend container
docker compose exec backend COMMAND

# Examples:
docker compose exec backend php cli help
docker compose exec backend php artisan migrate
docker compose exec backend php artisan tinker

# Run command in database container
docker compose exec mysql mariadb -u mythicaldash_v3 -p mythicaldash_v3

# Run command in Redis container
docker compose exec redis redis-cli ping
docker compose exec redis redis-cli -a PASSWORD COMMAND

# Run command in frontend container
docker compose exec frontend npm list
docker compose exec frontend curl http://localhost

# Run as specific user
docker compose exec -u www-data backend php -v
```

### Container Management

```bash
# View container details
docker ps
docker inspect CONTAINER_ID
docker inspect mythicaldash_v3_backend

# View container resource usage
docker stats

# View container processes
docker top mythicaldash_v3_backend

# Copy file from container
docker cp mythicaldash_v3_backend:/var/www/html/file.txt ./file.txt

# Copy file to container
docker cp ./file.txt mythicaldash_v3_backend:/var/www/html/file.txt

# Remove container
docker rm CONTAINER_ID

# View container environment
docker inspect mythicaldash_v3_backend --format='{{json .Config.Env}}' | jq

# View container IP
docker inspect mythicaldash_v3_backend --format='{{.NetworkSettings.IPAddress}}'
```

### Image Management

```bash
# List all images
docker images

# List MythicalDash images
docker images | grep mythicaldash

# Pull latest images
docker compose pull

# Build images locally
docker compose build

# Build without cache
docker compose build --no-cache

# Push images to registry
docker push ghcr.io/mrfreakcmd/mythicaldash_v3-backend:latest

# Remove unused images
docker image prune

# Remove all dangling images
docker image prune -a

# View image details
docker inspect ghcr.io/mrfreakcmd/mythicaldash_v3-backend:latest
```

### Volume Management

```bash
# List all volumes
docker volume ls

# Inspect volume
docker volume inspect mythicaldash_v3_attachments

# Check volume size
docker volume inspect mythicaldash_v3_attachments --format='{{.Mountpoint}}'

# Remove unused volumes
docker volume prune

# Backup volume
docker run --rm -v mythicaldash_v3_attachments:/source \
  -v $(pwd)/backups:/dest alpine \
  tar czf /dest/attachments_backup.tar.gz -C /source .

# Restore volume
docker run --rm -v mythicaldash_v3_attachments:/dest \
  -v $(pwd)/backups:/source alpine \
  tar xzf /source/attachments_backup.tar.gz -C /dest
```

### Network Management

```bash
# List networks
docker network ls

# Inspect network
docker network inspect mythicaldash_v3_network

# View connected containers
docker network inspect mythicaldash_v3_network --format='{{json .Containers}}'

# Test connectivity between containers
docker compose exec backend ping mysql
docker compose exec backend ping redis

# DNS resolution test
docker compose exec backend nslookup mysql
docker compose exec backend nslookup redis
```

### System Cleanup

```bash
# View disk usage
docker system df

# Remove unused containers
docker container prune

# Remove unused images
docker image prune

# Remove unused volumes
docker volume prune

# Remove unused networks
docker network prune

# Complete cleanup (be careful!)
docker system prune

# Complete cleanup including volumes
docker system prune -a --volumes
```

## Database Commands

### MySQL/MariaDB

```bash
# Connect to database
docker compose exec mysql mariadb -u mythicaldash_v3 -p mythicaldash_v3

# Run SQL command
docker compose exec mysql mariadb -u mythicaldash_v3 -p mythicaldash_v3 \
  -e "SHOW DATABASES;"

# Dump database
docker compose exec -T mysql mariadb-dump -u mythicaldash_v3 \
  -pmythicaldash_v3_password mythicaldash_v3 > backup.sql

# Restore database
docker compose exec -T mysql mariadb -u mythicaldash_v3 \
  -pmythicaldash_v3_password mythicaldash_v3 < backup.sql

# Check database health
docker compose exec mysql mariadb-admin ping -h localhost -u root

# View running processes
docker compose exec mysql mariadb -u root -p -e "SHOW PROCESSLIST;"

# Optimize tables
docker compose exec mysql mariadb -u mythicaldash_v3 -p mythicaldash_v3 \
  -e "OPTIMIZE TABLE table_name;"

# Check table integrity
docker compose exec mysql mariadb -u mythicaldash_v3 -p mythicaldash_v3 \
  -e "CHECK TABLE table_name;"

# View table size
docker compose exec mysql mariadb -u mythicaldash_v3 -p mythicaldash_v3 \
  -e "SELECT table_name, ROUND(((data_length + index_length) / 1024 / 1024), 2) \
      FROM information_schema.tables WHERE table_schema = 'mythicaldash_v3';"
```

### Redis

```bash
# Connect to Redis CLI
docker compose exec redis redis-cli

# Connect with password
docker compose exec redis redis-cli -a PASSWORD

# Ping Redis
docker compose exec redis redis-cli ping

# Get key
docker compose exec redis redis-cli GET key_name

# Set key
docker compose exec redis redis-cli SET key_name value

# Delete key
docker compose exec redis redis-cli DEL key_name

# Flush all data (be careful!)
docker compose exec redis redis-cli FLUSHALL

# View memory usage
docker compose exec redis redis-cli INFO memory

# View all keys
docker compose exec redis redis-cli KEYS "*"

# View database size
docker compose exec redis redis-cli DBSIZE

# View persistence status
docker compose exec redis redis-cli INFO persistence

# Trigger AOF rewrite
docker compose exec redis redis-cli BGREWRITEAOF

# Backup Redis database
docker run --rm -v redis_data:/data -v $(pwd)/backups:/dest alpine \
  tar czf /dest/redis_backup.tar.gz -C /data .
```

## Debugging

### Check Health Status

```bash
# All services
docker compose ps

# Specific service
docker compose ps backend

# Detailed health info
docker inspect mythicaldash_v3_backend --format='{{json .State.Health}}' | jq

# Health status only
docker inspect mythicaldash_v3_backend --format='{{.State.Health.Status}}'

# Health failing reason
docker inspect mythicaldash_v3_backend --format='{{.State.Health.Log}}' | jq
```

### Common Debug Scenarios

```bash
# Service won't start
docker compose logs SERVICE_NAME
docker compose restart SERVICE_NAME

# Connection refused
docker compose exec backend ping mysql
docker compose exec backend telnet mysql 3306

# Health check failing
docker compose logs SERVICE_NAME
docker compose exec SERVICE_NAME HEALTH_CHECK_COMMAND

# Memory issues
docker stats
docker compose exec CONTAINER_NAME free -h

# Disk space
docker system df
df -h

# Network issues
docker network inspect mythicaldash_v3_network
docker compose exec backend nslookup mysql
```

## Performance Monitoring

```bash
# Real-time resource usage
docker stats

# Continuous monitoring
watch docker stats

# Memory usage by container
docker stats --no-stream | grep -E "CONTAINER|mythicaldash"

# CPU usage
docker stats --format "table {{.Container}}\t{{.CPUPerc}}\t{{.MemUsage}}"

# Check disk I/O
docker stats --format="table {{.Container}}\t{{.MemUsage}}\t{{.NetIO}}\t{{.BlockIO}}"
```

## Useful Aliases

Add to your shell profile (`.bashrc`, `.zshrc`, `$profile` for PowerShell):

```bash
# Bash/Zsh
alias dc='docker compose'
alias dcl='docker compose logs -f'
alias dce='docker compose exec'
alias dcp='docker compose ps'
alias dcr='docker compose restart'
alias dcdown='docker compose down'
alias dcup='docker compose up -d'

# PowerShell
function dc { docker compose $args }
function dcl { docker compose logs -f $args }
function dce { docker compose exec $args }
function dcp { docker compose ps $args }
function dcr { docker compose restart $args }
function dcdown { docker compose down $args }
function dcup { docker compose up -d $args }
```

## Quick Deployment

### Development Quick Start
```bash
cd /path/to/MythicalDash
cp .env.example .env
# Edit .env
docker compose -f docker-compose-dev.yml up -d
docker compose logs -f
```

### Production Quick Start
```bash
cd /opt/mythicaldash
git clone https://github.com/mrfreakcmd/MythicalDash.git .
cp .env.example .env
# Edit .env with production values
docker compose up -d
docker compose ps
```

### Update Services
```bash
# Pull latest images
docker compose pull

# Restart with new images
docker compose down
docker compose up -d

# Verify
docker compose ps
docker compose logs
```

### Backup & Restore

```bash
# Full backup
mkdir -p backups
docker run --rm -v mythicaldash_v3_attachments:/source \
  -v $(pwd)/backups:/dest alpine \
  tar czf /dest/attachments_$(date +%Y%m%d).tar.gz -C /source .

docker run --rm -v mythicaldash_v3_snapshots:/source \
  -v $(pwd)/backups:/dest alpine \
  tar czf /dest/snapshots_$(date +%Y%m%d).tar.gz -C /source .

docker compose exec -T mysql mariadb-dump \
  -u mythicaldash_v3 -pmythicaldash_v3_password \
  mythicaldash_v3 > backups/db_$(date +%Y%m%d).sql

# Restore (if needed)
docker compose exec -T mysql mariadb \
  -u mythicaldash_v3 -pmythicaldash_v3_password \
  mythicaldash_v3 < backups/db_YYYYMMDD.sql
```

---

## Environment Variable Reference

```env
# Copy these into your .env file and update values

# Database
MARIADB_ROOT_PASSWORD=secure_password
MARIADB_DATABASE=mythicaldash_v3
MARIADB_USER=mythicaldash_v3
MARIADB_PASSWORD=secure_password

# Redis
REDIS_PASSWORD=secure_password

# Backend
DATABASE_ENCRYPTION=xchacha20
DATABASE_ENCRYPTION_KEY=base64_encoded_32_char_key

# Application
APP_ENV=production
APP_DEBUG=false
APP_URL=http://localhost:4832
```

---

**Remember:** Always backup before making changes!

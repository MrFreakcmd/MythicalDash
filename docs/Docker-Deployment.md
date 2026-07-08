# Docker Deployment Guide

Complete guide for deploying MythicalDash using Docker and Docker Compose.

## Table of Contents

- [Prerequisites](#prerequisites)
- [Quick Start](#quick-start)
- [Development Setup](#development-setup)
- [Production Deployment](#production-deployment)
- [Configuration](#configuration)
- [Environment Variables](#environment-variables)
- [Volumes & Persistence](#volumes--persistence)
- [Networking](#networking)
- [Health Checks](#health-checks)
- [Docker Images](#docker-images)
- [Troubleshooting](#troubleshooting)
- [Advanced Configuration](#advanced-configuration)
- [Maintenance](#maintenance)

## Prerequisites

### Required Software

- **Docker**: 20.10 or higher ([Install Docker](https://docs.docker.com/get-docker/))
- **Docker Compose**: 2.0 or higher (included with Docker Desktop)
- **Git**: For cloning the repository
- **At least 4GB RAM** allocated to Docker

### System Requirements

- **CPU**: 2+ cores recommended
- **Storage**: Minimum 10GB free space (for databases, files, and backups)
- **Memory**: 4GB minimum, 8GB+ recommended for production
- **Network**: Stable internet connection for initial image pull

### Verify Installation

```bash
docker --version
docker compose version
git --version
```

## Quick Start

### 1. Clone Repository

```bash
git clone https://github.com/mrfreakcmd/MythicalDash.git
cd MythicalDash
```

### 2. Set Up Environment

Copy the environment file:

```bash
cp .env.example .env
```

Edit `.env` with your configuration:

```env
MARIADB_ROOT_PASSWORD=your_secure_root_password
MARIADB_DATABASE=mythicaldash_v3
MARIADB_USER=mythicaldash_v3
MARIADB_PASSWORD=your_secure_db_password
REDIS_PASSWORD=your_secure_redis_password
DATABASE_ENCRYPTION_KEY=your_encryption_key
```

### 3. Start Services

**Development:**
```bash
docker compose -f docker-compose-dev.yml up -d
```

**Production:**
```bash
docker compose -f docker-compose.yml up -d
```

### 4. Verify Deployment

```bash
docker compose ps
```

All services should show `healthy` status after 30-60 seconds.

### 5. Access Application

- **Frontend**: http://localhost:4832
- **Backend API**: http://localhost:4832/api

## Development Setup

### Using Development Compose File

The `docker-compose-dev.yml` includes dev-specific images with additional tooling:

```bash
docker compose -f docker-compose-dev.yml up -d
```

### Services in Development

| Service | Image | Port | Purpose |
|---------|-------|------|---------|
| MySQL | mariadb:11.4 | 3306 | Development database |
| Redis | redis:7-alpine | 6379 | Cache & sessions |
| Backend | mythicaldash_v3-backend:dev | 9000 | PHP FPM |
| Frontend | mythicaldash_v3-frontend:dev | 80 | Nginx & Vue.js |

### View Logs

```bash
# All services
docker compose -f docker-compose-dev.yml logs -f

# Specific service
docker compose -f docker-compose-dev.yml logs -f backend
docker compose -f docker-compose-dev.yml logs -f frontend
docker compose -f docker-compose-dev.yml logs -f mysql
docker compose -f docker-compose-dev.yml logs -f redis
```

### Execute Commands in Containers

```bash
# Backend - Run PHP commands
docker compose exec backend php cli help
docker compose exec backend php artisan migrate

# Database - Run MySQL commands
docker compose exec mysql mariadb -u mythicaldash_v3 -p mythicaldash_v3

# Frontend - Run npm commands
docker compose exec frontend npm list
```

### Rebuild Images

```bash
docker compose -f docker-compose-dev.yml build --no-cache
docker compose -f docker-compose-dev.yml up -d
```

## Production Deployment

### Pre-Deployment Checklist

- [ ] Review all environment variables
- [ ] Set strong passwords for database and Redis
- [ ] Configure proper encryption keys
- [ ] Set up volume backups strategy
- [ ] Configure monitoring/logging
- [ ] Test on staging environment first

### Deployment Steps

#### 1. Prepare Server

```bash
# Update system
sudo apt-get update && sudo apt-get upgrade -y

# Install Docker
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh

# Start Docker service
sudo systemctl enable docker
sudo systemctl start docker
```

#### 2. Clone & Configure

```bash
sudo mkdir -p /opt/mythicaldash
cd /opt/mythicaldash
sudo git clone https://github.com/mrfreakcmd/MythicalDash.git .

# Set proper permissions
sudo chown -R $USER:$USER /opt/mythicaldash
```

#### 3. Set Up Environment

```bash
cp .env.example .env
# Edit .env with production values
nano .env
```

#### 4. Create Backup Strategy

```bash
# Create backup directory
mkdir -p /opt/mythicaldash/backups

# Create backup script
cat > /opt/mythicaldash/backup.sh << 'EOF'
#!/bin/bash
BACKUP_DIR="/opt/mythicaldash/backups"
DATE=$(date +%Y%m%d_%H%M%S)

# Backup volumes
docker run --rm -v mythicaldash_v3_attachments:/data \
  -v $BACKUP_DIR:/backup alpine \
  tar czf /backup/attachments_$DATE.tar.gz -C /data .

docker run --rm -v mythicaldash_v3_snapshots:/data \
  -v $BACKUP_DIR:/backup alpine \
  tar czf /backup/snapshots_$DATE.tar.gz -C /data .

# Backup database
docker compose exec -T mysql mariadb-dump -u mythicaldash_v3 \
  -pmythicaldash_v3_password mythicaldash_v3 > $BACKUP_DIR/db_$DATE.sql

echo "Backup completed: $DATE"
EOF

chmod +x /opt/mythicaldash/backup.sh
```

#### 5. Start Services

```bash
docker compose -f docker-compose.yml up -d
```

#### 6. Verify Health

```bash
docker compose ps
docker compose logs

# Wait for all services to be healthy (60-120 seconds)
watch docker compose ps
```

### Production Monitoring

```bash
# Check system resources
docker stats

# Monitor logs
docker compose logs -f --tail=100

# Check individual service health
docker inspect mythicaldash_v3_mysql --format='{{.State.Health}}'
docker inspect mythicaldash_v3_redis --format='{{.State.Health}}'
docker inspect mythicaldash_v3_backend --format='{{.State.Health}}'
docker inspect mythicaldash_v3_frontend --format='{{.State.Health}}'
```

## Configuration

### Docker Compose Files

#### Production: `docker-compose.yml`

- Uses `latest` tags for images
- Enables auto-pull of latest images
- Optimized for stability
- All services use health checks
- Persistent volumes for data

#### Development: `docker-compose-dev.yml`

- Uses `dev` tags for images
- Includes development tools
- Better logging verbosity
- Same infrastructure as production

### Compose File Structure

```yaml
services:           # Container definitions
  mysql:            # Database service
  redis:            # Cache service
  backend:          # Laravel/PHP backend
  frontend:         # Vue.js frontend

volumes:            # Named volumes for persistence
  mariadb_data:
  redis_data:
  mythicaldash_v3_attachments:
  mythicaldash_v3_snapshots:

networks:           # Docker networks
  mythicaldash_v3_network:
```

## Environment Variables

### Database Configuration

```env
MARIADB_ROOT_PASSWORD=<root_password>
MARIADB_DATABASE=mythicaldash_v3
MARIADB_USER=mythicaldash_v3
MARIADB_PASSWORD=<secure_password>
MARIADB_AUTO_UPGRADE=1
```

### Backend Configuration

```env
DATABASE_HOST=mysql
DATABASE_PORT=3306
DATABASE_DATABASE=mythicaldash_v3
DATABASE_USER=mythicaldash_v3
DATABASE_PASSWORD=<secure_password>
DATABASE_ENCRYPTION=xchacha20
DATABASE_ENCRYPTION_KEY=<32_char_key>

REDIS_HOST=redis
REDIS_PASSWORD=<secure_password>
```

### Recommended Secure Values

**Generate secure passwords:**

```bash
# Generate random 32-character password
openssl rand -base64 32

# Generate encryption key (base64 encoded)
openssl rand -base64 32
```

**Database encryption key requirements:**
- 32 characters minimum
- Base64 encoded
- Keep in secure location
- Do not share or expose

### Optional Environment Variables

```env
# Application settings
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Mail configuration
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=465
MAIL_USERNAME=<username>
MAIL_PASSWORD=<password>

# Logging
LOG_CHANNEL=daily
LOG_LEVEL=error
```

## Volumes & Persistence

### Named Volumes

MythicalDash uses Docker named volumes for data persistence:

| Volume | Purpose | Location in Container |
|--------|---------|----------------------|
| `mariadb_data` | Database files | `/var/lib/mysql` |
| `redis_data` | Cache data | `/data` |
| `mythicaldash_v3_attachments` | User files | `/var/www/html/public/attachments` |
| `mythicaldash_v3_snapshots` | Backups | `/var/www/html/storage/backups` |

### View Volume Information

```bash
# List all volumes
docker volume ls

# Inspect specific volume
docker volume inspect mythicaldash_v3_attachments

# Check volume usage
docker system df

# Locate volume on disk (Linux)
docker volume inspect mythicaldash_v3_attachments --format='{{.Mountpoint}}'
```

### Backup Volumes

```bash
# Backup attachments
docker run --rm -v mythicaldash_v3_attachments:/source \
  -v $(pwd)/backups:/dest alpine \
  tar czf /dest/attachments_backup.tar.gz -C /source .

# Restore attachments
docker run --rm -v mythicaldash_v3_attachments:/dest \
  -v $(pwd)/backups:/source alpine \
  tar xzf /source/attachments_backup.tar.gz -C /dest
```

### Database Backup & Restore

```bash
# Backup database
docker compose exec -T mysql mariadb-dump \
  -u mythicaldash_v3 -pmythicaldash_v3_password \
  mythicaldash_v3 > backup.sql

# Restore database
docker compose exec -T mysql mariadb \
  -u mythicaldash_v3 -pmythicaldash_v3_password \
  mythicaldash_v3 < backup.sql
```

## Networking

### Network Architecture

```
┌─────────────────────────────────────────────┐
│   mythicaldash_v3_network (bridge)          │
│                                              │
│  ┌─────────┐  ┌──────────┐  ┌───────────┐  │
│  │ MySQL   │  │  Redis   │  │ Backend   │  │
│  │ :3306   │  │ :6379    │  │ :9000     │  │
│  └─────────┘  └──────────┘  └───────────┘  │
│                                              │
│  ┌───────────────────────────────────────┐  │
│  │       Frontend (Nginx)                 │  │
│  │       :80 → :4832 (Host)              │  │
│  └───────────────────────────────────────┘  │
│                                              │
└─────────────────────────────────────────────┘
```

### Network Details

**Network Name:** `mythicaldash_v3_network`  
**Driver:** Bridge  
**DNS Resolution:** Docker's embedded DNS allows inter-container communication by service name

### Service-to-Service Communication

Services communicate using container names as hostnames:

```
backend → mysql:3306
backend → redis:6379
frontend → backend:80
```

### External Access

- Frontend accessible via: `http://localhost:4832`
- Backend API accessible via: `http://localhost:4832/api`
- Database: Not exposed (internal only) ✓ Secure
- Redis: Not exposed (internal only) ✓ Secure

## Health Checks

### How Health Checks Work

Each service includes a health check that Docker uses to:
- Detect failing services
- Restart unhealthy containers
- Wait for service availability before starting dependent services

### Health Check Configuration

**MySQL Health Check:**
```yaml
healthcheck:
  test: ["CMD", "mariadb-admin", "ping", "-h", "localhost", "-u", "root"]
  timeout: 20s
  retries: 10
  interval: 10s
```

**Redis Health Check:**
```yaml
healthcheck:
  test: ["CMD", "redis-cli", "ping"]
  timeout: 20s
  retries: 10
  interval: 10s
```

**Backend Health Check:**
```yaml
healthcheck:
  test: ["CMD", "php", "cli", "help"]
  timeout: 20s
  retries: 10
  interval: 10s
```

**Frontend Health Check:**
```yaml
healthcheck:
  test: ["CMD", "curl", "-f", "http://localhost:80"]
  timeout: 20s
  retries: 10
  interval: 10s
```

### Monitor Health Status

```bash
# Check health status
docker compose ps

# View health details
docker inspect mythicaldash_v3_mysql --format='{{.State.Health.Status}}'
docker inspect mythicaldash_v3_backend --format='{{.State.Health.Status}}'

# Follow health changes
watch docker compose ps
```

### Manual Health Testing

```bash
# Test MySQL
docker compose exec mysql mariadb-admin ping -h localhost -u root

# Test Redis
docker compose exec redis redis-cli ping

# Test Backend
docker compose exec backend php cli help

# Test Frontend
docker compose exec frontend curl -f http://localhost:80
```

## Docker Images

### Image Sources

All images are pulled from GitHub Container Registry (ghcr.io):

```
ghcr.io/mrfreakcmd/mythicaldash_v3-backend:latest
ghcr.io/mrfreakcmd/mythicaldash_v3-frontend:latest
```

### Image Tags

- `latest` - Production-ready stable releases
- `dev` - Development builds with additional tools
- `v3.x.x` - Specific version releases

### Building Images Locally

**Backend Image:**
```bash
cd backend
docker build -t mythicaldash-backend:local .
```

**Frontend Image:**
```bash
cd frontend
docker build -t mythicaldash-frontend:local .
```

**Using Local Images:**
```bash
# Update docker-compose.yml
# Change:
#   image: ghcr.io/mrfreakcmd/mythicaldash_v3-backend:latest
# To:
#   image: mythicaldash-backend:local

docker compose up -d
```

### Image Specifications

**Backend Image:**
- Base: `php:8.4-fpm`
- Size: ~1.2GB
- Includes: PHP extensions, Nginx, Supervisor, Cron
- Entrypoint: `/usr/local/bin/init.sh`

**Frontend Image:**
- Build Stage: `node:22-alpine`
- Production Stage: `nginx:alpine`
- Size: ~200MB
- Includes: Nginx with Vue.js built assets

### Check Image Size

```bash
docker images | grep mythicaldash
docker images | grep mariadb
docker images | grep redis
docker images | grep nginx
```

## Troubleshooting

### Common Issues

#### 1. Services Not Starting

**Problem:** Containers exit immediately or won't start

**Solution:**
```bash
# Check logs
docker compose logs

# Check specific service
docker compose logs backend

# Restart services
docker compose restart

# Full restart
docker compose down
docker compose up -d
```

#### 2. Health Check Failing

**Problem:** Service marked as "unhealthy"

**Solution:**
```bash
# Check health status
docker compose ps

# View detailed health info
docker inspect mythicaldash_v3_backend --format='{{json .State.Health}}' | jq

# Check service logs for errors
docker compose logs backend

# Manually test health
docker compose exec backend php cli help
```

#### 3. Database Connection Error

**Problem:** Backend can't connect to MySQL

**Solution:**
```bash
# Verify MySQL is running and healthy
docker compose ps mysql

# Test MySQL connection
docker compose exec mysql mariadb-admin ping -h localhost -u root -pmythicaldsah_v3_root

# Check database credentials
echo $MARIADB_PASSWORD

# Restart database
docker compose restart mysql
```

#### 4. Redis Connection Error

**Problem:** Backend can't connect to Redis

**Solution:**
```bash
# Verify Redis is running
docker compose ps redis

# Test Redis connection
docker compose exec redis redis-cli ping

# Check Redis password
echo $REDIS_PASSWORD

# Test with password
docker compose exec redis redis-cli -a $REDIS_PASSWORD ping

# Restart Redis
docker compose restart redis
```

#### 5. Port Already in Use

**Problem:** Error: "bind: address already in use"

**Solution:**
```bash
# Find what's using port 4832
lsof -i :4832              # macOS/Linux
netstat -ano | findstr :4832  # Windows

# Change port in docker-compose.yml
# Change: ports: ["4832:80"]
# To:     ports: ["8080:80"]

docker compose up -d
```

#### 6. Disk Space Issues

**Problem:** "No space left on device" error

**Solution:**
```bash
# Check disk usage
docker system df

# Clean up unused resources
docker system prune -a

# Remove specific volumes (be careful!)
docker volume prune

# Check volume sizes
du -sh /var/lib/docker/volumes/*
```

#### 7. Memory Issues

**Problem:** Services crashing or container killed

**Solution:**
```bash
# Check memory usage
docker stats

# In docker-compose.yml, add memory limits:
services:
  backend:
    deploy:
      resources:
        limits:
          memory: 2G
        reservations:
          memory: 1G

docker compose up -d
```

### Debug Commands

```bash
# Complete system diagnostics
docker compose ps
docker compose logs --tail=50
docker system df
docker stats

# Network diagnostics
docker network ls
docker network inspect mythicaldash_v3_network

# Volume diagnostics
docker volume ls
docker volume inspect mythicaldash_v3_attachments

# Container diagnostics
docker inspect mythicaldash_v3_backend
docker exec mythicaldash_v3_backend env
docker exec mythicaldash_v3_backend ps aux
```

## Advanced Configuration

### Custom Nginx Configuration

Edit `nginx.conf` for frontend customization:

```nginx
# Example: Add security headers
add_header X-Frame-Options "SAMEORIGIN" always;
add_header X-Content-Type-Options "nosniff" always;
add_header X-XSS-Protection "1; mode=block" always;
```

Rebuild frontend:
```bash
docker compose build frontend
docker compose up -d frontend
```

### PHP Configuration

Edit backend PHP settings in `backend/Dockerfile`:

```dockerfile
# Current limits
memory_limit = -1
upload_max_filesize = 100M
post_max_size = 100M
max_execution_time = 300
```

### Reverse Proxy Setup (Nginx)

Example proxy configuration for production:

```nginx
server {
    listen 80;
    server_name yourdomain.com;
    
    location / {
        proxy_pass http://localhost:4832;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```

### SSL/TLS Configuration

```bash
# Install Certbot
sudo apt-get install certbot python3-certbot-nginx

# Generate certificate
sudo certbot certonly --nginx -d yourdomain.com

# Update Nginx configuration
server {
    listen 443 ssl;
    ssl_certificate /etc/letsencrypt/live/yourdomain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/yourdomain.com/privkey.pem;
}

# Auto-renew
sudo certbot renew --quiet
```

### Database Optimization

```bash
# Connect to database
docker compose exec mysql mariadb -u mythicaldash_v3 -p mythicaldash_v3

# Optimize tables
OPTIMIZE TABLE table_name;
OPTIMIZE TABLE database_name.*; -- All tables

# Check table integrity
CHECK TABLE table_name;

# Analyze table statistics
ANALYZE TABLE table_name;
```

### Redis Persistence

Redis in this setup uses Append-Only File (AOF):

```bash
# Check AOF status
docker compose exec redis redis-cli CONFIG GET appendonly

# Monitor AOF rewrites
docker compose exec redis redis-cli INFO persistence

# Manual AOF rewrite
docker compose exec redis redis-cli BGREWRITEAOF
```

### Scaling Considerations

For increased traffic, consider:

1. **Load Balancing**: Use Nginx upstream
2. **Database Replication**: Configure MariaDB replication
3. **Cache Optimization**: Increase Redis memory
4. **Static Content CDN**: Serve assets from CDN
5. **Container Orchestration**: Migrate to Kubernetes

## Maintenance

### Regular Maintenance Tasks

#### Daily
```bash
# Monitor logs
docker compose logs --tail=100 | grep -i error

# Check health status
docker compose ps
```

#### Weekly
```bash
# Backup database
docker compose exec -T mysql mariadb-dump \
  -u mythicaldash_v3 -pmythicaldash_v3_password \
  mythicaldash_v3 > backup_$(date +%Y%m%d).sql

# Check disk usage
docker system df
```

#### Monthly
```bash
# Update images
docker compose pull
docker compose down
docker compose up -d

# Clean up old images
docker image prune -a --filter "until=720h"

# Full backup with volumes
./backup.sh
```

### Update Procedure

```bash
# 1. Create backup
./backup.sh

# 2. Stop services gracefully
docker compose down

# 3. Pull latest images
docker compose pull

# 4. Start services
docker compose up -d

# 5. Verify health
docker compose ps

# 6. Test application
curl http://localhost:4832
```

### Rollback Procedure

```bash
# 1. Stop services
docker compose down

# 2. Restore from backup (if needed)
docker compose exec -T mysql mariadb \
  -u mythicaldash_v3 -pmythicaldash_v3_password \
  mythicaldash_v3 < backup.sql

# 3. Use previous image tag
# Edit docker-compose.yml to use previous version

# 4. Restart
docker compose up -d
```

### Log Management

```bash
# View combined logs
docker compose logs --tail=200

# Stream logs
docker compose logs -f

# Service-specific logs
docker compose logs backend
docker compose logs frontend
docker compose logs mysql

# With timestamps
docker compose logs --timestamps

# Follow with filtering
docker compose logs -f | grep -i error

# Export logs
docker compose logs > logs_$(date +%Y%m%d_%H%M%S).txt
```

### Resource Cleanup

```bash
# Remove stopped containers
docker container prune

# Remove unused images
docker image prune

# Remove unused volumes
docker volume prune

# Remove unused networks
docker network prune

# Complete cleanup (careful!)
docker system prune -a --volumes
```

---

## Additional Resources

- [Docker Documentation](https://docs.docker.com/)
- [Docker Compose Reference](https://docs.docker.com/compose/compose-file/)
- [MariaDB Docker Documentation](https://hub.docker.com/_/mariadb)
- [Redis Docker Documentation](https://hub.docker.com/_/redis)
- [Nginx Documentation](https://nginx.org/en/docs/)

## Getting Help

For issues or questions:

1. Check [Troubleshooting](#troubleshooting) section
2. Review logs: `docker compose logs`
3. Open an issue on GitHub
4. Contact the development team

---

**Last Updated:** 2026-07-09  
**Version:** 3.5.4-aurora  
**Maintainer:** MythicalDash Team

# Docker Deployment FAQ

Frequently asked questions about deploying MythicalDash with Docker.

## General Questions

### Q: Do I need Docker Desktop or can I use Docker Engine directly?

**A:** You can use either:

- **Docker Desktop**: Easier setup, includes Docker Compose, good for development
- **Docker Engine + Docker Compose**: Leaner, better for servers, recommended for production

On Linux servers, install Docker Engine and Docker Compose separately:
```bash
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh
sudo apt-get install docker-compose-plugin
```

---

### Q: What are the minimum system requirements?

**A:** 
- **CPU**: 2 cores minimum (4+ recommended for production)
- **RAM**: 4GB minimum (8GB+ recommended for production)
- **Storage**: 10GB minimum free space
- **Network**: Stable internet for initial image pull

To check your system:
```bash
# Linux
free -h          # Check RAM
df -h /          # Check disk
nproc             # Check CPU cores
```

---

### Q: Can I run MythicalDash on Windows?

**A:** Yes! Use Docker Desktop for Windows:

1. Install Docker Desktop from https://www.docker.com/products/docker-desktop
2. Ensure WSL 2 is enabled (Windows Subsystem for Linux 2)
3. Follow the same deployment steps

**Note:** File path syntax differs:
```bash
# Windows PowerShell
docker compose -f .\docker-compose.yml up -d

# Git Bash (POSIX-like)
docker compose -f docker-compose.yml up -d
```

---

### Q: Do I need to know Docker to deploy MythicalDash?

**A:** No! The deployment is pre-configured. You need to:

1. Have Docker installed
2. Copy `.env.example` to `.env` and set passwords
3. Run `docker compose up -d`
4. Wait for services to be healthy

For complex tasks (customization, troubleshooting), basic Docker knowledge helps.

---

## Installation & Setup

### Q: Where do I clone the repository?

**A:**

**Development:**
```bash
# Anywhere convenient
git clone https://github.com/mrfreakcmd/MythicalDash.git ~/mythicaldash
cd ~/mythicaldash
```

**Production (Linux server):**
```bash
# Create application directory
sudo mkdir -p /opt/mythicaldash
cd /opt/mythicaldash

# Clone repository
sudo git clone https://github.com/mrfreakcmd/MythicalDash.git .

# Fix permissions
sudo chown -R $USER:$USER /opt/mythicaldash
```

---

### Q: How do I set up the .env file?

**A:**

1. Copy the example:
```bash
cp .env.example .env
```

2. Edit with your values:
```bash
# Linux/macOS
nano .env

# Windows PowerShell
notepad .env
```

3. Set these critical values:
```env
MARIADB_ROOT_PASSWORD=your_secure_password_here
MARIADB_PASSWORD=your_secure_password_here
REDIS_PASSWORD=your_secure_password_here
DATABASE_ENCRYPTION_KEY=your_32_char_base64_key
```

4. Generate secure passwords:
```bash
openssl rand -base64 32
```

---

### Q: What should I set DATABASE_ENCRYPTION_KEY to?

**A:** Generate a new 32-character base64-encoded key:

```bash
# Generate encryption key
openssl rand -base64 32

# Example output: zKZ7//AocUD2wCuzjW5rdTbb8FvGBgKTZ8iCATslZ/8=

# Add to .env
DATABASE_ENCRYPTION_KEY=zKZ7//AocUD2wCuzjW5rdTbb8FvGBgKTZ8iCATslZ/8=
```

**Important:**
- Generate a NEW key for your installation
- Store it safely (not in git)
- Changing it later will make existing data unreadable
- Use the same key across all backend instances

---

### Q: Can I change passwords after deployment?

**A:** Yes, but it requires stopping services:

```bash
# 1. Stop services
docker compose down

# 2. Edit .env
nano .env

# 3. For database password, also backup and restore:
docker compose up -d mysql redis

# Wait for MySQL to be healthy
docker compose ps

# 4. Restart all services
docker compose up -d

# 5. Verify health
docker compose ps
```

---

## Running Services

### Q: How do I start the services?

**A:**

**Development:**
```bash
docker compose -f docker-compose-dev.yml up -d
```

**Production:**
```bash
docker compose up -d
```

Or using the production file explicitly:
```bash
docker compose -f docker-compose.yml up -d
```

---

### Q: How long does it take for services to start?

**A:** 
- **Initial start**: 60-120 seconds for all services to be healthy
- **Subsequent starts**: 30-60 seconds
- **Full application ready**: 2-3 minutes

Monitor with:
```bash
watch docker compose ps
```

---

### Q: What does "health check" mean? Why is my service unhealthy?

**A:** Docker monitors service health. Unhealthy services may have issues:

```bash
# Check health
docker compose ps

# View health details
docker inspect mythicaldash_v3_backend --format='{{json .State.Health}}' | jq

# View service logs
docker compose logs backend

# Common issues:
# - Service not fully started yet (wait 2 minutes)
# - Database not initialized
# - Memory/resource constraints
# - Port conflicts
```

---

### Q: How do I know when services are ready?

**A:**

```bash
# Command 1: Watch status
watch docker compose ps

# Command 2: Check all healthy
docker compose ps | grep -c "healthy"

# All 4 should show "healthy"
# When ready:
curl http://localhost:4832
```

---

### Q: How do I stop services without deleting data?

**A:**

```bash
# Stop all services (data persists)
docker compose down

# Stop specific service
docker compose stop backend

# Pause services (lightweight, keeps memory)
docker compose pause
```

**Important:** `docker compose down` stops and removes containers, but keeps volumes (data).

---

## Ports & Access

### Q: What ports does MythicalDash use?

**A:**

| Service | Internal Port | External Port | Access |
|---------|---------------|---------------|--------|
| Frontend | 80 | 4832 | http://localhost:4832 |
| Backend | 9000 | - | Internal only |
| MySQL | 3306 | - | Internal only ✓ Secure |
| Redis | 6379 | - | Internal only ✓ Secure |

---

### Q: How do I access MythicalDash?

**A:**

- **Frontend**: http://localhost:4832
- **API**: http://localhost:4832/api
- **Local machine**: http://localhost:4832
- **From another machine**: http://YOUR_IP:4832

Replace `YOUR_IP` with your server's IP address.

---

### Q: Can I change the port 4832?

**A:** Yes, edit `docker-compose.yml`:

```yaml
frontend:
  ports:
    - "8080:80"  # Change 4832 to 8080
```

Then restart:
```bash
docker compose down
docker compose up -d
```

Access at: http://localhost:8080

---

### Q: How do I expose it to the internet?

**A:** Not recommended directly! Use a reverse proxy:

**Nginx on host (Linux):**
```nginx
server {
    listen 80;
    server_name yourdomain.com;
    
    location / {
        proxy_pass http://localhost:4832;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
    }
}
```

Then set up SSL with Let's Encrypt:
```bash
sudo apt-get install certbot python3-certbot-nginx
sudo certbot certonly --nginx -d yourdomain.com
```

---

### Q: Can I access the database from outside Docker?

**A:** Database is intentionally not exposed (secure by default). To access it:

```bash
# Connect through Docker
docker compose exec mysql mariadb -u mythicaldash_v3 -p mythicaldash_v3

# Or expose port (not recommended)
# Edit docker-compose.yml:
mysql:
  ports:
    - "3306:3306"

docker compose up -d
# Then connect from host: mysql -h localhost -u mythicaldash_v3 -p
```

---

## Environment & Configuration

### Q: How do I set environment variables?

**A:** Edit `.env` file:

```env
# Database
MARIADB_ROOT_PASSWORD=secure_password
MARIADB_PASSWORD=secure_password

# Redis
REDIS_PASSWORD=secure_password

# Application
APP_ENV=production
APP_DEBUG=false
```

Changes take effect on next `docker compose up -d`.

---

### Q: Can I use different environment variables for dev and production?

**A:** Yes! Create separate `.env` files:

```bash
# Development environment
cp .env.example .env.dev
nano .env.dev

# Production environment
cp .env.example .env.prod
nano .env.prod

# Use specific file
docker compose --env-file .env.dev up -d
docker compose --env-file .env.prod up -d
```

---

### Q: How do I update environment variables without restarting?

**A:** Most environment variables require restart:

```bash
# 1. Edit .env
nano .env

# 2. Restart services
docker compose restart

# Some changes (like database passwords) may require:
docker compose down
docker compose up -d
```

---

## Data & Persistence

### Q: Where is my data stored?

**A:** In Docker named volumes:

```bash
# List volumes
docker volume ls

# Locate volume on disk (Linux)
docker volume inspect mythicaldash_v3_attachments --format='{{.Mountpoint}}'

# Typical location
# Linux: /var/lib/docker/volumes/mythicaldash_v3_attachments/_data
# Windows: \\wsl$\docker-desktop\mnt\docker-desktop-disk\...
# macOS: Docker Desktop > Preferences > Resources > File Sharing
```

---

### Q: How do I backup my data?

**A:**

```bash
# Full backup script
#!/bin/bash
BACKUP_DIR="./backups"
DATE=$(date +%Y%m%d_%H%M%S)

# Database
docker compose exec -T mysql mariadb-dump \
  -u mythicaldash_v3 -pmythicaldash_v3_password \
  mythicaldash_v3 > $BACKUP_DIR/db_$DATE.sql

# Attachments
docker run --rm -v mythicaldash_v3_attachments:/source \
  -v $(pwd)/$BACKUP_DIR:/dest alpine \
  tar czf /dest/attachments_$DATE.tar.gz -C /source .

# Snapshots
docker run --rm -v mythicaldash_v3_snapshots:/source \
  -v $(pwd)/$BACKUP_DIR:/dest alpine \
  tar czf /dest/snapshots_$DATE.tar.gz -C /source .

echo "Backup completed at $BACKUP_DIR"
```

---

### Q: How do I restore from backup?

**A:**

```bash
# Stop services
docker compose down

# Restore database
docker compose up -d mysql
docker compose exec -T mysql mariadb -u mythicaldash_v3 \
  -pmythicaldash_v3_password mythicaldash_v3 < backups/db_20240709.sql

# Restore attachments
docker run --rm -v mythicaldash_v3_attachments:/dest \
  -v $(pwd)/backups:/source alpine \
  tar xzf /source/attachments_20240709.tar.gz -C /dest

# Restart all services
docker compose up -d
```

---

### Q: Will my data persist if I restart Docker?

**A:** Yes! Docker volumes persist across restarts:

```bash
docker compose down   # Data persists
docker compose up -d  # Data restored
```

Only deleting volumes removes data:
```bash
docker compose down -v  # WARNING: Deletes data!
```

---

## Updates & Maintenance

### Q: How do I update to the latest version?

**A:**

```bash
# 1. Backup first!
./backup.sh

# 2. Pull latest images
docker compose pull

# 3. Restart services
docker compose down
docker compose up -d

# 4. Verify
docker compose ps
curl http://localhost:4832
```

---

### Q: How do I rollback to a previous version?

**A:**

```bash
# 1. Restore database from backup
docker compose down
docker compose up -d mysql

# Wait for MySQL to be healthy
sleep 30

# Restore database
docker compose exec -T mysql mariadb -u mythicaldash_v3 \
  -pmythicaldash_v3_password mythicaldash_v3 < backups/db_previous.sql

# 2. Use previous image tag in docker-compose.yml
# Change: image: ghcr.io/mrfreakcmd/mythicaldash_v3-backend:latest
# To:     image: ghcr.io/mrfreakcmd/mythicaldash_v3-backend:v3.5.3

# 3. Restart
docker compose up -d
```

---

### Q: How often should I backup?

**A:**

- **Development**: Daily (automated)
- **Production**: Daily minimum, multiple times daily for high-traffic sites
- **Critical data**: After major changes

Set up automated backups with cron:
```bash
# Add to crontab (run daily at 2 AM)
0 2 * * * /path/to/backup.sh
```

---

## Performance & Optimization

### Q: Why are my containers using so much memory?

**A:**

```bash
# Check memory usage
docker stats

# Typical memory usage:
# MySQL: 200-500MB
# Redis: 100-300MB
# Backend: 200-400MB
# Frontend: 50-100MB

# Limit memory (edit docker-compose.yml):
services:
  backend:
    deploy:
      resources:
        limits:
          memory: 1G
        reservations:
          memory: 512M
```

---

### Q: How do I improve performance?

**A:**

1. **Increase resources**: More RAM/CPU to Docker
2. **Optimize database**: Add indexes, clean old data
3. **Cache optimization**: Increase Redis memory
4. **CDN**: Serve static assets from CDN
5. **Load balancing**: Use reverse proxy for multiple instances

---

### Q: Can I run multiple backend instances?

**A:** Not with standard `docker-compose.yml`. For scaling:

1. **Manually scale containers:**
```bash
docker compose up -d --scale backend=3
```

2. **Use Kubernetes for production clustering**

3. **Use Docker Swarm for orchestration**

Current setup is optimized for single-instance deployment.

---

## Troubleshooting

### Q: Services won't start. What do I do?

**A:**

```bash
# 1. Check logs
docker compose logs

# 2. Check specific service
docker compose logs backend

# 3. Check resources
docker system df
free -h

# 4. Check ports
netstat -an | grep 4832

# 5. Restart Docker daemon
sudo systemctl restart docker

# 6. Try fresh start
docker compose down
docker compose up -d
```

---

### Q: Health check keeps failing. Why?

**A:**

```bash
# 1. Wait - services take 60-120 seconds to start
sleep 60
docker compose ps

# 2. Check service-specific logs
docker compose logs SERVICE_NAME

# 3. Test manually
docker compose exec mysql mariadb-admin ping -h localhost -u root
docker compose exec redis redis-cli ping
docker compose exec backend php cli help
docker compose exec frontend curl -f http://localhost

# 4. Restart unhealthy service
docker compose restart SERVICE_NAME
```

---

### Q: Database connection fails. What do I check?

**A:**

```bash
# 1. MySQL is running
docker compose ps mysql

# 2. Test connection
docker compose exec mysql mariadb-admin ping -h localhost -u root \
  -pmythicaldsah_v3_root

# 3. Check credentials in .env
grep MARIADB .env

# 4. Check MySQL logs
docker compose logs mysql

# 5. Restart MySQL
docker compose restart mysql
```

---

### Q: How do I see what's happening in the containers?

**A:**

```bash
# Logs
docker compose logs -f backend

# Processes in container
docker top mythicaldash_v3_backend

# Shell access
docker compose exec backend bash

# Environment variables
docker compose exec backend env

# Disk usage
docker compose exec backend df -h
```

---

### Q: Port 4832 is already in use. What do I do?

**A:**

```bash
# Find what's using the port
lsof -i :4832                    # macOS/Linux
netstat -ano | findstr :4832    # Windows

# Option 1: Stop the other service
kill PID

# Option 2: Change MythicalDash port
# Edit docker-compose.yml:
frontend:
  ports:
    - "8080:80"

docker compose up -d
```

---

## Security

### Q: Is my data encrypted?

**A:** 

**In transit**: Database/Redis connections are internal (not encrypted by default)

**At rest**: 
- Database tables can be encrypted with `DATABASE_ENCRYPTION=xchacha20`
- Set `DATABASE_ENCRYPTION_KEY` to enable

**Recommendation**: For production, also:
- Use SSL/TLS for external connections
- Keep `.env` file secure
- Use strong passwords
- Regularly update Docker images

---

### Q: What should I do about the default passwords?

**A:**

Change IMMEDIATELY before going to production:

```env
# Before: Do NOT use
MARIADB_ROOT_PASSWORD=mythicaldash_v3_root
MARIADB_PASSWORD=mythicaldash_v3_password
REDIS_PASSWORD=mythicaldash_v3_redis

# After: Generate secure passwords
MARIADB_ROOT_PASSWORD=aB9xK2mL@pQ5vWxYz3nO
MARIADB_PASSWORD=kL7mN9pQ#rS1vWxYz5bC
REDIS_PASSWORD=dE3fG5hI$jK7lMnOpQ9r
```

Generate with:
```bash
openssl rand -base64 32
```

---

### Q: How do I secure external access?

**A:**

1. **Use reverse proxy** (not direct port exposure)
2. **Enable SSL/TLS** with Let's Encrypt
3. **Restrict firewall** (only allow necessary IPs)
4. **Use VPN** for admin access
5. **Keep Docker updated**
6. **Monitor logs** for suspicious activity

---

## Getting Help

### Q: Where can I get more information?

**A:**

- **Docker Documentation**: https://docs.docker.com/
- **Docker Compose Reference**: https://docs.docker.com/compose/
- **MythicalDash Docs**: See `/docs/` folder in repository
- **GitHub Issues**: https://github.com/mrfreakcmd/MythicalDash/issues
- **Community**: Discord/Forums (if available)

---

### Q: I still have questions not answered here!

**A:** 

1. Check the full Docker Deployment Guide: `docs/Docker-Deployment.md`
2. Review logs: `docker compose logs`
3. Check Docker documentation
4. Open a GitHub issue with details:
   - Docker version
   - OS (Windows/macOS/Linux)
   - Error messages/logs
   - Steps to reproduce

---

## Quick Reference

### Essential Commands
```bash
docker compose ps                    # Check status
docker compose logs -f               # View logs
docker compose up -d                 # Start
docker compose down                  # Stop
docker compose restart               # Restart
docker compose exec backend bash     # Shell access
```

### Database Access
```bash
docker compose exec mysql mariadb -u mythicaldash_v3 -p mythicaldash_v3
```

### Backup
```bash
docker compose exec -T mysql mariadb-dump \
  -u mythicaldash_v3 -pmythicaldash_v3_password \
  mythicaldash_v3 > backup.sql
```

### Restore
```bash
docker compose exec -T mysql mariadb \
  -u mythicaldash_v3 -pmythicaldash_v3_password \
  mythicaldash_v3 < backup.sql
```

---

**Last Updated:** 2026-07-09  
**Still have questions?** Create an issue or check the main Docker-Deployment.md guide!

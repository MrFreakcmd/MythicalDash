# Docker Deployment Checklist

Quick reference checklists for deploying MythicalDash using Docker.

## Pre-Deployment Checklist

### System Requirements
- [ ] Docker 20.10+ installed
- [ ] Docker Compose 2.0+ installed
- [ ] Git installed
- [ ] At least 4GB RAM available
- [ ] Minimum 10GB free disk space
- [ ] Stable internet connection

### Repository Preparation
- [ ] Repository cloned: `git clone https://github.com/mrfreakcmd/MythicalDash.git`
- [ ] Correct branch checked out: `v3-remastered`
- [ ] All files present (check for Dockerfile, docker-compose.yml, etc.)
- [ ] Nginx configuration present: `nginx.conf`
- [ ] Backend setup files present: `backend/Dockerfile`, `backend/init.sh`, etc.

### Configuration Files
- [ ] `.env.example` reviewed and understood
- [ ] `.env` file created: `cp .env.example .env`
- [ ] All environment variables configured:
  - [ ] `MARIADB_ROOT_PASSWORD` - Changed from default
  - [ ] `MARIADB_PASSWORD` - Changed from default
  - [ ] `REDIS_PASSWORD` - Changed from default
  - [ ] `DATABASE_ENCRYPTION_KEY` - Generated fresh key
  - [ ] `APP_ENV` - Set to `production` for prod
  - [ ] `APP_DEBUG` - Set to `false` for prod
  - [ ] `APP_URL` - Set to correct domain/IP
- [ ] Sensitive data not committed to git

### Security Review
- [ ] All default passwords changed
- [ ] Encryption key is 32+ characters, base64 encoded
- [ ] Database not exposed to external network
- [ ] Redis not exposed to external network
- [ ] Only frontend port (4832) exposed if needed
- [ ] Firewall rules configured appropriately

### Backup & Recovery
- [ ] Backup location identified: `/opt/mythicaldash/backups/`
- [ ] Backup script created and tested
- [ ] First backup completed before deployment
- [ ] Restore procedure tested
- [ ] Off-site backup configured (optional but recommended)

### Infrastructure Preparation
- [ ] Domain/IP address ready
- [ ] Reverse proxy configured (if using)
- [ ] SSL/TLS certificates ready (if HTTPS required)
- [ ] Monitoring/logging configured (optional)
- [ ] Mail server configured (if email needed)

---

## Development Deployment Checklist

### Quick Start
```bash
cd /path/to/MythicalDash
cp .env.example .env
# Edit .env with your dev settings
docker compose -f docker-compose-dev.yml up -d
```

### Verification
- [ ] All services started: `docker compose ps`
- [ ] All services healthy (Wait 60-120 seconds for health checks)
- [ ] Frontend accessible: `http://localhost:4832`
- [ ] Backend logs clean: `docker compose logs backend | grep -i error`
- [ ] Database initialized: `docker compose exec mysql mariadb-admin ping`
- [ ] Redis connected: `docker compose exec redis redis-cli ping`

### Development Environment
- [ ] View logs: `docker compose logs -f backend`
- [ ] Execute backend commands: `docker compose exec backend php cli help`
- [ ] Run migrations: `docker compose exec backend php artisan migrate`
- [ ] Seed database (if needed): `docker compose exec backend php artisan db:seed`
- [ ] Test API endpoints: `curl http://localhost:4832/api/health`

---

## Production Deployment Checklist

### Pre-Production Validation
- [ ] All dev testing completed
- [ ] Code reviewed and tested
- [ ] Breaking changes identified
- [ ] Rollback plan prepared
- [ ] Backup verified and accessible

### Server Preparation
- [ ] Dedicated server/VPS provisioned
- [ ] Minimum specs verified: 4GB RAM, 10GB storage
- [ ] Operating system updated: `sudo apt-get update && upgrade`
- [ ] Docker installed and running
- [ ] Directory created: `mkdir -p /opt/mythicaldash`
- [ ] Permissions set: `chown -R $USER:$USER /opt/mythicaldash`

### Deployment
- [ ] Repository cloned to server
- [ ] `.env` configured with production values
- [ ] Encryption keys generated and secured
- [ ] Backup script created and tested
- [ ] First backup completed
- [ ] Images pulled: `docker compose pull`
- [ ] Services started: `docker compose up -d`

### Post-Deployment Validation
- [ ] All containers running: `docker compose ps`
- [ ] All containers healthy (wait 2 minutes)
- [ ] Frontend accessible and loading
- [ ] API endpoints responding
- [ ] Database connected and initialized
- [ ] Redis cache working
- [ ] No error logs in past 5 minutes: `docker compose logs --tail=100 | grep -i error`
- [ ] Application features tested:
  - [ ] User login
  - [ ] Dashboard loading
  - [ ] File uploads
  - [ ] API requests
  - [ ] Database queries

### Monitoring Setup
- [ ] Docker stats monitored: `docker stats`
- [ ] Log aggregation configured (optional)
- [ ] Resource alerts set up (optional)
- [ ] Uptime monitoring configured (optional)
- [ ] Backup cron job scheduled

### Documentation
- [ ] Deployment documented in wiki/docs
- [ ] Emergency contacts listed
- [ ] Runbook created for common issues
- [ ] Architecture diagram saved
- [ ] Credentials stored securely

---

## Update Procedure Checklist

### Pre-Update
- [ ] New version reviewed: Release notes read
- [ ] Backup created: `./backup.sh`
- [ ] Backup verified: Files present and accessible
- [ ] Maintenance window scheduled
- [ ] Rollback procedure reviewed

### During Update
- [ ] Services stopped gracefully: `docker compose down`
- [ ] Latest images pulled: `docker compose pull`
- [ ] Environment variables reviewed for changes
- [ ] Services started: `docker compose up -d`
- [ ] Health checks monitored: `watch docker compose ps`
- [ ] Application tested: Features working as expected

### Post-Update
- [ ] All services healthy
- [ ] No error logs
- [ ] Performance acceptable
- [ ] Users notified if applicable
- [ ] Documentation updated
- [ ] Deployment recorded in changelog

---

## Rollback Procedure Checklist

### Initiate Rollback
- [ ] Issue identified and documented
- [ ] Decision made to rollback
- [ ] Stakeholders notified
- [ ] Backup location verified

### Execute Rollback
- [ ] Services stopped: `docker compose down`
- [ ] Database restored (if data changed):
  ```bash
  docker compose exec -T mysql mariadb \
    -u mythicaldash_v3 -p mythicaldash_v3 < backup.sql
  ```
- [ ] Volume restored (if needed):
  ```bash
  docker run --rm -v mythicaldash_v3_attachments:/dest \
    -v /path/to/backup:/source alpine \
    tar xzf /source/attachments_backup.tar.gz -C /dest
  ```
- [ ] Previous image tag used in docker-compose.yml
- [ ] Services restarted: `docker compose up -d`
- [ ] Health checks verified: `docker compose ps`
- [ ] Application tested: Features working correctly

### Post-Rollback
- [ ] All services healthy
- [ ] No error logs
- [ ] Root cause analysis started
- [ ] Fix prepared for next attempt
- [ ] Stakeholders notified of rollback completion
- [ ] Incident recorded

---

## Maintenance Checklist

### Daily
- [ ] Check health status: `docker compose ps`
- [ ] Review error logs: `docker compose logs | grep -i error`
- [ ] Monitor disk usage: `df -h`
- [ ] Monitor memory usage: `docker stats`

### Weekly
- [ ] Create backup: `./backup.sh`
- [ ] Verify backup integrity
- [ ] Test restore procedure (on staging)
- [ ] Review security logs
- [ ] Check for available updates

### Monthly
- [ ] Full system backup
- [ ] Database optimization: `OPTIMIZE TABLE ...`
- [ ] Redis memory analysis: `redis-cli INFO memory`
- [ ] Update Docker images if available
- [ ] Review and update documentation
- [ ] Capacity planning assessment

### Quarterly
- [ ] Security audit
- [ ] Disaster recovery test
- [ ] Performance baseline update
- [ ] Architecture review
- [ ] Update internal documentation

---

## Troubleshooting Checklist

### Services Won't Start
- [ ] Check Docker daemon: `sudo systemctl status docker`
- [ ] Check logs: `docker compose logs`
- [ ] Verify port availability: `netstat -an | grep 4832`
- [ ] Check disk space: `df -h`
- [ ] Restart services: `docker compose restart`

### Health Checks Failing
- [ ] Wait 2 minutes for startup
- [ ] Check specific service: `docker compose ps`
- [ ] Review service logs: `docker compose logs SERVICE_NAME`
- [ ] Test service manually (see Docker-Deployment.md)
- [ ] Restart unhealthy service

### Database Connection Issues
- [ ] Verify MySQL running: `docker compose ps mysql`
- [ ] Test connection: `docker compose exec mysql mariadb-admin ping`
- [ ] Check credentials in .env
- [ ] Review MySQL logs: `docker compose logs mysql`
- [ ] Restart MySQL: `docker compose restart mysql`

### Memory/Performance Issues
- [ ] Check memory usage: `docker stats`
- [ ] Check disk I/O: `iostat -x 1`
- [ ] Review slow query logs
- [ ] Optimize database tables
- [ ] Scale resources if needed

### Network Issues
- [ ] Check network: `docker network inspect mythicaldash_v3_network`
- [ ] Test inter-service connectivity
- [ ] Verify firewall rules
- [ ] Check DNS resolution: `docker compose exec backend nslookup mysql`

---

## Performance Optimization Checklist

### Database Optimization
- [ ] Enable query cache (if MariaDB < 10.10)
- [ ] Optimize table structure
- [ ] Add appropriate indexes
- [ ] Monitor slow queries
- [ ] Archive old data regularly

### Cache Optimization
- [ ] Increase Redis memory allocation
- [ ] Configure appropriate TTLs
- [ ] Monitor cache hit rate
- [ ] Use cache warming strategies

### File System Optimization
- [ ] Review volume mount types
- [ ] Enable compression if applicable
- [ ] Clean old logs regularly
- [ ] Monitor disk I/O patterns

### Application Optimization
- [ ] Enable PHP OPCache
- [ ] Increase PHP memory limit if needed
- [ ] Configure CDN for static assets
- [ ] Enable gzip compression in Nginx

---

## Security Hardening Checklist

- [ ] All default passwords changed
- [ ] SSH keys configured for server access
- [ ] Firewall rules restrictive (whitelist only needed ports)
- [ ] SSL/TLS certificates installed (if HTTPS required)
- [ ] Security headers configured in Nginx
- [ ] Regular security updates applied
- [ ] Database encryption enabled
- [ ] Regular security audits performed
- [ ] Incident response plan created
- [ ] Backups encrypted and secured

---

**Tip:** Print this checklist and keep it handy during deployments!

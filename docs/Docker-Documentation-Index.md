# Docker Documentation Index

Complete Docker deployment documentation for MythicalDash v3.

## 📚 Documentation Overview

This folder contains comprehensive Docker deployment documentation for MythicalDash. Start here to understand what's available and choose your path.

### Quick Navigation

**New to Docker?** → Start with [Getting Started](#getting-started)  
**Need to deploy?** → Use [Deployment Guides](#deployment-guides)  
**Something broken?** → Go to [Troubleshooting](#troubleshooting)  
**Quick reference?** → Check [Quick Reference](#quick-reference)

---

## 🚀 Getting Started

### For Development

```bash
cp .env.example .env
# Edit .env with your settings
docker compose -f docker-compose-dev.yml up -d
```

### For Production

```bash
cp .env.example .env
# Edit .env with production values (IMPORTANT!)
docker compose up -d
```

**Then check status:**
```bash
docker compose ps
```

All services should show `healthy` after 60-120 seconds.

---

## 📖 Documentation Files

### Core Guides

| Document | Purpose | Best For |
|----------|---------|----------|
| **[Docker-Deployment.md](Docker-Deployment.md)** | Complete deployment guide with all details | Comprehensive reference, in-depth learning |
| **[Docker-Deployment-Checklist.md](Docker-Deployment-Checklist.md)** | Step-by-step checklists | Following procedures systematically |
| **[Docker-Deployment-FAQ.md](Docker-Deployment-FAQ.md)** | Answers to common questions | Quick answers, decision making |
| **[Docker-Quick-Reference.md](Docker-Quick-Reference.md)** | Command reference and common tasks | Quick lookups, command syntax |
| **[Docker-Troubleshooting.md](Docker-Troubleshooting.md)** | Diagnosing and fixing issues | When something goes wrong |

### Configuration

| File | Purpose |
|------|---------|
| **[.env.example](../.env.example)** | Environment variable template |
| **[docker-compose.yml](../docker-compose.yml)** | Production compose file |
| **[docker-compose-dev.yml](../docker-compose-dev.yml)** | Development compose file |

---

## 🎯 Deployment Guides

### Development Deployment

**Files:** [Docker-Deployment.md - Development Setup](Docker-Deployment.md#development-setup)  
**Checklist:** [Docker-Deployment-Checklist.md - Dev Deployment](Docker-Deployment-Checklist.md#development-deployment-checklist)

Quick start:
```bash
docker compose -f docker-compose-dev.yml up -d
docker compose logs -f
```

### Production Deployment

**Files:** [Docker-Deployment.md - Production](Docker-Deployment.md#production-deployment)  
**Checklist:** [Docker-Deployment-Checklist.md - Production](Docker-Deployment-Checklist.md#production-deployment-checklist)

Steps:
1. Review [Pre-Deployment Checklist](Docker-Deployment-Checklist.md#pre-deployment-checklist)
2. Follow [Deployment Steps](Docker-Deployment.md#deployment-steps)
3. Verify with [Post-Deployment Validation](Docker-Deployment-Checklist.md#post-deployment-validation)

---

## 🔧 Common Tasks

### View Logs
```bash
docker compose logs -f backend
docker compose logs --tail=50
docker compose logs | grep error
```
**More:** [Docker-Quick-Reference.md - Viewing Logs](Docker-Quick-Reference.md#viewing-status--logs)

### Execute Commands
```bash
docker compose exec backend php cli help
docker compose exec mysql mariadb -u mythicaldash_v3 -p
```
**More:** [Docker-Quick-Reference.md - Executing Commands](Docker-Quick-Reference.md#executing-commands-in-containers)

### Backup Data
```bash
docker compose exec -T mysql mariadb-dump \
  -u mythicaldash_v3 -pmythicaldash_v3_password \
  mythicaldash_v3 > backup.sql
```
**More:** [Docker-Deployment.md - Backup & Restore](Docker-Deployment.md#backup-volumes)

### Update Services
```bash
docker compose pull
docker compose down
docker compose up -d
```
**More:** [Docker-Deployment.md - Update Procedure](Docker-Deployment.md#update-procedure)

---

## 🆘 Troubleshooting

### Quick Diagnosis

```bash
# Check status
docker compose ps

# View logs
docker compose logs

# Check resource usage
docker stats

# Test connectivity
docker compose exec backend ping mysql
```

**Detailed guide:** [Docker-Troubleshooting.md](Docker-Troubleshooting.md)

### Common Issues

| Issue | Solution |
|-------|----------|
| **Services won't start** | [Docker-Troubleshooting.md - Diagnostic Workflow](Docker-Troubleshooting.md#diagnostic-workflow) |
| **Health check failing** | [Docker-Troubleshooting.md - Health Checks](Docker-Troubleshooting.md#service-specific-issues) |
| **Database connection error** | [Docker-Troubleshooting.md - MySQL Issues](Docker-Troubleshooting.md#mysql-issues) |
| **Port already in use** | [Docker-Deployment-FAQ.md - Port Changes](Docker-Deployment-FAQ.md#can-i-change-the-port-4832) |
| **Memory issues** | [Docker-Troubleshooting.md - Memory](Docker-Troubleshooting.md#high-memory-usage) |

---

## 📋 Quick Reference

### Essential Commands

**Status & Monitoring**
```bash
docker compose ps                    # Check service status
docker compose logs -f               # View logs
docker stats                         # Resource usage
```

**Start & Stop**
```bash
docker compose up -d                 # Start services
docker compose down                  # Stop services
docker compose restart               # Restart all
docker compose restart SERVICE       # Restart specific service
```

**Access Containers**
```bash
docker compose exec backend bash     # Shell access
docker compose exec backend php ...  # Run PHP commands
docker compose exec mysql mariadb    # Connect to database
```

**More:** [Docker-Quick-Reference.md](Docker-Quick-Reference.md)

---

## 🔐 Security Checklist

Before going to production:

- [ ] All default passwords changed in `.env`
- [ ] Strong encryption key generated for `DATABASE_ENCRYPTION_KEY`
- [ ] `.env` file not committed to git
- [ ] Database and Redis not exposed externally
- [ ] Using reverse proxy for external access (if needed)
- [ ] SSL/TLS certificates configured (if HTTPS)
- [ ] Backup strategy tested
- [ ] Monitoring configured

**Full checklist:** [Docker-Deployment-Checklist.md - Security](Docker-Deployment-Checklist.md#security-hardening-checklist)

---

## 📊 Architecture

```
┌─────────────────────────────────────────────┐
│   mythicaldash_v3_network (bridge)          │
│                                              │
│  ┌─────────┐  ┌──────────┐  ┌───────────┐  │
│  │ MySQL   │  │  Redis   │  │ Backend   │  │
│  │ :3306   │  │ :6379    │  │ :9000     │  │
│  └─────────┘  └──────────┘  └───────────┘  │
│      ↑              ↑              ↑         │
│      └──────────────┴──────────────┘         │
│                    ↓                         │
│  ┌───────────────────────────────────────┐  │
│  │       Frontend (Nginx)                 │  │
│  │       :80 → :4832 (exposed)           │  │
│  └───────────────────────────────────────┘  │
│                                              │
└─────────────────────────────────────────────┘
```

**More:** [Docker-Deployment.md - Architecture](Docker-Deployment.md#network-architecture)

---

## 📁 Directory Structure

```
MythicalDash/
├── docs/
│   ├── Docker-Deployment.md              # Main guide
│   ├── Docker-Deployment-Checklist.md    # Checklists
│   ├── Docker-Deployment-FAQ.md          # FAQs
│   ├── Docker-Quick-Reference.md         # Commands
│   ├── Docker-Troubleshooting.md         # Troubleshooting
│   └── Docker-Documentation-Index.md     # This file
├── docker-compose.yml                    # Production
├── docker-compose-dev.yml                # Development
├── .env.example                          # Configuration template
├── backend/
│   ├── Dockerfile
│   ├── init.sh
│   └── ...
├── frontend/
│   ├── Dockerfile
│   └── ...
└── ...
```

---

## 🎓 Learning Path

### Beginner (Just starting)
1. Read [Quick Start](#quick-navigation) section above
2. Follow [Getting Started](#getting-started)
3. Refer to [Docker-Quick-Reference.md - Quick Deployment](Docker-Quick-Reference.md#quick-deployment)

### Intermediate (Deploying to staging)
1. Review [Pre-Deployment Checklist](Docker-Deployment-Checklist.md#pre-deployment-checklist)
2. Follow [Deployment Steps](Docker-Deployment.md#deployment-steps)
3. Bookmark [Docker-Quick-Reference.md](Docker-Quick-Reference.md)

### Advanced (Production deployment & troubleshooting)
1. Study [Production Deployment](Docker-Deployment.md#production-deployment)
2. Create backup strategy using [Backup Volumes](Docker-Deployment.md#backup-volumes)
3. Review [Advanced Configuration](Docker-Deployment.md#advanced-configuration)
4. Keep [Docker-Troubleshooting.md](Docker-Troubleshooting.md) handy

---

## 🔗 External Resources

- [Docker Documentation](https://docs.docker.com/)
- [Docker Compose Reference](https://docs.docker.com/compose/compose-file/)
- [MariaDB Documentation](https://mariadb.com/docs/)
- [Redis Documentation](https://redis.io/docs/)
- [Nginx Documentation](https://nginx.org/en/docs/)

---

## ✅ What's Documented

This Docker documentation covers:

- ✅ Quick start (dev & production)
- ✅ Complete deployment procedures
- ✅ Environment configuration
- ✅ Service architecture & networking
- ✅ Data persistence & backups
- ✅ Health checks & monitoring
- ✅ Common commands & tasks
- ✅ Troubleshooting & diagnostics
- ✅ Performance optimization
- ✅ Security hardening
- ✅ Update & rollback procedures
- ✅ Frequently asked questions

---

## 🚨 Emergency Procedures

### If something is wrong:

1. **Check status:** `docker compose ps`
2. **View logs:** `docker compose logs`
3. **Go to:** [Docker-Troubleshooting.md - Diagnostic Workflow](Docker-Troubleshooting.md#diagnostic-workflow)

### If data is lost:

**Have backup?**
```bash
docker compose exec -T mysql mariadb -u mythicaldash_v3 \
  -pmythicaldash_v3_password mythicaldash_v3 < backup.sql
```

**Read:** [Data Recovery](Docker-Troubleshooting.md#emergency-procedures)

---

## 📝 Notes

- **Always backup before making changes**
- **Test on staging before production**
- **Keep `.env` file secure and never commit it**
- **Monitor logs regularly for errors**
- **Update Docker and images regularly**

---

## 📞 Getting Help

1. Check the relevant guide (see [Documentation Files](#documentation-files))
2. Search [Docker-Deployment-FAQ.md](Docker-Deployment-FAQ.md)
3. Review [Docker-Troubleshooting.md](Docker-Troubleshooting.md)
4. Check [External Resources](#external-resources)
5. Open a GitHub issue with full error details and logs

---

**Last Updated:** 2026-07-09  
**Version:** 3.5.4-aurora  
**Maintained by:** MythicalDash Team

---

## Quick Links by Task

| Need to... | Go to... |
|-----------|----------|
| Start development environment | [Getting Started](#getting-started) → [Dev Deployment](Docker-Deployment-Checklist.md#development-deployment-checklist) |
| Deploy to production | [Production Deployment](Docker-Deployment.md#production-deployment) |
| Configure environment variables | [.env.example](../.env.example) → [Environment Variables](Docker-Deployment.md#environment-variables) |
| View/follow logs | [Quick Reference - Logs](Docker-Quick-Reference.md#viewing-status--logs) |
| Access container shell | [Quick Reference - Container Management](Docker-Quick-Reference.md#container-management) |
| Backup data | [Backup Volumes](Docker-Deployment.md#backup-volumes) |
| Restore from backup | [Backup & Restore](Docker-Deployment.md#backup-volumes) |
| Update to latest version | [Update Procedure](Docker-Deployment.md#update-procedure) |
| Troubleshoot issue | [Diagnostic Workflow](Docker-Troubleshooting.md#diagnostic-workflow) |
| Find a command | [Quick Reference](Docker-Quick-Reference.md) |
| Answer a question | [FAQ](Docker-Deployment-FAQ.md) |

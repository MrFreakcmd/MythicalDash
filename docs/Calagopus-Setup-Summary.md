# Calagopus Integration Setup Summary

Your Calagopus panel can now be connected to MythicalDash. Here's what was created:

## 📚 Documentation Created

1. **Calagopus-Integration-Guide.md** (Comprehensive)
   - Full setup instructions with all details
   - Network configuration & firewall rules
   - Troubleshooting for all common issues
   - Security considerations
   - Advanced configuration options
   - API endpoints reference

2. **Calagopus-QuickStart.md** (5-minute guide)
   - Step-by-step quick start
   - Essential commands reference
   - Quick troubleshooting table
   - Links to full documentation

## 🚀 Setup in 5 Steps

### 1. Get Calagopus API Key
On your Calagopus VPS:
- Log in as admin
- Settings → API (or Administration → API Keys)
- Create new API key with admin permissions
- Copy the key

### 2. Get Your Calagopus Panel URL
Example: `https://your-calagopus-panel.com`

### 3. Configure MythicalDash
```bash
docker compose exec backend php cli calagopus configure
# Enter URL and API key when prompted
```

### 4. Test Connection
```bash
docker compose exec backend php cli calagopus test
```

### 5. Switch to Calagopus
```bash
docker compose exec backend php cli calagopus switch
# Select: 2
```

## ✅ Verification Commands

```bash
# View current status
docker compose exec backend php cli calagopus status

# Debug if issues
docker compose exec backend php cli calagopus debug

# View logs
docker compose logs -f backend | grep -i calagopus
```

## 📋 Available Commands

| Command | Purpose |
|---------|---------|
| `calagopus configure` | Set up connection |
| `calagopus test` | Test if API works |
| `calagopus switch` | Switch between Pterodactyl/Calagopus |
| `calagopus status` | View current config |
| `calagopus debug` | Diagnose connection issues |

## 🎯 What Works

✅ Full Admin API integration
✅ Full Client API integration
✅ User management
✅ Server management
✅ File browser
✅ Database management
✅ Backups
✅ Schedules
✅ All resources (nodes, locations, eggs, nests, etc.)

## 🔐 Key Security Notes

- ✅ API key must have admin permissions
- ✅ Must use HTTPS (not HTTP)
- ✅ Calagopus must be publicly accessible from Docker host
- ✅ Firewall must allow port 443 between Docker host and Calagopus VPS
- ✅ Do not commit API keys to git

## 📍 File Locations

```
docs/
├── Calagopus-Integration-Guide.md    ← Full guide
└── Calagopus-QuickStart.md          ← Quick start

backend/app/Services/Calagopus/
├── Admin/
│   ├── CalagopusAdmin.php
│   └── Resources/ (7 resource classes)
├── Client/
│   ├── CalagopusClient.php
│   └── Resources/ (8 resource classes)
└── Exceptions/ (6 exception classes)

backend/app/Cli/Commands/
└── Calagopus.php (CLI commands)
```

## 🔗 Related Documentation

- [Docker Deployment Guide](Docker-Documentation-Index.md) - How to deploy MythicalDash
- [Docker Quick Reference](Docker-Quick-Reference.md) - Common Docker commands
- [Calagopus Integration Guide](Calagopus-Integration-Guide.md) - Detailed setup
- [Calagopus QuickStart](Calagopus-QuickStart.md) - 5-minute setup

## 🆘 Troubleshooting Quick Links

- **Connection refused** → Check URL is correct and public
- **401 Unauthorized** → Verify API key and permissions
- **SSL certificate failed** → Ensure Calagopus has valid HTTPS cert
- **Empty response** → Run `calagopus debug` command

---

**Ready to connect?** Start with: [Calagopus-QuickStart.md](Calagopus-QuickStart.md)

**Need detailed setup?** See: [Calagopus-Integration-Guide.md](Calagopus-Integration-Guide.md)

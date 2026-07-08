# Calagopus + MythicalDash Quick Start

**Goal:** Connect your Calagopus VPS panel to MythicalDash Docker deployment in 5 minutes.

## ⚡ Quick Start (5 minutes)

### Step 1: Get API Key from Calagopus (2 min)
On your Calagopus VPS:
1. Log in as admin
2. Go to **Settings** → **API** (or **Administration** → **API Keys**)
3. Create new API key with admin permissions
4. Copy the key

### Step 2: Configure MythicalDash (2 min)
```bash
# SSH to your Docker host and run:
docker compose exec backend php cli calagopus configure

# When prompted:
# URL: https://your-calagopus-panel.com
# API Key: [paste the key from step 1]
```

### Step 3: Test Connection (1 min)
```bash
docker compose exec backend php cli calagopus test
```

**Success looks like:**
```
Testing Calagopus connection...
✓ Connection successful!
Found X users in system.
```

### Step 4: Switch Panel Type
```bash
docker compose exec backend php cli calagopus switch
# Select: 2 (Calagopus)
```

### Step 5: Verify
```bash
docker compose exec backend php cli calagopus status
```

Done! ✅

---

## 📋 Commands Reference

```bash
# Configure connection
docker compose exec backend php cli calagopus configure

# Test if connected
docker compose exec backend php cli calagopus test

# Switch between Pterodactyl/Calagopus
docker compose exec backend php cli calagopus switch

# View current configuration
docker compose exec backend php cli calagopus status

# Debug connection issues
docker compose exec backend php cli calagopus debug

# View logs
docker compose logs -f backend | grep -i calagopus
```

---

## 🔍 Troubleshooting Quick Fixes

| Problem | Solution |
|---------|----------|
| "Connection refused" | Make sure Calagopus URL is correct and public: `curl https://your-calagopus-panel.com` |
| "401 Unauthorized" | Check API key is correct, regenerate in Calagopus if needed |
| "SSL certificate failed" | Ensure Calagopus has valid HTTPS certificate |
| "Empty response" | Run: `docker compose exec backend php cli calagopus debug` |

---

## 🔐 Security Checklist

- ✅ API key has admin permissions
- ✅ Using HTTPS (not HTTP)
- ✅ URL is correct (no typos)
- ✅ Firewall allows port 443 from Docker host to Calagopus VPS

---

## 📚 Full Documentation

For detailed setup, troubleshooting, and advanced configuration:
→ See: [Calagopus-Integration-Guide.md](Calagopus-Integration-Guide.md)

---

## What Works Now

✅ User management (import/sync from Calagopus)  
✅ Server management (view/control servers)  
✅ File browser (upload/download files)  
✅ Database management  
✅ Backups management  
✅ Schedules management  
✅ Account info  
✅ Locations, nodes, nests, eggs  
✅ All admin resources  

---

## Next Steps

1. Follow the Quick Start above (5 minutes)
2. Test with a user account
3. Create a server on Calagopus and verify it shows in MythicalDash
4. Consult full guide if issues arise

---

**Need help?** Check the full [Calagopus Integration Guide](Calagopus-Integration-Guide.md)

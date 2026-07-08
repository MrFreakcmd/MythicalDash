# Calagopus Integration Guide

Connect your Calagopus game panel to MythicalDash v3.

## Prerequisites

- ✅ MythicalDash deployed with Docker (backend running)
- ✅ Calagopus panel installed and running on your VPS
- ✅ Admin access to Calagopus panel
- ✅ API credentials from Calagopus

---

## Step 1: Generate Calagopus API Credentials

### On Your Calagopus VPS Panel

1. Log in to your Calagopus admin panel
2. Navigate to **Settings** → **API** or **Administration** → **API Keys**
3. Create a new API key with these permissions:
   - ✅ **Admin API Access** (required for full panel management)
   - ✅ **User Management**
   - ✅ **Server Management**
   - ✅ **Node Management**
   - ✅ **Location Management**

4. Copy the generated API key (you'll need this shortly)

**Note:** If Calagopus doesn't have a built-in API key generator, check if it uses token-based authentication. You may need to generate a personal API token.

---

## Step 2: Get Your Calagopus Panel URL

Determine your Calagopus panel's base URL:

```
Examples:
- https://panel.example.com
- https://games.yourdomain.com
- https://calagopus.yourvps.com:8080
```

**Important:** 
- Must be HTTPS for security
- Must be publicly accessible from MythicalDash Docker container
- Include the port if non-standard (443 is default)

---

## Step 3: Configure MythicalDash to Connect to Calagopus

### Method A: Using CLI Command (Recommended)

Access your MythicalDash backend container and run the configuration command:

```bash
# SSH into your Docker host or use Docker directly

# Execute the CLI command
docker compose exec backend php cli calagopus configure
```

Follow the interactive prompts:

```
=== Calagopus Configuration ===
Enter Calagopus Base URL (e.g., https://panel.example.com):
> https://your-calagopus-panel.com

Enter Calagopus API Key:
> your-api-key-here
```

**Output when successful:**
```
✓ Calagopus configuration saved!
```

### Method B: Direct Database Configuration

If CLI access isn't available, update the database directly:

```bash
# Connect to MySQL
docker compose exec mysql mariadb -u mythicaldash_v3 -p mythicaldash_v3

# Inside MySQL, run:
INSERT INTO config (key, value) VALUES 
  ('calagopus_base_url', 'https://your-calagopus-panel.com'),
  ('calagopus_api_key', 'your-api-key-here'),
  ('calagopus_enabled', 'true')
ON DUPLICATE KEY UPDATE value = VALUES(value);
```

---

## Step 4: Test the Connection

Verify your Calagopus connection is working:

```bash
docker compose exec backend php cli calagopus test
```

**Successful output:**
```
Testing Calagopus connection...
✓ Connection successful!
Found X users in system.
```

**If it fails:**
- Double-check the URL is correct and publicly accessible
- Verify the API key is valid
- Check firewall rules on Calagopus VPS (port 443 must be open)
- Review backend logs: `docker compose logs backend`

---

## Step 5: Switch Active Panel Type

Switch MythicalDash to use Calagopus instead of Pterodactyl:

```bash
docker compose exec backend php cli calagopus switch
```

Follow the prompts:

```
=== Switch Panel Type ===
1. Pterodactyl
2. Calagopus
Select panel (1-2):
> 2
```

**Output:**
```
✓ Active panel switched to CALAGOPUS!
```

---

## Step 6: Verify Configuration

Check the current panel status:

```bash
docker compose exec backend php cli calagopus status
```

**Expected output:**
```
=== Panel Configuration Status ===
Active Panel: CALAGOPUS
Calagopus URL: https://your-calagopus-panel.com
Calagopus API Key: ***your-last-8-chars
Calagopus Enabled: true
```

---

## Step 7: Debug (If Issues Occur)

Run the debug command for detailed diagnostics:

```bash
docker compose exec backend php cli calagopus debug
```

This will show:
- Detected active panel type
- Configuration status
- API connection status
- Any error messages

---

## Network Configuration

### Firewall Rules

Ensure these ports are open:

**From Docker Host to Calagopus VPS:**
- Port 443 (HTTPS) - Must be accessible from your Docker host
- Port 80 (HTTP) - Optional but recommended for redirects

**Test connectivity from Docker container:**
```bash
docker compose exec backend curl -I https://your-calagopus-panel.com
```

Expected response: `HTTP/1.1 200 OK` or similar

### Docker Network Communication

If MythicalDash and Calagopus are on the same network:

```bash
# Test DNS resolution
docker compose exec backend nslookup your-calagopus-panel.com

# Test connection
docker compose exec backend curl https://your-calagopus-panel.com/api/status
```

---

## Troubleshooting

### Connection Refused

**Problem:** "Connection refused" error

**Solutions:**
1. Verify Calagopus is running on VPS: `curl https://your-calagopus-panel.com`
2. Check firewall: `sudo ufw status` (Linux) or Windows Firewall rules
3. Verify DNS resolution: `nslookup your-calagopus-panel.com`
4. Check VPS security groups (if on cloud provider)

### Authentication Failed (401)

**Problem:** "401 Unauthorized" error

**Solutions:**
1. Verify API key is correct: `docker compose exec backend php cli calagopus status`
2. Regenerate API key in Calagopus panel
3. Ensure API key has admin permissions
4. Reconfigure: `docker compose exec backend php cli calagopus configure`

### Invalid Certificate

**Problem:** "SSL certificate verification failed"

**Solutions:**
1. Ensure Calagopus uses valid HTTPS certificate
2. Update certificate if expired
3. Verify certificate chain is complete

For development/testing only (NOT for production):
```bash
# Create .env entry to disable SSL verification (INSECURE!)
CALAGOPUS_VERIFY_SSL=false
```

### Empty Response

**Problem:** Connection successful but no data returned

**Solutions:**
1. Check Calagopus API is responding: `curl https://your-calagopus-panel.com/api/users`
2. Verify API key permissions include user listing
3. Ensure Calagopus has users in the system
4. Review Calagopus logs for API errors

---

## API Endpoints Used

MythicalDash communicates with Calagopus using these endpoints:

### Admin API
```
GET    /api/admin/users              - List users
GET    /api/admin/servers            - List servers
GET    /api/admin/nodes              - List nodes
GET    /api/admin/locations          - List locations
GET    /api/admin/eggs               - List eggs
GET    /api/admin/nests              - List nests
GET    /api/admin/database-hosts     - List database hosts
```

### Client API
```
GET    /api/client/account           - User account info
GET    /api/client/servers           - User's servers
GET    /api/client/files             - Server file browser
GET    /api/client/databases         - Server databases
GET    /api/client/backups           - Server backups
GET    /api/client/schedules         - Server schedules
```

**Note:** Ensure your Calagopus API key has access to these endpoints.

---

## Configuration Reference

### Environment Variables (Optional)

You can also set these in your `.env` file (if supported):

```env
# Calagopus Configuration
CALAGOPUS_BASE_URL=https://your-calagopus-panel.com
CALAGOPUS_API_KEY=your-api-key-here
CALAGOPUS_ENABLED=true
ACTIVE_PANEL_TYPE=calagopus

# SSL Verification (default: true)
CALAGOPUS_VERIFY_SSL=true

# Connection Timeout (seconds, default: 10)
CALAGOPUS_TIMEOUT=30
```

### Database Configuration Keys

Configuration is stored in the database with these keys:

```
calagopus_base_url          - Panel URL
calagopus_api_key           - API credentials
calagopus_enabled           - Enable/disable Calagopus
active_panel_type           - Currently active panel (pterodactyl/calagopus)
```

---

## Switching Between Panels

You can easily switch between Pterodactyl and Calagopus:

```bash
# View current panel
docker compose exec backend php cli calagopus status

# Switch to Calagopus
docker compose exec backend php cli calagopus switch
# Select: 2

# Switch back to Pterodactyl
docker compose exec backend php cli calagopus switch
# Select: 1
```

**Note:** Switching clears cached panel data. First load after switch may take slightly longer.

---

## Testing Integration

### Test Basic Connectivity

```bash
docker compose exec backend php cli calagopus test
```

### Test with Specific User

```bash
docker compose exec backend php cli calagopus debug
```

### Manual API Test

```bash
# From Docker container
docker compose exec backend bash

# Inside container:
curl -H "Authorization: Bearer YOUR_API_KEY" \
     -H "Accept: application/json" \
     https://your-calagopus-panel.com/api/admin/users?page=1
```

---

## Security Considerations

### API Key Management

✅ **Do:**
- Store API key securely
- Rotate API keys periodically
- Limit API key permissions to necessary scopes
- Use HTTPS only for API communication
- Monitor API usage for suspicious activity

❌ **Don't:**
- Commit API keys to version control
- Share API keys via email or chat
- Use overly permissive API key scopes
- Use HTTP (non-HTTPS) URLs

### Network Security

✅ **Recommended:**
- Use VPN/private network for API communication
- Restrict API access by IP address (if supported by Calagopus)
- Enable API rate limiting
- Use firewall rules to allow only necessary ports
- Monitor incoming API connections

### Database Security

The configuration is stored encrypted in the database:
- Database password should be strong
- Database should not be exposed externally
- Regular backups recommended

---

## Monitoring & Maintenance

### Regular Checks

Daily:
```bash
docker compose exec backend php cli calagopus status
```

Weekly:
```bash
docker compose exec backend php cli calagopus test
```

### View Connection Logs

```bash
# Recent logs
docker compose logs backend --tail=100 | grep -i calagopus

# Follow logs in real-time
docker compose logs -f backend | grep -i calagopus
```

### Performance Monitoring

Monitor API response times:

```bash
# Check Docker stats
docker stats mythicaldash_v3_backend

# If slow, check Calagopus VPS
ssh user@your-vps-ip
top  # View CPU/Memory usage
```

---

## Rollback to Pterodactyl

If you need to revert:

```bash
# Switch back to Pterodactyl
docker compose exec backend php cli calagopus switch
# Select: 1

# Verify
docker compose exec backend php cli calagopus status
```

---

## Advanced Configuration

### Custom Headers

If your Calagopus requires custom headers, you can modify the client classes:

File: `backend/app/Services/Calagopus/Admin/CalagopusAdmin.php`

```php
'headers' => [
    'Authorization' => 'Bearer ' . $this->apiKey,
    'Accept' => 'application/json',
    'Content-Type' => 'application/json',
    'X-Custom-Header' => 'value',  // Add custom headers here
],
```

### Proxy Configuration

If you need to route through a proxy:

```php
$this->httpClient = new Client([
    'base_uri' => $this->baseUrl,
    'proxy' => 'http://proxy.example.com:8080',
    'headers' => [ /* ... */ ],
]);
```

---

## Support & Documentation

- **Calagopus Docs**: Check your Calagopus installation for API documentation
- **MythicalDash GitHub**: https://github.com/mrfreakcmd/MythicalDash
- **Issue Tracking**: Report integration issues on GitHub

---

## Quick Reference Commands

```bash
# Configure connection
docker compose exec backend php cli calagopus configure

# Test connection
docker compose exec backend php cli calagopus test

# Switch active panel
docker compose exec backend php cli calagopus switch

# View status
docker compose exec backend php cli calagopus status

# Debug issues
docker compose exec backend php cli calagopus debug

# View backend logs
docker compose logs -f backend

# Connect to backend shell
docker compose exec backend bash

# Test connectivity from container
docker compose exec backend curl https://your-calagopus-panel.com
```

---

**Version:** MythicalDash 3.5.4-aurora  
**Last Updated:** 2026-07-09  
**Calagopus Support:** Full integration with Admin & Client APIs

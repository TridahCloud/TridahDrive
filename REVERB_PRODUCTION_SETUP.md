# Laravel Reverb Production Setup Guide

## Overview
This guide covers the changes needed to deploy Laravel Reverb to production.

## Key Differences from Development

### 1. Environment Variables

In your production `.env` file, set these variables:

```env
# Reverb Configuration
REVERB_APP_ID=your_app_id
REVERB_APP_KEY=your_app_key
REVERB_APP_SECRET=your_app_secret

# Reverb Server Configuration (where Reverb server runs)
REVERB_SERVER_HOST=0.0.0.0  # Listen on all interfaces
REVERB_SERVER_PORT=8080      # Port for WebSocket connections

# Reverb Client Configuration (what clients connect to)
REVERB_HOST=your-domain.com  # Your production domain (NOT host.docker.internal)
REVERB_PORT=8080            # Port for WebSocket connections (or 443 if using WSS)
REVERB_SCHEME=https         # Use https in production
REVERB_VERIFY_SSL=true      # Verify SSL certificates in production

# Broadcasting
BROADCAST_CONNECTION=reverb
```

### 2. Important Notes

**REVERB_HOST vs REVERB_SERVER_HOST:**
- `REVERB_SERVER_HOST`: Where the Reverb server listens (usually `0.0.0.0`)
- `REVERB_HOST`: The hostname clients (browsers) connect to (your domain)

**Docker vs Non-Docker:**
- **Development (Docker)**: Uses `host.docker.internal` so Laravel in Docker can reach Reverb on host
- **Production**: Use your actual domain name (e.g., `your-domain.com` or `reverb.your-domain.com`)

### 3. Running Reverb in Production

You need to run the Reverb server as a persistent service. Options:

**Option A: Supervisor (Recommended)**
```ini
[program:reverb]
process_name=%(program_name)s
command=php /path/to/your/app/artisan reverb:start
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/path/to/logs/reverb.log
```

**Option B: Systemd Service**
```ini
[Unit]
Description=Laravel Reverb Server
After=network.target

[Service]
Type=simple
User=www-data
WorkingDirectory=/path/to/your/app
ExecStart=/usr/bin/php artisan reverb:start
Restart=always

[Install]
WantedBy=multi-user.target
```

**Option C: Process Manager (PM2)**
```bash
pm2 start "php artisan reverb:start" --name reverb
```

### 4. Reverse Proxy Configuration

If using Nginx, configure it to proxy WebSocket connections:

```nginx
location /app {
    proxy_pass http://127.0.0.1:8080;
    proxy_http_version 1.1;
    proxy_set_header Upgrade $http_upgrade;
    proxy_set_header Connection "Upgrade";
    proxy_set_header Host $host;
    proxy_set_header X-Real-IP $remote_addr;
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    proxy_set_header X-Forwarded-Proto $scheme;
}
```

### 5. SSL/TLS Configuration

For production with HTTPS:
- Set `REVERB_SCHEME=https`
- Set `REVERB_PORT=443` (or your WSS port)
- Ensure SSL certificates are properly configured
- Set `REVERB_VERIFY_SSL=true` (or configure proper SSL certificates)

### 6. Firewall Rules

Ensure your firewall allows:
- Port 8080 (or your chosen port) for WebSocket connections
- HTTP/HTTPS access to your Laravel application

### 7. Testing Production Setup

1. Start the Reverb server: `php artisan reverb:start`
2. Check if it's listening: `netstat -tuln | grep 8080`
3. Test WebSocket connection from browser console
4. Test broadcasting by creating/updating a task

### 8. Monitoring

- Monitor Reverb server logs
- Set up process monitoring (Supervisor/PM2 will auto-restart)
- Monitor WebSocket connection counts
- Check Laravel logs for broadcasting errors

## Summary

**Required Changes for Production:**
1. ✅ Set `REVERB_HOST` to your production domain (not `host.docker.internal`)
2. ✅ Set `REVERB_SCHEME=https` for HTTPS
3. ✅ Set `REVERB_VERIFY_SSL=true` for SSL verification
4. ✅ Run Reverb server as a persistent service (Supervisor/PM2/Systemd)
5. ✅ Configure reverse proxy (Nginx/Apache) for WebSocket connections
6. ✅ Open firewall ports for WebSocket connections
7. ✅ Disable logging in production (already configured to auto-disable)

The configuration files have been updated to automatically use appropriate defaults based on `APP_ENV`, but you should still explicitly set these in your production `.env` file.


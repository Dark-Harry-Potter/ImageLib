#!/bin/bash
# cron/setup.sh – Cron Job Setup Script

echo "Setting up ImageLib cron jobs..."

# Add cron job for daily badge check at 2 AM
(crontab -l 2>/dev/null; echo "0 2 * * * php $(pwd)/cron/check_badges.php >> $(pwd)/logs/cron.log 2>&1") | crontab -

echo "✅ Cron job added successfully!"
echo "📋 To verify: crontab -l"
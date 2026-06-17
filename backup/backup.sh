#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="$(dirname "$0")/backups"
DB_NAME="${DB_NAME:-maternite}"
DB_USER="${DB_USER:-root}"
DB_PASS="${DB_PASS:-}"

echo "[$DATE] Démarrage backup..."
mysqldump -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" > "$BACKUP_DIR/db_$DATE.sql"
tar -czf "$BACKUP_DIR/files_$DATE.tar.gz" ../frontend/ ../api/
find "$BACKUP_DIR" -name "*.sql" -mtime +30 -delete
echo "[$DATE] Backup terminé : $BACKUP_DIR"

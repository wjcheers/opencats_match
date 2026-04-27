#!/usr/bin/env bash
set -euo pipefail

DB_USER="${DB_USER:-cats}"
DB_NAME_CATS="${DB_NAME_CATS:-cats}"
DB_NAME_OFFLINE="${DB_NAME_OFFLINE:-offline}"
OFFLINE_ATTACHMENTS="${OFFLINE_ATTACHMENTS:-/home/iliilearn/htdocs/offline/attachments}"
CATS_ATTACHMENTS="${CATS_ATTACHMENTS:-/home/iliilearn/htdocs/cats/attachments}"
DRY_RUN=0

if [ "${1:-}" = "--dry-run" ]; then
  DRY_RUN=1
fi

MISSING_CSV="offline_attachment_copy_missing.csv"
DONE_CSV="offline_attachment_copy_done.csv"
: > "$MISSING_CSV"
: > "$DONE_CSV"
printf 'offline_attachment_id,offline_candidate_id,target_cats_candidate_id,source,target\n' >> "$MISSING_CSV"
printf 'offline_attachment_id,offline_candidate_id,target_cats_candidate_id,source,target\n' >> "$DONE_CSV"

QUERY="
SELECT
  a.attachment_id,
  a.data_item_id,
  m.cats_candidate_id,
  CONCAT(a.directory_name, a.stored_filename) AS source_rel,
  CONCAT('site_1/offline_migration/', a.attachment_id, '/', a.stored_filename) AS target_rel
FROM ${DB_NAME_OFFLINE}.attachment a
INNER JOIN ${DB_NAME_CATS}.migration_candidate_id_map m
  ON a.data_item_id = m.offline_candidate_id
WHERE a.data_item_type = 100
AND m.action IN ('create', 'merge')
ORDER BY a.attachment_id;
"

QUERY_OUTPUT="$(mktemp)"
trap 'rm -f "$QUERY_OUTPUT"' EXIT

mysql -u "$DB_USER" -p --batch --raw --skip-column-names -e "$QUERY" > "$QUERY_OUTPUT"

while IFS=$'\t' read -r attachment_id offline_candidate_id cats_candidate_id source_rel target_rel; do
  [ -n "${attachment_id:-}" ] || continue
  source_path="${OFFLINE_ATTACHMENTS}/${source_rel}"
  target_path="${CATS_ATTACHMENTS}/${target_rel}"

  if [ ! -f "$source_path" ]; then
    printf '%s,%s,%s,"%s","%s"\n' "$attachment_id" "$offline_candidate_id" "$cats_candidate_id" "$source_path" "$target_path" >> "$MISSING_CSV"
    continue
  fi

  printf '%s,%s,%s,"%s","%s"\n' "$attachment_id" "$offline_candidate_id" "$cats_candidate_id" "$source_path" "$target_path" >> "$DONE_CSV"
  if [ "$DRY_RUN" -eq 0 ]; then
    mkdir -p "$(dirname "$target_path")"
    cp -p "$source_path" "$target_path"
  fi
done < "$QUERY_OUTPUT"

echo "Done CSV: $DONE_CSV"
echo "Missing CSV: $MISSING_CSV"
echo "Dry run: $DRY_RUN"

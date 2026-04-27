# Offline to CATS Migration

這組檔案是給正式站使用的 migration 腳本。

正式站假設：

- 目標資料庫：`cats`
- 來源資料庫：`offline`
- CATS 附件目錄：`/home/iliilearn/htdocs/cats/attachments`
- Offline 附件目錄：`/home/iliilearn/htdocs/offline/attachments`
- 目標建立者 / owner：`cats.user.user_id = 1`，也就是 Administrator

## 執行順序

請在正式機專案目錄執行，例如：

```bash
cd /home/iliilearn/htdocs/cats
```

先確認資料庫已經有 `cats` 和 `offline`：

```bash
mysql -uUSER -p -e "SHOW DATABASES LIKE 'cats'; SHOW DATABASES LIKE 'offline';"
```

### 1. 建立 staging 與 migration plan

```bash
mysql -uUSER -p < scripts/offline_migration/01_stage_and_plan.sql
```

檢查計畫：

```bash
mysql -uUSER -p -e "SELECT action, COUNT(*) FROM cats.migration_candidate_plan GROUP BY action;"
```

預期結果：

```text
create 416
merge  283
skip     1
```

### 2. 寫入 candidate 與 notes

這一步會：

- 建立 candidate 備份表
- 新增 416 筆 candidate
- 合併 283 筆 notes / 空欄位補值
- 跳過 offline candidate 704

```bash
mysql -uUSER -p < scripts/offline_migration/02_migrate_candidates.sql
```

檢查：

```bash
mysql -uUSER -p -e "SELECT action, COUNT(*) FROM cats.migration_candidate_id_map GROUP BY action;"
```

### 3. 複製附件實體檔

先 dry-run：

```bash
bash scripts/offline_migration/copy_attachments.sh --dry-run
```

確認沒問題再正式複製：

```bash
bash scripts/offline_migration/copy_attachments.sh
```

複製後會產生：

```text
offline_attachment_copy_missing.csv
offline_attachment_copy_done.csv
```

### 4. 寫入 attachment rows

確認附件實體檔已複製後再執行：

```bash
mysql -uUSER -p < scripts/offline_migration/03_import_attachments.sql
```

這一步會：

- 建立 attachment 備份表
- 將 offline candidate attachments 寫入 cats attachment table
- 使用新的目錄格式避免撞到既有附件：

```text
site_1/offline_migration/{offline_attachment_id}/
```

## 備份表

腳本會建立：

```text
cats.cats_backup_candidate_before_offline_migration_20260427
cats.cats_backup_attachment_before_offline_migration_20260427
```

## 注意

- 這組腳本不會刪除 offline 原始資料。
- `offline_candidate_id = 704` 只會 skip，不會刪除。
- 若附件 dry-run 顯示大量 missing，請先確認 `/home/iliilearn/htdocs/offline/attachments` 是否已完整同步。

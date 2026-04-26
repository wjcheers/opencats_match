# CATS ATS 功能強化 Roadmap

> 最後更新：2026-04-26
> 優先級：P0（立即）> P1（本季）> P2（下季）> P3（長期）

---

## 優先級總覽

| 優先 | 功能 | 狀態 |
|------|------|------|
| P0 | Tag / Label 標籤系統 | ⬜ 待辦 |
| P0 | SLA 超時自動提醒 | ⬜ 待辦 |
| P0 | 重複候選人偵測 | ⬜ 待辦 |
| P1 | AI 候選人 × JD 媒合評分 | ⬜ 待辦 |
| P1 | Kanban 看板視圖 | ⬜ 待辦 |
| P1 | LINE Notify 整合 | ⬜ 待辦 |
| P1 | 漏斗轉換率報表 | ⬜ 待辦 |
| P1 | AI 面試問題生成 | ⬜ 待辦 |
| P2 | Recruiter 個人 KPI Dashboard | ⬜ 待辦 |
| P2 | 多輪面試追蹤 | ⬜ 待辦 |
| P2 | Client Portal（唯讀客戶視圖） | ⬜ 待辦 |
| P2 | 個資同意管理（內建） | ⬜ 待辦 |
| P2 | Time-to-Fill / Time-to-Hire 報表 | ⬜ 待辦 |
| P2 | Offer 條件記錄 | ⬜ 待辦 |
| P2 | 候選人來源效益分析 | ⬜ 待辦 |
| P3 | Mobile-Responsive UI | ⬜ 待辦 |
| P3 | BD Pipeline（業務開發） | ⬜ 待辦 |
| P3 | 全域快速搜尋（Ctrl+K） | ⬜ 待辦 |
| P3 | JD AI 自動生成輔助 | ⬜ 待辦 |
| P3 | Email 開信率追蹤 | ⬜ 待辦 |

---

## P0 — 立即

### ⬜ Tag / Label 標籤系統
- **目標**：候選人可貼多個 tag（如「高潛力」「被動求職」「優先推薦」「客戶指定」），可依 tag 篩選/批次操作
- **實作點**：
  - 新增 `candidate_tag` 資料表（tag_id, site_id, name, color）
  - 新增 `candidate_tag_mapping` 資料表（candidate_id, tag_id）
  - `lib/Candidates.php` 加 tag CRUD methods
  - `modules/candidates/Show.tpl` 加 tag UI
  - `modules/candidates/dataGrids.php` 加 tag filter
- **預估工時**：2–3 天

---

### ⬜ SLA 超時自動提醒
- **目標**：Pipeline 狀態停留超過設定天數時，在 Dashboard 高亮提示 recruiter，可選 email 通知
- **實作點**：
  - `candidate_joborder` 表加 `last_status_date` 欄位
  - 新增 `modules/queue/tasks/CheckPipelineSLA.php`（每日執行）
  - `lib/Dashboard.php` 加 overdue pipeline widget
  - Settings 頁加每個狀態的 SLA 天數設定
- **預估工時**：2–3 天

---

### ⬜ 重複候選人偵測
- **目標**：儲存候選人時，偵測 name / phone / email 相似度，跳出「可能重複」警告
- **實作點**：
  - `lib/Candidates.php` 新增 `findPotentialDuplicates($name, $phone, $email)`
  - `modules/candidates/CandidatesUI.php` 的 `onAdd()` 中呼叫
  - 前端跳出 modal 列出疑似重複的候選人，讓 recruiter 決定合併或繼續
- **預估工時**：2–3 天

---

## P1 — 本季

### ⬜ AI 候選人 × JD 媒合評分
- **目標**：候選人加入 pipeline 時，AI 自動評估 fit score（0–100），顯示在 pipeline 列表
- **實作點**：
  - `lib/AIResumeParser.php` 新增 `scoreCandidate($candidateID, $jobOrderID)` method
  - `candidate_joborder` 表加 `ai_fit_score` 欄位
  - `lib/Pipelines.php` 的 `add()` 後觸發評分（async queue）
  - Pipeline DataGrid 加 fit score 欄位
- **預估工時**：3–5 天

---

### ⬜ Kanban 看板視圖
- **目標**：Job Order Show 頁的 pipeline 支援 Kanban drag-and-drop，可直接拖曳更新狀態
- **實作點**：
  - 引入輕量 sortable.js（或 Dragula）
  - `modules/joborders/Show.tpl` 加 Kanban view toggle
  - 後端 AJAX 包裝既有的 `Pipelines::setStatus()`
- **預估工時**：3–5 天

---

### ⬜ LINE Notify 整合
- **目標**：重要事件（SLA 超時、面試提醒、pipeline 狀態變更）推送 LINE 通知給 recruiter
- **實作點**：
  - 新增 `lib/LineNotify.php`（封裝 LINE Notify API）
  - `config.local.php` 加 `LINE_NOTIFY_TOKEN`
  - User settings 加 LINE Notify token 設定
  - Pipeline 狀態變更 hook 觸發通知
- **預估工時**：3–4 天

---

### ⬜ 漏斗轉換率報表
- **目標**：顯示完整招募漏斗（聯繫 → 回覆 → 送件 → 面試 → Offer → 成交）各階段轉換率
- **實作點**：
  - `lib/Statistics.php` 新增 `getFunnelData($period, $userID)`
  - `modules/reports/ReportsUI.php` 新增 `showFunnelReport()`
  - 新增 `modules/reports/FunnelReport.tpl`
- **預估工時**：3–4 天

---

### ⬜ AI 面試問題生成
- **目標**：在 interviewing 狀態旁加「AI 生成面試問題」按鈕，依候選人背景 + JD 生成結構化面試題
- **實作點**：
  - `lib/AIResumeParser.php` 新增 `generateInterviewQuestions($resumeText, $jdText)`
  - `modules/candidates/Show.tpl` 加按鈕 + modal
  - AJAX endpoint 回傳問題列表，可複製或存成 note
- **預估工時**：2–3 天

---

## P2 — 下季

### ⬜ Recruiter 個人 KPI Dashboard
- **目標**：每位 recruiter 的儀表板顯示本月目標 vs. 實際（聯繫數/送件數/面試數/成交數）+ 趨勢圖
- **實作點**：
  - `lib/Statistics.php` 擴充個人 KPI query
  - `lib/Dashboard.php` 新增 KPI widget
  - Settings 加 KPI 目標設定（月目標值）
- **預估工時**：5–7 天

---

### ⬜ 多輪面試追蹤
- **目標**：Interviewing 細分「一面/二面/三面/HR 面/CEO 面」，每輪記錄時間、面試官、評分、feedback
- **實作點**：
  - 新增 `interview_round` 表（candidate_joborder_id, round_number, interviewer, scheduled_at, result, notes）
  - `modules/candidates/Show.tpl` 加多輪面試 UI
  - 報表加面試輪次分析
- **預估工時**：5–7 天

---

### ⬜ Client Portal（唯讀客戶視圖）
- **目標**：產生 token URL 讓客戶無需登入即可查看自己 JD 的 pipeline 狀態和候選人摘要
- **實作點**：
  - 新增 `client_portal_token` 表
  - 新增 `modules/portal/` 模組（唯讀視圖，隱藏薪資等敏感欄位）
  - Job Order Show 頁加「產生客戶連結」功能
- **預估工時**：7–10 天

---

### ⬜ 個資同意管理（內建）
- **目標**：內建個資同意記錄，取代目前的外部 Google Form 連結
- **實作點**：
  - 新增 `candidate_consent` 表（candidate_id, consent_type, agreed_at, method, ip_address）
  - `modules/candidates/Show.tpl` 加同意狀態顯示與操作
  - 支援 email 發送同意確認信，記錄回覆
- **預估工時**：5–7 天

---

### ⬜ Time-to-Fill / Time-to-Hire 報表
- **目標**：統計從 JO 開單到成交的平均天數（Time-to-Fill）、送件到成交天數（Time-to-Hire），依 Function/Client 分組
- **實作點**：
  - `lib/Statistics.php` 新增相應 query
  - `modules/reports/ReportsUI.php` 新增 `showTimeToFillReport()`
- **預估工時**：3–4 天

---

### ⬜ Offer 條件記錄
- **目標**：Offered 狀態時記錄薪資/Title/開始日期/福利，追蹤候選人回覆時間，統計 offer acceptance rate
- **實作點**：
  - 新增 `offer_detail` 表（candidate_joborder_id, offered_salary, title, start_date, benefits, responded_at, accepted）
  - `modules/candidates/Show.tpl` 的 Offered 狀態加 offer 表單
- **預估工時**：3–4 天

---

### ⬜ 候選人來源效益分析
- **目標**：各來源（LinkedIn/104/CakeResume/Referral）的候選人數、面試率、成交率
- **實作點**：
  - `modules/reports/ReportsUI.php` 新增 `showSourceEffectivenessReport()`
  - 新增 `modules/reports/SourceReport.tpl`
- **預估工時**：2–3 天

---

## P3 — 長期

### ⬜ Mobile-Responsive UI
- **目標**：關鍵頁面（候選人 Show、pipeline 操作、activity 新增）支援手機瀏覽
- **實作點**：
  - 加 viewport meta tag
  - 對關鍵 tpl 加 responsive CSS media queries
  - 考慮長期遷移到現代前端框架
- **預估工時**：10+ 天

---

### ⬜ BD Pipeline（業務開發）
- **目標**：追蹤潛在客戶 BD 進度（初次接觸 → 拜訪 → 報價 → 簽約），與 Companies 整合
- **實作點**：
  - `company` 表新增 `bd_status` 欄位
  - 新增 BD 專用 activity type
  - Dashboard 加 BD pipeline widget
- **預估工時**：5–7 天

---

### ⬜ 全域快速搜尋（Ctrl+K）
- **目標**：按 Ctrl+K 呼出全域搜尋，同時搜尋候選人/JD/公司，結果即時顯示
- **實作點**：
  - 前端 JS 搜尋 modal
  - 後端 unified search AJAX endpoint（合併三個搜尋 class 結果）
- **預估工時**：3–5 天

---

### ⬜ JD AI 自動生成輔助
- **目標**：新增 Job Order 頁面加「AI 草擬 JD」按鈕，輸入職稱/技能要求後 AI 生成完整 job description
- **實作點**：
  - `lib/AIResumeParser.php` 新增 `generateJobDescription($title, $skills, $requirements)`
  - `modules/joborders/` 加 AJAX endpoint + 前端按鈕
- **預估工時**：2–3 天

---

### ⬜ Email 開信率追蹤
- **目標**：批次發信加 tracking pixel，記錄每封 email 的開信時間，在 activity 中顯示
- **實作點**：
  - `lib/Mailer.php` 加 tracking pixel 注入
  - 新增 `email_tracking` 表（email_id, opened_at, ip_address）
  - 新增 tracking endpoint（1x1 gif）
- **預估工時**：3–4 天

---

## 完成記錄

> 完成的項目請將 `⬜` 改為 `✅` 並填入完成日期

| 功能 | 完成日期 | PR / Commit |
|------|---------|------------|
| （尚無） | — | — |

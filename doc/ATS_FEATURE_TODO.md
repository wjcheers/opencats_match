# ATS 功能強化待辦事項

本文件整理目前系統作為 ATS 後續可加強的功能方向，方便之後逐項檢視、評估與實作。

## 第一階段：候選人匯入與 AI 解析

- [ ] 建立 AI 解析結果審核頁，讓使用者可逐欄位確認、接受或忽略 AI 結果。
- [ ] 在 AI 解析結果中顯示欄位來源、信心程度與原始文字片段。
- [ ] 新增候選人重複偵測，依 Email、電話、LinkedIn、GitHub、CakeResume、姓名加公司判斷疑似重複。
- [ ] 發現疑似重複候選人時，提供合併、更新履歷或仍建立新資料的選項。
- [ ] 建立標準化 Job Level 欄位，例如 Intern、Junior、Mid、Senior、Staff、Lead、Manager、Director、VP、C-Level。
- [ ] 建立技能標準化機制，支援中英文技能別名、去重與 canonical skill。
- [ ] 強化 PDF / DOCX 解析失敗訊息，顯示明確原因，例如檔案格式錯誤、套件不存在、權限不足或文字抽取失敗。
- [ ] 在 AI 解析紀錄中保存 parser 錯誤、轉檔輸出、使用者、時間與檔案資訊。
- [ ] 支援大量履歷匯入後排隊解析，顯示進度、成功率、失敗原因與重試操作。

## 第二階段：候選人資料模型

- [ ] 將技能、工作經歷、學歷、語言、證照、作品連結等資訊拆成結構化資料。
- [ ] 建立候選人履歷版本紀錄，可查看每次上傳與 AI 解析差異。
- [ ] 附件增加分類，例如 Resume、Jecho Report、Portfolio、Certificate、Other。
- [ ] 候選人頁面增加完整 timeline，集中顯示履歷更新、活動、Pipeline 變更、Email 與備註。
- [ ] 增加候選人資料完整度評分，提示缺少電話、Email、技能、最近職稱等資訊。

## 第三階段：搜尋與媒合

- [ ] 建立候選人與職缺的 AI Matching Score。
- [ ] Matching 結果需顯示原因，例如符合技能、缺少技能、年資差距、地點不符、語言不符。
- [ ] 支援依 Job Level、Function、技能、年資、地點、來源、最後聯絡時間進階篩選。
- [ ] 支援 saved search，讓 recruiter 保存常用搜尋條件。
- [ ] 建立 stale candidate 標記，提醒太久未聯絡或資料過舊的候選人。
- [ ] 建立 source quality 評估，分析不同來源的候選人轉換率。

## 第四階段：Pipeline 與工作流程

- [ ] 將 Pipeline 視覺化為 Kanban 看板，可拖拉候選人階段。
- [ ] 支援每個職缺自訂 Pipeline 階段。
- [ ] 顯示候選人在每個階段停留天數。
- [ ] 建立 Pipeline SLA / Aging 提醒，例如 Submitted 超過指定天數未回覆。
- [ ] 支援批次移動候選人階段、批次新增活動、批次寄信。
- [ ] 強化面試排程，記錄面試時間、面試官、面試形式與會議連結。
- [ ] 建立面試回饋表與評分機制。
- [ ] 建立 Offer / Placement 流程，記錄薪資、到職日、合約與核准狀態。

## 第五階段：客戶與職缺管理

- [ ] 強化 Job Order 欄位，拆分 must-have、nice-to-have、薪資範圍、地點、遠端條件、語言需求。
- [ ] 建立職缺 scorecard，讓候選人評估標準一致。
- [ ] 建立客戶 shortlist 頁面，讓客戶查看候選人摘要並回饋。
- [ ] 客戶 shortlist 支援喜歡、不適合、留言與面試邀請。
- [ ] 建立職缺健康度指標，例如開缺天數、候選人數、提交數、面試數、Offer 數。
- [ ] 建立 recruiter 工作負載檢視，顯示每人負責職缺、候選人與待辦事項。

## 第六階段：溝通與提醒

- [ ] 強化 Email template，支援更多變數與不同場景模板。
- [ ] 建立 Email queue，支援寄送失敗重試與錯誤紀錄。
- [ ] 將 Email、活動與候選人 timeline 串接。
- [ ] 建立 follow-up reminder，提醒 recruiter 聯絡候選人或客戶。
- [ ] 評估是否支援 SMS、LINE、WhatsApp 等通訊整合。
- [ ] 加入候選人同意、退訂與資料保留設定。

## 第七階段：報表與管理儀表板

- [ ] 建立招募漏斗報表，追蹤來源、提交、面試、Offer、Placement。
- [ ] 建立 time-to-submit、time-to-interview、time-to-fill 指標。
- [ ] 建立 recruiter productivity 報表。
- [ ] 建立 AI 使用成本報表，包含解析次數、成功率、失敗率、token 與成本。
- [ ] 建立職缺 aging 報表，找出長期未結案職缺。
- [ ] 建立自訂報表產生器，支援篩選、欄位選擇與匯出。

## 第八階段：安全、權限與部署

- [ ] 將正式機差異設定集中在 config.local.php 或環境變數。
- [ ] 確認 config.local.php 不會被打包或提交到版本控制。
- [ ] Install Wizard 增加 upload、attachments、parser path、PHP upload limit 等部署檢查。
- [ ] 強化上傳安全，檢查副檔名、MIME type 與實際檔案內容。
- [ ] 加入檔案大小限制、解析 timeout 與錯誤處理。
- [ ] 建立 audit log，記錄新增、修改、刪除、下載履歷、AI 解析等重要動作。
- [ ] 檢視角色權限矩陣，確保不同使用者只能存取需要的資料。
- [ ] 強化 session cookie、CSRF token 與表單安全。
- [ ] 建立 queue / cron health check 頁面，方便正式機排除問題。

## 第九階段：使用者體驗

- [ ] 改善 Candidate Add / Edit 表單，加入即時驗證與更清楚的錯誤訊息。
- [ ] 支援草稿或 autosave，避免新增候選人時資料遺失。
- [ ] 統一候選人、公司、職缺附件上傳介面。
- [ ] 改善履歷預覽體驗，支援 markdown、PDF、DOCX 轉文字預覽。
- [ ] 增加常用操作快捷入口，例如新增活動、加到 Pipeline、寄信、安排面試。
- [ ] 改善手機或小螢幕瀏覽體驗。

## 第十階段：工程重構與長期維護

- [ ] 將 AI resume parser 抽成獨立服務類別。
- [ ] 將附件文字抽取抽成 AttachmentTextExtractor。
- [ ] 將候選人欄位 mapping 抽成 CandidateFieldMapper。
- [ ] 將重複候選人判斷抽成 CandidateDuplicateDetector。
- [ ] 將搜尋與媒合抽成 Search / Matching service。
- [ ] 規劃 PHP 版本升級路線，逐步處理 deprecated 語法與相容性。
- [ ] 導入 Composer autoload，減少手動 include。
- [ ] 增加核心流程測試，例如新增候選人、上傳履歷、AI 解析、Pipeline 移動。
- [ ] 資料庫逐步改用 InnoDB 與 utf8mb4。
- [ ] 建立可重複執行的 migration 流程，避免正式機手動修改 SQL。

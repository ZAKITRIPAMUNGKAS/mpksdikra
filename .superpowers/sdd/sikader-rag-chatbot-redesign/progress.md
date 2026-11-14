# SDD ledger — plan: docs/superpowers/plans/2026-11-07-sikader-rag-chatbot-redesign.md

Pre-flight: Task 1 produces `window.smartEnrollChatbot` and text/token modules; Tasks 2–4 consume the state methods and endpoint configuration. Names and contracts agree with the approved specification.

Pre-flight: Task 2 produces normalized messages and events consumed by Task 3 component renderers and Task 4 feedback transport. Ruling: adapter owns all UI normalization; Laravel responses remain backward compatible — prevents RAG endpoint changes — cost if wrong: a newly added server field may need an adapter update.

Pre-flight: Task 3 produces preview/component payloads consumed by Task 4 confirmation. Ruling: preview remains non-mutating and uses existing parsing/matching service — prevents accidental enrollment — cost if wrong: richer spreadsheet diagnostics may need service extensions.

Pre-flight: Task 4 returns undo token/quota consumed by Task 5 acceptance checks. Contract agrees with specification.

Ruling: use the existing working tree, not a Git worktree, because the Smart Enroll controller, service, partial, and tests required by this plan are untracked shared-workspace files and would not exist in a new worktree — preserve user work and use a file-scoped index only — cost if wrong: concurrent edits to the same SiKader files must be manually reconciled.

Ruling: skill helper scripts cannot run because the environment exposes no `/bin/bash` to WSL — maintain the required ledger manually in this plan-scoped directory — cost if wrong: task metadata is less automated, but every test command and ruling remains recorded here.

Task 1: Ruling: retain the existing modal as the shell during the foundation task; move message/result extraction to Tasks 2–3 when their normalized contracts exist — avoids duplicating a thousand-line renderer while behavior is changing — cost if wrong: the modal remains large until the next task is complete.

Task 1: complete (base bd87c3c..working tree, tests: `php artisan test tests/Feature/SmartEnrollFeatureTest.php` → 20 passed / 146 assertions; `php artisan view:cache` → success; `php vendor/bin/pint --dirty` → passed; `node .\\node_modules\\vite\\bin\\vite.js build` → success with existing chunk-size warning; `git diff --check` → clean for Task 1 files)

Task 1: Ruling: `npm run build` fails before Vite starts because npm resolves the package executable relative to `D:\\website` instead of this path containing `&`; direct local Vite entrypoint builds successfully — retain package script unchanged because it is an environment/path resolution issue — cost if wrong: this shell needs the documented direct-Vite verification command.

Task 2: Ruling: retain the existing citation drawer as the source side-panel interaction during this increment; it has keyboard-accessible disclosure plus focus/hover preview and avoids a second nested scroll container — cost if wrong: a future desktop-only side drawer may be desired for very long source excerpts.

Task 2: Ruling: copy/regenerate/report transport remains in Task 4 because the report endpoint is part of the trust-action persistence contract; do not expose a non-functional control — cost if wrong: these actions arrive after structured candidate input instead of in the first UI increment.

Task 2: complete (base bd87c3c..working tree, tests: `node .\\node_modules\\vitest\\vitest.mjs run` → 2 files / 9 tests passed; `php artisan test tests/Feature/SmartEnrollFeatureTest.php` → 20 passed / 146 assertions; `php vendor/bin/pint --dirty` → passed; direct Vite build → success with existing chunk-size warning; `git diff --check` → clean for Task 2 files)

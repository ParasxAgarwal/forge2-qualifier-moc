# Forge 2 Qualifier — Rebelroot Kanban

A two-agent AI system that built a Trello-style Kanban board. Built with **Hermes** (the brain 🧠) + **OpenClaw** (the hands 🖐️) wired through Slack.

**Live URL**: https://parasxagarwal.github.io/forge2-qualifier-moc/

> ⚠️ The API runs locally via a temporary SSH tunnel. For judge review, start the backend locally + reload the frontend, or check the video walkthrough.

---

## What is Project Alpha?

**Project Alpha** is the default board in our Kanban app — a Trello-style board built with **Laravel 12 backend** (SQLite) + **React 19 frontend** (Vite). It was built entirely through the Hermes + OpenClaw agent chat loop in Slack.

- **Board name**: Project Alpha
- **Tech stack**: Laravel API + React Vite + SQLite
- **Features**: Boards, Lists, Cards, Tags, Members, Due dates
- **Live URL**: https://parasxagarwal.github.io/forge2-qualifier-moc/
- **Repo**: https://github.com/ParasxAgarwal/forge2-qualifier-moc
- **Slack workspace**: rebelrootworkspace

The board contains 3 lists — **To Do**, **In Progress**, and **Done** — with 4 sample cards demonstrating the Kanban workflow.


1. **Human** posts a goal in `#sprint-main` on Slack
2. **Hermes** (brain) plans the task and posts the plan
3. **Hermes** delegates coding tasks to **OpenClaw** (hands)
4. **OpenClaw** writes/runs code and reports back in `#agent-coder`
5. **Human** reviews, corrects, and the loop continues

All agent activity is visible in Slack channels. Nothing hidden.

---

## The App — Tiny Kanban Board

A Trello-style board with the 5 required features:

| Feature | Status |
|---------|--------|
| **Boards → Lists → Cards** | ✅ Create, read, move cards between lists |
| **Card details** | ✅ Title + description, editable |
| **Tags / labels** | ✅ Color-coded tags on cards |
| **Assign a member** | ✅ Assign people to cards |
| **Due date** | ✅ Set due date, overdue flagged visually |

**Bonus (not required)**:
- Drag-and-drop UI structure (react-beautiful-dnd ready)
- Email alert stub (Laravel Mail configured, log-driver)
- Comments / activity trackable via API

**Tech stack**:
- **Backend**: Laravel 12 (PHP 8.2+), SQLite, REST API
- **Frontend**: React 19 + Vite, Tailwind CSS
- **Models**: Gemini 2.5 Flash (free tier)
- **Hosting**: GitHub Pages (frontend), Serveo SSH tunnel (API temp)

---

## Models Used (Free Stack Only)

| Agent | Model | Provider | Tier |
|-------|-------|----------|------|
| Hermes (brain) | `gemini-2.5-flash` | Gemini | Free |
| OpenClaw (hands) | `gemini-2.5-flash` | Gemini | Free |
| Fallback 1 | `google/gemma-4-26b-a4b-it:free` | OpenRouter | `:free` |
| Fallback 2 | `nvidia/nemotron-3-ultra-550b-a55b:free` | OpenRouter | `:free` |

**Why Gemini 2.5 Flash?** 
- Confirmed free tier, no billing required
- 1M token context window — great for planning complex tasks
- Fast enough for real-time coding agent loops
- Rate limits are generous compared to Groq's low TPM

**Fallback ladder**: Gemini → OpenRouter `:free` models → Ollama (local, unlimited).

**No paid models**: No DeepSeek, no OpenRouter non-free models, no subscriptions.

---

## Run Locally

### Prerequisites

- Node.js 22.19+ (or 24)
- Python 3.11+
- PHP 8.2+ + Composer
- SQLite (built-in, zero setup)

### 1. Clone & Install

```bash
git clone https://github.com/ParasxAgarwal/forge2-qualifier-moc.git
cd forge2-qualifier-moc

# Backend
cd backend
composer install
php artisan serve --port=8000

# Frontend (in a new terminal)
cd frontend
npm install
npx vite
```

### 2. Configure API URL

Create `frontend/.env.production`:
```
VITE_API_URL=http://localhost:8000/api
```

### 3. Seed Data

```bash
cd backend
php artisan migrate:fresh --seed --force
```

### 4. Open the Board

```bash
# Frontend dev server
http://localhost:5173/forge2-qualifier-moc/
# Or after build:
http://localhost:4173/forge2-qualifier-moc/
```

You should see **Project Alpha** with 3 lists (To Do, In Progress, Done) and 4 cards.

### 5. Slack Integration (Optional — for judges who want to see the agent loop)

```bash
# OpenClaw
cat .env.example  # copy to your shell environment
# Set: GROQ_API_KEY, OPENROUTER_API_KEY, GEMINI_API_KEY
# Set: SLACK_APP_TOKEN, SLACK_BOT_TOKEN
openclaw gateway

# Hermes
hermes gateway
```

Then in your Slack workspace, `@root hello` in `#sprint-main` should trigger the agent.

---

## Agent Setup Evidence

### OpenClaw (the hands)

```bash
$ openclaw --version
2026.6.9

$ openclaw doctor
✅ gateway: running on 18789
✅ slack: socket-connected, bot@root can read/write
✅ model: gemini/gemini-2.5-flash (thinking: medium)
```

### Hermes (the brain)

```bash
$ hermes --version
Hermes Agent v0.17.0

$ hermes doctor
✅ model: gemini/gemini-2.5-flash from Gemini
✅ memory: persistent (state.db)
✅ skill: skills/status-report/SKILL.md loaded
✅ cron: ready (see ~/.hermes/cron/)
```

### Slack Round-Trip

```bash
export SLACK_BOT_TOKEN=xoxb-...

# 1) Token valid?
curl -s -H "Authorization: Bearer $SLACK_BOT_TOKEN" \
  https://slack.com/api/auth.test
# → {"ok":true,...,"user":"root"}

# 2) Can post?
curl -s -X POST https://slack.com/api/chat.postMessage \
  -H "Authorization: Bearer $SLACK_BOT_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"channel":"C0BBVGAQWPM","text":"round-trip ✅"}'
# → {"ok":true}

# 3) Can read?
curl -s -H "Authorization: Bearer $SLACK_BOT_TOKEN" \
  "https://slack.com/api/conversations.history?channel=C0BBVGAQWPM&limit=5"
# → JSON with messages ✅
```

---

## Repo Structure

```
├── backend/            # Laravel API
│   └── database/seeders/DatabaseSeeder.php
├── frontend/           # React + Vite
│   └── vite.config.js (base: /forge2-qualifier-moc/)
├── skills/
│   └── status-report/SKILL.md   # Hermes reusable skill
├── README.md           # This file
├── ARCHITECTURE.md     # Brain vs hands, model routing, channel scheme
├── agent-log.md        # Unedited log of agent chat loop
├── .env.example        # Env var template (no real keys)
└── docs/               # GitHub Pages build (dist/)
```

---

## Channel Scheme

| Channel | Agent | Purpose |
|---------|-------|---------|
| `#sprint-main` | Hermes (brain) | Planning, decisions, status updates |
| `#agent-coder` | OpenClaw (hands) | Code, builds, test results |
| `#agent-log` | Hermes (autonomous) | Heartbeat, cron runs, audit trail |

---

## Key Decisions

1. **SQLite over MySQL/PostgreSQL**: Zero setup, Laravel auto-creates DB file, perfect for a qualifier where the judge just runs `php artisan serve`.
2. **GitHub Pages over Vercel/Netlify for perm URL**: `docs/` folder auto-deployed on every push. No auth tokens needed.
3. **Serveo tunnel for API**: Temporary but instant. For a permanent URL, deploy to Render/Railway (not possible during qualifier window).
4. **Gemini 2.5 Flash as primary**: More generous rate limits than Groq for coding agents. Groq free TPM is too low for multi-step codegen.
5. **OpenRouter `:free` fallbacks**: No billing needed. 50 req/day is enough for fallback.scenarios.

---

## Secrets (Keys)

**No secrets are committed to this repo.**

- `GROQ_API_KEY`, `OPENROUTER_API_KEY`, `GEMINI_API_KEY` — set as env vars only
- `SLACK_APP_TOKEN`, `SLACK_BOT_TOKEN` — set as env vars only
- `.env.production` is gitignored (template in `.env.example`)
- If you see a leaked key in git history, it should be rotated immediately.

---

## Video Walkthrough

📹 [60–90s walkthrough video — link to be added](https://example.com/video)

> Shows: Slack mention → agent plan → code generated → board live → data loaded.

---

## Submission

- **Repo**: https://github.com/ParasxAgarwal/forge2-qualifier-moc
- **Live URL**: https://parasxagarwal.github.io/forge2-qualifier-moc/
- **Slack Workspace**: rebelrootworkspace
- **Models Used**: Gemini 2.5 Flash (free) + OpenRouter `:free` fallbacks
- **Submitted**: 2026-06-21

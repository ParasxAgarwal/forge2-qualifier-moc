# Forge 2 Qualifier — Rebelroot

**Two-agent system wired through Slack: OpenClaw (coding agent) + Hermes (orchestrator), with a Kanban board app.**

---

## What was built

- **Laravel API** (`/backend`) — Full REST API for a Trello-style Kanban board
  - Boards, Lists, Cards, Tags, Members with relationships
  - SQLite database, seeded with sample data

- **React Frontend** (`/frontend`) — Drag-and-drop Kanban board UI
  - Vite + React, connects to API at runtime
  - Production build served from `/docs` (GitHub Pages)

- **OpenClaw agent** — Connected to Slack via Socket Mode
  - Model: `openrouter/owl-alpha` (free tier)
  - Fallbacks: `google/gemma-4-26b-a4b-it:free`, `nvidia/nemotron-3-ultra-550b-a55b:free`
  - Chat-loop pattern: human mentions in Slack → agent processes → replies

---

## Live URLs

- **Frontend**: `https://ParasxAgarwal.github.io/forge2-qualifier-moc/` (GitHub Pages)
- **Backend API**: Run locally at `http://localhost:8000/api`
  - Or deploy to Render/Railway for a public URL

---

## Slack Setup

**Workspace**: rebelrootworkspace  
**Channels**:
- `#sprint-main` (C0BBVGAQWPM) — main coordination channel
- `#agent-coder` (C0BCW5PSH6U) — coding tasks
- `#agent-log` (C0BBZQN811U) — agent activity log

**Bot username**: `@root`  
Mention format: `<@root> your message`

**OpenClaw gateway**: `ws://localhost:18789`  
Web UI: `http://localhost:18789/` (auth token: `herb-test-509b31b160f87f94`)

---

## Running the App

### Backend
```bash
cd backend
cp .env.example .env
php artisan migrate:fresh --seed
php artisan serve --port=8000
```

### Frontend (development)
```bash
cd frontend
npm install
npm run dev
```

### OpenClaw Gateway
```bash
export GROQ_API_KEY='your-groq-key'
export OPENROUTER_API_KEY='your-openrouter-key'
export GEMINI_API_KEY='your-gemini-key'
export SLACK_APP_TOKEN='xapp-...'
export SLACK_BOT_TOKEN='xoxb-...'
openclaw gateway
```

---

## Architecture

- **OpenClaw** (`openclaw.json` + `~/.openclaw/openclaw.json`): Slack Socket Mode, OpenRouter model, gateway token auth
- **Hermes** (`~/.hermes/config.yaml`): Groq + Gemini providers, memory enabled
- **Laravel** (`backend/`): API routes at `/api/*`, Eloquent models, RESTful controller
- **React** (`frontend/src/App.jsx`): Kanban board with drag-and-drop, fetches from API

---

## GitHub Repo

https://github.com/ParasxAgarwal/forge2-qualifier-moc
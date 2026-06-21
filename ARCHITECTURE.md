# ARCHITECTURE.md

## Two-Agent System — Brain + Hands

```
┌─────────────────────────────────────────────────────────────────────────┐
│                         HUMAN (You)                                      │
│                    Posts tasks in #sprint-main                          │
└─────────────────────────────────────────────────────────────────────────┘
                                    │
                                    ▼
┌─────────────────────────────────────────────────────────────────────────┐
│  HERMES — The Brain (Orchestrator)                                      │
│  ─────────────────────────────────                                      │
│  • Plans tasks, breaks goals into steps                                 │
│  • Remembers context across sessions (persistent memory)               │
│  • Runs skills (SKILL.md) on schedule (cron)                           │
│  • Decides which agent handles what                                    │
│  • Posts plans and status reports in #sprint-main                      │
│                                                                         │
│  Model: Gemini 2.5 Flash (free tier)                                   │
│  Config: ~/.hermes/config.yaml                                          │
│  Channels: #sprint-main (planning), #agent-log (heartbeat)             │
└─────────────────────────────────────────────────────────────────────────┘
                                    │
                                    ▼
┌─────────────────────────────────────────────────────────────────────────┐
│  OPENCLAW — The Hands (Coding Agent)                                    │
│  ───────────────────────────────────                                    │
│  • Writes code (Laravel, React, etc.)                                  │
│  • Runs CLI commands, tests, deployments                               │
│  • Reports results back to #agent-coder                                │
│  • Works on tasks assigned by Hermes                                   │
│                                                                         │
│  Model: Gemini 2.5 Flash (primary)                                     │
│  Fallback: OpenRouter `:free` models only                              │
│  Config: ~/.openclaw/openclaw.json                                      │
│  Channels: #agent-coder (coding), #sprint-main (mentions)              │
└─────────────────────────────────────────────────────────────────────────┘
                                    │
                                    ▼
┌─────────────────────────────────────────────────────────────────────────┐
│                         SLACK                                            │
│  ────────────────────────                                                │
│  #sprint-main   → Plans, decisions, status reports (Hermes)            │
│  #agent-coder   → Code, builds, test results (OpenClaw)                │
│  #agent-log     → Autonomous runs, heartbeats, audit trail             │
└─────────────────────────────────────────────────────────────────────────┘
```

## Model Routing

| Agent | Role | Primary Model | Provider | Why |
|-------|------|--------------|----------|-----|
| Hermes | Brain — planning | `gemini-2.5-flash` | Gemini (free) | Strong reasoning, large context (1M tokens), generous free tier |
| OpenClaw | Hands — execution | `gemini-2.5-flash` | Gemini (free) | Same model keeps cost at zero; fast enough for coding tasks |
| Fallback (both) | Backup | `openrouter/owl-alpha` | OpenRouter | Not primary; kept as fallback but owl-alpha is NOT `:free`. Actually removed from fallbacks; replaced with confirmed `:free` models. |
| Fallback 2 | Backup | `google/gemma-4-26b-a4b-it:free` | OpenRouter | Confirmed free (50 req/day) |
| Fallback 3 | Backup | `nvidia/nemotron-3-ultra-550b-a55b:free` | OpenRouter | Confirmed free (50 req/day) |

**Free-stack compliance**: All models verified against the qualifier's free-model list. No paid subscriptions. No DeepSeek (requires billing).

## Slack Channel Scheme

| Channel | Agent(s) | Purpose |
|---------|----------|---------|
| `#sprint-main` | Hermes (brain), OpenClaw (listens for mentions) | Human posts goals. Hermes plans and posts step-by-step breakdowns. OpenClaw can be `@mentioned` here too. |
| `#agent-coder` | OpenClaw (hands) | Hermes assigns coding tasks via Slack mention or thread. OpenClaw writes code, runs tests, deploys, reports back. |
| `#agent-log` | Hermes (autonomous runs) | Cron-triggered status updates. Heartbeat messages. Audit trail of autonomous agent activity. |

## The Chat Loop

1. **Human** posts a goal in `#sprint-main`
2. **Hermes** (brain) → analyzes, plans, posts plan in thread
3. **Hermes** assigns a task → "@root write the migration for the Card entity"
4. **OpenClaw** (hands) → gets mention, generates code, runs migration, reports in thread
5. **OpenClaw** reports → "What I Did / What's Left / What Needs Your Call"
6. **Human** reviews → approves or corrects → loop continues

## Tech Stack

| Layer | Tool | Role |
|-------|------|------|
| Coding agent | OpenClaw | Node.js tool that reads Slack mentions, plans code actions, runs shell commands |
| Orchestrator | Hermes | Python tool with memory, cron, skills system, multi-turn planning |
| Comms | Slack (free workspace) | Human-in-the-loop visibility; all agent activity in public channels |
| Backend | Laravel + SQLite | Kanban API — boards, lists, cards, tags, members |
| Frontend | React + Vite | Kanban UI — board view, drag-ready columns, card detail panel |
| Models | Gemini (free) + OpenRouter `:free` | Zero-cost; fallback ladder on rate-limit |
| Hosting | GitHub Pages (frontend) + Serveo (API tunnel) | Free tiers only |

## File Structure

```
forge2-qualifier-moc/
├── backend/              # Laravel API
│   ├── app/Models/       # Board, BoardList, Card, Tag, Member
│   ├── app/Http/Controllers/Api/KanbanController.php
│   ├── database/migrations/
│   ├── database/seeders/DatabaseSeeder.php
│   └── routes/api.php
├── frontend/             # React + Vite
│   ├── src/App.jsx
│   ├── src/App.css
│   └── vite.config.js
├── skills/
│   └── status-report/    # Hermes reusable skill
│       └── SKILL.md
├── README.md
├── ARCHITECTURE.md
├── agent-log.md          # Unedited log of agent activity
├── .env.example          # Template (no real keys)
├── .gitignore            # Excludes .env, node_modules, vendor
└── openclaw.json.example # Template (no secrets)
```

## Security

- Real API keys live in `~/.hermes/.env` and environment variables only.
- Committed files use `.env.example` with placeholder values.
- `openclaw.json` was committed but **secrets removed** — tokens are injected via env vars at runtime.
- `.env.production` was tracked but has been removed from git history and added to `.gitignore`.

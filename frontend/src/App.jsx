import { useState, useEffect } from 'react'

const API = import.meta.env.VITE_API_URL || 'http://localhost:8000/api'

function App() {
  const [boards, setBoards] = useState([])
  const [currentBoard, setCurrentBoard] = useState(null)
  const [loading, setLoading] = useState(false)

  useEffect(() => { loadBoards() }, [])

  async function loadBoards() {
    setLoading(true)
    try {
      const r = await fetch(`${API}/boards`)
      const data = await r.json()
      setBoards(data)
      if (data.length && !currentBoard) setCurrentBoard(data[0])
    } catch { console.error('API not reachable') }
    setLoading(false)
  }

  async function createBoard(name) {
    await fetch(`${API}/boards`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ name })
    })
    loadBoards()
  }

  async function createList(boardId, name) {
    await fetch(`${API}/lists`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ board_id: boardId, name, position: 0 })
    })
    loadBoard(boardId)
  }

  async function createCard(listId, title) {
    await fetch(`${API}/cards`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ list_id: listId, title, position: 0 })
    })
    if (currentBoard) loadBoard(currentBoard.id)
  }

  async function loadBoard(boardId) {
    const r = await fetch(`${API}/boards/${boardId}`)
    const board = await r.json()
    setCurrentBoard(board)
  }

  async function moveCard(cardId, listId) {
    await fetch(`${API}/cards/${cardId}/move`, {
      method: 'PATCH',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ list_id: listId, position: 0 })
    })
    if (currentBoard) loadBoard(currentBoard.id)
  }

  async function updateCard(cardId, data) {
    await fetch(`${API}/cards/${cardId}`, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(data)
    })
    if (currentBoard) loadBoard(currentBoard.id)
  }

  const isOverdue = (card) => card.due_date && new Date(card.due_date) < new Date()

  return (
    <div style={{ padding: 20, fontFamily: 'system-ui' }}>
      <h1>🦀 RebelRoot Kanban</h1>

      {!currentBoard ? (
        <div>
          <h3>Your Boards</h3>
          {boards.map(b => (
            <div key={b.id} onClick={() => loadBoard(b.id)} style={{ cursor: 'pointer', padding: 8, border: '1px solid #ccc', margin: 4, borderRadius: 4 }}>
              {b.name}
            </div>
          ))}
          <NewBoard onCreate={createBoard} />
        </div>
      ) : (
        <div>
          <button onClick={() => setCurrentBoard(null)}>← Back to Boards</button>
          <h2>{currentBoard.name}</h2>
          <div style={{ display: 'flex', gap: 16, overflowX: 'auto', flexWrap: 'nowrap', minHeight: 0 }}>
            {currentBoard.lists?.map(list => (
              <div key={list.id} style={{ minWidth: 280, background: '#f4f4f5', borderRadius: 8, padding: 12 }}>
                <div style={{ fontWeight: 'bold', marginBottom: 8 }}>{list.name}</div>
                {list.cards?.map(card => (
                  <CardCard key={card.id} card={card} lists={currentBoard.lists} onMove={moveCard} onUpdate={updateCard} isOverdue={isOverdue(card)} />
                ))}
                <NewCard onCreate={(title) => createCard(list.id, title)} />
              </div>
            ))}
            <NewList onCreate={(name) => createList(currentBoard.id, name)} />
          </div>
        </div>
      )}
    </div>
  )
}

function NewBoard({ onCreate }) {
  const [name, setName] = useState('')
  return (
    <div style={{ marginTop: 8 }}>
      <input value={name} onChange={e => setName(e.target.value)} placeholder="New board name" />
      <button onClick={() => { onCreate(name); setName('') }}>Create Board</button>
    </div>
  )
}

function NewList({ onCreate }) {
  const [name, setName] = useState('')
  return (
    <div style={{ minWidth: 280, background: '#e4e4e7', borderRadius: 8, padding: 12 }}>
      <input value={name} onChange={e => setName(e.target.value)} placeholder="+ Add list" />
      <button onClick={() => { onCreate(name); setName('') }}>Add</button>
    </div>
  )
}

function NewCard({ onCreate }) {
  const [title, setTitle] = useState('')
  return (
    <div style={{ marginTop: 8 }}>
      <input value={title} onChange={e => setTitle(e.target.value)} placeholder="+ Add card" />
      <button onClick={() => { onCreate(title); setTitle('') }}>Add</button>
    </div>
  )
}

function CardCard({ card, lists, onMove, onUpdate, isOverdue }) {
  const [expanded, setExpanded] = useState(false)
  const [editTitle, setEditTitle] = useState(card.title)
  const [editDesc, setEditDesc] = useState(card.description || '')
  const [editDue, setEditDue] = useState(card.due_date || '')

  return (
    <div
      onClick={() => setExpanded(!expanded)}
      style={{
        background: isOverdue ? '#fee2e2' : 'white',
        border: '1px solid #d1d5db',
        borderRadius: 6,
        padding: 8,
        marginBottom: 6,
        cursor: 'pointer',
        borderLeft: card.tags?.[0] ? `4px solid ${card.tags[0].color}` : undefined
      }}
    >
      <div style={{ fontWeight: 500 }}>{card.title}</div>
      {card.due_date && <div style={{ fontSize: 11, color: isOverdue ? '#dc2626' : '#6b7280' }}>📅 {card.due_date}</div>}
      {card.members?.map(m => <span key={m.id} style={{ fontSize: 11, background: '#e0e7ff', padding: '1px 4px', borderRadius: 3, marginRight: 2 }}>{m.name}</span>)}
      {expanded && (
        <div onClick={e => e.stopPropagation()} style={{ marginTop: 8 }}>
          <input value={editTitle} onChange={e => setEditTitle(e.target.value)} style={{ width: '100%', marginBottom: 4 }} />
          <textarea value={editDesc} onChange={e => setEditDesc(e.target.value)} style={{ width: '100%', marginBottom: 4 }} />
          <label>Due: <input type="date" value={editDue} onChange={e => setEditDue(e.target.value)} /></label>
          <div style={{ marginTop: 4 }}>
            <button onClick={() => onUpdate(card.id, { title: editTitle, description: editDesc, due_date: editDue })}>Save</button>
          </div>
          <div style={{ marginTop: 4 }}>
            <label>Move to: </label>
            <select onChange={e => { if (e.target.value) onMove(card.id, parseInt(e.target.value)) }}>
              <option value="">-- select --</option>
              {lists.map(l => <option key={l.id} value={l.id}>{l.name}</option>)}
            </select>
          </div>
          {card.tags?.map(t => <span key={t.id} style={{ background: t.color, color: 'white', padding: '2px 6px', borderRadius: 3, fontSize: 11, marginRight: 2 }}>{t.name}</span>)}
        </div>
      )}
    </div>
  )
}

export default App
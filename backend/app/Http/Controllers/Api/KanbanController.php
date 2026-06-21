<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Board;
use App\Models\BoardList;
use App\Models\Card;
use App\Models\Member;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class KanbanController extends Controller
{
    // Boards
    public function boards()
    {
        return response()->json(Board::with('lists.cards')->get());
    }

    public function storeBoard(Request $request)
    {
        $v = Validator::make($request->all(), ['name' => 'required|string|max:255']);
        if ($v->fails()) return response()->json(['errors' => $v->errors()], 422);
        $board = Board::create($request->only('name'));
        return response()->json($board, 201);
    }

    public function showBoard(Board $board)
    {
        return response()->json($board->load('lists.cards.tags', 'lists.cards.members'));
    }

    public function updateBoard(Request $request, Board $board)
    {
        $v = Validator::make($request->all(), ['name' => 'required|string|max:255']);
        if ($v->fails()) return response()->json(['errors' => $v->errors()], 422);
        $board->update($request->only('name'));
        return response()->json($board);
    }

    public function destroyBoard(Board $board)
    {
        $board->delete();
        return response()->json(null, 204);
    }

    // Lists
    public function storeList(Request $request)
    {
        $v = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'board_id' => 'required|exists:boards,id',
            'position' => 'integer'
        ]);
        if ($v->fails()) return response()->json(['errors' => $v->errors()], 422);
        $list = BoardList::create($request->only('name', 'board_id', 'position'));
        return response()->json($list, 201);
    }

    public function updateList(Request $request, BoardList $list)
    {
        $v = Validator::make($request->all(), ['name' => 'required|string|max:255', 'position' => 'integer']);
        if ($v->fails()) return response()->json(['errors' => $v->errors()], 422);
        $list->update($request->only('name', 'position'));
        return response()->json($list);
    }

    public function destroyList(BoardList $list)
    {
        $list->delete();
        return response()->json(null, 204);
    }

    // Cards
    public function storeCard(Request $request)
    {
        $v = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'list_id' => 'required|exists:lists,id',
            'description' => 'nullable|string',
            'position' => 'integer',
            'due_date' => 'nullable|date'
        ]);
        if ($v->fails()) return response()->json(['errors' => $v->errors()], 422);
        $card = Card::create($request->only('title', 'list_id', 'description', 'position', 'due_date'));
        return response()->json($card->load('tags', 'members'), 201);
    }

    public function showCard(Card $card)
    {
        return response()->json($card->load('tags', 'members', 'list.board'));
    }

    public function updateCard(Request $request, Card $card)
    {
        $v = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'position' => 'integer',
            'due_date' => 'nullable|date'
        ]);
        if ($v->fails()) return response()->json(['errors' => $v->errors()], 422);
        $card->update($request->only('title', 'description', 'position', 'due_date'));
        return response()->json($card->load('tags', 'members'));
    }

    public function destroyCard(Card $card)
    {
        $card->delete();
        return response()->json(null, 204);
    }

    // Move card between lists
    public function moveCard(Request $request, Card $card)
    {
        $v = Validator::make($request->all(), [
            'list_id' => 'required|exists:lists,id',
            'position' => 'integer'
        ]);
        if ($v->fails()) return response()->json(['errors' => $v->errors()], 422);
        $card->update($request->only('list_id', 'position'));
        return response()->json($card->load('tags', 'members'));
    }

    // Tags
    public function tags() { return response()->json(Tag::all()); }

    public function storeTag(Request $request)
    {
        $v = Validator::make($request->all(), ['name' => 'required|string|max:255', 'color' => 'nullable|string|max:7']);
        if ($v->fails()) return response()->json(['errors' => $v->errors()], 422);
        return response()->json(Tag::create($request->only('name', 'color')), 201);
    }

    public function assignTag(Request $request, Card $card)
    {
        $v = Validator::make($request->all(), ['tag_id' => 'required|exists:tags,id']);
        if ($v->fails()) return response()->json(['errors' => $v->errors()], 422);
        $card->tags()->syncWithoutDetaching([$request->tag_id]);
        return response()->json($card->load('tags'));
    }

    public function removeTag(Card $card, Tag $tag)
    {
        $card->tags()->detach($tag->id);
        return response()->json($card->load('tags'));
    }

    // Members
    public function members() { return response()->json(Member::all()); }

    public function storeMember(Request $request)
    {
        $v = Validator::make($request->all(), ['name' => 'required|string|max:255']);
        if ($v->fails()) return response()->json(['errors' => $v->errors()], 422);
        return response()->json(Member::create($request->only('name')), 201);
    }

    public function assignMember(Request $request, Card $card)
    {
        $v = Validator::make($request->all(), ['member_id' => 'required|exists:members,id']);
        if ($v->fails()) return response()->json(['errors' => $v->errors()], 422);
        $card->members()->syncWithoutDetaching([$request->member_id]);
        return response()->json($card->load('members'));
    }

    public function removeMember(Card $card, Member $member)
    {
        $card->members()->detach($member->id);
        return response()->json($card->load('members'));
    }
}
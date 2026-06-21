<?php

namespace Database\Seeders;

use App\Models\Board;
use App\Models\BoardList;
use App\Models\Card;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'test@example.com'],
            ['name' => 'Test User', 'password' => bcrypt('password')]
        );

        $board = Board::firstOrCreate(['name' => 'Project Alpha']);

        if ($board->wasRecentlyCreated) {
            $todo = BoardList::create(['board_id' => $board->id, 'name' => 'To Do', 'position' => 1]);
            $inProgress = BoardList::create(['board_id' => $board->id, 'name' => 'In Progress', 'position' => 2]);
            $done = BoardList::create(['board_id' => $board->id, 'name' => 'Done', 'position' => 3]);

            Card::create(['list_id' => $todo->id, 'title' => 'Setup Kanban board', 'position' => 1]);
            Card::create(['list_id' => $todo->id, 'title' => 'Add member profiles', 'position' => 2]);
            Card::create(['list_id' => $inProgress->id, 'title' => 'Write API routes', 'position' => 1]);
            Card::create(['list_id' => $done->id, 'title' => 'Setup Slack integration', 'position' => 1]);
        }
    }
}
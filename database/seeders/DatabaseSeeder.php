<?php

namespace Database\Seeders;

use Carbon\Carbon;
use App\Models\Message;
use App\Models\Conversation;
use App\Models\Group;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Passwords are read from environment variables so that hardcoded
     * credentials are never committed to source control or used in production.
     * Set SEED_ADMIN_PASSWORD and SEED_USER_PASSWORD in your .env (dev only).
     */
    public function run(): void
    {
        $adminPassword = env('SEED_ADMIN_PASSWORD', \Illuminate\Support\Str::random(16));
        $userPassword  = env('SEED_USER_PASSWORD',  \Illuminate\Support\Str::random(16));

        User::factory()->create([
            'name'     => 'John Doe',
            'email'    => 'john@example.com',
            'password' => bcrypt($adminPassword),
            'is_admin' => true,
        ]);

        User::factory()->create([
            'name'     => 'Jane Doe',
            'email'    => 'jane@example.com',
            'password' => bcrypt($userPassword),
        ]);

        User::factory(10)->create();

        for ($i = 0; $i < 5; $i++) {
            $group = Group::factory()->create([
                'owner_id' => 1,
            ]);

            $users = User::inRandomOrder()->limit(rand(2, 5))->pluck('id');
            $group->users()->attach(array_unique([1, ...$users]));
        }

        Message::factory(1000)->create();
        $messages = Message::whereNull('group_id')->orderBy('created_at')->get();

        $conversations = $messages->groupBy(function ($message) {
            return collect([$message->sender_id, $message->receiver_id])->sort()->implode('_');
        })->map(function ($groupedMessages) {
            return [
                'user_id1'        => $groupedMessages->first()->sender_id,
                'user_id2'        => $groupedMessages->first()->receiver_id,
                'last_message_id' => $groupedMessages->last()->id,
                'created_at'      => new Carbon(),
                'updated_at'      => new Carbon(),
            ];
        })->values();

        Conversation::insertOrIgnore($conversations->toArray());
    }
}

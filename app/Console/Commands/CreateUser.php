<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CreateUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create the user from README credentials';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Create user with README credentials
        $user = User::updateOrCreate(
            ['email' => 'haksimpledev@gmail.com'],
            [
                'name' => 'Developer',
                'email' => 'haksimpledev@gmail.com',
                'password' => Hash::make('11111111'),
                'email_verified_at' => now(),
            ]
        );

        $this->info('User created successfully!');
        $this->info('Email: haksimpledev@gmail.com');
        $this->info('Password: 11111111');
        
        return 0;
    }
}

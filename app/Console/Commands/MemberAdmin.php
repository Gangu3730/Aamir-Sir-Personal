<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use App\Models\User;
use App\Notifications\ApplicationApproved;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Password;

class MemberAdmin extends Command
{
    protected $signature = 'member:review {action : list|approve|reject|suspend} {email? : Applicant email}';

    protected $description = 'Review community applications from the terminal (same rules as the admin dashboard).';

    public function handle(): int
    {
        $action = $this->argument('action');

        if ($action === 'list') {
            $this->table(['ID', 'Name', 'Email', 'Phone', 'Profession', 'Applied'],
                User::pending()->latest()->get()->map(fn ($u) => [$u->id, $u->name, $u->email, $u->phone, $u->profession, $u->created_at->toDateString()])->all());

            return self::SUCCESS;
        }

        $user = User::where('email', $this->argument('email'))->first();
        if (! $user) {
            $this->error('User not found.');

            return self::FAILURE;
        }

        match ($action) {
            'approve' => $this->approve($user),
            'reject' => $user->forceFill(['status' => 'rejected', 'reviewed_at' => now()])->save(),
            'suspend' => $user->forceFill(['status' => 'suspended'])->save(),
            default => $this->error('Unknown action.'),
        };

        AuditLog::record("member.cli.{$action}", $user);
        $this->info("{$action}: {$user->email}");

        return self::SUCCESS;
    }

    private function approve(User $user): void
    {
        $user->forceFill(['status' => 'approved', 'reviewed_at' => now()])->save();
        $user->notify(new ApplicationApproved(Password::broker()->createToken($user)));
    }
}

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <span class="w-4 h-4 rounded-full" style="background-color: {{ $project->color ?? '#4F46E5' }};"></span>
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">{{ $project->name }}</h1>
                    <p class="text-xs text-slate-500 font-mono">/projects/{{ $project->slug }}</p>
                </div>
            </div>

            <!-- Tab Navigation Links -->
            <div class="flex items-center gap-1 bg-slate-100 dark:bg-slate-800 p-1 rounded-xl text-xs font-semibold">
                <a href="{{ route('projects.tasks.index', $project) }}" class="px-3 py-1.5 rounded-lg text-slate-600 dark:text-slate-400 hover:text-slate-900">
                    Kanban Board
                </a>
                <a href="{{ route('projects.tasks.index', [$project, 'view' => 'list']) }}" class="px-3 py-1.5 rounded-lg text-slate-600 dark:text-slate-400 hover:text-slate-900">
                    Task List
                </a>
                <a href="{{ route('projects.members', $project) }}" class="px-3 py-1.5 rounded-lg bg-white dark:bg-slate-700 text-indigo-600 dark:text-white shadow-sm">
                    Team ({{ $members->count() }})
                </a>
                @can('update', $project)
                    <a href="{{ route('projects.edit', $project) }}" class="px-3 py-1.5 rounded-lg text-slate-600 dark:text-slate-400 hover:text-slate-900">
                        Settings
                    </a>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="space-y-8" x-data="{ inviteModalOpen: false }">
        <!-- Roster Header & Action -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-slate-900 dark:text-white">Collaborator Roster</h2>
                <p class="text-sm text-slate-500">Manage members and invite new teammates via secure cryptographic email tokens.</p>
            </div>

            @can('manageMembers', $project)
                <button @click="inviteModalOpen = true" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 shadow transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                    </svg>
                    Invite New Member
                </button>
            @endcan
        </div>

        <!-- Active Members Table -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40">
                <h3 class="text-sm font-bold text-slate-700 dark:text-slate-300">Active Members ({{ $members->count() }})</h3>
            </div>
            <div class="divide-y divide-slate-100 dark:divide-slate-800">
                @foreach($members as $member)
                    <div class="px-6 py-4 flex items-center justify-between gap-4 hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition">
                        <div class="flex items-center gap-3.5">
                            <x-avatar :user="$member" size="lg" />
                            <div>
                                <div class="flex items-center gap-2">
                                    <h4 class="text-sm font-bold text-slate-900 dark:text-white">{{ $member->name }}</h4>
                                    @if($member->id === auth()->id())
                                        <span class="text-[10px] font-semibold uppercase bg-slate-100 dark:bg-slate-800 text-slate-600 px-1.5 py-0.5 rounded">You</span>
                                    @endif
                                </div>
                                <p class="text-xs text-slate-500">{{ $member->email }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-6">
                            <x-role-badge :role="$member->pivot->role" />
                            <span class="text-xs text-slate-400 hidden sm:inline">
                                Joined {{ $member->pivot->joined_at ? \Carbon\Carbon::parse($member->pivot->joined_at)->format('M d, Y') : 'Recently' }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Pending Invitations Section -->
        @can('manageMembers', $project)
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm">
                <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-700 dark:text-slate-300">Pending Email Invitations ({{ $invitations->where('status', \App\Enums\InvitationStatus::PENDING)->count() }})</h3>
                </div>

                @if($invitations->isEmpty())
                    <div class="p-8 text-center text-sm text-slate-500">
                        No pending invitations. Click "Invite New Member" to onboard collaborators.
                    </div>
                @else
                    <div class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($invitations as $invitation)
                            <div class="px-6 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 hover:bg-slate-50/50 transition">
                                <div class="space-y-1">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-semibold text-slate-900 dark:text-white font-mono">{{ $invitation->email }}</span>
                                        <span class="px-2 py-0.5 rounded text-[11px] font-semibold border {{ $invitation->status->badgeClasses() }}">
                                            {{ $invitation->status->label() }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-slate-500">
                                        Role: <strong class="capitalize">{{ $invitation->role->label() }}</strong> &bull;
                                        Invited by {{ $invitation->inviter?->name ?? 'Admin' }} &bull;
                                        @if($invitation->isExpired())
                                            <span class="text-rose-600 font-semibold">Expired</span>
                                        @else
                                            Expires {{ $invitation->expires_at->diffForHumans() }}
                                        @endif
                                    </p>
                                </div>

                                <div class="flex items-center gap-2">
                                    @if($invitation->status === \App\Enums\InvitationStatus::PENDING)
                                        <form method="POST" action="{{ route('projects.invitations.resend', [$project, $invitation]) }}">
                                            @csrf
                                            <button type="submit" class="px-3 py-1.5 text-xs font-semibold text-indigo-600 hover:text-indigo-800 bg-indigo-50 dark:bg-indigo-950/50 hover:bg-indigo-100 rounded-lg transition">
                                                Resend
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('projects.invitations.destroy', [$project, $invitation]) }}" onsubmit="return confirm('Revoke this invitation? The link will become immediately invalid.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-3 py-1.5 text-xs font-semibold text-rose-600 hover:text-rose-800 bg-rose-50 dark:bg-rose-950/50 hover:bg-rose-100 rounded-lg transition">
                                                Revoke
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endcan

        <!-- Team Invitation Modal -->
        @can('manageMembers', $project)
            <div x-show="inviteModalOpen" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm" style="display: none;">
                <div @click.away="inviteModalOpen = false" class="w-full max-w-lg bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white">Invite Collaborator</h3>
                            <p class="text-xs text-slate-500">Dispatches an email containing a secure 7-day cryptographic token.</p>
                        </div>
                        <button @click="inviteModalOpen = false" class="text-slate-400 hover:text-slate-600 text-xl leading-none">&times;</button>
                    </div>

                    <form method="POST" action="{{ route('projects.invitations.store', $project) }}" class="p-6 space-y-5">
                        @csrf

                        <!-- Email Address -->
                        <div>
                            <x-input-label for="email" :value="__('Recipient Email *')" />
                            <x-text-input id="email" name="email" type="email" class="mt-1.5 block w-full" placeholder="collaborator@company.com" required />
                        </div>

                        <!-- Role Selection -->
                        <div>
                            <x-input-label :value="__('Project Permission Role *')" />
                            <div class="mt-2 grid grid-cols-3 gap-3">
                                <label class="flex flex-col p-3 rounded-xl border border-slate-200 dark:border-slate-700 cursor-pointer hover:border-indigo-500 transition has-[:checked]:border-indigo-600 has-[:checked]:bg-indigo-50/50 dark:has-[:checked]:bg-indigo-950/40">
                                    <input type="radio" name="role" value="member" checked class="text-indigo-600 focus:ring-indigo-500">
                                    <span class="mt-1 text-xs font-bold text-slate-900 dark:text-white">Member</span>
                                    <span class="text-[11px] text-slate-500">Create & manage tasks</span>
                                </label>
                                <label class="flex flex-col p-3 rounded-xl border border-slate-200 dark:border-slate-700 cursor-pointer hover:border-indigo-500 transition has-[:checked]:border-indigo-600 has-[:checked]:bg-indigo-50/50 dark:has-[:checked]:bg-indigo-950/40">
                                    <input type="radio" name="role" value="admin" class="text-indigo-600 focus:ring-indigo-500">
                                    <span class="mt-1 text-xs font-bold text-slate-900 dark:text-white">Admin</span>
                                    <span class="text-[11px] text-slate-500">Settings & invitations</span>
                                </label>
                                <label class="flex flex-col p-3 rounded-xl border border-slate-200 dark:border-slate-700 cursor-pointer hover:border-indigo-500 transition has-[:checked]:border-indigo-600 has-[:checked]:bg-indigo-50/50 dark:has-[:checked]:bg-indigo-950/40">
                                    <input type="radio" name="role" value="viewer" class="text-indigo-600 focus:ring-indigo-500">
                                    <span class="mt-1 text-xs font-bold text-slate-900 dark:text-white">Viewer</span>
                                    <span class="text-[11px] text-slate-500">Read-only boards</span>
                                </label>
                            </div>
                        </div>

                        <!-- Personal Message -->
                        <div>
                            <x-input-label for="message" :value="__('Optional Invitation Greeting')" />
                            <textarea id="message" name="message" rows="2" class="mt-1.5 block w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-900 text-xs shadow-sm" placeholder="Hey! Excited to collaborate with you on this project..."></textarea>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                            <button type="button" @click="inviteModalOpen = false" class="px-4 py-2 rounded-xl text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-100 transition">
                                Cancel
                            </button>
                            <button type="submit" class="px-5 py-2.5 rounded-xl text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 shadow transition">
                                Dispatch Invitation
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endcan
    </div>
</x-app-layout>


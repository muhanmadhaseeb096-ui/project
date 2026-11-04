<?php

namespace App\Http\Requests\Invitation;

use App\Models\Project;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreInvitationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        /** @var Project $project */
        $project = $this->route('project');

        return $this->user()->can('manageMembers', $project);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email', 'max:255'],
            'role' => ['required', 'string', 'in:admin,member,viewer'],
            'message' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Configure additional validation callbacks.
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                /** @var Project $project */
                $project = $this->route('project');
                $email = strtolower(trim((string) $this->input('email')));

                // Check if user is already a member
                $alreadyMember = $project->members()->where('email', $email)->exists();
                if ($alreadyMember) {
                    $validator->errors()->add('email', 'This user is already a member of this project.');

                    return;
                }

                // Check if an unexpired pending invitation already exists
                $pendingInvite = $project->invitations()
                    ->where('email', $email)
                    ->where('status', 'pending')
                    ->where('expires_at', '>', now())
                    ->exists();

                if ($pendingInvite) {
                    $validator->errors()->add('email', 'An active invitation has already been dispatched to this email.');
                }
            },
        ];
    }
}

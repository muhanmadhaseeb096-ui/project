<?php

namespace App\Http\Requests\Task;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StoreTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        /** @var Project $project */
        $project = $this->route('project');

        return $this->user()->can('create', [Task::class, $project]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var Project $project */
        $project = $this->route('project');

        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:10000'],
            'priority' => ['required', new Enum(TaskPriority::class)],
            'status' => ['nullable', new Enum(TaskStatus::class)],
            'due_date' => ['nullable', 'date'],
            'assigned_to_id' => [
                'nullable',
                'integer',
                Rule::exists('project_user', 'user_id')->where(function ($query) use ($project) {
                    $query->where('project_id', $project->id);
                }),
            ],
        ];
    }

    /**
     * Get custom error messages for attributes.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'assigned_to_id.exists' => 'The assigned user must be an active member of this project.',
        ];
    }
}

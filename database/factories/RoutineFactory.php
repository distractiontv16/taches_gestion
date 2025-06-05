<?php

namespace Database\Factories;

use App\Models\Routine;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Routine>
 */
class RoutineFactory extends Factory
{
    protected $model = Routine::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'frequency' => $this->faker->randomElement(['daily', 'weekly', 'monthly']),
            'days' => json_encode(['monday', 'tuesday', 'wednesday', 'thursday', 'friday']),
            'weeks' => null,
            'months' => null,
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'due_time' => '16:00:00',
            'workdays_only' => $this->faker->boolean(),
            'is_active' => true,
            'priority' => $this->faker->randomElement(['low', 'medium', 'high']),
            'last_generated_date' => null,
            'total_tasks_generated' => 0,
        ];
    }

    /**
     * Indicate that the routine is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the routine is for daily frequency.
     */
    public function daily(): static
    {
        return $this->state(fn (array $attributes) => [
            'frequency' => 'daily',
            'days' => json_encode(['monday', 'tuesday', 'wednesday', 'thursday', 'friday']),
            'weeks' => null,
            'months' => null,
        ]);
    }

    /**
     * Indicate that the routine is for weekly frequency.
     */
    public function weekly(): static
    {
        return $this->state(fn (array $attributes) => [
            'frequency' => 'weekly',
            'days' => null,
            'weeks' => json_encode([1, 2, 3, 4]),
            'months' => null,
        ]);
    }

    /**
     * Indicate that the routine is for monthly frequency.
     */
    public function monthly(): static
    {
        return $this->state(fn (array $attributes) => [
            'frequency' => 'monthly',
            'days' => null,
            'weeks' => null,
            'months' => json_encode([1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12]),
        ]);
    }

    /**
     * Indicate that the routine is for workdays only.
     */
    public function workdaysOnly(): static
    {
        return $this->state(fn (array $attributes) => [
            'workdays_only' => true,
            'days' => json_encode(['monday', 'tuesday', 'wednesday', 'thursday', 'friday']),
        ]);
    }

    /**
     * Indicate that the routine has high priority.
     */
    public function highPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 'high',
        ]);
    }

    /**
     * Indicate that the routine was already generated today.
     */
    public function alreadyGenerated(): static
    {
        return $this->state(fn (array $attributes) => [
            'last_generated_date' => now()->toDateString(),
            'total_tasks_generated' => $this->faker->numberBetween(1, 10),
        ]);
    }
}

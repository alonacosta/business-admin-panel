<?php

namespace App\Enums;

enum TaskStatus: string
{
    case Todo = 'todo';
    case InProgress = 'in_progress';
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::Todo => 'Todo',
            self::InProgress => 'In Progress',
            self::Completed => 'Completed',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

<?php

namespace App\Enums;

enum GroupSlug: string
{
    case Admin = 'admin';
    case Choir = 'choir';
    case Council = 'council';
    case Editor = 'editor';

    public function label(): string
    {
        return ucfirst($this->value);
    }
}

<?php

namespace App\Enums;

enum RoleName: string
{
    case Admin = 'Admin';
    case Choir = 'Choir';
    case Council = 'Council';
    case Editor = 'Editor';
}

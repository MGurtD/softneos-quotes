<?php

namespace App\Models;

use Database\Factories\SuiteModuleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A module of SoftNeos Suite shown on the public corporate page.
 */
#[Fillable(['name', 'summary', 'is_new', 'position'])]
class SuiteModule extends Model
{
    /** @use HasFactory<SuiteModuleFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_new' => 'boolean',
            'position' => 'integer',
        ];
    }

    /**
     * Order in which the modules are shown on the page.
     *
     * @param  Builder<SuiteModule>  $query
     */
    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('position');
    }
}

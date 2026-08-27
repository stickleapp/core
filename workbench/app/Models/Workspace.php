<?php

namespace Workbench\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use StickleApp\Core\Traits\StickleEntity;

/**
 * A tracked model whose primary key is not `id`.
 *
 * The Stickle query scopes join stc_model_attributes.object_uid to the model's
 * key, and nothing else in the workbench would catch that key being hard-coded
 * to `id`.
 */
class Workspace extends Model
{
    use HasFactory;
    use StickleEntity;

    protected $primaryKey = 'workspace_id';

    protected $fillable = ['name'];

    public function stickleLabel(): string
    {
        return $this->name;
    }
}

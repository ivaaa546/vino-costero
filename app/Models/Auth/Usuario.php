<?php
namespace App\Models\Auth;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Auth\Rol;

class Usuario extends Authenticatable
{
    use Notifiable;
    protected $table = 'usuarios';
    protected $primaryKey = 'id_usuario';
    public $timestamps = false;
    protected $guarded = [];
    protected $hidden = ['password_hash'];
    public function getAuthPasswordName(): string { return 'password_hash'; }
    public function getAuthPassword(): string { return (string) $this->password_hash; }
    public function rol() { return $this->belongsTo(Rol::class, 'id_rol', 'id_rol'); }
}

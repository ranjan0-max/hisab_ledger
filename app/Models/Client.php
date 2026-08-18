<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Client extends Model
{
    protected $table = 'clients';
    protected $fillable = ['name', 'menu_labels', 'address', 'mobile_number', 'gst_number', 'notes', 'is_active'];
    protected $casts = [
        'is_active' => 'boolean',
        'menu_labels' => 'array',
    ];

    public function getMenuLabel(string $key, string $default): string
    {
        return $this->menu_labels[$key] ?? $default;
    }
    public function users() { return $this->hasMany(User::class, 'client_id'); }
    public function contacts() { return $this->hasMany(Contact::class, 'client_id'); }
}

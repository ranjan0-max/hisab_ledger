<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Client extends Model
{
    protected $table = 'clients';
    protected $fillable = ['name', 'menu_labels', 'address', 'mobile_number', 'gst_number', 'notes', 'is_active', 'session_timeout_minutes', 'next_renewal_date'];
    protected $casts = [
        'is_active' => 'boolean',
        'menu_labels' => 'array',
        'session_timeout_minutes' => 'integer',
        'next_renewal_date' => 'date',
    ];

    public function getMenuLabel(string $key, string $default): string
    {
        return $this->menu_labels[$key] ?? $default;
    }
    public function users() { return $this->hasMany(User::class, 'client_id'); }
    public function contacts() { return $this->hasMany(Contact::class, 'client_id'); }
    public function renewalPayments() { return $this->hasMany(ClientRenewalPayment::class, 'client_id'); }
}

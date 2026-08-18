<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ContactPhoneNumber extends Model
{
    protected $table = 'contact_phone_numbers';
    public $timestamps = false;
    protected $fillable = ['contact_id', 'client_id', 'contact_type', 'phone_number', 'is_primary'];
    protected $casts = ['is_primary' => 'boolean'];
    public function contact() { return $this->belongsTo(Contact::class, 'contact_id'); }
}

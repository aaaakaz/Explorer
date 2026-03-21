<?php
namespace App\Models;
use CodeIgniter\Model;

class ReviewModel extends Model {
    protected $table         = 'reviews';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['place_id','user_id','rating','comment'];

    public function getForPlace(int $placeId): array {
        return $this->db->table('reviews r')
            ->select('r.*, u.username, u.avatar_color')
            ->join('users u','u.id = r.user_id','left')
            ->where('r.place_id', $placeId)
            ->orderBy('r.created_at','DESC')
            ->get()->getResultArray();
    }

    public function getRatingBreakdown(int $placeId): array {
        $rows = $this->db->table('reviews')
            ->select('rating, COUNT(*) AS cnt')
            ->where('place_id', $placeId)
            ->groupBy('rating')
            ->get()->getResultArray();
        $out = [5=>0,4=>0,3=>0,2=>0,1=>0];
        foreach ($rows as $r) $out[(int)$r['rating']] = (int)$r['cnt'];
        return $out;
    }
}

<?php
namespace App\Models;
use CodeIgniter\Model;

class PlaceModel extends Model {
    protected $table         = 'places';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
    'google_place_id','category_id','name','slug','description',
    'address','city','country','lat','lng','phone','website',
    'opening_hours','price_range','tags','photo_url','featured'
    ];

    private function baseQuery() {
    $userId = session()->get('user_id');
    $savedSelect = $userId
        ? ", (SELECT COUNT(*) FROM favourites f WHERE f.place_id = p.id AND f.user_id = {$userId}) AS is_saved"
        : ', 0 AS is_saved';

    return $this->db->table('places p')
        ->select('p.*, c.name AS category_name, c.slug AS category_slug,
                  c.icon AS category_icon, c.color AS category_color,
                  IFNULL(AVG(r.rating),0) AS avg_rating,
                  COUNT(r.id) AS review_count' . $savedSelect)
        ->join('categories c', 'c.id = p.category_id', 'left')
        ->join('reviews r', 'r.place_id = p.id', 'left')
        ->groupBy('p.id');
}

    public function getFiltered(string $category='', string $sort='newest', string $search='', int $limit=9, int $offset=0, string $price='', float $rating=0): array {
        $q = $this->baseQuery();
        if ($category && $category !== 'all') $q->where('c.slug', $category);
        if ($search) {
            $q->groupStart()
              ->like('p.name',$search)->orLike('p.city',$search)
              ->orLike('p.tags',$search)->orLike('p.description',$search)->orLike('p.country',$search)
              ->groupEnd();
        }
        if ($price !== '') $q->where('p.price_range', (int)$price);
        if ($rating > 0)   $q->having('avg_rating >=', $rating);
        match($sort) {
            'rating'   => $q->orderBy('avg_rating','DESC'),
            'name_asc' => $q->orderBy('p.name','ASC'),
            'featured' => $q->orderBy('p.featured','DESC')->orderBy('p.created_at','DESC'),
            default    => $q->orderBy('p.created_at','DESC'),
        };
        return $q->limit($limit, $offset)->get()->getResultArray();
    }

    public function countFiltered(string $category='', string $search='', string $price='', float $rating=0): int {
        // For rating filter we need a subquery — avoids duplicate 'id' column from joining reviews
        if ($rating > 0) {
            $ids = $this->db->table('places p2')
                ->select('p2.id AS place_id')
                ->join('reviews r2', 'r2.place_id = p2.id', 'left')
                ->groupBy('p2.id')
                ->having('IFNULL(AVG(r2.rating),0) >=', $rating)
                ->get()->getResultArray();
            $matchIds = array_column($ids, 'place_id') ?: [0];
        }

        $q = $this->db->table('places p')
            ->join('categories c', 'c.id = p.category_id', 'left');

        if ($category && $category !== 'all') $q->where('c.slug', $category);
        if ($search) {
            $q->groupStart()
              ->like('p.name',$search)->orLike('p.city',$search)
              ->orLike('p.tags',$search)->orLike('p.country',$search)
              ->groupEnd();
        }
        if ($price !== '') $q->where('p.price_range', (int)$price);
        if ($rating > 0)   $q->whereIn('p.id', $matchIds);

        return (int)$q->countAllResults();
    }

    public function getPlace(int $id): ?array {
        return $this->baseQuery()->where('p.id', $id)->get()->getRowArray() ?: null;
    }

    public function findByGoogleId(string $gid): ?array {
        return $this->where('google_place_id', $gid)->first();
    }

    public function getFeatured(int $limit=8): array {
        return $this->baseQuery()->where('p.featured',1)->orderBy('RAND()')->limit($limit)->get()->getResultArray();
    }

    public function getRelated(int $categoryId, int $excludeId, int $limit=3): array {
        return $this->baseQuery()->where('p.category_id',$categoryId)->where('p.id !=',$excludeId)->limit($limit)->get()->getResultArray();
    }

    public function suggest(string $q, int $limit=5): array {
        return $this->db->table('places p')
            ->select('p.id, p.name, p.city, p.country, c.name AS category_name, c.icon AS category_icon, c.color AS category_color')
            ->join('categories c','c.id = p.category_id','left')
            ->groupStart()
              ->like('p.name',$q)->orLike('p.city',$q)->orLike('p.tags',$q)->orLike('p.country',$q)
            ->groupEnd()
            ->orderBy('p.name','ASC')->limit($limit)->get()->getResultArray();
    }

    public function makeSlug(string $name, string $city): string {
        $base = trim(strtolower(preg_replace('/[^a-z0-9]+/i','-',$name.'-'.$city)),'-');
        $slug = $base; $i = 1;
        while ($this->where('slug',$slug)->countAllResults()) $slug = $base.'-'.($i++);
        return $slug;
    }
}
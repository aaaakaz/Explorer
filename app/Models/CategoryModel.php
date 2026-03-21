<?php
namespace App\Models;
use CodeIgniter\Model;

class CategoryModel extends Model {
    protected $table      = 'categories';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    public function allWithCount(): array {
        return $this->db->table('categories c')
            ->select('c.*, COUNT(p.id) AS place_count')
            ->join('places p','p.category_id = c.id','left')
            ->groupBy('c.id')
            ->orderBy('c.name','ASC')
            ->get()->getResultArray();
    }
}

<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

/**
 * CityExplore — Database Migration
 * Run with: php spark migrate
 * Or import config/schema.sql directly via phpMyAdmin
 */
class CreateCityExploreTables extends Migration {

    public function up(): void {

        // ── users ─────────────────────────────────────────────────────────
        $this->forge->addField([
            'id'            => ['type'=>'INT','auto_increment'=>true],
            'username'      => ['type'=>'VARCHAR','constraint'=>60,'null'=>false],
            'email'         => ['type'=>'VARCHAR','constraint'=>120,'null'=>false],
            'password_hash' => ['type'=>'VARCHAR','constraint'=>255,'null'=>false],
            'created_at'    => ['type'=>'TIMESTAMP','null'=>true,'default'=>null],
            'updated_at'    => ['type'=>'TIMESTAMP','null'=>true,'default'=>null],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('email');
        $this->forge->createTable('users', true);

        // ── places ────────────────────────────────────────────────────────
        $this->forge->addField([
            'id'          => ['type'=>'INT','auto_increment'=>true],
            'name'        => ['type'=>'VARCHAR','constraint'=>120,'null'=>false],
            'category'    => ['type'=>'ENUM','constraint'=>['food','parks','museums','hotels','nightlife','cafes'],'null'=>false],
            'city'        => ['type'=>'VARCHAR','constraint'=>80,'null'=>false,'default'=>'London'],
            'address'     => ['type'=>'VARCHAR','constraint'=>255,'null'=>true],
            'description' => ['type'=>'TEXT','null'=>true],
            'rating'      => ['type'=>'DECIMAL','constraint'=>'3,1','default'=>0.0],
            'saves'       => ['type'=>'INT','default'=>0],
            'lat'         => ['type'=>'DECIMAL','constraint'=>'10,7','null'=>true],
            'lng'         => ['type'=>'DECIMAL','constraint'=>'10,7','null'=>true],
            'created_at'  => ['type'=>'TIMESTAMP','null'=>true,'default'=>null],
            'updated_at'  => ['type'=>'TIMESTAMP','null'=>true,'default'=>null],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey(['city','category']);
        $this->forge->createTable('places', true);

        // ── saved_places ──────────────────────────────────────────────────
        $this->forge->addField([
            'id'         => ['type'=>'INT','auto_increment'=>true],
            'user_id'    => ['type'=>'INT','null'=>false],
            'place_id'   => ['type'=>'INT','null'=>false],
            'created_at' => ['type'=>'TIMESTAMP','null'=>true,'default'=>null],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['user_id','place_id']);
        $this->forge->addForeignKey('user_id',  'users',  'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('place_id', 'places', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('saved_places', true);

        // ── reviews ───────────────────────────────────────────────────────
        $this->forge->addField([
            'id'         => ['type'=>'INT','auto_increment'=>true],
            'place_id'   => ['type'=>'INT','null'=>false],
            'user_id'    => ['type'=>'INT','null'=>false],
            'rating'     => ['type'=>'DECIMAL','constraint'=>'3,1','null'=>false],
            'comment'    => ['type'=>'TEXT','null'=>false],
            'created_at' => ['type'=>'TIMESTAMP','null'=>true,'default'=>null],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('place_id', 'places', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('user_id',  'users',  'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('reviews', true);
    }

    public function down(): void {
        $this->forge->dropTable('reviews',      true);
        $this->forge->dropTable('saved_places', true);
        $this->forge->dropTable('places',       true);
        $this->forge->dropTable('users',        true);
    }
}

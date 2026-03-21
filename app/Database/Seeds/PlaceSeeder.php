<?php
namespace App\Database\Seeds;
use CodeIgniter\Database\Seeder;

/**
 * Sample data seeder
 * Run with: php spark db:seed PlaceSeeder
 */
class PlaceSeeder extends Seeder {
    public function run(): void {
        $places = [
            ['name'=>'Dishoom Covent Garden',  'category'=>'food',      'city'=>'London','address'=>"12 Upper St Martin's Ln",'description'=>'Legendary Bombay cafe, famous for the bacon naan.','rating'=>4.8,'lat'=>51.5121,'lng'=>-0.1246],
            ['name'=>'Nobu London',             'category'=>'food',      'city'=>'London','address'=>'19 Old Park Ln',          'description'=>'World-renowned Japanese-Peruvian fusion dining.','rating'=>4.7,'lat'=>51.5060,'lng'=>-0.1497],
            ['name'=>'Flat Iron',               'category'=>'food',      'city'=>'London','address'=>'17-18 Henrietta St',      'description'=>'Cult London steakhouse with incredible value.', 'rating'=>4.5,'lat'=>51.5119,'lng'=>-0.1237],
            ['name'=>'Monmouth Coffee',         'category'=>'cafes',     'city'=>'London','address'=>'27 Monmouth St',          'description'=>"One of London's finest specialty coffee roasters.",'rating'=>4.7,'lat'=>51.5130,'lng'=>-0.1260],
            ['name'=>'Hyde Park',               'category'=>'parks',     'city'=>'London','address'=>'Hyde Park, London W2',    'description'=>'Royal park in the heart of London, 350 acres.','rating'=>4.9,'lat'=>51.5073,'lng'=>-0.1657],
            ["name"=>"Regent's Park",           'category'=>'parks',     'city'=>'London','address'=>'Chester Rd, London NW1', 'description'=>'Elegant royal park with open air theatre.','rating'=>4.8,'lat'=>51.5313,'lng'=>-0.1570],
            ['name'=>'Tate Modern',             'category'=>'museums',   'city'=>'London','address'=>'Bankside, London SE1',    'description'=>'World-class modern art in a former power station.','rating'=>4.7,'lat'=>51.5076,'lng'=>-0.0994],
            ['name'=>'National Gallery',        'category'=>'museums',   'city'=>'London','address'=>'Trafalgar Square WC2N',   'description'=>'One of the greatest painting collections on earth.','rating'=>4.9,'lat'=>51.5089,'lng'=>-0.1283],
            ['name'=>'The Savoy',               'category'=>'hotels',    'city'=>'London','address'=>'Strand, London WC2R',     'description'=>'Iconic 5-star hotel on the Strand since 1889.','rating'=>4.8,'lat'=>51.5101,'lng'=>-0.1206],
            ['name'=>'The Ritz London',         'category'=>'hotels',    'city'=>'London','address'=>'150 Piccadilly W1',       'description'=>'The most famous luxury hotel in Britain.','rating'=>4.9,'lat'=>51.5067,'lng'=>-0.1426],
            ['name'=>'Nightjar Bar',            'category'=>'nightlife', 'city'=>'London','address'=>'129 City Rd EC1',         'description'=>'Award-winning speakeasy cocktail bar.','rating'=>4.6,'lat'=>51.5272,'lng'=>-0.0878],
            ["name"=>"Ronnie Scott's",          'category'=>'nightlife', 'city'=>'London','address'=>'47 Frith St W1D',         'description'=>'Legendary Soho jazz club since 1959.','rating'=>4.7,'lat'=>51.5131,'lng'=>-0.1315],
        ];
        foreach ($places as $place) {
            $this->db->table('places')->insert($place);
        }
        echo "Seeded " . count($places) . " places.\n";
    }
}

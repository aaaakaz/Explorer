-- Explorer2 Full Schema — import via phpMyAdmin SQL tab

SET NAMES utf8mb4;
SET foreign_key_checks = 0;
DROP TABLE IF EXISTS reviews;
DROP TABLE IF EXISTS places;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS users;
SET foreign_key_checks = 1;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(60) NOT NULL,
    email VARCHAR(120) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    avatar_color VARCHAR(20) NOT NULL DEFAULT '#f59e0b',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(80) NOT NULL,
    slug VARCHAR(80) NOT NULL UNIQUE,
    icon VARCHAR(50) NOT NULL DEFAULT 'bi-pin-map',
    color VARCHAR(20) NOT NULL DEFAULT '#6b7280'
) ENGINE=InnoDB;

INSERT INTO categories (name,slug,icon,color) VALUES
('Parks & Nature','parks','bi-tree','#16a34a'),
('Cafes & Coffee','cafes','bi-cup-hot','#92400e'),
('Museums','museums','bi-building','#7c3aed'),
('Restaurants','restaurants','bi-fork-knife','#dc2626'),
('Shopping','shopping','bi-bag','#2563eb'),
('Entertainment','entertainment','bi-ticket','#d97706'),
('Historic Sites','historic','bi-bank','#475569'),
('Nightlife','nightlife','bi-moon-stars','#4f46e5');

CREATE TABLE places (
    id INT AUTO_INCREMENT PRIMARY KEY,
    google_place_id VARCHAR(255) NULL UNIQUE,
    category_id INT NOT NULL DEFAULT 1,
    name VARCHAR(200) NOT NULL,
    slug VARCHAR(220) NOT NULL UNIQUE,
    description TEXT,
    address VARCHAR(350),
    city VARCHAR(100) NOT NULL DEFAULT '',
    country VARCHAR(100) NOT NULL DEFAULT '',
    lat DECIMAL(10,7) NOT NULL DEFAULT 0,
    lng DECIMAL(10,7) NOT NULL DEFAULT 0,
    phone VARCHAR(40),
    website VARCHAR(300),
    opening_hours VARCHAR(300),
    price_range TINYINT(1) DEFAULT 2,
    tags VARCHAR(500),
    featured TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET DEFAULT
) ENGINE=InnoDB;

CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    place_id INT NOT NULL,
    user_id INT NOT NULL,
    rating TINYINT(1) NOT NULL,
    comment TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (place_id) REFERENCES places(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO places (category_id,name,slug,description,address,city,country,lat,lng,phone,website,opening_hours,price_range,tags,featured) VALUES
(3,'British Museum','british-museum','One of the worlds greatest museums. Home to the Rosetta Stone, Egyptian mummies, and 8 million objects spanning two million years.','Great Russell St','London','United Kingdom',51.5194,-0.1269,'+44 20 7323 8000','https://www.britishmuseum.org','Mon-Sun 10:00-17:00',1,'history,art,rosetta stone,egypt,free,london',1),
(1,'Hyde Park','hyde-park','One of the largest Royal Parks in central London, 350 acres. Serpentine lake, Speakers Corner and Diana Memorial.','Hyde Park','London','United Kingdom',51.5079,-0.1657,'+44 300 061 2000','https://www.royalparks.org.uk','Daily 05:00-24:00',1,'park,nature,royal,lake,free,london',1),
(4,'Dishoom Covent Garden','dishoom-covent-garden','Bombay cafe inspired by the Irani cafes of old Bombay. Famous for legendary black daal and bacon naan rolls.','12 Upper St Martins Lane','London','United Kingdom',51.5120,-0.1257,'+44 20 7420 9320','https://www.dishoom.com','Mon-Fri 08:00-23:00',3,'indian,bombay,daal,naan,brunch,london',1),
(7,'Tower of London','tower-of-london','Over 900 years of history. Home to the Crown Jewels, Yeoman Warders and ravens.','Tower Hill','London','United Kingdom',51.5081,-0.0756,'+44 333 320 6000','https://www.hrp.org.uk','Tue-Sat 09:00-17:30',3,'historic,castle,crown jewels,medieval,london',1),
(2,'Monmouth Coffee','monmouth-coffee','London institution since 1978. Single-origin coffees roasted to perfection.','27 Monmouth St','London','United Kingdom',51.5131,-0.1272,'+44 20 7232 3010','https://www.monmouthcoffee.co.uk','Mon-Sat 08:00-18:30',2,'coffee,specialty,artisan,london',0),
(3,'Natural History Museum','natural-history-museum','Home to 80 million specimens including the blue whale and Dippy the dinosaur. Free entry.','Cromwell Rd','London','United Kingdom',51.4967,-0.1761,'+44 20 7942 5000','https://www.nhm.ac.uk','Daily 10:00-17:50',1,'museum,dinosaurs,blue whale,science,free,london',1),
(7,'St Pauls Cathedral','st-pauls-cathedral','Wren baroque masterpiece. Climb to the Whispering Gallery for panoramic views.','St Pauls Churchyard','London','United Kingdom',51.5135,-0.0985,'+44 20 7246 8350','https://www.stpauls.co.uk','Mon-Sat 08:30-16:30',3,'cathedral,wren,dome,views,london',1),
(4,'Rudys Pizza','rudys-pizza','Authentic Neapolitan pizza with 00 Caputo flour and San Marzano tomatoes.','Cotton Field Wharf','Manchester','United Kingdom',53.4800,-2.2279,'+44 161 237 5272','https://rudyspizza.co.uk','Mon-Thu 11:30-21:30',2,'pizza,italian,neapolitan,manchester',0),
(1,'Heaton Park','heaton-park','One of the largest municipal parks in Europe at 650 acres, with boating lake and tram museum.','Middleton Rd','Manchester','United Kingdom',53.5299,-2.2408,'','','Daily 08:00-Dusk',1,'park,lake,nature,free,manchester',0),
(3,'Manchester Museum','manchester-museum','Free museum with natural history, ancient Egypt and Stan the T-Rex.','Oxford Rd','Manchester','United Kingdom',53.4663,-2.2337,'+44 161 275 2648','https://www.museum.manchester.ac.uk','Tue-Sun 10:00-17:00',1,'museum,egypt,t-rex,free,manchester',0),
(7,'Edinburgh Castle','edinburgh-castle','Scotlands most famous fortress atop volcanic rock. Crown Jewels and Stone of Destiny.','Castlehill','Edinburgh','United Kingdom',55.9487,-3.1965,'+44 131 225 9846','https://www.historicenvironment.scot','Daily 09:30-18:00',3,'castle,scotland,crown jewels,edinburgh',1),
(7,'York Minster','york-minster','One of the largest Gothic cathedrals in Northern Europe. Worlds largest medieval stained glass window.','Deangate','York','United Kingdom',53.9620,-1.0818,'+44 1904 557216','https://www.yorkminster.org','Mon-Sat 09:00-17:30',2,'cathedral,gothic,medieval,stained glass,york',0),
(2,'200 Degrees Coffee','200-degrees-coffee','Independent UK coffee roaster hand-roasting since 2012. Exceptional flat whites.','16 Cardinal St','Birmingham','United Kingdom',52.4813,-1.8962,'','https://200degs.com','Mon-Fri 07:30-19:00',2,'coffee,roasters,specialty,birmingham',0),
(7,'Eiffel Tower','eiffel-tower','Gustave Eiffels 1889 iron lattice masterpiece. Most visited paid monument in the world.','Champ de Mars','Paris','France',48.8584,2.2945,'+33 892 70 12 39','https://www.toureiffel.paris','Daily 09:00-23:45',3,'eiffel tower,paris,landmark,views,romance',1),
(3,'The Louvre','the-louvre','Worlds largest art museum. Home to 35000 works including the Mona Lisa and Venus de Milo.','Rue de Rivoli','Paris','France',48.8607,2.3377,'+33 1 40 20 50 50','https://www.louvre.fr','Mon Thu Sat Sun 09:00-18:00',2,'museum,art,mona lisa,paris',1),
(1,'Central Park','central-park','New York Citys 843-acre oasis in Manhattan. Bethesda Fountain, Strawberry Fields and the Great Lawn.','Central Park','New York','United States',40.7851,-73.9684,'','https://www.centralparknyc.org','Daily 06:00-01:00',1,'park,nature,new york,manhattan,free',1),
(3,'Metropolitan Museum of Art','met-museum','One of the worlds greatest art museums with over 2 million works spanning 5000 years.','1000 5th Ave','New York','United States',40.7794,-73.9632,'+1 212 535 7710','https://www.metmuseum.org','Sun-Thu 10:00-17:00',2,'museum,art,new york,metropolitan',1),
(7,'Colosseum','colosseum-rome','The greatest amphitheatre ever built, completed in 80 AD holding 80000 spectators.','Piazza del Colosseo 1','Rome','Italy',41.8902,12.4924,'+39 06 3996 7700','https://www.colosseo.it','Daily 09:00-19:00',3,'colosseum,rome,gladiators,ancient,history',1),
(4,'Ichiran Ramen Shinjuku','ichiran-ramen','The famous solo ramen experience. Rich tonkotsu broth perfected over decades.','3-34-11 Shinjuku','Tokyo','Japan',35.6897,139.7008,'','https://en.ichiran.com','Daily 24hrs',2,'ramen,tonkotsu,tokyo,japan',1),
(1,'Bondi Beach','bondi-beach','Australias most famous beach. Golden sands, surf culture and the iconic coastal walk.','Queen Elizabeth Dr','Sydney','Australia',-33.8915,151.2767,'','https://www.sydney.com','Always open',1,'beach,surf,sydney,australia',1);

INSERT INTO users (username,email,password_hash,avatar_color) VALUES
('explorer','demo@explorer.com','$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TsI1YPWHhPODTHIFkE8F3.nNlhGi','#f59e0b');

INSERT INTO reviews (place_id,user_id,rating,comment) VALUES
(1,1,5,'Absolutely incredible. The Rosetta Stone alone is worth the trip. Free entry makes it one of the greatest public gifts anywhere in the world.'),
(2,1,5,'Perfect for a summer afternoon. The Serpentine is gorgeous. Speakers Corner on a Sunday is endlessly entertaining.'),
(3,1,5,'The black daal is life-changing. Queue moved fast and atmosphere is electric. Order the bacon naan roll.'),
(4,1,5,'Stood where Anne Boleyn was executed. Crown Jewels are jaw-dropping. Book tickets well in advance.'),
(6,1,5,'The blue whale suspended from the ceiling never gets old. Free and genuinely world-class.'),
(7,1,5,'The Whispering Gallery lives up to its name. Whisper and your friend on the other side hears you.'),
(11,1,5,'One OClock Gun fired right next to us, terrifying and brilliant. Views over Edinburgh are superb.'),
(14,1,5,'Nothing prepares you for standing beneath it. Take the stairs at least one way for the full experience.'),
(15,1,5,'The Mona Lisa is smaller than expected but the Winged Victory stopped me in my tracks.'),
(16,1,5,'Bethesda Fountain, Strawberry Fields, the Great Lawn. Watching New York go by is one of lifes great pleasures.'),
(18,1,5,'Standing inside knowing this was built 2000 years ago is genuinely humbling. Book the underground arena tour.'),
(19,1,5,'The private booth concept is genius. Best ramen I have ever had.'),
(20,1,5,'Bondi to Coogee coastal walk is stunning. The beach is beautiful but the walk steals the show.');

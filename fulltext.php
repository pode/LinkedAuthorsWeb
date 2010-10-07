<?php

/*

Copyright 2010 ABM-utvikling

This file is part of "Podes LinkedAuthorsWeb".

"Podes LinkedAuthorsWeb" is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

"Podes LinkedAuthorsWeb" is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with "Podes LinkedAuthorsWeb". If not, see <http://www.gnu.org/licenses/&gt;.

*/

// This is a manually maintained mapping between work IDs and Gutenberg texts

$works = array(

	'http://www.bibpode.no/work/Hamsun_Knut_markens_groede' => array(
		array('title' => 'Growth of the Soil (English) (as Author)', 'url' => 'http://www.gutenberg.org/ebooks/10984')
	), 
	
	'http://www.bibpode.no/work/Hamsun_Knut_sult' => array(
		array('title' => 'Hunger (English) (as Author)', 'url' => 'http://www.gutenberg.org/ebooks/8387'), 
		array('title' => 'Hunger Book One (Hebrew) (as Author)', 'url' => 'http://www.gutenberg.org/ebooks/18291'), 
		array('title' => 'Sult (Norwegian) (as Author)', 'url' => 'http://www.gutenberg.org/ebooks/30027')
	),

	'http://www.bibpode.no/work/Hamsun_Knut_den_sidste_glaede' => array(
		array('title' => 'Look Back on Happiness (English) (as Author)', 'url' => 'http://www.gutenberg.org/ebooks/8445'),
	), 
	
	'http://www.bibpode.no/work/Hamsun_Knut_pan' => array(
		array('title' => 'Pan (English) (as Author)', 'url' => 'http://www.gutenberg.org/ebooks/7214'),
	), 
	
	'http://www.bibpode.no/work/Hamsun_Knut_ny_jord' => array(
		array('title' => 'Shallow Soil (English) (as Author)', 'url' => 'http://www.gutenberg.org/ebooks/7537'),
	), 
	
	'http://www.bibpode.no/work/Hamsun_Knut_landstrykere' => array(
		array('title' => 'Wanderers (English) (as Author)', 'url' => 'http://www.gutenberg.org/ebooks/7762'),
	)

);

if (!empty($_GET['work']) && $works[$_GET['work']]) {

	echo(json_encode($works[$_GET['work']]));
	
}

?>
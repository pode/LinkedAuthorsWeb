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

// Fetch language data from Lexvo
if (!empty($_GET['lexvo']) && !empty($_GET['label'])) {
	
	if (preg_match('/^http:\/\/lexvo.org\/id\/iso639-3\/[a-z]{2,3}$/', $_GET['lexvo'])) {
		$url = $_GET['lexvo'];
		// We need to massage the URL to get it in a format Lexvo likes
		$url = preg_replace('/\/id\//', '/data/', $url);
		$url = preg_replace('/lexvo/', 'www.lexvo', $url);
		
		// create curl resource
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_HEADER, true);
		//return the transfer as a string
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array (
         	"Accept: application/rdf+xml"
    	));
    	curl_setopt($ch, CURLOPT_FAILONERROR, true);
		// Get the contents of the page
		$rdf = curl_exec($ch);
		// close curl resource to free up system resources
		curl_close($ch); 
		
		preg_match('/' . $_GET['label'] . '">(.*)</', $rdf, $match);
		echo(json_encode(array('label' => $match[1])));
		exit;
	}
	
// A simple proxy for non-JSONP sources
} elseif (!empty($_GET['what']) && !empty($_GET['who'])) {


	$url = '';
	if ($_GET['what'] == 'influenced') {
		
		$sparql = '
select distinct * where {
<' . $_GET["who"] . '> dbpprop:influenced ?author .
?author foaf:name ?authorName .
}';

		$url = 'http://dbpedia.org/sparql?default-graph-uri=http://dbpedia.org&should-sponge=&query=' . urlencode($sparql) . '&format=' . urlencode('application/sparql-results+json');
		
	} elseif ($_GET['what'] == 'influences') {
		
		$sparql = '
select distinct * where {
<' . $_GET["who"] . '> dbpprop:influences ?author .
?author foaf:name ?authorName .
}';

		$url = 'http://dbpedia.org/sparql?default-graph-uri=http://dbpedia.org&should-sponge=&query=' . urlencode($sparql) . '&format=' . urlencode('application/sparql-results+json');
		
	} elseif($_GET['what'] == 'dbpedia') {
		// urlencode($_GET['who'])
		
		$sparql = '
SELECT DISTINCT * WHERE {
<' . $_GET['who'] . '> dbpedia-owl:thumbnail ?thumbnail 
OPTIONAL { <' . $_GET['who'] . '> dbpprop:name ?name } .
OPTIONAL { <' . $_GET['who'] . '> dbpedia-owl:activeYearsEndYear ?activeYearsEndYear } .
OPTIONAL { <' . $_GET['who'] . '> dbpedia-owl:activeYearsStartYear ?activeYearsStartYear } .
OPTIONAL { <' . $_GET['who'] . '> dbpedia-owl:birthDate ?birthDate } .
OPTIONAL { <' . $_GET['who'] . '> dbpedia-owl:birthName ?birthName } .
OPTIONAL { <' . $_GET['who'] . '> dbpedia-owl:birthPlace ?birthPlace } .
OPTIONAL { ?birthPlace dbpprop:name ?birthPlaceName } .
OPTIONAL { <' . $_GET['who'] . '> dbpedia-owl:deathDate ?deathDate } .
OPTIONAL { <' . $_GET['who'] . '> dbpedia-owl:deathPlace ?deathPlace } .
OPTIONAL { ?deathPlace dbpprop:name ?deathPlaceName } .
OPTIONAL { <' . $_GET['who'] . '> dbpedia-owl:nationality ?nationality } .
OPTIONAL { ?nationality dbpedia-owl:thumbnail ?nationalityThumbnail } .
OPTIONAL { ?nationality dbpprop:nativeName ?nationalityLabel } .
}';
		
/* Example: 
SELECT DISTINCT * WHERE {
<' . urlencode($_GET['who']) . '> dbpedia-owl:thumbnail ?thumbnail 
OPTIONAL { <' . urlencode($_GET['who']) . '> dbpedia-owl:activeYearsEndYear ?activeYearsEndYear } .
OPTIONAL { <' . urlencode($_GET['who']) . '> dbpedia-owl:activeYearsStartYear ?activeYearsStartYear } .
OPTIONAL { <' . urlencode($_GET['who']) . '> dbpedia-owl:birthDate ?birthDate } .
OPTIONAL { <' . urlencode($_GET['who']) . '> dbpedia-owl:birthName ?birthName } .
OPTIONAL { <' . urlencode($_GET['who']) . '> dbpedia-owl:birthPlace ?birthPlace } .
OPTIONAL { <' . urlencode($_GET['who']) . '> dbpedia-owl:deathDate ?deathDate } .
OPTIONAL { <' . urlencode($_GET['who']) . '> dbpedia-owl:deathPlace ?deathPlace } .
OPTIONAL { <' . urlencode($_GET['who']) . '> dbpedia-owl:nationality ?nationality } .
}
*/
		$url = 'http://dbpedia.org/sparql?default-graph-uri=http://dbpedia.org&should-sponge=&query=' . urlencode($sparql) . '&format=' . urlencode('application/sparql-results+json');
		// w/nationality $url = 'http://dbpedia.org/sparql?default-graph-uri=http://dbpedia.org&should-sponge=&query=SELECT+DISTINCT+*+WHERE+{%0D%0A%3C' . urlencode($_GET['who']) . '%3E+dbpedia-owl:thumbnail+%3Fthumbnail+.%0D%0AOPTIONAL+{+%3C' . urlencode($_GET['who']) . '%3E+dbpedia-owl:activeYearsEndYear+%3FactiveYearsEndYear+}+.+%0D%0AOPTIONAL++{+%3C' . urlencode($_GET['who']) . '%3E+dbpedia-owl:activeYearsStartYear+%3FactiveYearsStartYear+}+.+%0D%0AOPTIONAL++{+%3C' . urlencode($_GET['who']) . '%3E+dbpedia-owl:birthDate+%3FbirthDate+}+.+%0D%0AOPTIONAL++{+%3C' . urlencode($_GET['who']) . '%3E+dbpedia-owl:birthName+%3FbirthName+}+.+%0D%0AOPTIONAL++{+%3C' . urlencode($_GET['who']) . '%3E+dbpedia-owl:nationality+%3Fnationality+}+.+%0D%0AOPTIONAL++{+%3C' . urlencode($_GET['who']) . '%3E+dbpedia-owl:birthPlace+%3FbirthPlace+}+.+%0D%0AOPTIONAL++{+%3C' . urlencode($_GET['who']) . '%3E+dbpedia-owl:deathDate+%3FdeathDate+}+.+%0D%0AOPTIONAL++{+%3C' . urlencode($_GET['who']) . '%3E+dbpedia-owl:deathPlace+%3FdeathPlace+}+.+%0D%0A}&format=' . urlencode('application/sparql-results+json');
	
	} elseif($_GET['what'] == 'dbpedianame') {
		$url = 'http://dbpedia.org/sparql?default-graph-uri=http%3A%2F%2Fdbpedia.org&should-sponge=&query=select+distinct+%3Fname+where+{%3C' . urlencode($_GET['who']) . '%3E+dbpprop%3Aname+%3Fname}&format=' . urlencode('application/sparql-results+json');
	}

	// create curl resource
	$ch = curl_init();
	
	// set url
	curl_setopt($ch, CURLOPT_URL, $url);
	
	//return the transfer as a string
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	
	// $output contains the output string
	$output = curl_exec($ch);
	
	// close curl resource to free up system resources
	curl_close($ch); 
	
	header('Content-Type: application/sparql-results+json');
	echo($_GET['url'] . ' ' . $output);

} 

?>

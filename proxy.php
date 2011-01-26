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

// A simple proxy for non-JSONP sources
if (!empty($_GET['what']) && !empty($_GET['who'])) {


	$url = '';
	
	if ($_GET['what'] == 'gutenberg') {
		
		// Chop off the 32 first characters, to get the numeric ID
		$gutenid = substr($_GET['who'], 32);
		// Construct the SPARQL query
		$sparql = '
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
SELECT ?title WHERE {
<http://www4.wiwiss.fu-berlin.de/gutendata/resource/etext' . $gutenid . '> rdfs:label ?title .
}';
		// Assemble the URL
		$url = 'http://www4.wiwiss.fu-berlin.de/gutendata/sparql?query=' . urlencode($sparql) . '&output=json';

	} elseif ($_GET['what'] == 'influenced') {
		
		$sparql = '
select distinct * where {
<' . $_GET["who"] . '> dbpprop:influenced ?author .
}';

		$url = 'http://dbpedia.org/sparql?default-graph-uri=http://dbpedia.org&should-sponge=&query=' . urlencode($sparql) . '&format=' . urlencode('application/sparql-results+json');
		
	} elseif ($_GET['what'] == 'influences') {
		
		$sparql = '
select distinct * where {
<' . $_GET["who"] . '> dbpprop:influences ?author .
}';

		$url = 'http://dbpedia.org/sparql?default-graph-uri=http://dbpedia.org&should-sponge=&query=' . urlencode($sparql) . '&format=' . urlencode('application/sparql-results+json');
		
	} elseif ($_GET['what'] == 'abstract') {
		
		$sparql = '
SELECT DISTINCT * WHERE {
<' . $_GET['who'] . '> foaf:page ?page ;
dbpedia-owl:abstract ?abstract .
filter langMatches(lang(?abstract), "nn")
}';

		$url = 'http://dbpedia.org/sparql?default-graph-uri=http://dbpedia.org&should-sponge=&query=' . urlencode($sparql) . '&format=' . urlencode('application/sparql-results+json');
		
	} elseif($_GET['what'] == 'dbpedia') {
		// urlencode($_GET['who'])
		
		$sparql = '
PREFIX dbpediaowl: <http://dbpedia.org/ontology/>
PREFIX owl: <http://www.w3.org/2002/07/owl#>
PREFIX dbpprop: <http://dbpedia.org/property/>
SELECT DISTINCT ?thumbnail,?activeYearsEndYear,?activeYearsStartYear,?birthDate,?birthName,?birthPlace,?birthPlaceName,?deathDate,?deathPlace,?deathPlaceName,?nationality,?nationalityThumbnail WHERE {
<' . $_GET['who'] . '> dbpprop:name ?name  .
OPTIONAL { <' . $_GET['who'] . '> dbpediaowl:activeYearsEndYear ?activeYearsEndYear } .
OPTIONAL { <' . $_GET['who'] . '> dbpediaowl:activeYearsStartYear ?activeYearsStartYear } .
OPTIONAL { <' . $_GET['who'] . '> dbpediaowl:birthDate ?birthDate } .
OPTIONAL { <' . $_GET['who'] . '> dbpediaowl:birthName ?birthName } .
OPTIONAL { <' . $_GET['who'] . '> dbpediaowl:birthPlace ?birthPlace .
?birthPlace dbpprop:name ?birthPlaceName } .
OPTIONAL { <' . $_GET['who'] . '> dbpediaowl:deathDate ?deathDate } .
OPTIONAL { <' . $_GET['who'] . '> dbpediaowl:deathPlace ?deathPlace .
?deathPlace dbpprop:name ?deathPlaceName } .
OPTIONAL { <' . $_GET['who'] . '> dbpediaowl:nationality ?nationality .
?nationality dbpediaowl:thumbnail ?nationalityThumbnail } .
 <' . $_GET['who'] . '> dbpedia-owl:thumbnail ?thumbnail .

}';
		
/* Example: 
PREFIX dbpediaowl: <http://dbpedia.org/ontology/>
PREFIX owl: <http://www.w3.org/2002/07/owl#>
PREFIX dbpprop: <http://dbpedia.org/property/>
SELECT DISTINCT ?thumbnail,?activeYearsEndYear,?activeYearsStartYear,?birthDate,?birthName,?birthPlace,?birthPlaceName,?deathDate,?deathPlace,?deathPlaceName,?nationality,?nationalityThumbnail WHERE {
<http://dbpedia.org/resource/Knut_Hamsun> dbpprop:name ?name  .
OPTIONAL { <http://dbpedia.org/resource/Knut_Hamsun> dbpediaowl:activeYearsEndYear ?activeYearsEndYear } .
OPTIONAL { <http://dbpedia.org/resource/Knut_Hamsun> dbpediaowl:activeYearsStartYear ?activeYearsStartYear } .
OPTIONAL { <http://dbpedia.org/resource/Knut_Hamsun> dbpediaowl:birthDate ?birthDate } .
OPTIONAL { <http://dbpedia.org/resource/Knut_Hamsun> dbpediaowl:birthName ?birthName } .
OPTIONAL { <http://dbpedia.org/resource/Knut_Hamsun> dbpediaowl:birthPlace ?birthPlace .
?birthPlace dbpprop:name ?birthPlaceName } .
OPTIONAL { <http://dbpedia.org/resource/Knut_Hamsun> dbpediaowl:deathDate ?deathDate } .
OPTIONAL { <http://dbpedia.org/resource/Knut_Hamsun> dbpediaowl:deathPlace ?deathPlace .
?deathPlace dbpprop:name ?deathPlaceName } .
OPTIONAL { <http://dbpedia.org/resource/Knut_Hamsun> dbpediaowl:nationality ?nationality .
?nationality dbpediaowl:thumbnail ?nationalityThumbnail } .
 <http://dbpedia.org/resource/Knut_Hamsun> dbpedia-owl:thumbnail ?thumbnail .

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

<?php

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
	if ($_GET['what'] == 'dbpedia') {
		// urlencode($_GET['who'])
		
		$sparql = '
SELECT DISTINCT * WHERE {
<' . $_GET['who'] . '> dbpedia-owl:thumbnail ?thumbnail 
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

} else {

echo('<?xml version="1.0" encoding="utf-8" ?>');
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="nb_NO">
<head>
<title>LinkedAuthors</title>
<script src="jquery/jquery-1.4.2.min.js" type="text/javascript"></script>
<script type="text/javascript">

var endpointprefix = 'http://bibpode.no/rdfstore/endpoint.php?query=';
var endpointpostfix = '&jsonp=?';

var languages = new Array();

$(document).ready(function() {

});

var author = '';

function showWorks() {

	// Clear the list of works
	$('#workslist').empty();
	
	// alert('author: ' + author);
	if ($("#authorselect option:selected").val() != '' && $("#authorselect option:selected").val() != author) {
		
		// alert('ny forfatter! : ' + $("#authorselect option:selected").val());
		author = $("#authorselect option:selected").val();
		showAuthor(author);
		
		// Display the list of available languages for this author
					  var language_sparql = 'PREFIX dct: <http://purl.org/dc/terms/> \n';
		language_sparql = language_sparql + 'PREFIX frbr: <http://purl.org/vocab/frbr/core#> \n';
		language_sparql = language_sparql + 'PREFIX person: <http://www.bibpode.no/person/> \n';
		language_sparql = language_sparql + 'SELECT DISTINCT ?language WHERE { \n';
		language_sparql = language_sparql + '?work a frbr:Work ; \n';
		language_sparql = language_sparql + 'dct:title ?title ; \n';
		language_sparql = language_sparql + 'dct:creator ' + author + ' . \n';
		language_sparql = language_sparql + '?expression frbr:realizationOf ?work . \n';
		language_sparql = language_sparql + '?expression dct:language ?language . \n';
		language_sparql = language_sparql + '}';
		var language_url = endpointprefix + escape(language_sparql) + endpointpostfix;
		var params = { 'output': 'json' };
		// Empty the list
		$('#language_select').empty();
		$('#language_select').append('<option value="">Velg språk...</option>');
		// Get the actual data
		$.getJSON(language_url, params, function(json, status) {
			if (json.results.bindings){
				$.each(json.results.bindings, function(i, n) {
					var item = json.results.bindings[i];
					if (languages[item.language.value]) {
						$('#language_select').append('<option value="' + item.language.value + '">' + languages[item.language.value] + '</option>');
					} else {
						$('#language_select').append('<option value="' + item.language.value + '">' + item.language.value + '</option>');
					}
				});
			} else {
				alert('Something went wrong...');
			}
		});
		
	} 
	var sortby = 'firstEdition';
	if ($("#sortby option:selected").val() == 'title') {
		sortby = 'title';
	}
	var sortorder = 'ASC';
	if ($("#sortorder option:selected").val() == 'desc') {
		sortorder = 'DESC';
	}
	
	// Show works
			   var works_sparql = 'PREFIX pode: <http://www.bibpode.no/vocabulary#> \n';
	works_sparql = works_sparql + 'PREFIX dct: <http://purl.org/dc/terms/> \n';
	works_sparql = works_sparql + 'PREFIX frbr: <http://purl.org/vocab/frbr/core#> \n';
	works_sparql = works_sparql + 'PREFIX person: <http://www.bibpode.no/person/> \n';
	works_sparql = works_sparql + 'SELECT DISTINCT ?work ?firstEdition ?title WHERE { \n';
	works_sparql = works_sparql + '?work a frbr:Work ; \n';
	works_sparql = works_sparql + 'pode:firstEdition ?firstEdition ; \n';
	works_sparql = works_sparql + 'dct:title ?title ; \n';
	works_sparql = works_sparql + 'dct:creator ' + author + ' . \n';
	// Check if a particular language has been chosen
	// alert('språk valgt ' + $("#language_select option:selected").val());
	if ($("#language_select option:selected").val() != '') {
		// alert('språk valgt ' + $("#language_select option:selected").val());
		works_sparql = works_sparql + '?expression a frbr:Expression ; \n';
		works_sparql = works_sparql + 'frbr:realizationOf ?work ; \n';
		works_sparql = works_sparql + 'dct:language <' + $("#language_select option:selected").val() + '> . \n';
	}
	works_sparql = works_sparql + 'OPTIONAL { ?host frbr:part ?work } . \n';
	works_sparql = works_sparql + 'FILTER (!bound(?host))  \n';
	works_sparql = works_sparql + '} ORDER BY ' + sortorder + ' (?' + sortby + ') \n';
	
	var works_url = endpointprefix + escape(works_sparql) + endpointpostfix;
	var params = { 'output': 'json' };

	$.getJSON(works_url, params, function(json, status) {
		if (json.results.bindings){
			// alert(json.results.bindings[0].title.value);
			$.each(json.results.bindings, function(i, n) {
				var item = json.results.bindings[i];
				$('#workslist').append('<li><span class="work" id="work' + i + '" onClick="showExpressions(\'work' + i + '\', \'' + item.work.value + '\');">' + item.title.value + ' (<span class="firstEdition">' + item.firstEdition.value + '</span>)</span></li>');
			});
		} else {
			alert('Something went wrong...');
		}
	});
	
}

function showExpressions(elemid, workuri) {
	
	// Check to see if this work already has (hidden) children attached, if so show them
	if ($('#' + elemid).siblings().length > 0) {
		
		// Do nothing if the clicked work already has visible children
		if ($('#' + elemid).siblings('ul').is(":visible")) {
			return;
		}
		
		// Hide all "open" works
		$('.work').siblings().hide();
		// Show this one
		$('#' + elemid).siblings().show();
		
	} else {

		// Show expressions
			  var expr_sparql = 'PREFIX pode: <http://www.bibpode.no/vocabulary#> \n';
		expr_sparql = expr_sparql + 'PREFIX pode_lf: <http://www.bibpode.no/lf/> \n';
		expr_sparql = expr_sparql + 'PREFIX dct: <http://purl.org/dc/terms/> \n';
		expr_sparql = expr_sparql + 'PREFIX frbr: <http://purl.org/vocab/frbr/core#> \n';
		expr_sparql = expr_sparql + 'PREFIX person: <http://www.bibpode.no/person/> \n';
		expr_sparql = expr_sparql + 'PREFIX work: <http://www.bibpode.no/work/> \n';
		expr_sparql = expr_sparql + 'SELECT DISTINCT * WHERE { \n';
		expr_sparql = expr_sparql + '?expression a frbr:Expression . \n';
		expr_sparql = expr_sparql + '?expression frbr:realizationOf <' + workuri + '> . \n';
		if ($("#language_select option:selected").val() != '') {
			expr_sparql = expr_sparql + '?expression dct:language <' + $("#language_select option:selected").val() + '> . \n';
		} else {
			expr_sparql = expr_sparql + '?expression dct:language ?language . \n';
		}
		expr_sparql = expr_sparql + '?expression dct:format ?format . \n';
		expr_sparql = expr_sparql + '?format rdfs:label ?formatlabel . \n';
		expr_sparql = expr_sparql + '} \n';
		
		var expr_url = endpointprefix + escape(expr_sparql) + endpointpostfix;
		var params = { 'output': 'json' };
	
		$.getJSON(expr_url, params, function(json, status) {
			if (json.results.bindings){
				var out = '<ul>';
				var expressions = new Array();
				$.each(json.results.bindings, function(i, n) {
					var item = json.results.bindings[i];
					if (expressions[item.expression.value]) {
						// Add the formatlabel to the existing formatlabel
						expressions[item.expression.value].formatlabel.value = expressions[item.expression.value].formatlabel.value + ', ' + item.formatlabel.value;
						// If format has been sasved as http://www.bibpode.no/ff/di, replace it with a new URI
						if (expressions[item.expression.value].format.value && expressions[item.expression.value].format.value == 'http://www.bibpode.no/ff/di') {
							expressions[item.expression.value].format.value = item.format.value;
						}
					} else {
						expressions[item.expression.value] = item;
					} 
				});
				var i = 0;
				for (var e in expressions) {
					// alert(e);
					var item = expressions[e];
					var idattribute = elemid + 'expression' + i;
					out = out + '<li><span id="' + idattribute + '" class="expression" onClick="showManifestations(\'' + idattribute + '\', \'' + workuri + '\', \'';
					if (item.language) {
						out = out + item.language.value;
					} else {
						out = out + $("#language_select option:selected").val();
					}
					out = out + '\', \'' + item.format.value + '\');">';
					out = out + '<span class="format">' + item.formatlabel.value + '</span>';
					if (item.language) {
						out = out + ' på ';
						if (languages[item.language.value]) {
							out = out + '<span class="language">' + languages[item.language.value] + '</span>';
						} else {
							out = out + '<span class="language languageuri" id="' + idattribute + 'language">' + item.language.value + '</span>';
						}
					}
					out = out + '</span></li>';
					i++;
				}
				var out = out + '</ul>';
				// Hide all "open" works
				$('.work').siblings().hide();
				// Insert the output after the span that was clicked
				$('#' + elemid).after(out);
			 	// Turn URIs into labels
				$('.languageuri').each(function(index) {
					// alert($(this).attr('id'));
			   		setLanguageLabels($(this).attr('id'), $(this).text());
				});			} else {
				alert('Something went wrong...');	
			}
		});
		
		// Show parts
/* Example: 
PREFIX dct: <http://purl.org/dc/terms/>
PREFIX frbr: <http://purl.org/vocab/frbr/core#>
PREFIX pode: <http://www.bibpode.no/vocabulary#>

SELECT DISTINCT ?title ?subtitle ?firstEdition WHERE {
<http://www.bibpode.no/work/Hamsun_Knut_siesta> frbr:part ?part .
?part dct:title ?title ;
pode:firstEdition ?firstEdition .
OPTIONAL { ?part dct:subtitle ?subtitle . }
}
*/
		var parturl = 'http://bibpode.no/rdfstore/endpoint.php?query=PREFIX+dct%3A+%3Chttp%3A%2F%2Fpurl.org%2Fdc%2Fterms%2F%3E%0D%0APREFIX+frbr%3A+%3Chttp%3A%2F%2Fpurl.org%2Fvocab%2Ffrbr%2Fcore%23%3E%0D%0APREFIX+pode%3A+%3Chttp%3A%2F%2Fwww.bibpode.no%2Fvocabulary%23%3E%0D%0A%0D%0ASELECT+DISTINCT+%3Ftitle+%3Fsubtitle+%3FfirstEdition+WHERE+{%0D%0A%3C' + workuri + '%3E+frbr%3Apart+%3Fpart+.%0D%0A%3Fpart+dct%3Atitle+%3Ftitle+%3B%0D%0Apode%3AfirstEdition+%3FfirstEdition+.%0D%0AOPTIONAL+{+%3Fpart+dct%3Asubtitle+%3Fsubtitle+.+}%0D%0A}&jsonp=?';
		var partparams = { 'output': 'json' };
		$.getJSON(parturl, partparams, function(json, status) {
			if (json.results.bindings[0]){
				// alert(json.results.bindings[0].title.value);
				var out = '<ul><li>Deler:<ul>';
				$.each(json.results.bindings, function(i, n) {
					var item = json.results.bindings[i];
					var idattribute = elemid + 'expression' + i;
					// alert(item.title.value);
					out = out + '<li>' + item.title.value;
					if (item.subtitle) {
						out = out + ' : ' + item.title.value;
					}
					if (item.firstEdition) {
						out = out + ' (' + item.firstEdition.value + ')';
					}
					out = out + '</li>';
				});
				var out = out + '</ul></li></ul>';
				// Insert the output after the span that was clicked
				$('#' + elemid).after(out);
			}
		});
	
	}	
}

function setLanguageLabels(id, uri){
	
	var url = 'index.php?label=nb&lexvo=' + uri;
	var name = '';
	$.getJSON(url, function(json){
		// alert(json.label);
		if (json && json.label){
			name = json.label;
		} else {
			// Return the URI if a name was not found
			// TODO: Guess the name from the URI?
			name = uri;	
		}
		// Save the label for later re-use
		languages[uri] = name;
		$('#' + id).empty().append(name).removeClass('languageuri');
 	});

}

function showManifestations(elemid, workuri, languageuri, formaturi) {
	
/* Example: 
PREFIX pode: <http://www.bibpode.no/vocabulary#>
PREFIX dct: <http://purl.org/dc/terms/>
PREFIX frbr: <http://purl.org/vocab/frbr/core#>
SELECT DISTINCT ?title ?subtitle ?responsibility ?issued ?physicalDescription WHERE {
?expression a frbr:Expression .
?expression frbr:realizationOf <http://www.bibpode.no/work/Hamsun_Knut_sult> .
?expression dct:language <http://www.lingvoj.org/lang/nb> .
?expression dct:format <http://www.bibpode.no/ff/l> .
?expression frbr:embodiment ?manifestation .
?manifestation a frbr:Manifestation .
?manifestation dct:title ?title .
OPTIONAL{ ?manifestation pode:subtitle ?subtitle . }
OPTIONAL{ ?manifestation pode:responsibility ?responsibility . }
OPTIONAL{ ?manifestation pode:publicationPlace ?publicationPlace . }
OPTIONAL{ ?manifestation pode:publisher ?publisher . }
OPTIONAL{ ?manifestation dct:issued ?issued . }
OPTIONAL{ ?manifestation pode:physicalDescription ?physicalDescription . }
}
ORDER BY ?title ?issued
*/

	var url = 'http://bibpode.no/rdfstore/endpoint.php?query=PREFIX+pode%3A+%3Chttp%3A%2F%2Fwww.bibpode.no%2Fvocabulary%23%3E%0D%0APREFIX+dct%3A+%3Chttp%3A%2F%2Fpurl.org%2Fdc%2Fterms%2F%3E%0D%0APREFIX+frbr%3A+%3Chttp%3A%2F%2Fpurl.org%2Fvocab%2Ffrbr%2Fcore%23%3E%0D%0ASELECT+DISTINCT+%3Ftitle+%3Fsubtitle+%3Fresponsibility+%3Fissued+%3FphysicalDescription+WHERE+{%0D%0A%3Fexpression+a+frbr%3AExpression+.%0D%0A%3Fexpression+frbr%3ArealizationOf+%3C' + workuri + '%3E+.%0D%0A%3Fexpression+dct%3Alanguage+%3C' + languageuri + '%3E+.%0D%0A%3Fexpression+dct%3Aformat+%3C' + formaturi + '%3E+.%0D%0A%3Fexpression+frbr%3Aembodiment+%3Fmanifestation+.%0D%0A%3Fmanifestation+a+frbr%3AManifestation+.%0D%0A%3Fmanifestation+dct%3Atitle+%3Ftitle+.%0D%0AOPTIONAL{+%3Fmanifestation+pode%3Asubtitle+%3Fsubtitle+.+}%0D%0AOPTIONAL{+%3Fmanifestation+pode%3Aresponsibility+%3Fresponsibility+.+}%0D%0AOPTIONAL{+%3Fmanifestation+pode%3ApublicationPlace+%3FpublicationPlace+.+}%0D%0AOPTIONAL{+%3Fmanifestation+pode%3Apublisher+%3Fpublisher+.+}%0D%0AOPTIONAL{+%3Fmanifestation+dct%3Aissued+%3Fissued+.+}%0D%0AOPTIONAL{+%3Fmanifestation+pode%3AphysicalDescription+%3FphysicalDescription+.+}%0D%0A}%0D%0AORDER+BY+%3Ftitle+%3Fissued&jsonp=?';
	
	var params = { 'output': 'json' };
	
	$.getJSON(url, params, function(json, status) {
		if (json.results.bindings){
			var out = '<ul>';
			$.each(json.results.bindings, function(i, n) {
				var item = json.results.bindings[i];
				var idattribute = elemid + 'manifestation' + i;
				out = out + '<li id="' + idattribute + '" class="manifestation">' + item.title.value;
				if (item.subtitle) {
					out = out + ' : ' + item.subtitle.value;
				}
				if (item.responsibility) {
					out = out + ' / ' + item.responsibility.value;
				}
				if (item.issued) {
					out = out + '. ' + item.issued.value;
				}
				if (item.physicalDescription) {
					out = out + '. ' + item.physicalDescription.value;
				}
				out = out + '</li>';
			});
			var out = out + '</ul>';
			$('#' + elemid).after(out);
			// Remove the onClick attribute
			$('#' + elemid).removeAttr("onClick");
			$('#' + elemid).addClass("expanded");
		} else {
			alert('Something went wrong...');	
		}
	});
	
}

function showAuthor(author) {
	
	// Clear the box
	$('#authorbox').empty();

	// Fill the box

	// Translate our ID to the DBPedia one
/* Example: 
PREFIX person: <http://www.bibpode.no/person/> 
SELECT DISTINCT ?id WHERE {
person:Hamsun_Knut owl:sameAs ?id .
FILTER(REGEX(?id, "dbpedia"))
}
*/
	var url = 'http://bibpode.no/rdfstore/endpoint.php?query=PREFIX+person%3A+%3Chttp%3A%2F%2Fwww.bibpode.no%2Fperson%2F%3E+%0D%0ASELECT+DISTINCT+%3Fid+WHERE+{%0D%0A' + author + '+owl%3AsameAs+%3Fid+.%0D%0AFILTER%28REGEX%28%3Fid%2C+%22dbpedia%22%29%29%0D%0A}&jsonp=?';
	var params = { 'output': 'json' };
	$.getJSON(url, params, function(json, status) {
		if (json.results.bindings){
			getDbpediaData(json.results.bindings[0].id.value);
		} else {
			alert('Something went wrong...');	
		}
	});
	
}

function getDbpediaData(dbpediaid) {
	
	var url = 'index.php?what=dbpedia&who=' + dbpediaid;
	$.getJSON(url, function(json){
		if (json.results.bindings){
			// alert(json.results.bindings[0].title.value);
			$.each(json.results.bindings, function(i, n) {
				var item = json.results.bindings[i];
				if (item.thumbnail) {
					$('#authorbox').append('<img src="' + item.thumbnail.value + '" title="Portrett fra Wikipedia" />');
				}
				if (item.birthName) {
					$('#authorbox').append('<p>Fødselsnavn: ' + item.birthName.value + '</p>');
				}
				if (item.birthDate) {
					var birthDate = new Date(item.birthDate.value);
					$('#authorbox').append('<p>Født: ' + birthDate.toLocaleDateString() + '</p>');
				}
				if (item.birthPlaceName) {
					$('#authorbox').append('<p>Fødselssted: <span id="birthPlace" class="uri">' + item.birthPlaceName.value + '</span></p>');
				} else if (item.birthPlace) {
					var name = dbpediauri2name(item.birthPlace.value);
					$('#authorbox').append('<p>Fødselssted: <span id="birthPlace" class="uri">' + name + '</span></p>');
				}
				if (item.deathDate) {
					var deathDate = new Date(item.deathDate.value);
					$('#authorbox').append('<p>Død: ' + deathDate.toLocaleDateString() + '</p>');
				}
				if (item.deathPlaceName) {
					$('#authorbox').append('<p>Dødssted: <span id="deathPlace">' + item.deathPlaceName.value + '</span></p>');
				} else if (item.deathPlace) {
					var name = dbpediauri2name(item.deathPlace.value);
					$('#authorbox').append('<p>Dødssted: <span id="deathPlace" class="uri">' + name + '</span></p>');
				}
				if (item.nationalityThumbnail) {
					$('#authorbox').append('<p id="nationality">Nasjonalitet:<br /> <img height="72" width="100" src="' + item.nationalityThumbnail.value + '" /></p>');
					if (item.nationalityLabel) {
						$('#nationality').append('<br />' + item.nationalityLabel.value);
					}
				}
				if (item.activeYearsStartYear) {
					var activeYearsStartYear = new Date(item.activeYearsStartYear.value);
					$('#authorbox').append('<p>Karriere start: ' + activeYearsStartYear.getFullYear() + '</p>');
				}
				if (item.activeYearsEndYear) {
					var activeYearsEndYear = new Date(item.activeYearsEndYear.value);
					$('#authorbox').append('<p>Karriere slutt: ' + activeYearsEndYear.getFullYear() + '</p>');
				}
			});
		}
 		// Turn URIs into names
 		// $('.uri').each(function(index) {
    	// 	setDbpediaName($(this).attr('id'), $(this).text());
		// });
 	});
 	
}

/*
Sometimes DBpedia only returns a URI for a place, not a name. 
This function chops off the first 28 cahracters from a URI and returns the reminder in place of a name. 
Example: 
http://dbpedia.org/resource/Lom -> Lom
*/
function dbpediauri2name(s) {
	return s.substring(28);
}

function setDbpediaName(id, uri){
	
	var url = 'index.php?what=dbpedianame&who=' + uri;
	var name = '';
	$.getJSON(url, function(json){
		if (json.results.bindings[0]){
			$.each(json.results.bindings, function(i, n) {
				var item = json.results.bindings[i];
				if (item.name) {
					name = item.name.value;
				} 
			});
		} else {
			// Return the URI if a name was not found
			// TODO: Guess the name from the URI?
			name = uri;	
		}
		$('#' + id).empty().append(name).removeClass('uri');
 	});

}

</script>
<style>
.work {
  cursor: pointer;	
}
.expression {
  cursor: pointer;	
}
.manifestation {
  cursor: default;	
}
.expanded {
  cursor: default;		
}
#authorbox {
  border: 1px solid black;
  padding: 1em;
  float: right;
}
</style>
</head>
<body>

<form>
<!-- Authors -->
<select name="author" id="authorselect" onChange="showWorks()">
 <option value="">Velg forfatter...</option>
 <option value="person:Hamsun_Knut">Knut Hamsun</option>
 <option value="person:Petterson_Per">Per Petterson</option>
</select>
<!-- Sorting - firstEdition is the default -->
<select name="sortby" id="sortby" onChange="showWorks()">
 <option value="firstEdition"">Sorter på...</option>
 <option value="firstEdition">Utgivelsesår</option>
 <option value="title">Tittel</option>
</select>
<!-- Sorting - ASC is the default -->
<select name="sortorder" id="sortorder" onChange="showWorks()">
 <option value="asc"">Velg sortering...</option>
 <option value="asc">Stigende</option>
 <option value="desc">Synkende</option>
</select>
<!-- Select language - this list is filled at runtime, based on the author selected -->
<select name="language_select" id="language_select" onChange="showWorks()">
 <option value="">Velg språk...</option>
</select>
</form>

<div id="authorbox">
</div>
<ul id="workslist">
</ul>

</body>
</html>

<?php } ?>
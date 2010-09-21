<?php

// A simple proxy for non-JSONP sources
if (!empty($_GET['what']) && !empty($_GET['who'])) {


	$url = '';
	if ($_GET['what'] == 'dbpedia') {
		// urlencode($_GET['who'])
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
}
*/
		$url = 'http://dbpedia.org/sparql?default-graph-uri=http://dbpedia.org&should-sponge=&query=SELECT+DISTINCT+*+WHERE+{%0D%0A%3C' . urlencode($_GET['who']) . '%3E+dbpedia-owl:thumbnail+%3Fthumbnail+.%0D%0AOPTIONAL+{+%3C' . urlencode($_GET['who']) . '%3E+dbpedia-owl:activeYearsEndYear+%3FactiveYearsEndYear+}+.+%0D%0AOPTIONAL++{+%3C' . urlencode($_GET['who']) . '%3E+dbpedia-owl:activeYearsStartYear+%3FactiveYearsStartYear+}+.+%0D%0AOPTIONAL++{+%3C' . urlencode($_GET['who']) . '%3E+dbpedia-owl:birthDate+%3FbirthDate+}+.+%0D%0AOPTIONAL++{+%3C' . urlencode($_GET['who']) . '%3E+dbpedia-owl:birthName+%3FbirthName+}+.+%0D%0AOPTIONAL++{+%3C' . urlencode($_GET['who']) . '%3E+dbpedia-owl:birthPlace+%3FbirthPlace+}+.+%0D%0AOPTIONAL++{+%3C' . urlencode($_GET['who']) . '%3E+dbpedia-owl:deathDate+%3FdeathDate+}+.+%0D%0AOPTIONAL++{+%3C' . urlencode($_GET['who']) . '%3E+dbpedia-owl:deathPlace+%3FdeathPlace+}+.+%0D%0A}&format=' . urlencode('application/sparql-results+json');
	
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

$(document).ready(function() {

});

function showWorks() {

	// Clear the list of works
	$('#workslist').empty();
	
	var author = '';
	if ($("#authorselect option:selected").val() != '') {
		author = $("#authorselect option:selected").val();
		showAuthor($("#authorselect option:selected").val());
	} else {
		return;
	}
	var sortby = 'firstEdition';
	if ($("#sortby option:selected").val() == 'title') {
		sortby = 'title';
	}
	var sortorder = 'ASC';
	if ($("#sortorder option:selected").val() == 'desc') {
		sortorder = 'DESC';
	}
	
/* Example: 
PREFIX pode: <http://www.bibpode.no/vocabulary#>
PREFIX dct: <http://purl.org/dc/terms/>
PREFIX frbr: <http://purl.org/vocab/frbr/core#>
PREFIX person: <http://www.bibpode.no/person/>

SELECT DISTINCT ?work ?firstEdition ?title WHERE {
?work a frbr:Work ;
pode:firstEdition ?firstEdition ;
dct:title ?title ;
dct:creator person:Petterson_Per .
OPTIONAL { ?host frbr:part ?work } .
FILTER (!bound(?host)) 
} ORDER BY ASC (?work)
*/
	
	var url = 'http://bibpode.no/rdfstore/endpoint.php?query=PREFIX+pode%3A+%3Chttp%3A%2F%2Fwww.bibpode.no%2Fvocabulary%23%3E%0D%0APREFIX+dct%3A+%3Chttp%3A%2F%2Fpurl.org%2Fdc%2Fterms%2F%3E%0D%0APREFIX+frbr%3A+%3Chttp%3A%2F%2Fpurl.org%2Fvocab%2Ffrbr%2Fcore%23%3E%0D%0APREFIX+person%3A+%3Chttp%3A%2F%2Fwww.bibpode.no%2Fperson%2F%3E%0D%0A%0D%0ASELECT+DISTINCT+%3Fwork+%3FfirstEdition+%3Ftitle+WHERE+{%0D%0A%3Fwork+a+frbr%3AWork+%3B%0D%0Apode%3AfirstEdition+%3FfirstEdition+%3B%0D%0Adct%3Atitle+%3Ftitle+%3B%0D%0Adct%3Acreator+' + author + '+.%0D%0AOPTIONAL+{+%3Fhost+frbr%3Apart+%3Fwork+}+.%0D%0AFILTER+%28!bound%28%3Fhost%29%29+%0D%0A}+ORDER+BY+' + sortorder + '+%28%3F' + sortby + '%29&jsonp=?';
	var params = { 'output': 'json' };

	$.getJSON(url, params, function(json, status) {
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

		var url='http://bibpode.no/rdfstore/endpoint.php?query=PREFIX+pode%3A+%3Chttp%3A%2F%2Fwww.bibpode.no%2Fvocabulary%23%3E%0D%0APREFIX+pode_lf%3A+%3Chttp%3A%2F%2Fwww.bibpode.no%2Flf%2F%3E+%0D%0APREFIX+dct%3A+%3Chttp%3A%2F%2Fpurl.org%2Fdc%2Fterms%2F%3E%0D%0APREFIX+frbr%3A+%3Chttp%3A%2F%2Fpurl.org%2Fvocab%2Ffrbr%2Fcore%23%3E%0D%0APREFIX+person%3A+%3Chttp%3A%2F%2Fwww.bibpode.no%2Fperson%2F%3E+%0D%0APREFIX+work%3A+%3Chttp%3A%2F%2Fwww.bibpode.no%2Fwork%2F%3E%0D%0A%0D%0ASELECT+DISTINCT+%3Flanguage+%3Fformat+WHERE+{%0D%0A%3Fexpression+a+frbr%3AExpression+.%0D%0A%3Fexpression+frbr%3ArealizationOf+%3C' + workuri + '%3E+.%0D%0A%3Fexpression+dct%3Alanguage+%3Flanguage+.%0D%0A%3Fexpression+dct%3Aformat+%3Fformat+.%0D%0A}%0D%0AORDER+BY+%3Flanguage&jsonp=?'
		
		var params = { 'output': 'json' };
	
		$.getJSON(url, params, function(json, status) {
			if (json.results.bindings){
				// alert(json.results.bindings[0].title.value);
				var out = '<ul>';
				$.each(json.results.bindings, function(i, n) {
					var item = json.results.bindings[i];
					var idattribute = elemid + 'expression' + i;
					out = out + '<li><span id="' + idattribute + '" class="expression" onClick="showManifestations(\'' + idattribute + '\', \'' + workuri + '\', \'' + item.language.value + '\', \'' + item.format.value + '\');">' + item.language.value + ' ' + item.format.value + '</span></li>';
				});
				var out = out + '</ul>';
				// Hide all "open" works
				$('.work').siblings().hide();
				// Insert the output after the span that was clicked
				$('#' + elemid).after(out);
				// Remove the onClick attribute
				// $('#' + elemid).removeAttr("onClick");
				// $('#' + elemid).addClass("expanded");
			} else {
				alert('Something went wrong...');	
			}
		});
	
	}	
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
				if (item.birthPlace) {
					$('#authorbox').append('<p>Fødselssted: <span id="birthPlace" class="uri">' + item.birthPlace.value + '</span></p>');
				}
				if (item.deathDate) {
					var deathDate = new Date(item.deathDate.value);
					$('#authorbox').append('<p>Død: ' + deathDate.toLocaleDateString() + '</p>');
				}
				if (item.deathPlace) {
					$('#authorbox').append('<p>Dødssted: <span id="deathPlace" class="uri">' + item.deathPlace.value + '</span></p>');
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
 		$('.uri').each(function(index) {
    		setDbpediaName($(this).attr('id'), $(this).text());
		});
 	});
 	
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
		$('#' + id).empty().append(name);
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
</form>

<div id="authorbox">
</div>
<ul id="workslist">
</ul>

</body>
</html>

<?php } ?>
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

var endpointprefix = 'http://bibpode.no/rdfstore/endpoint.php?query=';
var endpointpostfix = '&jsonp=?';

// var languages = new Array();

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
		language_sparql = language_sparql + 'SELECT DISTINCT ?language ?langlabel WHERE { \n';
		language_sparql = language_sparql + '?work a frbr:Work ; \n';
		language_sparql = language_sparql + 'dct:title ?title ; \n';
		language_sparql = language_sparql + 'dct:creator ' + author + ' . \n';
		language_sparql = language_sparql + '?expression frbr:realizationOf ?work . \n';
		language_sparql = language_sparql + '?expression dct:language ?language . \n';
		language_sparql = language_sparql + '?language rdfs:label ?langlabel . \n';
		language_sparql = language_sparql + 'FILTER langMatches( lang(?langlabel), "nb" ) \n';
		language_sparql = language_sparql + '} ORDER BY ?langlabel';
		
		/* Example: 
		PREFIX dct: <http://purl.org/dc/terms/> 
		PREFIX frbr: <http://purl.org/vocab/frbr/core#> 
		PREFIX person: <http://www.bibpode.no/person/> 
		SELECT DISTINCT ?language ?langlabel WHERE { 
			?work a frbr:Work ; 
			dct:title ?title ; 
			dct:creator person:Hamsun_Knut . 
			?expression frbr:realizationOf ?work . 
			?expression dct:language ?language . 
			?language rdfs:label ?langlabel . 
			FILTER langMatches( lang(?langlabel), "nb" ) 
		} ORDER BY ?langlabel
		*/	
		
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
					$('#language_select').append('<option value="' + item.language.value + '">' + item.langlabel.value + '</option>');
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
		
		// Hide the children if the clicked work already has visible children
		if ($('#' + elemid).siblings('ul').is(":visible")) {
			// alert('ouch');
			$('.work').siblings().hide();
		} else {
			// Hide all "open" works
			// alert('gjemmer alle');
			$('.work').siblings().hide();
			// Show this one
			// alert('viser 1');
			$('#' + elemid).siblings().show();
		}
		
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
		expr_sparql = expr_sparql + '?expression dct:format ?format . \n';
		expr_sparql = expr_sparql + '?format rdfs:label ?formatlabel . \n';
		if ($("#language_select option:selected").val() != '') {
			expr_sparql = expr_sparql + '?expression dct:language <' + $("#language_select option:selected").val() + '> . \n';
			expr_sparql = expr_sparql + '} \n';
		} else {
			expr_sparql = expr_sparql + '?expression dct:language ?language . \n';
			expr_sparql = expr_sparql + '?language rdfs:label ?langlabel . \n';
			expr_sparql = expr_sparql + 'FILTER langMatches( lang(?langlabel), "nb" ) \n';
			expr_sparql = expr_sparql + '} ORDER BY ?langlabel \n';
		}
		
		/* Example, language is chosen: 
		PREFIX pode: <http://www.bibpode.no/vocabulary#> 
		PREFIX pode_lf: <http://www.bibpode.no/lf/> 
		PREFIX dct: <http://purl.org/dc/terms/> 
		PREFIX frbr: <http://purl.org/vocab/frbr/core#> 
		PREFIX person: <http://www.bibpode.no/person/> 
		PREFIX work: <http://www.bibpode.no/work/> 
		SELECT DISTINCT * WHERE { 
		?expression a frbr:Expression . 
		?expression frbr:realizationOf <http://www.bibpode.no/work/Hamsun_Knut_pan> . 
		?expression dct:format ?format . 
		?format rdfs:label ?formatlabel . 
		?expression dct:language <http://lexvo.org/id/iso639-3/nob> . 
		} 
		
		* Example, language is not chosen: 
		PREFIX pode: <http://www.bibpode.no/vocabulary#> 
		PREFIX pode_lf: <http://www.bibpode.no/lf/> 
		PREFIX dct: <http://purl.org/dc/terms/> 
		PREFIX frbr: <http://purl.org/vocab/frbr/core#> 
		PREFIX person: <http://www.bibpode.no/person/> 
		PREFIX work: <http://www.bibpode.no/work/> 
		SELECT DISTINCT * WHERE { 
		?expression a frbr:Expression . 
		?expression frbr:realizationOf <http://www.bibpode.no/work/Hamsun_Knut_pan> . 
		?expression dct:language ?language . 
		?expression dct:format ?format . 
		?format rdfs:label ?formatlabel . 
		?language rdfs:label ?langlabel . 
		FILTER langMatches( lang(?langlabel), "nb" ) 
		} ORDER BY ?langlabel 
		*/
		
		var expr_url = endpointprefix + escape(expr_sparql) + endpointpostfix;
		var params = { 'output': 'json' };
	
		$.getJSON(expr_url, params, function(json, status) {
			if (json.results.bindings){
				var out = '<ul class="expressions">';
				var expressions = new Array();
				$.each(json.results.bindings, function(i, n) {
					var item = json.results.bindings[i];
					if (expressions[item.expression.value]) {
						// Add the formatlabel to the existing formatlabel
						expressions[item.expression.value].formatlabel.value = expressions[item.expression.value].formatlabel.value + ', ' + item.formatlabel.value;
						// If format has been sasved as http://www.bibpode.no/physicalFormat/di, replace it with a new URI
						if (expressions[item.expression.value].format.value && expressions[item.expression.value].format.value == 'http://www.bibpode.no/physicalFormat/di') {
							expressions[item.expression.value].format.value = item.format.value;
						}
					} else {
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
					} else {
						out = out + $("#language_select option:selected").val();
					}
					out = out + '\', \'' + item.format.value + '\');">';
					out = out + '<span class="format">' + item.formatlabel.value + '</span>';
					if (item.langlabel) {
						out = out + ' på ';
						out = out + '<span class="language">' + item.langlabel.value.toLowerCase() + '</span>';
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
				// $('.languageuri').each(function(index) {
				// 	// alert($(this).attr('id'));
			   	// 	setLanguageLabels($(this).attr('id'), $(this).text());
				// });			
				
			} else {
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
				var out = '<ul class="parts"><li>Deler:<ul>';
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

/*
function setLanguageLabels(id, uri){
	
	var url = 'proxy.php?label=nb&lexvo=' + uri;
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
*/

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
			var out = '<ul class="manifestations">';
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

			// Show Gutenberg editions
					   var guten_sparql = 'PREFIX frbr: <http://purl.org/vocab/frbr/core#> \n';
			guten_sparql = guten_sparql + 'PREFIX lexvo: <http://lexvo.org/id/iso639-3/> \n';
			guten_sparql = guten_sparql + 'PREFIX dct: <http://purl.org/dc/terms/> \n';
			guten_sparql = guten_sparql + 'SELECT DISTINCT ?manifestation WHERE { \n';
			guten_sparql = guten_sparql + '?expression a frbr:Expression . \n';
			guten_sparql = guten_sparql + '?expression frbr:realizationOf <' + workuri + '> . \n';
			guten_sparql = guten_sparql + '?expression frbr:embodiment ?manifestation . \n';
			guten_sparql = guten_sparql + '?expression dct:language <' + languageuri + '> . \n';
			guten_sparql = guten_sparql + 'FILTER(REGEX(?manifestation, "gutenberg")) \n';
			guten_sparql = guten_sparql + '} ';
	
			var guten_url = endpointprefix + escape(guten_sparql) + endpointpostfix;
			var params = { 'output': 'json' };
			
			$.getJSON(guten_url, params, function(json){
				// alert(json.label);
				if (json.results.bindings){
					var thiselemid = elemid + 'fulltext';
					$('#' + elemid).after('<ul id="' + thiselemid + '"></ul>'); 
					var name = '';
					$.each(json.results.bindings, function(i, n) {
						var item = json.results.bindings[i];
						
						var url = 'proxy.php?what=gutenberg&who=' + item.manifestation.value;
						$.getJSON(url, function(json){
							if (json.results.bindings){
								// alert(json.results.bindings[0].title.value);
								$.each(json.results.bindings, function(i, n) {
									var gutenitem = json.results.bindings[i];
									// alert(thiselemid + ' <li><a href="' + item.manifestation.value + '" target="_blank">' + gutenitem.title.value + '</a> fulltekst fra Gutenberg</li>');
									$('#' + thiselemid).append('<li><a href="' + item.manifestation.value + '" target="_blank">' + gutenitem.title.value + '</a> fulltekst fra Gutenberg</li>');
								});
							}
					 	});
						
					});
				}
		 	});

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
	$('#authorbox').show();
	$('.authorboxpart').empty();

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
	
	var url = 'proxy.php?what=dbpedia&who=' + dbpediaid;
	$.getJSON(url, function(json){
		if (json.results.bindings){
			// alert(json.results.bindings[0].title.value);
			$.each(json.results.bindings, function(i, n) {
				var item = json.results.bindings[i];
				$('#authorfacts').append('<p id="authorsname">' + $("#authorselect option:selected").text() + '</p>');
				if (item.thumbnail) {
					$('#authorfacts').append('<img src="' + item.thumbnail.value + '" title="Portrett fra Wikipedia" />');
				}
				$('#authorfacts').append('<ul id="authorfactlist"></ul>');
				if (item.birthName) {
					$('#authorfactlist').append('<li>Fødselsnavn: ' + item.birthName.value + '</li>');
				}
				if (item.birthDate) {
					var year  = item.birthDate.value.substring(0,4);
					var month = item.birthDate.value.substring(5,7);
					var day   = item.birthDate.value.substring(8,10);
					$('#authorfactlist').append('<li>Født: ' + day + '.' + month + '.' + year + '</li>');
				}
				if (item.birthPlaceName) {
					$('#authorfactlist').append('<li>Fødselssted: <span id="birthPlace" class="uri">' + item.birthPlaceName.value + '</span></li>');
				} else if (item.birthPlace) {
					var name = dbpediauri2name(item.birthPlace.value);
					$('#authorfactlist').append('<li>Fødselssted: <span id="birthPlace" class="uri">' + name + '</span></li>');
				}
				if (item.deathDate) {
					var year  = item.deathDate.value.substring(0,4);
					var month = item.deathDate.value.substring(5,7);
					var day   = item.deathDate.value.substring(8,10);
					$('#authorfactlist').append('<li>Død: ' + day + '.' + month + '.' + year + '</li>');
				}
				if (item.deathPlaceName) {
					$('#authorfactlist').append('<li>Dødssted: <span id="deathPlace">' + item.deathPlaceName.value + '</span></li>');
				} else if (item.deathPlace) {
					var name = dbpediauri2name(item.deathPlace.value);
					$('#authorfactlist').append('<li>Dødssted: <span id="deathPlace" class="uri">' + name + '</span></li>');
				}
				if (item.activeYearsStartYear) {
					$('#authorfactlist').append('<li>Karriere start: ' + item.activeYearsStartYear.value.substring(0,4) + '</li>');
				}
				if (item.activeYearsEndYear) {
					$('#authorfactlist').append('<li>Karriere slutt: ' + item.activeYearsEndYear.value.substring(0,4) + '</li>');
				}
				if (item.nationalityThumbnail) {
					$('#authorfacts').append('<p id="nationality">Nasjonalitet:<br /> <img height="72" width="100" src="' + item.nationalityThumbnail.value + '" /></p>');
					if (item.nationalityLabel) {
						$('#nationality').append('<br />' + item.nationalityLabel.value);
					}
				}
			});
		}
 		// Turn URIs into names
 		// $('.uri').each(function(index) {
    	// 	setDbpediaName($(this).attr('id'), $(this).text());
		// });
 	});

	var url = 'proxy.php?what=abstract&who=' + dbpediaid;
	$.getJSON(url, function(json){
		if (json.results.bindings[0]){
			$.each(json.results.bindings, function(i, n) {
				var item = json.results.bindings[i];
				if (item.abstract) {
					$('#authorabstract').append('<p id="abstract">' + item.abstract.value.substring(0, item.abstract.value.indexOf('.', 100)+1) + ' <a href="' + item.page.value + '" target="_blank">Les mer på Wikipedia</a></p>');
				}
			});
		}
	});

	var url = 'proxy.php?what=influenced&who=' + dbpediaid;
	$.getJSON(url, function(json){
		if (json.results.bindings[0]){
			$('#authorinfluenced').append('<p>Påvirket:</p><ul id="influenced"></ul>');
			$.each(json.results.bindings, function(i, n) {
				var item = json.results.bindings[i];
				if (item.author) {
					$('#influenced').append('<li>' + item.author.value + '</li>');
				}
			});
		}
	});
	
	var url = 'proxy.php?what=influences&who=' + dbpediaid;
	$.getJSON(url, function(json){
		if (json.results.bindings[0]){
			$('#authorinfluences').append('<p>Påvirket av:</p><ul id="influences"></ul>');
			$.each(json.results.bindings, function(i, n) {
				var item = json.results.bindings[i];
				if (item.author) {
					$('#influences').append('<li>' + item.author.value + '</li>');
				}
			});
		}
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
	
	var url = 'proxy.php?what=dbpedianame&who=' + uri;
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


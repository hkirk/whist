<!DOCTYPE html>
<html>
	<head>
		<link type="text/css" href="index.css" rel="stylesheet" media="all" />
		<script src="http://code.jquery.com/jquery-latest.js"></script>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<title>Whist calculator</title>
		<script type="text/javascript">
			var G = 1;
			var round = 0;
			// Id's are not used yet
			var extras = [
				{ id: 'none', name: null,   multiplier: 1   },
				{ id: 'vip',  name: 'vip',  multiplier: 1.5 }, 
				{ id: 'gode', name: 'gode', multiplier: 2   }
			];
			var solos = [
				{ id: 'sol',     name: 'Sol',            multiplier: 1, maxTricks: 1 },
				{ id: 'rensol',  name: 'Ren Sol',        multiplier: 2, maxTricks: 0 },
				{ id: 'bord',    name: 'Bordlægger',     multiplier: 4, maxTricks: 1 },
				{ id: 'renbord', name: 'Ren Bordl.', multiplier: 8, maxTricks: 0 }
			];
			var totalPoints = [ 0, 0, 0, 0 ];
			var lastPoints = [ null, null, null, null ];
			var isRoundCalculated = true; // pseudo round 0 is calculated to totalPoints

			function addRound() {
				if(!isRoundCalculated) {
					alert("Round is not calculated!");
					return;
				}
				round++;
				var html = "<tr id='round-" + round + "' class=\"" + round + "\">";
				html += "<th class='round'>" + round + "</th>";
				html += "<td class='bid'></td>";
				html += "<td class='tricks'></td>"
				for (var player=0; player<4; player++) {
					html += "<td class='result result-"+player+"'></td>"
					html += "<td class='total total-"+player+"'></td>";
				}
				html += "</tr>";
				$('tbody').append(html);
				// Reset controls
				$("#bid").val("");
				$("#tricks").val("");
				for(var p=0;p<4;p++) {
					$("#bidderteam-"+p).prop('checked',false);
				}
				lastPoints = totalPoints.slice(0); // clone
				isRoundCalculated = false;
			}
			
			function updateTotalPoints(currentPoints, row) {
				for(var p=0;p<4;p++) {
					var points = totalPoints[p] = lastPoints[p] + currentPoints[p];
					$('#total-'+p).text(points);
					row.find(".total-"+p).text(points);
				}
			}
			
			function populateBidSelect() {
				var select = getSelect("#bid");
				for (var i = 7; i <= 13; i++) {
					$(extras).each(function(index,extra) {
						var points = calculateNormal(i, i, extra);
						select.addOption(i + "-" + index, i + (extra.name ? " " + extra.name : "") + " ("+points+")");
					});
				}
				$(solos).each(function(index, solo) {
					var points = calculateSolo(0, solo);
					select.addOption('solo-'+index, solo.name + " ("+points+")");
				});
			}
			
			function populateTrickSelect() {
				var select = getSelect("#tricks");
				for (var i = 0; i <= 13; i++) {
					select.addOption(i, i);
				}
			}
			
			function getSelect(selector) {
				var select = $(selector);
				select.addOption = function(value, text) {
					createOption(value, text, this);
				}
				return select;
			}
			
			function createSelect(id) {
				var select = $("<select/>", {
					id: id
				});
				select.addOption = function(value, text) {
					createOption(value, text, this);
				}
				return select;
			}
			
			function createOption(value,text,select) {
				var option = $("<option/>", {
					value: value,
					text: text
				});
				select &&	select.append(option);
				return option;
			}
			
			/*
			 * 
			 */
			function calculatePoints() {
				if(round<1) {
					alert("No latest round!");
					return;
				}

				var bidSelect = $("#bid");
				var bid = bidSelect.val();
				var bidText = bidSelect.find(":selected").text();
				var tricksSelect = $("#tricks");
				var tricks = tricksSelect.val();
				var tricksText = tricksSelect.find(":selected").text();
				if(bid===''||tricks==='') {
					alert("Missing input!");
					return;
				}
				var temp = bid.split("-");
				var result = 0;
				var bidders = getBidderTeamPlayers();
				if(bidders.length===0) {
					alert("Missing bidder(s)!"); return;
				}
				var isSolo = temp[0]==='solo';
				
				var points = [null, null, null, null];
				// Note temp[0] and temp[1] are strings, but are coerced into numbers in the formula
				if (isSolo) { // sol
					result = calculateSolo(tricks, solos[temp[1]]);
				} else { // normal
					result = calculateNormal(temp[0], tricks, extras[temp[1]]);
				}				
				// Initialize all players points to non-bidders points
				for(var p=0;p<4;p++) {
					points[p] = result * -1;
				}
				
				if(isSolo) {
					switch(bidders.length) {
						case 1 : 
							// Set lonely bidder points
							points[bidders[0]] = result * 3;
							break;
						default: alert("We dont support more than one solo-player :("); return;
					}					
				} else { // normal
					switch(bidders.length) {
						case 1 : 
							// Set lonely bidder points
							points[bidders[0]] = result * 3;
							break;
						case 2 :
							// Set "normal" game bidders points
							points[bidders[0]] = points[bidders[1]] = result;
							break;
						default: alert("Too many bidders!"); return;
					}
				}
				var row = $("tr#round-"+round);
				for(var p=0;p<4;p++) {
					var td = row.find("td.result-"+ p);
					td.text(points[p]);
					var removeClass = points[p]<0.0?"positive":"negative";
					var addClass = points[p]<0.0?"negative":"positive";
					td.removeClass(removeClass).addClass(addClass);
					td.removeClass("bidderteam"); // default is not bidder team
				}
//				$(bidders).each(function(index,player) {
//					var td = row.find("td.result-"+ player);
//					td.addClass("bidderteam");
//				});
				row.find("td.bid").text(bidText);
				row.find("td.tricks").text(tricksText);
				updateTotalPoints(points, row);
				isRoundCalculated = true;
			}
			
			function getBidderTeamPlayers() {
				var bidders = [];
				for(var player=0; player<4; player++) {
					if($("#bidderteam-"+player).is(':checked')) {
						bidders.push(player);
					}
				}
				return bidders;
			}
			/*
			 * Sol:
					 S = soltakst = G * 2^(9-7) *1,5 = G * 6
					 t = soltype. Beskidt = 1, Ren =2, Oplægger =4, Ren oplægger = 8
					 v = væltet? Nej = 1, Ja = -2
            
					 Syg formel:
					 S * t * v  (*3...)
			 */
			function calculateSolo(tricks, type) {
				var S = G * 6;
				var looserMultiplier = tricks > type.maxTricks ? -2 : 1;
				return S * type.multiplier * looserMultiplier;
			}
			/*whist
			 * G = Grundtakst = 1
					 n = antal stik meldt [7-13] (7 = mindste melding)
					 m = antal stik fået [0-13]
					 p = påhæng? Intet = 1, Med påhæng = 2 (tæller vip med?)
					 b = bet? (m-n<0?). Nej = 1, Ja = 2
					 f = bet-forskydning. Bet? Nej = 1, Ja = 0
            
					 Den sygeste formel:
					 G * 2^(n-7) * p *  b * (m - n + f)
			 */
			function calculateNormal(bid, tricks, extra) {
				var looserMultiplier = bid > tricks ? 2 : 1;
				var looserDisplacement = bid > tricks ? 0 : 1;
				//alert("bid: "+bid+", tricks: "+tricks+", lm: "+looserMultiplier+", ld: "+looserDisplacement+", em: "+extra.multiplier);
				return G * Math.pow(2, bid-7) * extra.multiplier * looserMultiplier * (tricks-bid+looserDisplacement);
			}
			
			function init() {
				populateBidSelect();
				populateTrickSelect();
				addRound();
			}
			
			$(function() {
				init();			
			});
		</script>
	</head>
	<body>
		<h1>The funky Whist Calculator</h1>
		<table class="results">
			<thead>
				<tr>
					<th>#</th>
					<th>Melding</th>
					<th>Stik</th>
					<?php for ($player = 1; $player <= 4; $player++) { ?>
						<th colspan='2'>
							Player <?= $player ?> <br />
							<input type="text" size="5" name="player[<?= $player ?>]" />
						</th>
					<?php } ?>
				</tr>
			</thead>
			<tbody>
			</tbody>
			<tfoot>
				<tr id="controls">
					<td colspan="3">
						<select id="bid">
							<option value="">Melding</option>
						</select>
						/
						<select id="tricks">
							<option value="">Stik</option>
						</select>
					</td>
					<?php for ($p = 0; $p < 4; $p++) { ?>
						<td colspan="2">
							<input type="checkbox" id="bidderteam-<?= $p ?>" />
						</td>
					<?php } ?>
				</tr>
				<tr id="total">
					<th colspan="3">Total:</th>
					<?php for ($p = 0; $p < 4; $p++) { ?>
						<th colspan='2' id='total-<?= $p ?>'> </th>
					<?php } ?>
				</tr>				
			</tfoot>
		</table>
		<div id="buttons-container">
			<input type="button" value="Add round" onclick="addRound()"/>
			<input type="button" value="Calculate latest round" onclick="calculatePoints()" />
		</div>
	</body>
</html>

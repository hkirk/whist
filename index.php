<!DOCTYPE html>
<html>
	<head>
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
				{ id: 'renbord', name: 'Ren Bordlægger', multiplier: 8, maxTricks: 0 }
			];
			var totalPoints  = [ 0, 0, 0, 0 ];
			var lastPoints = null;

			function addRound() {
				round++;
				var html = "<tr class=\"" + round + "\">";
				html += "<td>" + round + "</td>";
				html += "<td>";
				html += createBidSelect(round);
				html += createTrickSelect(round);
				html += "</td>"
				for (var player=0; player<4; player++) {
					html += "<td>"
					html += "<input type='checkbox' id='bidderteam-" + round + "-" + player+"' />";
					html += "<br/>";
					html += "<input type=\"text\" id=\"result-" + round + "-" + player + "\" readonly='readonly' />";
					html += "</td>";
				}
				html += "</tr>";
				$('#round-results').append(html);
				updateTotalPoints();
			}
			
			// TODO: Find a better strategy for updating total points!
			function updateTotalPoints() {
				if(lastPoints) {
					for(var p=0;p<4;p++) {
						totalPoints[p] += lastPoints[p];
						$('#total-'+p).val(totalPoints[p]);
					}
				}
			}
			
			function createBidSelect(round) {
				var select = createSelect("bid-" + round);
				select.addOption('', 'Melding');
				for (var i = 7; i <= 13; i++) {
					$(extras).each(function(index,extra) {
						//var points = calculateNormal(i, i, extra);
						var points = 8;
						select.addOption(i + "-" + index, i + (extra.name ? " " + extra.name : "") + " ("+points+")");
					});
				}
				$(solos).each(function(index, solo) {
					var points = calculateSolo(0, solo);
					select.addOption('solo-'+index, solo.name + " ("+points+")");
				});
				return select.wrap("<div/>").parent().html();
			}
			
			function createTrickSelect(round) {
				var select = createSelect("tricks-" + round);
				select.addOption('', 'Stik');
				for (var i = 0; i <= 13; i++) {
					select.addOption(i, i);
				}
				return select.wrap("<div/>").parent().html();
			}
			
			function createSelect(id) {
				var select = $("<select/>");
				select.attr('id', id);
				select.addOption = function(value, text) {
					createOption(value, text, this);
				}
				return select;
			}
			
			function createOption(value,text,select) {
				var option = $("<option/>");
				option.attr('value', value);
				option.text(text);
				if(select) {
					select.append(option);
				}
				return option;
			}
			/*
			 * 
			 */
			function calculatePoints() {
				// save result

				var bid = $("#bid-" + round).val();
				var tricks = $("#tricks-" + round).val();
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
				for(var p=0;p<4;p++) {
					$("#result-" + round + "-" + p).val(points[p]);
				}
				lastPoints = points;
			}
			
			function getBidderTeamPlayers() {
				var bidders = [];
				for(var player=0; player<4; player++) {
					if($("#bidderteam-"+round+"-"+player).is(':checked')) {
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
		</script>
	</head>
	<body>
		<input type="button" value="Add round" onclick="addRound()"/>
		<table class="results">
			<thead>
				<tr>
					<th>#</th>
					<th>Melding</th>
					<?php
					for ($player = 1; $player <= 4; $player++) {
						echo "<th>";
						echo "Player $player</br/>";
						echo "<input type=\"text\" name=\"player[$player]\" />";
						echo "</th>";
					}
					?>
				</tr>
			</thead>
			<tbody id="round-results">
				
			</tbody>
			<tfoot>
					<th>#</th>
					<th>Total:</th>
					<?php
					for ($player = 0; $player < 4; $player++) {
						echo "<th>";
						echo "<input type='text' readonly='readonly' id='total-$player' />";
						echo "</th>";
					}
					?>
				</tr>				
			</tfoot>
		</table>
		<input type="button" value="Calculate latest round" onclick="calculatePoints()" />
	</body>
</html>

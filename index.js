$(function() {

	var G = 1;
	var round = 0;
	// Id's are not used yet
	var extras = [
	{
		id: 'none', 		
		name: null,   		
		multiplier: 1
	},
	{
		id: 'vip',  
		name: 'vip',  
		multiplier: 1.5
	}, 
	{
		id: 'gode', 
		name: 'gode', 
		multiplier: 2
	}
	];
	var solos = [
	{
		id: 'sol',     
		name: 'Sol',        
		multiplier: 1, 
		maxTricks: 1
	},
	{
		id: 'rensol',  
		name: 'Ren Sol',    
		multiplier: 2, 
		maxTricks: 0
	},
	{
		id: 'bord',    
		name: 'Bordl.',     
		multiplier: 4, 
		maxTricks: 1
	},
	{
		id: 'renbord', 
		name: 'Ren Bordl.', 
		multiplier: 8, 
		maxTricks: 0
	}
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
		var html = "<tr id='round-" + round + "'>";
		html += "<th class='round'>" + round + "</th>";
		html += "<td class='bid-tricks'></td>";
		//html += "<td class='tricks'></td>"
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
	
	function getNormalBidText(bidTricks, extraIndex) {
		var extra = extras[extraIndex];
		return bidTricks + (extra.name ? " " + extra.name : "")
	}
	
	function getSoloBidText(soloIndex) {
		var solo = solos[soloIndex];
		return solo.name;
	}
			
	function updateTotalPoints(currentPoints) {
		for(var p=0;p<4;p++) {
			var points = totalPoints[p] = lastPoints[p] + currentPoints[p];
			$('#total-'+p).text(points);
		}
	}
			
	function populateBidSelect() {
		var select;
		select = getOptionAdder("#bid-normal");
		for (var i = 7; i <= 13; i++) {
			$(extras).each(function(index,extra) {
				var points = calculateNormal(i, i, extra);
				var text = getNormalBidText(i, index);
				select.addOption(i + "-" + index, text + " ("+points+")");
			});
		}
		select = getOptionAdder("#bid-solo");
		$(solos).each(function(index, solo) {
			var points = calculateSolo(0, solo);
			var text = getSoloBidText(index);
			select.addOption('solo-'+index, text + " ("+points+")");
		});
	}
			
	function populateTrickSelect() {
		var select = getOptionAdder("#tricks");
		for (var i = 0; i <= 13; i++) {
			select.addOption(i, i);
		}
	}
			
	function getOptionAdder(selector) {
		var element = $(selector);
		element.addOption = function(value, text) {
			createOption(value, text, this);
		}
		return element;
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

		var bidTemp = $("#bid").val();
		var tricks = $("#tricks").val();
		if(bidTemp===''||tricks==='') {
			alert("Missing input!");
			return;
		}
		var splittedBidTemp = bidTemp.split("-");
		var bidTricksTemp = splittedBidTemp[0];
		var bidTricks = undefined;
		var extraOrSoloIndex = parseInt(splittedBidTemp[1]);
		var isSolo = bidTricksTemp==='solo';

		var bidders = getBidderTeamPlayers();
		if(bidders.length===0) {
			alert("Missing bidder(s)!");
			return;
		}
				
		var points = [null, null, null, null];
		var result = 0;
		if (isSolo) { // sol
			result = calculateSolo(tricks, solos[extraOrSoloIndex]);
		} else { // normal
			bidTricks = parseInt(bidTricksTemp);
			result = calculateNormal(bidTricks, tricks, extras[extraOrSoloIndex]);
		}				
		// Initialize all players points to opponent team points
		var opponentTeamPoints = result * -1;
		for(var p=0;p<4;p++) {
			points[p] = opponentTeamPoints;
		}
				
		if(isSolo) {
			switch(bidders.length) {
				case 1 :
					// Set lonely bidder points
					points[bidders[0]] = result * 3;
					break;
				default:
					alert("We don't support more than one solo-player :(");
					return;
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
				default:
					alert("Too many bidders!");
					return;
			}
		}
		updateTotalPoints(points, row);
		var row = $("tr#round-"+round);
		for(var p=0;p<4;p++) {
			var isOpponent = bidders.indexOf(p)<0;
			var td = row.find("td.result-"+p);
			td.text(points[p]);
			toggleClass(td, points[p]<0.0, "negative", "positive");
			toggleClass(td, isOpponent, "opponent-team", "bidder-team");
			td = row.find("td.total-"+p);
			td.text(totalPoints[p]);
			toggleClass(td, isOpponent, "opponent-team", "bidder-team");
		}
		var bidTricksText;
		if(isSolo) {
			bidTricksText = getSoloBidText(extraOrSoloIndex);
		} else {
			bidTricksText = getNormalBidText(bidTricks, extraOrSoloIndex);
		}
		row.find("td.bid-tricks").text(bidTricksText+" / "+tricks);
		isRoundCalculated = true;
	}
	
	function toggleClass(element, bool, trueClass, falseClass) {
		if(bool) {
			element.removeClass(falseClass).addClass(trueClass);
		} else {
			element.removeClass(trueClass).addClass(falseClass);
		}
	}
			
	function getBidderTeamPlayers() {
		var bidders = [];
		for(var player=0; player<4; player++) {
			if($("#bidderteam-"+player).prop('checked')) {
				bidders.push(player);
			}
		}
		return bidders;
	}

	function calculateSolo(tricks, type) {
		var S = G * 6;
		var looserMultiplier = tricks > type.maxTricks ? -2 : 1;
		return S * type.multiplier * looserMultiplier;
	}

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
		$("#addRound").click(addRound);
		$("#calculatePoints").click(calculatePoints);
	}
			
	init();
});

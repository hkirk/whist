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
	
	var domRefs = undefined;

	function addRound() {	
		if(!isRoundCalculated) {
			alert("Round is not calculated!");
			return;
		}
		round++;
		var html = "<tr id='round-" + round + "'>";
		html += "<th class='round'>" + round + "</th>";
		html += "<td class='bid-tricks'></td>";
		for (var player=0; player<4; player++) {
			html += "<td class='result result-"+player+"'></td>"
			html += "<td class='total total-"+player+"'></td>";
		}
		html += "</tr>";
		$('tbody').append(html);
		// Reset controls
		domRefs.bid.val("");
		domRefs.tricks.val("");
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
	
	function clearRoundPoints(row) {
		var p;
		var td;
		for(p=0;p<4;p++) {
			td = row.find("td.result-"+p+", td.total-"+p);
			td.text("");
			td.removeClass("negative positive opponent-team bidder-team");
		}
	}
	
	function resetRound() {
		var row = getRoundRow();
		updateTotalPoints([0,0,0,0]);
		clearRoundPoints(row);
		isRoundCalculated = false;
	}
	
	function inputChanged() {
		var p;
		if(getBidderTeamPlayers().length === 0 || domRefs.bid.val()==="" || domRefs.tricks.val()==="") {
			resetRound();
		} else {
			calculatePoints();
		}
	}
			
	function populateBidSelect() {
		var optgroup;
		var points;
		var text;
		optgroup = getOptionAdder("#bid-normal");
		for (var i = 7; i <= 13; i++) {
			$(extras).each(function(index,extra) {
				points = calculateNormal(i, i, extra);
				text = getNormalBidText(i, index);
				optgroup.addOption(i + "-" + index, text + " ("+points+")");
			});
		}
		optgroup = getOptionAdder("#bid-solo");
		$(solos).each(function(index, solo) {
			points = calculateSolo(0, solo);
			text = getSoloBidText(index);
			optgroup.addOption('solo-'+index, text + " ("+points+")");
		});
	}
			
	function populateTrickSelect() {
		var select = getOptionAdder(domRefs.tricks);
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
			
	function calculatePoints() {
		var p;
		if(round<1) {
			alert("No latest round!");
			return;
		}

		var bidTemp = domRefs.bid.val();
		var tricks = domRefs.tricks.val();
		if(bidTemp===''||tricks==='') {
			alert("Missing input!");
			resetRound();
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
			resetRound();
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
		for(p=0;p<4;p++) {
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
					resetRound();
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
					resetRound();
					return;
			}
		}
		updateTotalPoints(points);
		var row = getRoundRow();
		for(p=0;p<4;p++) {
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
	
	function getRoundRow() {
		return $("tr#round-"+round);
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
		for(var p=0; p<4; p++) {
			if(domRefs.bidderteam[p].prop('checked')) {
				bidders.push(p);
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
	
	function initDOMRefs() {
		domRefs = {
			bid: $("#bid"),
			tricks: $("#tricks"),
			bidderteam: (function() {
				var bidderteam = [];
				for(var p=0; p<4; p++) {
					bidderteam.push($("#bidderteam-"+p));
				}
				return bidderteam;
			})(),
			addRound: $("#addRound")
		};
	}
	
	function initHandlers() {
		domRefs.bid.change(inputChanged);
		domRefs.tricks.change(inputChanged);
		$.each(domRefs.bidderteam, function(index, member) {
			member.change(inputChanged);
		});
		domRefs.addRound.click(addRound);
	}
			
	function init() {
		initDOMRefs();
		initHandlers();
		populateBidSelect();
		populateTrickSelect();
		addRound();
	}
			
	init();
});

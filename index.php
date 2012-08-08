<!DOCTYPE html>
<html>
    <head>
        <script src="http://code.jquery.com/jquery-latest.js"></script>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>Whist calculator</title>
        <script type="text/javascript">
            var round = 0;
            var extra = ['vip', 'gode'];
            var sol = ['sol', 'rensol', 'bordlægger'];

            function addRound() {
                round++;
                var html = "<tr><td colspan=\"4\">Round " + round + "</td></tr>";
                html += "<tr class=\"" + round + "\">";
                for (var player = 1; player <= 4; player++) {
                    html += "<td>"
                    html += createBidSelect(round, player);
                    html += createTrickSelect(round, player);
                    html += "<br/>";
                    html += "<input type=\"text\" id=\"result-" + round + "-" + player + "\" />";
                    html += "</td>";
                }
                html += "</tr>";
                $('.results').append(html);
            }
            function createBidSelect(round, player) {
                var html = "<select id=\"bid-" + round + "-" + player + "\">";
                html += "<option>None</option>";
                for (var i = 7; i <= 13; i++) {
                    html += "<option>" + i + "</option>";
                    for (var j = 0; j < extra.length; j++) {
                        html += "<option>" + i + "-" + extra[j] + "</option>";
                    }
                }
                for (var i = 0; i < sol.length; i++) {
                    html += "<option>" + sol[i] + "</option>";
                }
                html += "</select>";
                return html;
            }
            function createTrickSelect(round, player) {
                var html = "<select id=\"trick-" + round + "-" + player + "\">";
                for (var i = 0; i <= 13; i++) {
                    html += "<option>" + i + "</option>";
                }
                html += "<select>";
                return html;
            }
            /*
             * 
             */
            function calculatePoints() {
                // save result
                for (var player = 1; player <= 4; player++) {
                    var bid = $("#bid-" + round + "-" + player).val();
                    var trick = $("#trick" + round + "-" + player);
                    var temp = bid.split("-");
                    var result = 0;
                    if (parseInt(temp[0]) == "NaN") { // sol
                        result = calculateSol(temp[0], trick);
                    } else { // normal
                        result = calculateNormal(temp[0], trick, temp[1]);
                    }
                    $("result-" + round + "-" + player).val(result);
                }
            }
            /*
             * Sol:
               S = soltakst = G * 2^(9-7) *1,5 = G * 6
               t = soltype. Beskidt = 1, Ren =2, Oplægger =4, Ren oplægger = 8
               v = væltet? Nej = 1, Ja = -2
            
               Syg formel:
               S * t * v  (*3...)
            */
            function calculateSol($bid, $type) {
                
            }
            /*
             * G = Grundtakst = 1
               n = antal stik meldt [7-13] (7 = mindste melding)
               m = antal stik fået [0-13]
               p = påhæng? Intet = 1, Med påhæng = 2 (tæller vip med?)
               b = bet? (m-n<0?). Nej = 1, Ja = 2
               f = bet-forskydning. Bet? Nej = 1, Ja = 0
            
               Den sygeste formel:
               G * 2^(n-7) * p *  b * (m - n + f)
             */
            function calculateNormal(bid, tricks, type) {
                
            }
        </script>
    </head>
    <body>
        <input type="button" value="Add round" onclick="addRound()"/>
        <table class="results">
            <tr>
            <?php
            for ($player = 1; $player <= 4; $player++) {
                echo "<td>";
                echo "Player $player</br/>";
                echo "<input type=\"text\" name=\"player[$player]\" />";
                echo "</td>";
            }
            ?>
            </tr>
        </table>
        <input type="button" value="Calculate latest round" onclick="calculatePoints()" />
    </body>
</html>

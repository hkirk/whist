
<script src="http://code.highcharts.com/highcharts.js"></script>
<script src="http://code.highcharts.com/modules/exporting.js"></script>

<div id="container" style="min-width: 310px; height: 500px; margin: 0 auto"></div>

<script type="text/javascript">
$(function () {
        $('#container').highcharts({
            title: {
                text: 'Points progression',
                x: -20 //center
            },
            xAxis: {
              title: {
                    text: 'Round #'
              }
            },
            yAxis: {
                title: {
                    text: 'Points'
                },
                plotLines: [{
                    value: 0,
                    width: 1,
                    color: '#808080'
                }]
            },
            legend: {
                layout: 'vertical',
                align: 'right',
                verticalAlign: 'middle',
                borderWidth: 0
            },
            series: [
            <?php foreach ($players as $player): ?>
              {
                name: '<?php echo htmlspecialchars($player['nickname']); ?>',
                data: [ <?php echo implode(",", $cumulative[$player['id']]); ?> ]
              },
            <?php endforeach ?>
            ]
        });
    });
</script>

<table>
<thead>
  <tr>
    <th>#round</th>
    <?php foreach($players as $player): ?>
    <th><?php echo $player['nickname'] ?></th>
    <?php endforeach ?>
  </tr>
</thead>

<tbody>
  <?php foreach($rounds as $round) : ?>
  <tr>
    <td><?php echo $round['round'] ?></td>
    <?php foreach($players as $player):
      $id = $player['id'];  
    ?>
    <td>
      <?php 
        if (array_key_exists($id, $round['points'])) {
          echo $round['points'][$id];
        } else {
          echo '-';
        }      
      ?>
    </td>
    <?php endforeach ?>
  </tr>
  <?php endforeach ?>
  <tr>
    <td><b>Sum</b></td>
      <?php foreach($players as $player):
        $id = $player['id'];  
      ?>
      <td><b><?php echo $sum[$id]; ?></b></td>
      <?php endforeach ?>
  </td>
</tbody>

</table>

</pre>

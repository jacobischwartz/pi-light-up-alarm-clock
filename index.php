<?php

$days = array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday');
$hour_options = array(5, 6, 7, 8, 9);
$minute_options = array(0, 5, 10, 15, 20, 25, 30, 35, 40, 45, 50, 55);
$config = array();

if(!empty($_POST)) {
  foreach($days as $day) { $machine = strtolower($day);
    $config[$machine] = array(
        'active' => empty($_POST[$machine . '-active']) ? FALSE : TRUE,
        'hour' => intval($_POST[$machine . '-hour']),
        'minute' => intval($_POST[$machine . '-minute'])
    );
  }
  $dest_folder = realpath('./');
  $filepath = $dest_folder . '/config.json';
  $handle = fopen( $filepath, "w");
  if(FALSE === $handle) throw new Exception('Could not open config file here: ' . $filepath . '. Could be a permissions issue.');
  $timestamp = time();
  $wrote = fwrite($handle, json_encode($config));
  if(FALSE === $wrote) throw new Exception('Could not write to config file here: ' . $filepath . '. Could be a permissions issue.');
  fclose($handle);
  chmod($filepath, 0750);
}

if(empty($config) && file_exists('config.json')) {
  $config = json_decode(file_get_contents('config.json'), TRUE);
}

if(empty($config)) {
  foreach ($days as $day) {
    $config[ strtolower($day) ] = array(
        'active' => FALSE,
        'hour'   => 9,
        'minute' => 0
    );
  }
}

?>
<!DOCTYPE html>
<html>
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Alarm Clock Settings</title>
  <style>
    h3 {
      margin: 1em 0 0.5em;
    }
    select {
      display: inline-block;
    }
  </style>
</head>
<body>

  <h1>Alarm Clock Settings</h1>

  <form action="index.php" method="post">

    <?php foreach($days as $day) { $machine = strtolower($day); ?>
      <div class="day">
        <h3><label><input type="checkbox" name="<?php echo $machine; ?>-active" <?php if(!empty($config[$machine]['active'])) echo 'checked="checked"' ?> /> <?php echo $day; ?></label></h3>
        <div class="hidable">
          <select name="<?php echo $machine; ?>-hour">
            <?php foreach($hour_options as $hour) { ?>
              <option <?php if($hour === intval($config[$machine]['hour'])) echo 'selected="selected"' ?>><?php echo $hour; ?></option>
            <?php } ?>
          </select>
          <select name="<?php echo $machine; ?>-minute">
            <?php foreach($minute_options as $minute) { ?>
              <option value="<?php echo $minute; ?>" <?php if($minute === intval($config[$machine]['minute'])) echo 'selected="selected"'; ?>><?php if($minute < 10) echo '0'; echo $minute; ?></option>
            <?php } ?>
          </select>am
        </div>
      </div>
    <?php } ?>

    <p>
      <input type="submit" value="Save" />
    </p>

  </form>
  <script>
    function accordionDay(el) {
      if('undefined' !== typeof(el.currentTarget)) el = this;
      var isActive = el.querySelectorAll('input[type="checkbox"]')[0].checked;
      el.querySelectorAll('.hidable')[0].style.display = isActive ? '' : 'none';
    }
    var allDays = document.getElementsByClassName('day');
    Array.prototype.forEach.call(allDays, function (el, i) {
      el.addEventListener('change', accordionDay);
      accordionDay(el);
    });
  </script>
</body>
</html>
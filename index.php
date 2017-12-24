<?php

require_once 'functions.php';

$days = array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday');
$hour_options = array(5, 6, 7, 8, 9, 10, 11);
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
  try {
    write_config($config);
    write_status(array());
  } catch(Exception $e) {
    die('Error saving configuration! ' . $e->getMessage());
  }
  $saved = TRUE;
}

$stored_config = read_config();
if(empty($config) && (NULL !== $stored_config)) $config = $stored_config;

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
  <link rel="apple-touch-icon" href="icon.png" />
  <link rel="icon" href="icon.png" />
  <link rel="shortcut icon" href="icon.png" />
  <title>Wake Up Settings</title>
  <style>
    body {
      background: #403F4C;
      color: #F9DC5C;
      font-family: sans-serif;
    }
    h3 {
      margin: 1em 0 0.5em;
      font-weight: 300;
    }
    select {
      display: inline-block;
    }
    input[type="checkbox"] {
      vertical-align: bottom;
    }
    input[type="submit"] {
      border: solid 1px transparent;
      background: #F9DC5C;
      color: #403F4C;
      font-weight: bold;
      font-size: larger;
      padding: 5px 10px;
      margin-top: 10px;
    }
  </style>
</head>
<body>

  <h1>Wake Up Settings</h1>

  <?php if(isset($saved)) { ?>
    <h3><i>Settings saved</i></h3>
  <?php } ?>

  <h3>It is <?php echo date('g:ia \o\n l'); ?> and I love you</h3>

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
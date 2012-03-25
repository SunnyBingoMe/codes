<?php
error_reporting(E_ALL);

session_start();

define('SELF', 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['PHP_SELF']);

if (array_key_exists('name', $_REQUEST)
  && array_key_exists('passwd', $_REQUEST)
  && 'admin' == $_REQUEST['name']
  && 'secret' == $_REQUEST['passwd']) {
  $_SESSION['name'] = 'admin';
  header('Location: '.SELF);
}

if (array_key_exists('clear', $_REQUEST)) {
  unset($_SESSION['name']);
}

if (array_key_exists('name', $_SESSION)
  && $_SESSION['name']) { ?>
  <html>
  <body>Welcome <?=$_SESSION['name']?>
  <br>Super secret member only content here.
  <a href="<?php echo SELF; ?>?clear">Logout</a>
  </body>
  </html> <?php
} else { ?>
  <html>
  <body>
  <form method="post">
  Name:<input type="text" name="name">
  Password:<input type="password" name="passwd">
  <input type="submit" value="Login">
  </form>
  </body>
  </html> <?php
}

<?php
  function url_for($script_path) {
    // add the leading '/' if not present
    if($script_path[0] !== '/') {
      $script_path = "/" . $script_path;
    }
    return WWW_ROOT . $script_path;
  }

  function error_404() {
    header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
    exit();
  }

  function error_500() {
    header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error');
    exit();
  }

  function redirect_to($location) {
    header("Location: " . $location);
    exit();
  }

  function request_is_post() {
    return $_SERVER['REQUEST_METHOD'] == 'POST';
  }

  function request_is_get() {
    return $_SERVER['REQUEST_METHOD'] == 'GET';
  }

  function display_alert($type, $title, $msg) {
    if($type == 'primary') {
      echo '<div class="alert alert-' . $type . '"><strong>' . $title . '</strong> ' . $msg . '</div>';
    } elseif ($type == 'warning') {
      echo '<div class="alert alert-' . $type . '"><strong>' . $title . '</strong> ' . $msg . '</div>';
    } elseif ($type == 'danger') {
      echo '<div class="alert alert-' . $type . '"><strong>' . $title . '</strong> ' . $msg . '</div>';
    } else {
      echo '<div class="alert alert-warning"><strong>ALERT!</strong> BAD VARS IN display_alert()!</div>';
    }
  }
?>

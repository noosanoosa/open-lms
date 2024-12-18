<?php
  function find_all_admins() {
    global $db;

    $sql = "SELECT * FROM admins";
    $result = mysqli_query($db, $sql);
    return $result;
  }

  function find_all_responses() {
    global $db;

    $sql = "SELECT * FROM responses";
    $result2 = mysqli_query($db, $sql);
    return $result2;
  }

  function add_admin($usrh, $pwdh) {
    global $db;

    $sql = "INSERT INTO admins (username, password, insession) VALUES ('" . $usrh . "', '" . $pwdh . "', '0')";

    if (mysqli_query($db, $sql)) {
        return TRUE;
    } else {
        echo "Error: " . $sql . "<br>" . mysqli_error($db);
    }
  }

  function upd_admin($usr, $newpwd) {
    global $db;

    $sql = "UPDATE admins SET password='" . $newpwd . "' WHERE username='" . $usr . "'";
    if(mysqli_query($db, $sql)) {
      return TRUE;
    } else {
      return FALSE;
    }
  }

  function start_session($usr) {
    global $db;

    $sql = "UPDATE admins SET insession='1' WHERE username='" . $usr . "' ";
    mysqli_query($db, $sql);
  }

  function end_session($usr) {
    global $db;

    $sql = "UPDATE admins SET insession='0' WHERE username='" . $usr . "' ";
    mysqli_query($db, $sql);
  }
?>

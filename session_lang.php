<?php
if (!isset($_SESSION["lang"])) {
    $_SESSION["lang"] = "TH";
}

if ($_SESSION["lang"] == "EN") {
    include("en.php");
} else {
    include("th.php");
}

?>
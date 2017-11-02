<?php
    chdir("../Public");
    require "settings.php";

    /*  This file can be customised if need be. The default behaviour of the built-in session file clean up is to remove all sessions over 1 day old.
    Pass a greater time value (in seconds) to the default function to extend the clean up time.
    */
    cleanupOldSessions();
?>
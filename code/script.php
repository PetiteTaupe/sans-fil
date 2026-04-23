<?php
$stderr = fopen('php://stderr', 'w');
fwrite($stderr, file_get_contents('php://input') . "\n");
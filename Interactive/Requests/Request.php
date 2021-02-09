<?php

$rawData = json_decode(file_get_contents("php://input"), true);

$rawData['class'] && require __DIR__."/../../Interactive/Classes/{$rawData['class']}.php";

$class = new $rawData['class'];

// Deployment
echo json_encode($class->renderJSON($rawData['variables']));
// Debug
// echo $class->renderJSON($rawData['variables'])['html'];

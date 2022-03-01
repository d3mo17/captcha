<?php

include '../Code.php';
include '../HTMLGenerator.php';

$code = new \DMo\Captcha\Code();
$html = (new \DMo\Captcha\HTMLGenerator($code))->setInputType();

if (!empty($_POST["sent"])) {
    if (!$code->validate($html->restoreCode())) {
        $errorMsg = $code->getLastError()->getMessage();
    }
}

?>

<style>
    input {
        display: block;
        width: 200px;
    }
</style>

<h3>Example Form</h3>
<?php
    if (!empty($errorMsg)) {
        echo sprintf('<p>%s</p>', $errorMsg);
    }
?>
<form method="post">
    <p>Name: <input type="text" name="name" /></p>
    <button name="sent" value="1">Send</button>
    <p>
    <?=$html->get()?>
    </p>
</form>

<?php
/** @var array<string, list<string>> $alerts */

foreach ($alerts as $key => $messages):
    foreach ($messages as $message):
        ?>
        <div class="alert <?php echo s($key); ?>">
            <?php echo s($message); ?>
        </div>
        <?php
    endforeach;
endforeach;
?>
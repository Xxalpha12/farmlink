<?php
function flash_set($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function flash_render() {
    if (!empty($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        $class = $f['type'] === 'error' ? 'alert alert-error' : 'alert alert-success';
        echo '<div class="' . $class . '">' . h($f['message']) . '</div>';
    }
}

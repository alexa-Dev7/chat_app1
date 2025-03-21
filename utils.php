<?php

function loadUsers() {
    $file = 'persistent_data/users.json';
    return file_exists($file) ? json_decode(file_get_contents($file), true) : [];
}

function saveUsers($users) {
    file_put_contents('persistent_data/users.json', json_encode($users, JSON_PRETTY_PRINT));
}

function loadMessages() {
    $file = 'persistent_data/messages.json';
    return file_exists($file) ? json_decode(file_get_contents($file), true) : [];
}

function saveMessages($messages) {
    file_put_contents('persistent_data/messages.json', json_encode($messages, JSON_PRETTY_PRINT));
}

function loadSessions() {
    $file = 'persistent_data/sessions.json';
    return file_exists($file) ? json_decode(file_get_contents($file), true) : [];
}

function saveSessions($sessions) {
    file_put_contents('persistent_data/sessions.json', json_encode($sessions, JSON_PRETTY_PRINT));
}
